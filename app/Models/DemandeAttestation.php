<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemandeAttestation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'type',
        'motif',
        'destinataire',
        'salaire_net',
        'inclure_salaire',
        'date_embauche',
        'poste',
        'statut',
        'valide_par',
        'date_validation',
        'commentaire_dg',
        'numero_reference',
    ];

    protected $casts = [
        'date_validation'  => 'datetime',
        'date_embauche'    => 'date',
        'inclure_salaire'  => 'boolean',
        'salaire_net'      => 'decimal:2',
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

    // ── Accesseurs ───────────────────────────────────────────────────────────

    public function getLibelleTypeAttribute(): string
    {
        return match ($this->type) {
            'attestation_simple'    => 'Attestation de travail simple',
            'attestation_banque'    => 'Attestation de travail (usage bancaire / crédit)',
            'attestation_ambassade' => 'Attestation de travail (ambassade / visa)',
            'attestation_autre'     => 'Attestation de travail — format spécifique',
            default                 => ucfirst($this->type),
        };
    }

    public function getIconeTypeAttribute(): string
    {
        return match ($this->type) {
            'attestation_simple'    => 'fas fa-file-alt text-primary',
            'attestation_banque'    => 'fas fa-university text-success',
            'attestation_ambassade' => 'fas fa-globe text-info',
            'attestation_autre'     => 'fas fa-pen-fancy text-secondary',
            default                 => 'fas fa-file',
        };
    }

    public function getBadgeTypeAttribute(): string
    {
        return match ($this->type) {
            'attestation_simple'    => 'badge-primary',
            'attestation_banque'    => 'badge-success',
            'attestation_ambassade' => 'badge-info',
            'attestation_autre'     => 'badge-secondary',
            default                 => 'badge-light',
        };
    }

    public function getStatutBadgeAttribute()
    {
        return match ($this->statut) {
            'approuve' => '<span class="badge-approuve"><i class="fas fa-check-circle"></i>Approuvé</span>',
            'en_attente' => '<span class="badge-en_attente"><i class="fas fa-clock"></i>En attente</span>',
            'refuse' => '<span class="badge-refuse"><i class="fas fa-times-circle"></i>Refusé</span>',
            'acceptee' => '<span class="badge-acceptee"><i class="fas fa-check"></i>Accepté</span>',
            default => '<span class="badge-default">—</span>',
        };
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    public function scopeApprouve($query)
    {
        return $query->where('statut', 'approuve');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Génère un numéro de référence unique
     * Format : ATT-2026-001
     */
    public static function genererNumeroReference(): string
    {
        $annee = now()->year;

        $dernierNumeroReference = self::withTrashed()
            ->whereYear('created_at', $annee)
            ->where('numero_reference', 'like', "ATT-{$annee}-%")
            ->orderByDesc('numero_reference')
            ->value('numero_reference');

        $dernierNumero = 0;
        if ($dernierNumeroReference) {
            $dernierNumero = (int) substr($dernierNumeroReference, strrpos($dernierNumeroReference, '-') + 1);
        }

        $nextNumero = $dernierNumero + 1;
        $reference = sprintf('ATT-%s-%03d', $annee, $nextNumero);

        while (self::withTrashed()->where('numero_reference', $reference)->exists()) {
            $nextNumero++;
            $reference = sprintf('ATT-%s-%03d', $annee, $nextNumero);
        }

        return $reference;
    }

    /**
     * Indique si ce type nécessite une rédaction manuelle par le RH
     */
    public function isManuel(): bool
    {
        return $this->type === 'attestation_autre';
    }
}
