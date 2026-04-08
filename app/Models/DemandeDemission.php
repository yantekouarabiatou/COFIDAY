<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemandeDemission extends Model
{
    use SoftDeletes;

    protected $table = 'demande_demissions';

    protected $fillable = [
        'user_id',
        'date_depart_souhaitee',
        'lettre',
        'statut',
        'valide_par',
        'date_validation',
        'commentaire_dg',
        'numero_certificat',
        'numero_reference',
        'certificat_genere',
        'date_generation_certificat',
    ];

    protected $casts = [
        'date_depart_souhaitee'      => 'date',
        'date_validation'             => 'datetime',
        'date_generation_certificat'  => 'datetime',
        'certificat_genere'           => 'boolean',
    ];

    // ── Relations ─────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getStatutBadgeAttribute(): string
    {
        return match ($this->statut) {
            'en_attente' => '<span class="badge badge-warning"><i class="fas fa-clock"></i> En attente</span>',
            'acceptee'   => '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Acceptée</span>',
            'refusee'    => '<span class="badge badge-danger"><i class="fas fa-times-circle"></i> Refusée</span>',
            default      => '<span class="badge badge-secondary">' . $this->statut . '</span>',
        };
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    // AJOUTER CE SCOPE — utilisé dans DemissionController::index()
    public function scopeApprouve($query)
    {
        return $query->where('statut', 'acceptee');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Génère le numéro de certificat de travail
     * Format : CERT-2026-001
     */
    public static function genererNumeroCertificat(): string
    {
        $annee   = now()->year;
        $dernier = self::withTrashed()
            ->whereYear('created_at', $annee)
            ->whereNotNull('numero_certificat')
            ->count();

        return sprintf('CERT-%s-%03d', $annee, $dernier + 1);
    }

    /**
     * Génère un numéro de référence pour la demande
     * Format : DEM-2026-001
     */
    public static function genererNumeroReference(): string
    {
        $annee   = now()->year;
        $dernier = self::withTrashed()
            ->whereYear('created_at', $annee)
            ->count();

        return sprintf('DEM-%s-%03d', $annee, $dernier + 1);
    }
}
