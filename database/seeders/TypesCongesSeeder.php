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
                'libelle' => 'Congés payés',
                'nombre_jours_max' => 30,
                'est_paye' => true,
                'est_annuel' => true, // Seul ce type est un congé annuel
                'actif' => true,
                'couleur' => '#3B82F6',
            ],
            [
                'libelle' => 'Congés sans solde',
                'nombre_jours_max' => 90,
                'est_paye' => false,
                'est_annuel' => false, // Pas annuel
                'actif' => true,
                'couleur' => '#6B7280',
            ],
            [
                'libelle' => 'Congés maladie',
                'nombre_jours_max' => 180,
                'est_paye' => true,
                'est_annuel' => false, // Payé mais pas annuel
                'actif' => true,
                'couleur' => '#EF4444',
            ],
            [
                'libelle' => 'Congés maternité',
                'nombre_jours_max' => 98,
                'est_paye' => true,
                'est_annuel' => false, // Payé mais pas annuel
                'actif' => true,
                'couleur' => '#8B5CF6',
            ],
            [
                'libelle' => 'Congés paternité',
                'nombre_jours_max' => 25,
                'est_paye' => true,
                'est_annuel' => false, // Payé mais pas annuel
                'actif' => true,
                'couleur' => '#10B981',
            ],
        ]);
    }
}
