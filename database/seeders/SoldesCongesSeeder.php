<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\RegleConge;

class SoldesCongesSeeder extends Seeder
{
    public function run(): void
    {
        $annee = now()->year;
        $regles = RegleConge::first();
        $joursAcquisBase = $regles ? $regles->jours_par_mois * 12 : 25;

        // Types d'utilisateurs avec soldes différents
        $typesUtilisateurs = [
            'senior' => ['coef' => 1.0, 'jours_pris_min' => 5, 'jours_pris_max' => 15],
            'junior' => ['coef' => 1.0, 'jours_pris_min' => 2, 'jours_pris_max' => 8],
            'stagiaire' => ['coef' => 0.67, 'jours_pris_min' => 0, 'jours_pris_max' => 5], // 2/3 du temps
            'admin' => ['coef' => 1.0, 'jours_pris_min' => 10, 'jours_pris_max' => 20],
        ];

        User::all()->each(function ($user) use ($annee, $joursAcquisBase, $typesUtilisateurs) {
            // Déterminer le type d'utilisateur
            $type = $this->determinerTypeUtilisateur($user);
            $coef = $typesUtilisateurs[$type]['coef'];

            // Calculer les jours acquis (proportionnel pour stagiaires, etc.)
            $joursAcquis = round($joursAcquisBase * $coef, 1);

            // Générer des jours pris aléatoires
            $joursPris = rand(
                $typesUtilisateurs[$type]['jours_pris_min'],
                $typesUtilisateurs[$type]['jours_pris_max']
            );

            // Calculer les jours restants
            $joursRestants = max(0, $joursAcquis - $joursPris);

            // Vérifier si un solde existe déjà
            $soldeExistant = DB::table('soldes_conges')
                ->where('user_id', $user->id)
                ->where('annee', $annee)
                ->exists();

            if (!$soldeExistant) {
                DB::table('soldes_conges')->insert([
                    'user_id' => $user->id,
                    'annee' => $annee,
                    'jours_acquis' => $joursAcquis,
                    'jours_pris' => $joursPris,
                    'jours_restants' => $joursRestants,
                    'jours_reportes' => $type === 'senior' ? rand(0, 5) : 0, 
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->command->info("✅ {$user->name} ({$type}) : {$joursAcquis} acquis, {$joursPris} pris, {$joursRestants} restants");
            }
        });

        $this->command->info("🎯 Soldes initialisés avec des données réalistes pour l'année {$annee}");
    }

    private function determinerTypeUtilisateur(User $user): string
    {
        $email = strtolower($user->email);

        if ($user->hasRole('admin')) {
            return 'admin';
        }

        // Détection basée sur l'email ou d'autres critères
        if (str_contains($email, 'stagiaire') || str_contains($email, 'intern')) {
            return 'stagiaire';
        }

        if (str_contains($email, 'senior') || $user->created_at->diffInYears(now()) > 3) {
            return 'senior';
        }

        // Par défaut, junior
        return 'junior';
    }
}
