<?php

namespace App\Services;

use App\Models\RegleConge;
use Carbon\Carbon;

/**
 * Source unique de vérité pour les jours fériés et le calcul des jours ouvrés.
 *
 * Les jours fériés sont stockés dans regles_conges.jours_feries sous forme de
 * liste d'entrées : ['nom' => ..., 'date' => 'MM-JJ', 'annee' => null|int]
 *   - annee = null  → férié récurrent, chaque année (ex. 01-01 Nouvel An, 12-25 Noël)
 *   - annee = 2027  → férié ponctuel, uniquement cette année-là
 *                     (ex. Vodun days 07-08/01/2027, Tabaski, Maouloud, Pentecôte…)
 *
 * Les jours fériés ne sont jamais décomptés du solde de congés.
 */
class JoursFeriesService
{
    /** Fériés fixes garantis chaque année (règle de gestion). */
    public const FERIES_FIXES_OBLIGATOIRES = [
        '01-01' => 'Nouvel An',
        '12-25' => 'Noël',
    ];

    /** Mots-clés des fériés dont la date change chaque année (à saisir par année). */
    public const MOTS_CLES_FERIES_MOBILES = [
        'vodun', 'pentec', 'paques', 'pâques', 'ascension',
        'tabaski', 'maouloud', 'mawlid', 'ramadan', 'korit', 'aid', 'aïd',
    ];

    private static ?array $cache = null;

    // =========================================================================
    //  Lecture / normalisation
    // =========================================================================

    /**
     * Décode la valeur brute stockée (parfois doublement encodée en JSON).
     */
    public static function decoder($valeur): array
    {
        $i = 0;
        while (is_string($valeur) && $i++ < 3) {
            $valeur = json_decode($valeur, true);
        }

        if (!is_array($valeur)) {
            return [];
        }

        return self::normaliser($valeur);
    }

    /**
     * Normalise une liste d'entrées : date MM-JJ, annee int|null, dédoublonnage.
     */
    public static function normaliser(array $entrees): array
    {
        $resultat = [];

        foreach ($entrees as $e) {
            if (!is_array($e) || empty($e['date'])) {
                continue;
            }

            $date  = trim((string) $e['date']);
            $annee = isset($e['annee']) && $e['annee'] !== '' ? (int) $e['annee'] : null;

            // Accepte aussi une date complète AAAA-MM-JJ → férié ponctuel
            if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $m)) {
                $annee = (int) $m[1];
                $date  = $m[2] . '-' . $m[3];
            }

            if (!preg_match('/^(\d{2})-(\d{2})$/', $date, $m) || !checkdate((int) $m[1], (int) $m[2], $annee ?? 2028)) {
                continue;
            }

            $cle = $date . '|' . ($annee ?? '*');
            $resultat[$cle] = [
                'nom'   => trim((string) ($e['nom'] ?? 'Jour férié')) ?: 'Jour férié',
                'date'  => $date,
                'annee' => $annee,
            ];
        }

        // Tri : récurrents d'abord, puis par année, puis par date
        uasort($resultat, fn($a, $b) => [$a['annee'] ?? 0, $a['date']] <=> [$b['annee'] ?? 0, $b['date']]);

        return array_values($resultat);
    }

    /**
     * Liste normalisée de tous les jours fériés configurés.
     */
    public static function liste(): array
    {
        if (self::$cache === null) {
            $regles = RegleConge::first();
            self::$cache = $regles ? self::decoder($regles->getRawOriginal('jours_feries')) : [];
        }

        return self::$cache;
    }

    public static function viderCache(): void
    {
        self::$cache = null;
    }

    /**
     * Enregistre la liste (même format de stockage que l'écran d'administration).
     */
    public static function sauvegarder(array $entrees): void
    {
        $regles = RegleConge::getRegles();
        $regles->jours_feries = json_encode(self::normaliser($entrees), JSON_UNESCAPED_UNICODE);
        $regles->save();
        self::viderCache();
    }

    // =========================================================================
    //  Tests sur une date
    // =========================================================================

    /**
     * Retourne le nom du férié si la date est fériée, sinon null.
     */
    public static function nomFerie($date): ?string
    {
        $date = Carbon::parse($date);
        $md   = $date->format('m-d');
        $an   = (int) $date->year;

        foreach (self::liste() as $f) {
            if ($f['date'] === $md && ($f['annee'] === null || $f['annee'] === $an)) {
                return $f['nom'];
            }
        }

        return null;
    }

    public static function estFerie($date): bool
    {
        return self::nomFerie($date) !== null;
    }

    public static function estJourOuvrable($date): bool
    {
        $date = Carbon::parse($date);

        return !$date->isWeekend() && !self::estFerie($date);
    }

    /**
     * Nombre de jours ouvrés (hors week-ends et jours fériés) entre deux dates incluses.
     */
    public static function calculerJoursOuvres($debut, $fin): int
    {
        $current = Carbon::parse($debut)->startOfDay();
        $fin     = Carbon::parse($fin)->startOfDay();
        $jours   = 0;

        while ($current->lte($fin)) {
            if (self::estJourOuvrable($current)) {
                $jours++;
            }
            $current->addDay();
        }

        return $jours;
    }

    /**
     * Fériés effectifs d'une année : ['AAAA-MM-JJ' => nom].
     */
    public static function feriesDeLAnnee(int $annee): array
    {
        $dates = [];
        foreach (self::liste() as $f) {
            if ($f['annee'] === null || $f['annee'] === $annee) {
                $dates[$annee . '-' . $f['date']] = $f['nom'];
            }
        }
        ksort($dates);

        return $dates;
    }

    // =========================================================================
    //  Génération automatique
    // =========================================================================

    /**
     * Fériés calculables automatiquement pour une année (fixes obligatoires +
     * Lundi de Pentecôte, calculé à partir de la date de Pâques).
     */
    public static function feriesCalculables(int $annee): array
    {
        $paques = Carbon::create($annee, 3, 21)->addDays(easter_days($annee));

        return [
            ['nom' => 'Lundi de Pentecôte', 'date' => $paques->copy()->addDays(50)->format('m-d'), 'annee' => $annee],
        ];
    }

    /**
     * Ajoute les fériés manquants (fixes obligatoires + calculables) pour les années données.
     * Retourne la liste des entrées ajoutées.
     */
    public static function completerAnnees(array $annees, bool $simulation = false): array
    {
        $liste   = self::liste();
        $ajoutes = [];

        // 1er janvier et 25 décembre : récurrents, chaque année
        foreach (self::FERIES_FIXES_OBLIGATOIRES as $md => $nom) {
            $existe = collect($liste)->contains(fn($f) => $f['date'] === $md && $f['annee'] === null);
            if (!$existe) {
                $ajoutes[] = ['nom' => $nom, 'date' => $md, 'annee' => null];
            }
        }

        foreach ($annees as $annee) {
            foreach (self::feriesCalculables((int) $annee) as $calc) {
                $existe = collect($liste)->contains(function ($f) use ($calc) {
                    if ($f['annee'] !== $calc['annee'] && $f['annee'] !== null) {
                        return false;
                    }
                    return $f['date'] === $calc['date']
                        || ($f['annee'] === $calc['annee'] && str_contains(mb_strtolower($f['nom']), 'pentec'));
                });
                if (!$existe) {
                    $ajoutes[] = $calc;
                }
            }
        }

        if (!empty($ajoutes) && !$simulation) {
            self::sauvegarder(array_merge($liste, $ajoutes));
        }

        return $ajoutes;
    }

    /**
     * Indique si une année possède au moins un férié mobile saisi (Vodun, Tabaski…).
     */
    public static function anneeAFeriesMobilesSaisis(int $annee): bool
    {
        return collect(self::liste())->contains(function ($f) use ($annee) {
            if ($f['annee'] !== $annee) {
                return false;
            }
            $nom = mb_strtolower($f['nom']);
            if (str_contains($nom, 'pentec')) {
                return false; // calculé automatiquement, ne compte pas
            }
            foreach (self::MOTS_CLES_FERIES_MOBILES as $mc) {
                if (str_contains($nom, $mc)) {
                    return true;
                }
            }
            return false;
        });
    }
}
