<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class SoldesCongesSeeder extends Seeder
{
    public function run(): void
    {
        $annee = now()->year; // 2026
        $joursAcquis = 24; // 2 jours × 12 mois

        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('⚠️ Aucun utilisateur trouvé. Exécutez d\'abord UserSeeder.');
            return;
        }

        foreach ($users as $user) {
            $exists = DB::table('soldes_conges')
                ->where('user_id', $user->id)
                ->where('annee', $annee)
                ->exists();

            if (!$exists) {
                DB::table('soldes_conges')->insert([
                    'user_id' => $user->id,
                    'annee' => $annee,
                    'jours_acquis' => $joursAcquis,
                    'jours_pris' => 0,
                    'jours_restants' => $joursAcquis,
                    'jours_reportes' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->command->info("✅ Solde créé pour {$user->email} : {$joursAcquis} jours acquis, 0 pris.");
            } else {
                $this->command->warn("⚠️ Solde déjà existant pour {$user->email} en {$annee}.");
            }
        }

        $this->command->info("🎯 Soldes initialisés pour l'année {$annee} avec {$joursAcquis} jours par utilisateur.");
    }
}
