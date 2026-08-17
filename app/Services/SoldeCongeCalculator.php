<?php

namespace App\Services;

use App\Models\RegleConge;
use App\Models\SoldeConge;
use App\Models\User;
use Carbon\Carbon;

/**
 * Calcule les NOUVEAUX droits acquis pour le cycle de congé en cours
 * (majoration ancienneté + prorata mois d'arrivée).
 *
 * ⚠️ Portée strictement prospective : cette classe ne lit/écrit jamais que la
 * ligne soldes_conges de l'ANNÉE EN COURS (création ou mise à jour de
 * jours_acquis suite à un changement de date_embauche/ancienneté). Elle ne
 * doit jamais recalculer ni modifier une ligne d'une année antérieure
 * (déjà acquise/affichée/validée).
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
                $moisAuto = (int) $dateEmbauche->diffInMonths($reference) % 12;
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
     * d'embauche — accrual PROGRESSIF pour tous les salariés : le solde ne
     * monte que mois après mois, jamais d'un coup en début d'année.
     *
     * - Année $annee déjà terminée (< année de référence) : les 12 mois
     *   comptent (année figée, plus rien à accumuler).
     * - Année $annee future : 0 mois.
     * - Année $annee = année de référence (année en cours) : voir le détail
     *   des trois cas ci-dessous.
     *
     * Cas "année en cours" :
     * - Embauche PENDANT l'année en cours : présence au plus tard le 15 du
     *   mois = mois compté entier, sinon le mois d'arrivée n'est pas compté
     *   (on démarre au mois suivant) ; on compte jusqu'au mois courant inclus.
     * - Embauche une année antérieure, quelle que soit l'ancienneté : le
     *   cycle d'acquisition est ANCRÉ SUR LE MOIS D'EMBAUCHE, pas sur
     *   l'année civile. Le compteur repart à 0 à chaque anniversaire
     *   d'embauche (cycle glissant de 12 mois), pas au 1er janvier — on
     *   prend donc le nombre de mois pleins écoulés depuis l'embauche,
     *   modulo 12.
     */
    public static function moisTravailles(Carbon $dateEmbauche, int $annee, ?Carbon $reference = null): int
    {
        $reference ??= Carbon::now();

        if ($dateEmbauche->year > $annee) {
            return 0;
        }

        if ($annee > $reference->year) {
            return 0;
        }

        if ($annee < $reference->year) {
            return 12;
        }

        if ($dateEmbauche->year === $annee) {
            $premierMoisCompte = $dateEmbauche->day <= 15
                ? $dateEmbauche->month
                : $dateEmbauche->month + 1;

            if ($premierMoisCompte > $reference->month) {
                return 0;
            }

            return $reference->month - $premierMoisCompte + 1;
        }

        $moisDepuisEmbauche = (int) $dateEmbauche->diffInMonths($reference);

        return $moisDepuisEmbauche % 12;
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

        // Ancienneté DANS L'ENTREPRISE uniquement (Code du travail, art. 158 :
        // majoration après "vingt ans de service continus ou non DANS LA
        // MÊME ENTREPRISE"). La reprise d'ancienneté saisie par les RH pour
        // une expérience acquise ailleurs (anciennete_reprise_ans/mois)
        // n'entre PAS dans ce calcul — elle reste purement informative
        // (affichage, cf. User::ancienneteTexte).
        $anneesAnciennete = $dateEmbauche
            ? self::anneesAnciennete($dateEmbauche, Carbon::now())
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

    /**
     * Détecte si le salarié vient de franchir un ou plusieurs anniversaires
     * d'embauche depuis le dernier calcul (cycle de 12 mois clos). Si oui :
     * reporte le solde restant du cycle qui vient de se clore dans
     * jours_reportes (plafonné à regles.limite_report si report_autorise,
     * sinon 0), puis remet jours_pris à 0 pour repartir sur un compteur
     * propre au nouveau cycle. N'écrit rien si $persist=false (dry-run).
     *
     * Idempotent : ne fait rien si aucun nouveau cycle n'a démarré depuis le
     * dernier appel (comparaison à users.cycles_conges_reportes).
     *
     * Cas "bootstrap" : la toute première fois que cette fonction s'exécute
     * pour un utilisateur (cycles_conges_reportes encore NULL en base), il
     * est impossible de reconstituer les cycles déjà passés (aucun solde
     * n'a jamais été figé par cycle avant cette fonctionnalité) : on
     * initialise juste le compteur sur le cycle actuel, sans report
     * rétroactif.
     */
    public static function appliquerReportSiNouveauCycle(
        User $user,
        SoldeConge $solde,
        Carbon $dateEmbauche,
        ?RegleConge $regles = null,
        ?Carbon $reference = null,
        bool $persist = true
    ): array {
        $regles ??= RegleConge::first();
        $reference ??= Carbon::now();

        $cycleActuel      = intdiv((int) $dateEmbauche->diffInMonths($reference), 12);
        $dernierCycleConnu = $user->cycles_conges_reportes;

        if ($dernierCycleConnu === null) {
            if ($persist) {
                $user->cycles_conges_reportes = $cycleActuel;
                $user->save();
            }

            return ['nouveau_cycle' => false, 'bootstrap' => true];
        }

        if ($cycleActuel <= $dernierCycleConnu) {
            return ['nouveau_cycle' => false, 'bootstrap' => false];
        }

        $soldeRestantAvant = max(0, (float) $solde->jours_acquis + (float) ($solde->jours_reportes ?? 0) - (float) $solde->jours_pris);

        $peutReporter = $regles && $regles->report_autorise;
        $plafond      = $regles && $regles->limite_report !== null ? (float) $regles->limite_report : null;

        $nouveauReport = $peutReporter
            ? ($plafond !== null ? min($soldeRestantAvant, $plafond) : $soldeRestantAvant)
            : 0.0;

        $solde->jours_reportes = round($nouveauReport, 2);
        $solde->jours_pris     = 0;

        if ($persist) {
            $user->cycles_conges_reportes = $cycleActuel;
            $user->save();
        }

        return [
            'nouveau_cycle'       => true,
            'bootstrap'           => false,
            'solde_restant_avant' => round($soldeRestantAvant, 2),
            'jours_reportes'      => $solde->jours_reportes,
        ];
    }

    /**
     * Synchronise automatiquement le solde de l'ANNÉE EN COURS d'un salarié
     * avec sa date d'embauche / ancienneté actuelles (crée la ligne si elle
     * n'existe pas encore, la met à jour sinon). Aucune autre année n'est
     * jamais touchée.
     *
     * Avant de recalculer jours_acquis, vérifie si un nouveau cycle
     * d'ancienneté vient de démarrer (anniversaire d'embauche franchi) :
     * si oui, reporte le solde restant du cycle clos dans jours_reportes et
     * remet jours_pris à 0 — voir appliquerReportSiNouveauCycle().
     *
     * À appeler chaque fois que date_embauche ou l'ancienneté "reprise" d'un
     * salarié changent (ex. création/modification de fiche salarié), pour
     * que le solde reflète automatiquement le mois d'embauche sans action
     * manuelle (commande artisan) supplémentaire.
     */
    public static function synchroniserSoldeAnneeCourante(User $user, ?int $annee = null, ?RegleConge $regles = null): SoldeConge
    {
        $annee ??= Carbon::now()->year;
        $regles ??= RegleConge::first();

        $solde = SoldeConge::firstOrNew([
            'user_id' => $user->id,
            'annee'   => $annee,
        ]);

        $joursPris = $solde->exists ? (float) $solde->jours_pris : 0;

        $solde->jours_pris     = $joursPris;
        $solde->jours_reportes = $solde->jours_reportes ?? 0;

        if ($user->date_embauche) {
            self::appliquerReportSiNouveauCycle($user, $solde, Carbon::parse($user->date_embauche), $regles);
        }

        $calcul = self::calculerNouveauxDroitsAcquis($user, $annee, $regles);
        $solde->jours_acquis = $calcul['jours_acquis']; // saving() du modèle recalcule jours_restants
        $solde->save();

        return $solde;
    }
}
