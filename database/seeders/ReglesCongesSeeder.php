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
                'jours_par_mois' => 2,
                'report_autorise' => true,
                'limite_report' => 10,
                'validation_multiple' => false,
                'jours_feries' => json_encode([
                    [
                        'date'=>'01-01', 
                        'nom'=>'Nouvel An'],
                    [
                        'date'=>'01-08', 
                        'nom'=>'Vodun days'],
                    [
                        'date'=>'01-09', 
                        'nom'=>'Vodun days'],
                    [
                        'date'=>'01-10',   
                        'nom'=>'Vodun days'],
                    [
                        'date'=>'05-01', 
                        'nom'=>'Fête du Travail'],
                    [
                        'date'=>'08-01', 
                        'nom'=>'Fête Nationale'],
                    [
                        'date'=>'08-15', 
                        'nom'=>'Assomption'],
                    [
                        'date'=>'11-01', 
                        'nom'=>'Toussaint'],
                    [
                        'date'=>'12-25', 
                        'nom'=>'Noël'
                    ]
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
