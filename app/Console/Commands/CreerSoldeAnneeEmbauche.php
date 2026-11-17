<?php

namespace App\Console\Commands;

use App\Models\RegleConge;
use App\Models\SoldeConge;
use App\Models\User;
use App\Services\SoldeCongeCalculator;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Seeder ADDITIF UNIQUEMENT — sûr à exécuter sur une base déjà en production :
 * il ne fait jamais d'UPDATE, seulement des INSERT.
 *
 * Crée la ligne soldes_conges de l'ANNÉE D'EMBAUCHE (prorata mois d'arrivée)
 * pour les salariés embauchés une année déjà terminée mais qui n'ont ENCORE
 * AUCUNE ligne pour cette année-là (ex. : salarié recruté en 2025, jamais
 * couvert par l'import Excel historique ni par un recalcul manuel).
 *
 * jours_pris est initialisé à la somme des demandes_conges réellement
 * approuvées cette année-là (0 si aucune) — jamais une valeur inventée.
 *
 * Ne touche JAMAIS une ligne soldes_conges déjà existante, ni l'année en
 * cours (voir SoldesAnneeCouranteSeeder pour celle-ci). Rejouable à volonté :
 * sans effet sur les salariés déjà traités.
 */
class CreerSoldeAnneeEmbauche extends Command
{
    protected $signature = 'conges:creer-solde-annee-embauche {--dry-run : Affiche les créations sans écrire en base} {--annee= : Ne traiter que les salariés embauchés cette année-là (ex. --annee=2025)}';

    protected $description = "Crée la ligne soldes_conges manquante de l'année d'embauche (prorata) pour les salariés recrutés une année déjà terminée, sans jamais toucher aux lignes existantes";

    public function handle(): int
    {
        $regles = RegleConge::first();
        $dryRun = (bool) $this->option('dry-run');
        $anneeFiltre = $this->option('annee') !== null ? (int) $this->option('annee') : null;
        $annee_actuelle = now()->year;

        $users = User::whereNotNull('date_embauche')->get();

        $crees = 0;

        foreach ($users as $user) {
            $dateEmbauche = Carbon::parse($user->date_embauche);
            $hireYear = $dateEmbauche->year;

            if ($hireYear >= $annee_actuelle) {
                continue; // année en cours ou future : géré ailleurs / pas encore pertinent
            }

            if ($anneeFiltre !== null && $hireYear !== $anneeFiltre) {
                continue; // hors du filtre --annee demandé
            }

            $existe = SoldeConge::where('user_id', $user->id)->where('annee', $hireYear)->exists();
            if ($existe) {
                continue; // ligne déjà présente (import Excel ou création antérieure) : on n'y touche pas
            }

            $calcul = SoldeCongeCalculator::calculerNouveauxDroitsAcquis($user, $hireYear, $regles);

            $joursPrisReels = \App\Models\DemandeConge::where('user_id', $user->id)
                ->where('statut', 'approuve')
                ->whereYear('date_debut', $hireYear)
                ->sum('nombre_jours');

            $this->line(sprintf(
                '  %s %s (%d) : %.2f j. acquis (ancienneté %d an(s), +%d j. majoration, %d/12 mois) - %.2f j. pris réels',
                $dryRun ? '👁' : '✔',
                $user->email,
                $hireYear,
                $calcul['jours_acquis'],
                $calcul['annees_anciennete'],
                $calcul['majoration_anciennete'],
                $calcul['mois_travailles'],
                $joursPrisReels,
            ));

            if (!$dryRun) {
                SoldeConge::create([
                    'user_id'        => $user->id,
                    'annee'          => $hireYear,
                    'jours_acquis'   => $calcul['jours_acquis'],
                    'jours_pris'     => $joursPrisReels,
                    'jours_restants' => $calcul['jours_acquis'],
                    'jours_reportes' => 0,
                ]);
            }

            $crees++;
        }

        if ($crees === 0) {
            $this->info('Aucune ligne à créer : tous les salariés ont déjà un solde pour leur année d\'embauche (ou ont été embauchés cette année).');
            return self::SUCCESS;
        }

        $this->info(
            $dryRun
                ? "{$crees} ligne(s) seraient créées (--dry-run, rien n'a été écrit)."
                : "{$crees} ligne(s) de solde créées. Aucune ligne existante n'a été modifiée."
        );

        return self::SUCCESS;
    }
}
