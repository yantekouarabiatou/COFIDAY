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
    /**
     * Exécuter le seeder.
     */
    public function run(): void
    {
        // Vider la table si nécessaire (seulement en développement)
        if (app()->environment('local')) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            DB::table('historiques_conges')->truncate();
            DB::table('demandes_conges')->truncate();

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }


        $users = User::all();
        $typesConges = TypeConge::all();
        $anneeCourante = now()->year;

        // Statuts possibles avec probabilités
        $statuts = [
            'en_attente' => 0.2,  // 20%
            'pre_approuve' => 0.4, // 10%
            'approuve' => 0.2,     // 60%
            'refuse' => 0.1,       // 10%
            'annule' => 0.1,       // 10%
        ];

        // Motifs possibles
        $motifs = [
            'Vacances en famille',
            'Voyage à l\'étranger',
            'Raisons personnelles',
            'Événement familial',
            'Repos et détente',
            'Projets personnels',
            'Formation personnelle',
            'Raisons médicales',
            'Déplacement professionnel',
            'Célébration spéciale',
            'Célébration spéciale',
        ];

        // Administrateurs pour validation
        $validateurs = User::role('admin')->orWhere('id', 1)->get();

        foreach ($users as $user) {
            // Générer entre 1 et 5 demandes par utilisateur
            $nombreDemandes = rand(1, 5);

            for ($i = 0; $i < $nombreDemandes; $i++) {
                $typeConge = $typesConges->random();
                $statut = $this->getStatutAleatoire($statuts);

                // Générer des dates aléatoires pour l'année courante
                $dateDebut = $this->genererDateAleatoire($anneeCourante);
                $dateFin = $this->genererDateFin($dateDebut, $typeConge);
                $nombreJours = $this->calculerJoursOuvres($dateDebut, $dateFin);

                // Vérifier que l'utilisateur a assez de jours pour les congés payés
                if ($typeConge->est_paye) {
                    $solde = SoldeConge::where('user_id', $user->id)
                        ->where('annee', $anneeCourante)
                        ->first();

                    if (!$solde || $solde->jours_restants < $nombreJours) {
                        // Changer le type en non payé ou réduire la durée
                        $typeConge = $typesConges->where('est_paye', false)->first();
                    }
                }

                // Créer la demande
                $demande = DemandeConge::create([
                    'user_id' => $user->id,
                    'type_conge_id' => $typeConge->id,
                    'date_debut' => $dateDebut,
                    'date_fin' => $dateFin,
                    'nombre_jours' => $nombreJours,
                    'motif' => $motifs[array_rand($motifs)],
                    'statut' => $statut,
                    'valide_par' => $statut !== 'en_attente' && $validateurs->isNotEmpty()
                        ? $validateurs->random()->id
                        : null,
                    'date_validation' => $statut !== 'en_attente'
                        ? $dateDebut->copy()->subDays(rand(1, 10))
                        : null,
                    'created_at' => $dateDebut->copy()->subDays(rand(5, 30)),
                    'updated_at' => now(),
                ]);

                // Créer l'historique pour cette demande
                $this->creerHistoriqueDemande($demande, $validateurs);
            }
        }

        $this->command->info("✅ " . DemandeConge::count() . " demandes de congés créées avec succès !");
    }

    /**
     * Générer une date aléatoire pour l'année donnée.
     */
    private function genererDateAleatoire(int $annee): Carbon
    {
        // Générer une date entre janvier et novembre de l'année donnée
        $mois = rand(1, 11);
        $jour = rand(1, 28); // Éviter les problèmes avec février

        return Carbon::create($annee, $mois, $jour);
    }

    /**
     * Générer une date de fin basée sur la date de début et le type de congé.
     */
    private function genererDateFin(Carbon $dateDebut, TypeConge $typeConge): Carbon
    {
        // Durée aléatoire basée sur le type de congé
        $durees = [
            'Congés payés' => [1, 15],           // 1-15 jours
            'Congés sans solde' => [1, 30],      // 1-30 jours
            'Congés maladie' => [1, 10],         // 1-10 jours
            'Congés maternité' => [90, 98],      // 90-98 jours
            'Congés paternité' => [11, 25],      // 11-25 jours
        ];

        $libelle = $typeConge->libelle;
        $minJours = $durees[$libelle][0] ?? 1;
        $maxJours = $typeConge->nombre_jours_max
            ? min($durees[$libelle][1] ?? 30, $typeConge->nombre_jours_max)
            : ($durees[$libelle][1] ?? 30);

        $jours = rand($minJours, $maxJours);

        return $dateDebut->copy()->addDays($jours);
    }

    /**
     * Calculer les jours ouvrés entre deux dates.
     */
    private function calculerJoursOuvres(Carbon $debut, Carbon $fin): float
    {
        $jours = 0;
        $date = $debut->copy();

        while ($date->lte($fin)) {
            if (!$date->isWeekend()) {
                $jours++;
            }
            $date->addDay();
        }

        return $jours;
    }

    /**
     * Obtenir un statut aléatoire selon les probabilités.
     */
    private function getStatutAleatoire(array $statuts): string
    {
        $rand = mt_rand() / mt_getrandmax();
        $cumulative = 0;

        foreach ($statuts as $statut => $probabilite) {
            $cumulative += $probabilite;
            if ($rand <= $cumulative) {
                return $statut;
            }
        }

        return 'en_attente';
    }

    /**
     * Créer l'historique pour une demande.
     */
    private function creerHistoriqueDemande(DemandeConge $demande, $validateurs): void
    {
        $historiques = [];

        // Historique de création
        $historiques[] = [
            'demande_conge_id' => $demande->id,
            'action' => 'demande_soumise',
            'effectue_par' => $demande->user_id,
            'commentaire' => 'Demande initiale soumise',
            'date_action' => $demande->created_at,
            'created_at' => $demande->created_at,
            'updated_at' => $demande->created_at,
        ];

        // Si la demande a été traitée, ajouter l'historique de traitement
        if ($demande->statut !== 'en_attente' && $demande->valide_par) {
            $action = $demande->statut === 'pre_approuve' ? 'demande_approuvee' : 'demande_refusee';
            $commentaire = $demande->statut === 'pre_approuve'
                ? 'Demande approuvée par le superviseur, en attente de validation finale'
                : 'Demande refusée pour cause de planification';

            $historiques[] = [
                'demande_conge_id' => $demande->id,
                'action' => $action,
                'effectue_par' => $demande->valide_par,
                'commentaire' => $commentaire,
                'date_action' => $demande->date_validation,
                'created_at' => $demande->date_validation,
                'updated_at' => $demande->date_validation,
            ];
        }

        // Si la demande a été modifiée
        if (rand(0, 1) && $demande->statut === 'en_attente') {
            $historiques[] = [
                'demande_conge_id' => $demande->id,
                'action' => 'demande_modifiee',
                'effectue_par' => $demande->user_id,
                'commentaire' => 'Dates modifiées par l\'employé',
                'date_action' => $demande->created_at->copy()->addHours(rand(1, 24)),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insérer les historiques
        DB::table('historiques_conges')->insert($historiques);
    }
}
