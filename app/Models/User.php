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
        'manager_id',
        'created_by',
        'telephone',
        'sexe',
        'is_active',
        'date_embauche'
    ];

    protected $hidden = ['password', 'remember_token', 'otp_code'];
    protected $dates  = ['deleted_at'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'otp_expires_at'    => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    // ── Accessors ────────────────────────────────────────────────
    public function getFullNameAttribute(): string
    {
        return trim("{$this->prenom} {$this->nom}");
    }

    /** Vérifie si $this est le manager de $userId */
    public function isManagerOf(int $userId): bool
    {
        return $this->subordinates()->where('id', $userId)->exists();
    }

    // ── Autres relations ─────────────────────────────────────────
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
    public function conges()
    {
        return $this->hasMany(DemandeConge::class, 'user_id');
    }
    public function weeklyValidations()
    {
        return $this->hasMany(WeeklyValidation::class);
    }

    public function dossiersCollaborations()
    {
        return $this->belongsToMany(Dossier::class, 'collaborateur_dossier')
            ->withPivot('role', 'is_active', 'added_at', 'removed_at')
            ->wherePivot('is_active', true)
            ->withTimestamps();
    }

    public function accessibleDossiers()
    {
        $query = Dossier::query();
        if ($this->hasRole(['admin', 'super-admin', 'rh', 'manager', 'directeur-general'])) {
            return $query;
        }
        return $query->where(function ($q) {
            $q->where('created_by', $this->id)
                ->orWhereHas('collaborateurs', fn($s) => $s->where('user_id', $this->id)->where('is_active', true));
        });
    }

    public function collaborateurDossiers()
    {
        return $this->belongsToMany(Dossier::class, 'collaborateur_dossier')
            ->withPivot('role', 'is_active', 'added_at', 'removed_at')
            ->wherePivot('is_active', true);
    }

    public function certificats()
    {
        return $this->hasMany(DemandeDemission::class);
    }

    public function attestations()
    {
        return $this->hasMany(DemandeAttestation::class);
    }

    // Dans app/Models/User.php

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function subordinates()
    {
        return $this->hasMany(User::class, 'manager_id');
    }
}
