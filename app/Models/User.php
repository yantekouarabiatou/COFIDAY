<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'nom',
        'prenom',
        'username',
        'email',
        'photo',
        'password',
        'poste_id',
        'created_by',
        'telephone',
        'role_id',
        'is_active', // tu l'as dans $fillable mais pas dans la migration → à ajouter si tu veux l'utiliser
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp_code',
    ];

    protected $dates = ['deleted_at']; // ← Et celle-ci


    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Accessor nom complet
    public function getFullNameAttribute(): string
    {
        return trim("{$this->prenom} {$this->nom}");
    }

    // Relations
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function createdUsers()
    {
        return $this->hasMany(User::class, 'created_by');
    }

    public function poste()
    {
        return $this->belongsTo(Poste::class);
    }

    public function legacyRole()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    // Relations avec tes autres modèles existants
    public function dailyEntries()
    {
        return $this->hasMany(DailyEntry::class);
    }

    public function timeEntries()
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function conges()
    {
        return $this->hasMany(DemandeConge::class, 'user_id');
    }

    // Relation avec les dossiers où l'utilisateur est collaborateur
    public function dossiersCollaborations()
    {
        return $this->belongsToMany(Dossier::class, 'collaborateur_dossier')
            ->withPivot('role', 'is_active', 'added_at', 'removed_at')
            ->wherePivot('is_active', true)
            ->withTimestamps();
    }

    // Tous les dossiers accessibles par l'utilisateur
    public function accessibleDossiers()
    {
        $query = Dossier::query();

        if ($this->hasRole(['admin', 'super-admin', 'rh', 'manager', 'directeur-general'])) {
            return $query;
        }

        return $query->where(function ($q) {
            $q->where('created_by', $this->id)
                ->orWhereHas('collaborateurs', function ($subq) {
                    $subq->where('user_id', $this->id)->where('is_active', true);
                });
        });
    }

    /**
     * Vérifier si l'utilisateur est collaborateur sur un dossier
     */
    public function isCollaborateurOnDossier($dossierId)
    {
        return $this->collaborateurDossiers()
            ->where('dossier_id', $dossierId)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Relation avec les dossiers où l'utilisateur est collaborateur
     */
    public function collaborateurDossiers()
    {
        return $this->belongsToMany(Dossier::class, 'collaborateur_dossier')
            ->withPivot('role', 'is_active', 'added_at', 'removed_at')
            ->wherePivot('is_active', true);
    }
}
