<?php

namespace App\Services;

use App\Models\RegleConge;
use App\Models\User;
use Carbon\Carbon;

/**
 * Calcule les NOUVEAUX droits acquis pour le cycle de congé en cours
 * (majoration ancienneté + prorata mois d'arrivée).
 *
 * ⚠️ Portée strictement prospective : cette classe ne doit être appelée que
 * lors de la CRÉATION de la ligne soldes_conges de l'année en cours (quand
 * elle n'existe pas encore). Elle ne doit jamais recalculer ni modifier une
 * ligne déjà existante (années antérieures déjà acquises/affichées).
 */
class SoldeCongeCalculator
{
    /**
     * Paliers légaux de majoration pour ancienneté (jours ouvrables).
     * Clé = ancienneté minimale en années complètes, valeur = jours de majoration.
     */
    private const SENIORITY_TIERS = [
        30 => 6,
        25 => 4,
        20 => 2,
    ];

    /**
     * Interprétation des paliers :
     * - false (défaut) : "palier unique" → seul le palier le plus élevé atteint s'applique (max 6 j.).
     * - true            : "paliers cumulatifs" → on additionne tous les paliers atteints (max 2+4+6=12 j.).
     *
     * Le texte légal fourni est ambigu sur ce point. Défaut = palier unique,
     * conformément à la demande. Basculer sur true si l'interprétation cumulative
     * est confirmée.
     */
    private const SENIORITY_TIERS_CUMULATIVE = false;

    /**
     * Plafond légal : congé principal + majoration ne peut jamais dépasser
     * ce nombre de jours ouvrables pour 12 mois de service.
     */
    private const PLAFOND_JOURS_OUVRABLES = 30;

    /**
     * Ancienneté en années complètes à une date de référence donnée.
     */
    public static function anneesAnciennete(Carbon $dateEmbauche, ?Carbon $reference = null): int
    {
        $reference ??= Carbon::now();

        if ($dateEmbauche->greaterThan($reference)) {
            return 0;
        }

        return (int) $dateEmbauche->diffInYears($reference);
    }

    /**
     * Majoration pour ancienneté (jours ouvrables), à appliquer uniquement
     * aux nouveaux droits du cycle en cours — jamais rétroactivement.
     */
    public static function majorationAnciennete(int $anneesAnciennete): int
    {
        if (self::SENIORITY_TIERS_CUMULATIVE) {
            $total = 0;
            foreach (self::SENIORITY_TIERS as $seuil => $jours) {
                if ($anneesAnciennete >= $seuil) {
                    $total += $jours;
                }
            }
            return $total;
        }

        foreach (self::SENIORITY_TIERS as $seuil => $jours) {
            if ($anneesAnciennete >= $seuil) {
                return $jours;
            }
        }

        return 0;
    }

    /**
     * Ancienneté TOTALE d'un salarié (années + mois), en combinant :
     *  - l'ancienneté calculée automatiquement depuis date_embauche (temps
     *    passé dans l'entreprise),
     *  - la "reprise d'ancienneté" saisie manuellement par les RH
     *    (anciennete_reprise_ans / anciennete_reprise_mois), qui reconnaît une
     *    expérience professionnelle antérieure dans d'autres entreprises.
     *
     * C'est ce total qui sert de référence pour la majoration légale et pour
     * l'affichage — jamais la seule ancienneté "maison".
     */
    public static function ancienneteTotaleDetail(User $user, ?Carbon $reference = null): array
    {
        $reference ??= Carbon::now();

        $ansAuto  = 0;
        $moisAuto = 0;

        if ($user->date_embauche) {
            $dateEmbauche = Carbon::parse($user->date_embauche);
            if (!$dateEmbauche->greaterThan($reference)) {
                $ansAuto  = self::anneesAnciennete($dateEmbauche, $reference);
                $moisAuto = $dateEmbauche->diffInMonths($reference) % 12;
            }
        }

        $repriseAns  = (int) ($user->anciennete_reprise_ans ?? 0);
        $repriseMois = (int) ($user->anciennete_reprise_mois ?? 0);

        $totalMois = $moisAuto + $repriseMois;

        return [
            'ans'  => $ansAuto + $repriseAns + intdiv($totalMois, 12),
            'mois' => $totalMois % 12,
        ];
    }

    /**
     * Nombre de mois "travaillés" pour l'année $annee, en fonction de la date
     * d'embauche : un salarié déjà présent les années précédentes est réputé
     * avoir travaillé les 12 mois du cycle. Un salarié arrivé en cours
     * d'année $annee ne compte que les mois à partir de son entrée, avec la
     * règle : présence au plus tard le 15 du mois = mois compté entier,
     * sinon le mois d'arrivée n'est pas compté (on démarre au mois suivant).
     */
    public static function moisTravailles(Carbon $dateEmbauche, int $annee): int
    {
        if ($dateEmbauche->year < $annee) {
            return 12;
        }

        if ($dateEmbauche->year > $annee) {
            return 0;
        }

        $premierMoisCompte = $dateEmbauche->day <= 15
            ? $dateEmbauche->month
            : $dateEmbauche->month + 1;

        if ($premierMoisCompte > 12) {
            return 0;
        }

        return 12 - $premierMoisCompte + 1;
    }

    /**
     * Calcule les nouveaux jours acquis pour l'année $annee d'un salarié,
     * en intégrant majoration ancienneté + prorata mois d'arrivée + plafond légal.
     *
     * Retourne le détail du calcul (utile pour affichage/tests), jamais un
     * solde cumulé : c'est à l'appelant d'ajouter ce delta à la ligne
     * soldes_conges de l'année en cours au moment de sa création.
     */
    public static function calculerNouveauxDroitsAcquis(User $user, int $annee, ?RegleConge $regles = null): array
    {
        $regles ??= RegleConge::first();
        $baseAnnuelle = $regles ? (float) $regles->jours_par_mois * 12 : 24.0;

        $dateEmbauche = $user->date_embauche ? Carbon::parse($user->date_embauche) : null;

        // Ancienneté TOTALE (temps dans l'entreprise + reprise d'ancienneté
        // saisie par les RH pour l'expérience acquise ailleurs) : c'est elle
        // qui détermine le palier de majoration légale, jamais la seule
        // ancienneté "maison".
        $anneesAnciennete = $dateEmbauche
            ? self::ancienneteTotaleDetail($user, Carbon::now())['ans']
            : 0;
        $majoration = $dateEmbauche ? self::majorationAnciennete($anneesAnciennete) : 0;

        $droitAnnuelAvantPlafond = $baseAnnuelle + $majoration;
        $droitAnnuelPlafonne = min($droitAnnuelAvantPlafond, self::PLAFOND_JOURS_OUVRABLES);
        $plafonne = $droitAnnuelAvantPlafond > self::PLAFOND_JOURS_OUVRABLES;

        $moisTravailles = $dateEmbauche ? self::moisTravailles($dateEmbauche, $annee) : 12;

        $joursAcquis = round(($droitAnnuelPlafonne / 12) * $moisTravailles, 2);

        return [
            'annee'                    => $annee,
            'base_annuelle'            => $baseAnnuelle,
            'annees_anciennete'        => $anneesAnciennete,
            'majoration_anciennete'    => $majoration,
            'droit_annuel_plafonne'    => $droitAnnuelPlafonne,
            'plafond_applique'         => $plafonne,
            'mois_travailles'          => $moisTravailles,
            'jours_acquis'             => $joursAcquis,
        ];
    }
}
