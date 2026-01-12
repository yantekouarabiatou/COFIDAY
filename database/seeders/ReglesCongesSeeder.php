<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RegleConge;

class ReglesCongesSeeder extends Seeder
{
    public function run(): void
    {
        // S'assurer qu'il n'y a qu'une seule ligne de règles
        $reglesExistantes = RegleConge::count();

        if ($reglesExistantes === 0) {
            RegleConge::create([
                'jours_par_mois' => 2.5,
                'report_autorise' => true,
                'limite_report' => 10,
                'validation_multiple' => false,
                'jours_feries' => json_encode([
                    '01-01', // Nouvel An
                    '01-05', // Fête du Travail
                    '08-05', // Victoire 1945
                    '07-14', // Fête Nationale
                    '08-15', // Assomption
                    '11-01', // Toussaint
                    '11-11', // Armistice
                    '12-25', // Noël
                ]),
                'periodes_bloquees' => json_encode([
                    [
                        'nom' => 'Période estivale',
                        'debut' => '07-15',
                        'fin' => '08-15',
                        'raison' => 'Congés d\'été collectifs'
                    ],
                    [
                        'nom' => 'Fêtes de fin d\'année',
                        'debut' => '12-24',
                        'fin' => '12-26',
                        'raison' => 'Fermeture annuelle'
                    ]
                ]),
                'preavis_minimum' => 48, // heures
                'delai_annulation' => 24, // heures
                'couleur_calendrier' => '#3B82F6'
            ]);
        }
    }
}
