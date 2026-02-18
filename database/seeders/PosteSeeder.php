<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PosteSeeder extends Seeder
{
    public function run(): void
    {
        // Désactiver les contraintes de clé étrangère
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('postes')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $postes = [
            ['intitule' => 'INFORMATICIEN', 'description' => 'Service informatique', 'created_at' => now(), 'updated_at' => now()],
            ['intitule' => 'DIRECTEUR INFORMATIQUE', 'description' => 'Service informatique', 'created_at' => now(), 'updated_at' => now()],
            ['intitule' => 'CONSULTANTS', 'description' => 'Département des consultants', 'created_at' => now(), 'updated_at' => now()],
            ['intitule' => 'DIRECTEUR GENERALE', 'description' => 'Direction générale', 'created_at' => now(), 'updated_at' => now()],
            ['intitule' => 'SECRETAIRE', 'description' => 'Secrétariat', 'created_at' => now(), 'updated_at' => now()],
            ['intitule' => 'COMPTABLE', 'description' => 'Service comptabilité', 'created_at' => now(), 'updated_at' => now()],
            ['intitule' => 'AUDITEUR', 'description' => 'Département audit', 'created_at' => now(), 'updated_at' => now()],
            ['intitule' => 'STATISTICIEN', 'description' => 'Département Etude de Projet', 'created_at' => now(), 'updated_at' => now()],
            ['intitule' => 'CHEF SERVICES QUALITÉS ', 'description' => 'Département Qualité', 'created_at' => now(), 'updated_at' => now()],

        ];

        DB::table('postes')->insert($postes);
    }
}
