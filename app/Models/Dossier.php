<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\User;


class Dossier extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'nom',
        'reference',
        'type_dossier',
        'description',
        'date_ouverture',
        'date_cloture_prevue',
        'date_cloture_reelle',
        'heure_theorique_sans_weekend',
        'heure_theorique_avec_weekend',
        'statut',
        'budget',
        'frais_dossier',
        'document',
        'notes'
    ];

    protected $casts = [
        'date_ouverture' => 'date',
        'date_cloture_prevue' => 'date',
        'date_cloture_reelle' => 'date',
        'heure_theorique_sans_weekend' => 'decimal:2',
        'heure_theorique_avec_weekend' => 'decimal:2',
        'budget' => 'decimal:2',
        'frais_dossier' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relation avec le client
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function timeEntries()
    {
        return $this->hasMany(TimeEntry::class, 'dossier_id');
    }

    /**
     * Accessor pour l'URL du document
     */
    public function getDocumentUrlAttribute()
    {
        if (!$this->document) {
            return null;
        }

        return Storage::disk('public')->url($this->document);
    }

    /**
     * Accessor pour le nom du fichier document
     */
    public function getDocumentNameAttribute()
    {
        if (!$this->document) {
            return null;
        }

        return basename($this->document);
    }

    /**
     * Accessor pour le type de dossier formaté
     */
    public function getTypeDossierBadgeAttribute()
    {
        $badges = [
            'audit' => 'primary',
            'conseil' => 'info',
            'formation' => 'success',
            'expertise' => 'warning',
            'autre' => 'secondary'
        ];

        $labels = [
            'audit' => 'Audit',
            'conseil' => 'Conseil',
            'formation' => 'Formation',
            'expertise' => 'Expertise',
            'autre' => 'Autre'
        ];

        return '<span class="badge badge-' . ($badges[$this->type_dossier] ?? 'secondary') . '">'
            . ($labels[$this->type_dossier] ?? ucfirst($this->type_dossier)) . '</span>';
    }

    /**
     * Accessor pour le statut formaté
     */
    public function getStatutBadgeAttribute()
    {
        $badges = [
            'ouvert' => 'info',
            'en_cours' => 'primary',
            'suspendu' => 'warning',
            'cloture' => 'success',
            'archive' => 'secondary'
        ];

        $labels = [
            'ouvert' => 'Ouvert',
            'en_cours' => 'En cours',
            'suspendu' => 'Suspendu',
            'cloture' => 'Clôturé',
            'archive' => 'Archivé'
        ];

        return '<span class="badge badge-' . ($badges[$this->statut] ?? 'secondary') . '">'
            . ($labels[$this->statut] ?? ucfirst($this->statut)) . '</span>';
    }

    /**
     * Accessor pour la durée du dossier
     */
    public function getDureeAttribute()
    {
        if ($this->date_cloture_reelle) {
            $end = Carbon::parse($this->date_cloture_reelle);
        } elseif ($this->date_cloture_prevue) {
            $end = Carbon::parse($this->date_cloture_prevue);
        } else {
            $end = now();
        }

        $start = Carbon::parse($this->date_ouverture);

        return $start->diffInDays($end);
    }

    /**
     * Accessor pour le budget formaté
     */
    public function getBudgetFormateAttribute()
    {
        if (!$this->budget) {
            return '-';
        }

        return number_format($this->budget, 2, ',', ' ') . ' €';
    }

    /**
     * Accessor pour les frais de dossier formatés
     */
    public function getFraisDossierFormateAttribute()
    {
        if (!$this->frais_dossier) {
            return '-';
        }

        return number_format($this->frais_dossier, 2, ',', ' ') . ' €';
    }

    /**
     * Vérifier si le dossier est en retard
     */
    public function getEnRetardAttribute()
    {
        if ($this->statut == 'cloture' || $this->statut == 'archive') {
            return false;
        }

        if (!$this->date_cloture_prevue) {
            return false;
        }

        return Carbon::parse($this->date_cloture_prevue)->isPast();
    }

    /**
     * Scope pour les dossiers en cours
     */
    public function scopeEnCours($query)
    {
        return $query->whereIn('statut', ['ouvert', 'en_cours']);
    }

    /**
     * Scope pour les dossiers clôturés
     */
    public function scopeCloture($query)
    {
        return $query->where('statut', 'cloture');
    }

    /**
     * Scope pour les dossiers en retard
     */
    public function scopeEnRetard($query)
    {
        return $query->whereIn('statut', ['ouvert', 'en_cours'])
            ->whereDate('date_cloture_prevue', '<', now());
    }

    /**
     * Scope par type de dossier
     */
    public function scopeParType($query, $type)
    {
        return $query->where('type_dossier', $type);
    }

    /**
     * Scope par client
     */
    public function scopeParClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Recherche par nom, référence ou description
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('nom', 'LIKE', "%{$search}%")
                ->orWhere('reference', 'LIKE', "%{$search}%")
                ->orWhere('description', 'LIKE', "%{$search}%");
        });
    }
    /**
     * Récupérer tous les personnels ayant travaillé sur ce dossier
     */
    public function personnels()
    {
        return $this->belongsToMany(User::class, 'time_entries', 'dossier_id', 'user_id')
            ->withPivot(['heures_reelles', 'created_at'])
            ->withTimestamps()
            ->distinct();
    }

    /**
     * Calculer le temps total passé sur le dossier
     */
    public function getTempsTotalAttribute()
    {
        return $this->timeEntries()->sum('heures_reelles');
    }

    /**
     * Récupérer les personnels avec leur temps détaillé
     */
    public function personnelsAvecTemps($dateDebut = null, $dateFin = null)
    {
        $query = $this->timeEntries()
            ->selectRaw('user_id, SUM(heures_reelles) as total_heures, COUNT(*) as nb_interventions')
            ->with('user')
            ->groupBy('user_id');

        if ($dateDebut) {
            $query->whereHas('dailyEntry', function ($q) use ($dateDebut) {
                $q->where('jour', '>=', $dateDebut);
            });
        }

        if ($dateFin) {
            $query->whereHas('dailyEntry', function ($q) use ($dateFin) {
                $q->where('jour', '<=', $dateFin);
            });
        }

        return $query->get();
    }
    // Dans Dossier.php
    public function collaborateurs()
    {
        return $this->belongsToMany(User::class, 'collaborateur_dossier')
            ->withPivot('role', 'is_active', 'added_at', 'removed_at')
            ->wherePivot('is_active', true)
            ->withTimestamps();
    }

    // ET/OU ajoutez cette méthode pour les requêtes personnalisées
    public function collaborateursActifs()
    {
        return $this->belongsToMany(User::class, 'collaborateur_dossier')
            ->withPivot('role', 'is_active', 'added_at', 'removed_at')
            ->wherePivot('is_active', 1) // Utiliser 1 au lieu de true pour MySQL
            ->withTimestamps();
    }

    // Tous les collaborateurs (actifs et inactifs)
    public function allCollaborateurs()
    {
        return $this->belongsToMany(User::class, 'collaborateur_dossier')
            ->withPivot('role', 'is_active', 'added_at', 'removed_at')
            ->withTimestamps();
    }

    // Vérifier si un utilisateur est collaborateur sur ce dossier
    public function isCollaborateur($userId)
    {
        return $this->collaborateurs()->where('user_id', $userId)->exists();
    }

    // Ajouter un collaborateur
    public function addCollaborateur($userId, $role = 'agent')
    {
        if (!$this->isCollaborateur($userId)) {
            $this->collaborateurs()->attach($userId, [
                'role' => $role,
                'is_active' => true,
                'added_at' => now()
            ]);
            return true;
        }
        return false;
    }

    // Supprimer un collaborateur (désactiver)
    public function removeCollaborateur($userId)
    {
        $this->allCollaborateurs()->updateExistingPivot($userId, [
            'is_active' => false,
            'removed_at' => now()
        ]);
    }

    // Dans le modèle Dossier.php

    /**
     * Scope pour les dossiers accessibles par un utilisateur
     */
    public function scopeAccessibleBy($query, $userId = null)
    {
        if (!$userId) {
            $userId = auth()->id();
        }

        $user = User::find($userId);

        if ($user->hasRole(['admin', 'super-admin', 'manager', 'directeur-general'])) {
            return $query;
        }

        return $query->where(function ($q) use ($userId) {
            $q->where('created_by', $userId)
                ->orWhereHas('collaborateurs', function ($subq) use ($userId) {
                    $subq->where('collaborateur_dossier.user_id', $userId)
                        ->where('collaborateur_dossier.is_active', true); // ← QUALIFIÉ !
                });
        });
    }

    /**
     * Vérifier si un utilisateur a accès au dossier
     */
    public function userCanAccess($userId = null)
    {
        if (!$userId) $userId = auth()->id();

        $user = User::find($userId);

        if ($user->hasRole(['admin', 'super-admin', 'manager', 'directeur-general'])) {
            return true;
        }

        if ($this->created_by == $userId) {
            return true;
        }

        return $this->collaborateurs()
            ->where('collaborateur_dossier.user_id', $userId)
            ->where('collaborateur_dossier.is_active', true)
            ->exists();
    }
}
