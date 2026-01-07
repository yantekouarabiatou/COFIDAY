<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HistoriquesCongesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('historiques_conges')->insert([
            [
                'demande_conge_id' => 1,
                'action' => 'création',
                'effectue_par' => 1,
                'commentaire' => 'Demande de congé créée',
                'date_action' => now(),
            ],
        ]);
    }
}
