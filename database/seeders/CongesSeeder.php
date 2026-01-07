<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CongesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('conges')->insert([
            [
                'user_id' => 1,
                'type_conge_id' => 1,
                'date_debut' => now()->addDays(10)->toDateString(),
                'date_fin' => now()->addDays(15)->toDateString(),
                'nombre_jours' => 5,
                'statut' => 'en_attente',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
