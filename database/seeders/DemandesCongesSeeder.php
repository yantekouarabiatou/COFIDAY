<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\DemandeConge;
use App\Models\TypeConge;
use App\Models\SoldeConge;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DemandesCongesSeeder extends Seeder
{
    public function run(): void
    {
        // Vider les tables en développement
        if (app()->environment('local')) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('historiques_conges')->truncate();
            DB::table('demandes_conges')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        $typesConges = TypeConge::all();
        $typeCongePaye = $typesConges->where('est_paye', true)->first();
        if (!$typeCongePaye) {
            $this->command->error('Aucun type de congé payé trouvé. Veuillez d\'abord exécuter TypeCongeSeeder.');
            return;
        }

        // Récupérer tous les utilisateurs ayant des soldes (on va générer les demandes à partir des soldes)
        $users = User::all();

        foreach ($users as $user) {
            // Récupérer tous les soldes de cet utilisateur
            $soldes = SoldeConge::where('user_id', $user->id)
                ->whereIn('annee', [2022, 2023, 2024, 2025])
                ->get();

            foreach ($soldes as $solde) {
                $annee = $solde->annee;
                $joursPris = $solde->jours_pris;

                if ($joursPris <= 0) {
                    continue; // Pas de congés pris cette année
                }

                // On va répartir les jours pris en 1 à 3 demandes
                $nombreDemandes = rand(1, 3);
                $joursRestantARepartir = $joursPris;

                for ($i = 0; $i < $nombreDemandes; $i++) {
                    // Si c'est la dernière demande, on prend tout le reste
                    if ($i == $nombreDemandes - 1) {
                        $joursDemande = $joursRestantARepartir;
                    } else {
                        // Sinon, on prend une partie aléatoire
                        $max = min($joursRestantARepartir - ($nombreDemandes - $i - 1), $joursRestantARepartir);
                        $joursDemande = rand(1, $max);
                    }

                    if ($joursDemande <= 0) break;

                    $joursRestantARepartir -= $joursDemande;

                    // Générer des dates dans l'année concernée
                    $dateDebut = $this->genererDateAleatoire($annee);
                    $dateFin = $this->calculerDateFin($dateDebut, $joursDemande);

                    // Créer la demande
                    $demande = DemandeConge::create([
                        'user_id' => $user->id,
                        'type_conge_id' => $typeCongePaye->id,
                        'date_debut' => $dateDebut,
                        'date_fin' => $dateFin,
                        'nombre_jours' => $joursDemande,
                        'motif' => 'Congés annuels',
                        'statut' => 'approuve', // On considère que les congés passés ont été approuvés
                        'valide_par' => User::role('admin')->inRandomOrder()->first()?->id ?? 1,
                        'date_validation' => $dateDebut->copy()->subDays(rand(1, 15)),
                        'created_at' => $dateDebut->copy()->subDays(rand(10, 30)),
                        'updated_at' => now(),
                    ]);

                    // Créer l'historique
                    $this->creerHistorique($demande);
                }
            }
        }

        $this->command->info('✅ Demandes de congés générées à partir des soldes réels.');
    }

    private function genererDateAleatoire(int $annee): Carbon
    {
        $mois = rand(1, 12);
        $jour = rand(1, 28); // Pour éviter les problèmes de fin de mois
        return Carbon::create($annee, $mois, $jour);
    }

    private function calculerDateFin(Carbon $debut, int $jours): Carbon
    {
        $fin = $debut->copy();
        $joursAjoutes = 0;
        while ($joursAjoutes < $jours) {
            $fin->addDay();
            if (!$fin->isWeekend()) {
                $joursAjoutes++;
            }
        }
        return $fin;
    }

    private function creerHistorique(DemandeConge $demande): void
    {
        $historiques = [
            [
                'demande_conge_id' => $demande->id,
                'action' => 'demande_soumise',
                'effectue_par' => $demande->user_id,
                'commentaire' => 'Demande initiale soumise',
                'date_action' => $demande->created_at,
                'created_at' => $demande->created_at,
                'updated_at' => $demande->created_at,
            ],
            [
                'demande_conge_id' => $demande->id,
                'action' => 'demande_approuvee',
                'effectue_par' => $demande->valide_par,
                'commentaire' => 'Demande approuvée',
                'date_action' => $demande->date_validation,
                'created_at' => $demande->date_validation,
                'updated_at' => $demande->date_validation,
            ]
        ];

        DB::table('historiques_conges')->insert($historiques);
    }
}
