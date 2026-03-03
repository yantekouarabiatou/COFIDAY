<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class SoldesCongesSeeder extends Seeder
{
    public function run(): void
    {
        // Données extraites du fichier Excel (colonnes : email, soldes 2022,2023,2024,2025)
        $soldesData = [
            ['email' => 'ahdomingo@cofima.cc',      'soldes' => [2022=>0, 2023=>0, 2024=>0, 2025=>23]],
            ['email' => 'agaba@cofima.cc',           'soldes' => [2022=>0, 2023=>18, 2024=>24, 2025=>24]],
            ['email' => 'canani@cofima.cc',          'soldes' => [2022=>0, 2023=>6, 2024=>0, 2025=>24]],
            ['email' => 'houorouzoumarou@cofima.cc', 'soldes' => [2022=>0, 2023=>0, 2024=>0, 2025=>0]],
            ['email' => 'akinkpo@cofima.cc',         'soldes' => [2022=>0, 2023=>0, 2024=>0, 2025=>19]],
            ['email' => 'falli@cofima.cc',           'soldes' => [2022=>0, 2023=>0, 2024=>0, 2025=>22]],
            ['email' => 'hzohoun@cofima.cc',         'soldes' => [2022=>0, 2023=>0, 2024=>0, 2025=>22]],
            ['email' => 'isossa@cofima.cc',          'soldes' => [2022=>0, 2023=>0, 2024=>0, 2025=>24]],
            ['email' => 'ehoundonougbo@cofima.cc',   'soldes' => [2022=>0, 2023=>0, 2024=>5, 2025=>24]],
            ['email' => 'jmavande@cofima.cc',        'soldes' => [2022=>0, 2023=>0, 2024=>30, 2025=>30]],
            ['email' => 'akouzoubanda@cofima.cc',    'soldes' => [2022=>18, 2023=>0, 2024=>24, 2025=>24]],
            ['email' => 'biroko@cofima.cc',          'soldes' => [2022=>0, 2023=>0, 2024=>0, 2025=>0]],
            ['email' => 'ryantekoua@cofima.cc',      'soldes' => [2022=>0, 2023=>0, 2024=>0, 2025=>0]],
            ['email' => 'plima@cofima.cc',           'soldes' => [2022=>0, 2023=>0, 2024=>24, 2025=>24]],
            ['email' => 'meguagie@cofima.cc',        'soldes' => [2022=>0, 2023=>0, 2024=>0, 2025=>11]],
            ['email' => 'kouessiahouangnimonpierreguida@gmail.com', 'soldes' => [2022=>0, 2023=>0, 2024=>14, 2025=>24]],
            ['email' => 'jegbessoua@cofimabenin.cc', 'soldes' => [2022=>0, 2023=>0, 2024=>0, 2025=>8]],
            ['email' => 'nsacari@cofima.cc',         'soldes' => [2022=>0, 2023=>0, 2024=>0, 2025=>14]],
        ];

        // Traitement des années 2022–2025
        foreach ($soldesData as $data) {
            $user = User::where('email', $data['email'])->first();
            if (!$user) {
                $this->command->warn("Utilisateur non trouvé : {$data['email']}");
                continue;
            }

            $soldesParAn = $data['soldes'];
            // Le quota annuel de l'utilisateur est la valeur maximale observée (ou 24 par défaut)
            $quota = max($soldesParAn) ?: 24; // si max est 0, on prend 24

            foreach ($soldesParAn as $annee => $soldeFinAnnee) {
                // Calcul des jours pris dans l'année (sans tenir compte du report, on suppose quota constant)
                $joursPris = $quota - $soldeFinAnnee;
                if ($joursPris < 0) {
                    // Cas où le solde est supérieur au quota (report possible) – on ajuste
                    // Pour simplifier, on considère que le quota est déjà majoré du report, donc on ne peut pas calculer simplement.
                    // On va plutôt fixer les jours pris à 0 et laisser le solde comme acquis + report.
                    // Mais comme on ne connaît pas le report, on garde la logique simple : quota = max, et on ne calcule pas les pris pour ces années.
                    // En pratique, on veut juste enregistrer les soldes finaux, pas les pris. Les pris seront générés par le DemandesCongesSeeder.
                    // Donc on va insérer directement les soldes finaux avec des valeurs par défaut pour les autres champs.
                    $joursPris = 0;
                }

                DB::table('soldes_conges')->updateOrInsert(
                    ['user_id' => $user->id, 'annee' => $annee],
                    [
                        'jours_acquis'  => $quota,
                        'jours_pris'    => $joursPris,
                        'jours_restants' => $soldeFinAnnee,
                        'jours_reportes' => 0, // on ne gère pas le report pour les années antérieures
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]
                );
            }
        }

        // --- Année 2026 : quota 24 pour tous + report du solde 2025 ---
        $users = User::all();
        $anneeCourante = 2026;
        $quota2026 = 24;

        foreach ($users as $user) {
            // Récupérer le solde de fin 2025
            $solde2025 = DB::table('soldes_conges')
                ->where('user_id', $user->id)
                ->where('annee', 2025)
                ->value('jours_restants');

            $report = $solde2025 ?? 0;

            DB::table('soldes_conges')->updateOrInsert(
                ['user_id' => $user->id, 'annee' => $anneeCourante],
                [
                    'jours_acquis'   => $quota2026,
                    'jours_reportes' => $report,
                    'jours_pris'     => 0,
                    'jours_restants' => $quota2026 + $report,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]
            );

            $this->command->info("Solde 2026 pour {$user->email} : {$quota2026} + report {$report} = " . ($quota2026+$report));
        }

        $this->command->info('✅ Soldes de congés 2022–2026 importés avec reports.');
    }
}
