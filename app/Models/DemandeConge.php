<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class DemandeConge extends Model
{
    use HasFactory;

    protected $table = 'demandes_conges';

    protected $fillable = [
        'user_id',
        'superieur_hierarchique_id',
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
        'justificatif_retour',
        'date_depot_justificatif',
        'meta_deductions',
        'statut_final',
        'valide_par_final',
        'date_validation_finale',
        'commentaire_final',
        'demande_attestation',
        'heure_debut',
        'heure_fin',
        'nombre_heures',
        'statut_suspension',
        'date_suspension',
        'jours_restitues',
        'motif_suspension',
    ];

    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
        'date_validation' => 'datetime',
        'date_annulation' => 'datetime',
        'nombre_jours'            => 'integer',
        'nombre_heures'           => 'decimal:2',
        'jours_restitues'         => 'integer',
        'date_suspension'         => 'date',
        'date_depot_justificatif' => 'date',
        'meta_deductions'         => 'array',
        'statut_final'            => 'string',
        'valide_par_final'        => 'integer',
        'date_validation_finale'  => 'datetime',
        'commentaire_final'       => 'string',
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

    public function superieurHierarchique()
    {
        return $this->belongsTo(User::class, 'superieur_hierarchique_id');
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

    public function getDateDebutFormattedAttribute()
    {
        return Carbon::parse($this->date_debut)->format('d/m/Y');
    }

    public function getDateFinFormattedAttribute()
    {
        return Carbon::parse($this->date_fin)->format('d/m/Y');
    }
}
