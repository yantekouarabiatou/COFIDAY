<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReglesCongesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('regles_conges')->insert([
            'jours_par_mois' => 2.5,
            'report_autorise' => true,
            'limite_report' => 5,
            'validation_multiple' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
