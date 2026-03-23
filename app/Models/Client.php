<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'email',
        'siege_social',
        'adresse',
        'telephone',
        'contact_principal',
        'secteur_activite',
        'numero_siret',
        'code_naf',
        'logo',
        'site_web',
        'notes',
        'statut',
        'source_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relation avec les dossiers
     */
    public function dossiers()
    {
        return $this->hasMany(Dossier::class);
    }

    /**
     * Accessor pour l'URL du logo
     */
    public function getLogoUrlAttribute()
    {
        if (!$this->logo) {
            return asset('assets/img/default-client.png');
        }

        return Storage::disk('public')->url($this->logo);
    }

    /**
     * Accessor pour le statut formaté
     */
    public function getStatutBadgeAttribute()
    {
        $badges = [
            'actif' => 'success',
            'inactif' => 'danger',
            'prospect' => 'warning'
        ];

        return '<span class="badge badge-' . ($badges[$this->statut] ?? 'secondary') . '">'
            . ucfirst($this->statut) . '</span>';
    }

    /**
     * Scope pour les clients actifs
     */
    public function scopeActif($query)
    {
        return $query->where('statut', 'actif');
    }

    /**
     * Scope pour les clients inactifs
     */
    public function scopeInactif($query)
    {
        return $query->where('statut', 'inactif');
    }

    /**
     * Scope pour les prospects
     */
    public function scopeProspect($query)
    {
        return $query->where('statut', 'prospect');
    }

    /**
     * Recherche par nom, email ou téléphone
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('nom', 'LIKE', "%{$search}%")
              ->orWhere('email', 'LIKE', "%{$search}%")
              ->orWhere('telephone', 'LIKE', "%{$search}%")
              ->orWhere('contact_principal', 'LIKE', "%{$search}%");
        });
    }

}
