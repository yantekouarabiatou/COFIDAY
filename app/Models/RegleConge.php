<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RegleConge extends Model
{
    use HasFactory;

    protected $table = 'regles_conges';

    protected $fillable = [
        'jours_par_mois',
        'report_autorise',
        'limite_report',
        'validation_multiple',
        'jours_feries',
        'periodes_bloquees',
        'preavis_minimum',
        'delai_annulation',
        'couleur_calendrier'
    ];

    protected $casts = [
        'jours_par_mois' => 'decimal:2',
        'report_autorise' => 'boolean',
        'validation_multiple' => 'boolean',
        'jours_feries' => 'array',
        'periodes_bloquees' => 'array',
        'preavis_minimum' => 'integer',
        'delai_annulation' => 'integer',
    ];

    /**
     * Récupérer la seule instance de règles (singleton)
     */
    public static function getRegles()
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'jours_par_mois' => 2.5,
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
                        'nom' => 'Été',
                        'debut' => '07-15',
                        'fin' => '08-15',
                        'raison' => 'Période estivale'
                    ],
                    [
                        'nom' => 'Noël',
                        'debut' => '12-24',
                        'fin' => '12-26',
                        'raison' => 'Fêtes de fin d\'année'
                    ]
                ]),
                'preavis_minimum' => 48, // Heures
                'delai_annulation' => 24, // Heures
                'couleur_calendrier' => '#3B82F6'
            ]
        );
    }

    /**
     * Calculer les jours acquis annuels
     */
    public function calculerJoursAcquisAnnuels(): float
    {
        return round($this->jours_par_mois * 12, 2);
    }

    /**
     * Vérifier si un jour est férié
     */
    public function estJourFerie(\DateTime $date): bool
    {
        $joursFeries = $this->jours_feries ? json_decode($this->jours_feries, true) : [];
        $formatDate = $date->format('m-d');

        return in_array($formatDate, $joursFeries);
    }

    /**
     * Vérifier si une période est bloquée
     */
    public function estPeriodeBloquee(\DateTime $date): bool
    {
        $periodesBloquees = $this->periodes_bloquees ? json_decode($this->periodes_bloquees, true) : [];

        foreach ($periodesBloquees as $periode) {
            $annee = date('Y');
            $debut = \DateTime::createFromFormat('Y-m-d', $annee . '-' . $periode['debut']);
            $fin = \DateTime::createFromFormat('Y-m-d', $annee . '-' . $periode['fin']);

            if ($date >= $debut && $date <= $fin) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obtenir la couleur pour un type de congé spécifique
     */
    public function getCouleurPourType($typeCongeId): string
    {
        $couleurs = [
            1 => '#3B82F6', // Congés payés - Bleu
            2 => '#6B7280', // Sans solde - Gris
            3 => '#EF4444', // Maladie - Rouge
            4 => '#8B5CF6', // Maternité - Violet
            5 => '#10B981', // Paternité - Vert
        ];

        return $couleurs[$typeCongeId] ?? $this->couleur_calendrier;
    }

    /**
     * Vérifier si le report est autorisé
     */
    public function peutReporterJours(): bool
    {
        return $this->report_autorise;
    }

    /**
     * Obtenir la limite de report
     */
    public function getLimiteReport(): ?int
    {
        return $this->limite_report;
    }

    /**
     * Vérifier si la validation multiple est requise
     */
    public function requiertValidationMultiple(): bool
    {
        return $this->validation_multiple;
    }

    /**
     * Mettre à jour les règles
     */
    public function mettreAJourRegles(array $data): bool
    {
        return $this->update($data);
    }

    /**
     * Formater les jours par mois pour l'affichage
     */
    public function getJoursParMoisFormatted(): string
    {
        return number_format($this->jours_par_mois, 2, ',', ' ');
    }

    /**
     * Formater les jours acquis annuels pour l'affichage
     */
    public function getJoursAcquisAnnuelsFormatted(): string
    {
        return number_format($this->calculerJoursAcquisAnnuels(), 2, ',', ' ');
    }

    public function getJoursFeriesArrayAttribute(): array
    {
        if (is_array($this->jours_feries)) {
            return $this->jours_feries;
        }

        return json_decode($this->jours_feries, true) ?? [];
    }
    public function getPeriodesBloqueesArrayAttribute(): array
    {
        if (is_array($this->periodes_bloquees)) {
            return $this->periodes_bloquees;
        }

        return json_decode($this->periodes_bloquees, true) ?? [];
    }
}
