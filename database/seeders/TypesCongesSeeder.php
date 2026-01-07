<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypesCongesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('types_conges')->insert([
            [
                'libelle' => 'Congé annuel',
                'nombre_jours_max' => 30,
                'est_paye' => true,
                'actif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'libelle' => 'Congé maladie',
                'nombre_jours_max' => null,
                'est_paye' => true,
                'actif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'libelle' => 'Congé maternité',
                'nombre_jours_max' => 98,
                'est_paye' => true,
                'actif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'libelle' => 'Congé sans solde',
                'nombre_jours_max' => null,
                'est_paye' => false,
                'actif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
