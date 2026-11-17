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
     *
     * Code du travail, art. 158 : "vingt ans de service continus ou non, dans
     * la même entreprise ou non" → l'ancienneté prise en compte est TOTALE
     * (service dans l'entreprise + reprise d'ancienneté saisie par les RH
     * pour une expérience acquise ailleurs), pas seulement "maison".
     * Voir calculerNouveauxDroitsAcquis() / ancienneteTotaleDetail().
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
     * aux nouveaux droits du cycle en cours  jamais rétroactivement.
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
     * l'affichage  jamais la seule ancienneté "maison".
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
     * d'embauche  accrual PROGRESSIF, cycle ANCRÉ SUR L'ANNÉE CIVILE (1er
     * janvier → 31 décembre) pour tous les salariés, quelle que soit leur
     * ancienneté. Le solde ne monte que mois après mois ENTIÈREMENT TERMINÉ
     * (jamais le mois en cours, pas encore fini), et repart à 0 chaque 1er
     * janvier (le reliquat éventuel de l'année précédente est reporté
     * séparément, voir calculerReportDepuisAnneePrecedente()).
     *
     * - Salarié déjà présent avant $annee (embauché une année antérieure) :
     *   dernier mois compté = décembre si $annee est une année déjà
     *   terminée, sinon le dernier mois ENTIÈREMENT ÉCOULÉ de l'année en
     *   cours (mois en cours exclu tant qu'il n'est pas terminé). Premier
     *   mois compté = janvier (présent dès le début de l'année).
     * - Salarié embauché PENDANT $annee : présence au plus tard le 15 du
     *   mois = mois compté (une fois terminé), sinon le mois d'arrivée
     *   n'est pas compté (on démarre au mois suivant) ; dernier mois compté
     *   = idem ci-dessus (mois en cours toujours exclu tant qu'il n'est pas
     *   terminé).
     * - Salarié pas encore embauché ($annee avant l'embauche) ou année
     *   future : 0 mois.
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

        $dernierMoisCompte = $annee < $reference->year ? 12 : $reference->month - 1;

        $premierMoisCompte = $dateEmbauche->year < $annee
            ? 1
            : ($dateEmbauche->day <= 15 ? $dateEmbauche->month : $dateEmbauche->month + 1);

        if ($premierMoisCompte > $dernierMoisCompte) {
            return 0;
        }

        return $dernierMoisCompte - $premierMoisCompte + 1;
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

        // Ancienneté TOTALE (service dans l'entreprise + reprise d'ancienneté
        // saisie par les RH pour une expérience acquise ailleurs) : les deux
        // comptent désormais pour déterminer le palier de majoration
        // (20/25/30 ans, Code du travail art. 158). Voir ancienneteTotaleDetail().
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

    /**
     * Calcule le report de l'année $anneePrecedente = $nouvelleAnnee - 1 vers
     * $nouvelleAnnee, à appliquer UNE SEULE FOIS au moment où la ligne
     * soldes_conges de $nouvelleAnnee est créée pour la première fois
     * (réinitialisation au 1er janvier  le compteur n'est plus ancré sur le
     * mois d'embauche mais sur l'année civile, pour tous les salariés).
     *
     * Si le salarié a été embauché exactement l'année précédente, sa ligne
     * de cette année-là peut ne jamais avoir été calculée au prorata (ex. :
     * import Excel historique figé à une valeur générique) : on la
     * finalise ici avec le calcul prorata mois d'arrivée → décembre, une
     * bonne fois pour toutes, avant de lire son solde restant.
     *
     * Retourne 0 si : pas de ligne pour l'année précédente (aucun historique
     * à reporter), ou report non autorisé dans les règles de congés.
     * Sinon, retourne le solde restant de l'année précédente, plafonné à
     * regles.limite_report si celle-ci est définie.
     */
    public static function calculerReportDepuisAnneePrecedente(User $user, int $nouvelleAnnee, ?RegleConge $regles = null): float
    {
        $regles ??= RegleConge::first();
        $anneePrecedente = $nouvelleAnnee - 1;

        $soldePrecedent = SoldeConge::where('user_id', $user->id)
            ->where('annee', $anneePrecedente)
            ->first();

        if (!$soldePrecedent) {
            return 0.0;
        }

        if ($user->date_embauche && Carbon::parse($user->date_embauche)->year === $anneePrecedente) {
            $calculHistorique = self::calculerNouveauxDroitsAcquis($user, $anneePrecedente, $regles);
            if ($calculHistorique['jours_acquis'] > (float) $soldePrecedent->jours_acquis) {
                $soldePrecedent->jours_acquis = $calculHistorique['jours_acquis'];
                $soldePrecedent->save(); // saving() du modèle recalcule jours_restants
            }
        }

        if (!$regles || !$regles->report_autorise) {
            return 0.0;
        }

        $soldeRestant = max(0, (float) $soldePrecedent->jours_acquis + (float) $soldePrecedent->jours_reportes - (float) $soldePrecedent->jours_pris);
        $plafond      = $regles->limite_report !== null ? (float) $regles->limite_report : null;

        return round($plafond !== null ? min($soldeRestant, $plafond) : $soldeRestant, 2);
    }

    /**
     * Synchronise automatiquement le solde de l'ANNÉE EN COURS d'un salarié
     * avec sa date d'embauche / ancienneté actuelles (crée la ligne si elle
     * n'existe pas encore, la met à jour sinon). N'écrit jamais une ligne
     * d'une autre année, sauf la finalisation ponctuelle de l'année
     * précédente lors du calcul du report (voir calculerReportDepuisAnneePrecedente()).
     *
     * jours_pris et jours_reportes ne sont fixés qu'à la CRÉATION de la
     * ligne (jours_pris = 0, jours_reportes = report de l'année précédente).
     * Sur une ligne déjà existante, ils ne sont jamais modifiés ici  seul
     * jours_acquis est recalculé.
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

        if (!$solde->exists) {
            $solde->jours_pris     = 0;
            $solde->jours_reportes = self::calculerReportDepuisAnneePrecedente($user, $annee, $regles);
        }

        $calcul = self::calculerNouveauxDroitsAcquis($user, $annee, $regles);
        $solde->jours_acquis = $calcul['jours_acquis']; // saving() du modèle recalcule jours_restants
        $solde->save();

        return $solde;
    }
}
