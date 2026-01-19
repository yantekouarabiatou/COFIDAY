<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Models\User;

class LogActivite extends Model
{
    protected $table = 'log_activites';

    protected $fillable = [
        'user_id',
        'action',
        'loggable_type',
        'loggable_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'status',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    // ------------------------------------------------------------------
    // Relations
    // ------------------------------------------------------------------

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation polymorphe robuste
     */
    public function loggable(): MorphTo
    {
        return $this->morphTo('loggable', 'loggable_type', 'loggable_id')
            ->withTrashed()
            ->withDefault(function () {
                return new class extends Model {
                    public $exists = false;
                    protected $attributes = ['id' => null];
                    public function getKey()
                    {
                        return null;
                    }
                    public function __toString()
                    {
                        return 'Ressource supprimée';
                    }
                };
            });
    }

    // ------------------------------------------------------------------
    // Accesseurs
    // ------------------------------------------------------------------

    public function getLoggableExistsAttribute(): bool
    {
        return $this->loggable && $this->loggable->exists;
    }

    /**
     * Icône FontAwesome selon l'action
     */
    public function getIconAttribute(): string
    {
        return match ($this->action) {
            'created', 'create'   => 'fa-plus-circle text-success',
            'updated', 'update'   => 'fa-edit text-warning',
            'deleted', 'delete'   => 'fa-trash text-danger',
            'restored', 'restore' => 'fa-undo text-info',
            'login'               => 'fa-sign-in-alt text-primary',
            'logout'              => 'fa-sign-out-alt text-secondary',
            default               => 'fa-cog text-muted',
        };
    }

    /**
     * Couleur Bootstrap selon l'action
     */
    public function getActionColorAttribute(): string
    {
        return match ($this->action) {
            'created', 'create'   => 'success',
            'updated', 'update'   => 'warning',
            'deleted', 'delete'   => 'danger',
            'restored', 'restore' => 'info',
            'login', 'logout'     => 'primary',
            default               => 'secondary',
        };
    }

    /**
     * URL de redirection selon le modèle loggé
     */
    public function getUrlAttribute(): ?string
    {
        if (!$this->loggable?->exists) {
            return null;
        }

        return match (true) {
            $this->loggable instanceof \App\Models\Client     => route('clients.show', $this->loggable),
            $this->loggable instanceof \App\Models\Dossier    => route('dossiers.show', $this->loggable),
            $this->loggable instanceof \App\Models\DailyEntry => route('daily-entries.show', $this->loggable),
            $this->loggable instanceof \App\Models\TimeEntry  => route('daily-entries.show', $this->loggable),
            $this->loggable instanceof \App\Models\DemandeConge      => route('conges.show', $this->loggable),
            $this->loggable instanceof \App\Models\User       => route('users.show', $this->loggable),
            default                                           => null,
        };
    }

    /**
     * Référence lisible affichée dans les logs
     */
    public function getReferenceAttribute(): string
    {
        if (!$this->loggable?->exists) {
            return '<em class="text-muted">Ressource supprimée</em>';
        }

        $model = $this->loggable;

        return
            $model->reference
            ?? $model->nom
            ?? $model->full_name
            ?? $model->email
            ?? ($model->jour?->format('d/m/Y') ?? null)
            ?? class_basename($model) . ' #' . $model->id;
    }

    public function getOldValuesAttribute($value)
    {
        if (is_string($value)) {
            try {
                return json_decode($value, true) ?? [];
            } catch (\Exception) {
                return [];
            }
        }
        return $value ?? [];
    }

    public function getNewValuesAttribute($value)
    {
        if (is_string($value)) {
            try {
                return json_decode($value, true) ?? [];
            } catch (\Exception) {
                return [];
            }
        }
        return $value ?? [];
    }
}
