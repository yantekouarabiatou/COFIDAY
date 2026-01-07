<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemandesCongesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('demandes_conges')->insert([
            [
                'conge_id' => 1,
                'demandeur_id' => 1,
                'statut' => 'en_attente',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
