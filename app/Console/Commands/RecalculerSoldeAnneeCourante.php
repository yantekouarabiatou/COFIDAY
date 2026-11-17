<?php

namespace App\Console\Commands;

use App\Models\RegleConge;
use App\Models\SoldeConge;
use App\Services\SoldeCongeCalculator;
use Illuminate\Console\Command;

/**
 * Met à jour jours_acquis (et jours_restants) des lignes soldes_conges DÉJÀ
 * EXISTANTES pour l'année en cours, avec la nouvelle logique (majoration
 * ancienneté + prorata mois d'arrivée + plafond légal). jours_pris n'est
 * jamais modifié.
 *
 * Complément de SoldesAnneeCouranteSeeder (qui ne crée que les lignes
 * manquantes) : à relancer chaque fois que des date_embauche / reprises
 * d'ancienneté sont complétées sur des salariés qui ont déjà un solde de
 * l'année en cours.
 *
 * Ne touche JAMAIS une ligne d'une autre année (historique 2022-2025 intact).
 */
class RecalculerSoldeAnneeCourante extends Command
{
    protected $signature = 'conges:recalculer-solde-annee-courante {--dry-run : Affiche les changements sans écrire en base}';

    protected $description = "Recalcule jours_acquis des soldes de l'année en cours (majoration ancienneté + prorata), sans toucher aux années antérieures ni à jours_pris";

    public function handle(): int
    {
        $annee   = now()->year;
        $regles  = RegleConge::first();
        $dryRun  = (bool) $this->option('dry-run');

        $soldes = SoldeConge::with('user')->where('annee', $annee)->get();

        if ($soldes->isEmpty()) {
            $this->info("Aucun solde {$annee} existant à recalculer.");
            return self::SUCCESS;
        }

        $modifies = 0;

        foreach ($soldes as $solde) {
            $user = $solde->user;
            if (!$user) {
                continue;
            }

            $ancienJoursAcquis   = (float) $solde->jours_acquis;
            $ancienJoursReportes = (float) $solde->jours_reportes;

            $calcul         = SoldeCongeCalculator::calculerNouveauxDroitsAcquis($user, $annee, $regles);
            $nouveauxAcquis = $calcul['jours_acquis'];

            if (abs($nouveauxAcquis - $ancienJoursAcquis) < 0.005) {
                continue; // déjà à jour
            }

            $nouveauxRestants = max(0, $nouveauxAcquis + (float) $solde->jours_reportes - (float) $solde->jours_pris);

            $this->line(sprintf(
                '  %s %s (%d) : %.2f → %.2f j. acquis (ancienneté %d an(s), +%d j. majoration, %d/12 mois)  reportés %.2f → %.2f, restants %.2f',
                $dryRun ? '👁' : '✔',
                $user->email,
                $annee,
                $ancienJoursAcquis,
                $nouveauxAcquis,
                $calcul['annees_anciennete'],
                $calcul['majoration_anciennete'],
                $calcul['mois_travailles'],
                $ancienJoursReportes,
                (float) $solde->jours_reportes,
                $nouveauxRestants,
            ));

            if (!$dryRun) {
                $solde->jours_acquis = $nouveauxAcquis; // jours_pris/jours_reportes déjà positionnés ci-dessus
                $solde->save(); // saving() du modèle recalcule jours_restants
            }

            $modifies++;
        }

        if ($modifies === 0) {
            $this->info("Tous les soldes {$annee} sont déjà à jour.");
            return self::SUCCESS;
        }

        $this->info(
            $dryRun
                ? "{$modifies} solde(s) {$annee} seraient mis à jour (--dry-run, rien n'a été écrit)."
                : "{$modifies} solde(s) {$annee} mis à jour. Années antérieures et jours_pris non modifiés."
        );

        return self::SUCCESS;
    }
}
