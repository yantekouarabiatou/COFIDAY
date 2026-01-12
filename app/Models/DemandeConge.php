<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandeConge extends Model
{
    use HasFactory;

    protected $table = 'demandes_conges';

    protected $fillable = [
        'user_id',
        'type_conge_id',
        'date_debut',
        'date_fin',
        'nombre_jours',
        'motif',
        'statut',
        'valide_par',
        'date_validation',
        'motif_refus',
        'annule_par',
        'date_annulation',
        'fichier_justificatif',
    ];

    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
        'date_validation' => 'datetime',
        'date_annulation' => 'datetime',
        'nombre_jours' => 'integer',
    ];

    /**
     * Relations
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function typeConge()
    {
        return $this->belongsTo(TypeConge::class);
    }

    public function validePar()
    {
        return $this->belongsTo(User::class, 'valide_par');
    }
    public function historiques()
    {
        return $this->hasMany(HistoriqueConge::class, 'demande_conge_id');
    }

    public function annulePar()
    {
        return $this->belongsTo(User::class, 'annule_par');
    }

    /**
     * Scopes
     */
    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    public function scopeApprouve($query)
    {
        return $query->where('statut', 'approuve');
    }

    public function scopeRefuse($query)
    {
        return $query->where('statut', 'refuse');
    }

    public function scopeAnnule($query)
    {
        return $query->where('statut', 'annule');
    }

    /**
     * Accesseurs
     */
    public function getStatutBadgeAttribute()
    {
        $badges = [
            'en_attente' => 'badge-warning',
            'approuve' => 'badge-success',
            'refuse' => 'badge-error',
            'annule' => 'badge-secondary',
        ];

        return $badges[$this->statut] ?? 'badge-secondary';
    }

    public function getStatutLabelAttribute()
    {
        $labels = [
            'en_attente' => 'En attente',
            'approuve' => 'Approuvé',
            'refuse' => 'Refusé',
            'annule' => 'Annulé',
        ];

        return $labels[$this->statut] ?? $this->statut;
    }
}
