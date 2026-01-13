<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class HistoriqueConge extends Model
{
    use HasFactory;

    protected $table = 'historiques_conges';

    protected $fillable = [
        'demande_conge_id',
        'action',
        'effectue_par',
        'commentaire',
        'date_action'
    ];

    protected $casts = [
        'date_action' => 'datetime',
    ];

    /**
     * Actions possibles
     */
    const ACTIONS = [
        'demande_soumise' => 'Demande soumise',
        'demande_modifiee' => 'Demande modifiée',
        'demande_annulee' => 'Demande annulée',
        'demande_approuvee' => 'Demande approuvée',
        'demande_refusee' => 'Demande refusée',
        'solde_ajuste' => 'Solde ajusté',
        'relance_envoyee' => 'Relance envoyée',
        'rappel_automatique' => 'Rappel automatique',
        'notification_envoyee' => 'Notification envoyée',
        'statut_modifie' => 'Statut modifié',
        'commentaire_ajoute' => 'Commentaire ajouté',
    ];

    /**
     * Relation avec la demande de congé
     */
    public function demande(): BelongsTo
    {
        return $this->belongsTo(DemandeConge::class, 'demande_conge_id');
    }

    /**
     * Relation avec l'utilisateur qui a effectué l'action
     */
    public function effectuePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'effectue_par');
    }

    /**
     * Obtenir le libellé de l'action
     */
    public function getActionLabelAttribute(): string
    {
        return self::ACTIONS[$this->action] ?? $this->action;
    }

    /**
     * Obtenir la classe CSS pour l'action
     */
    public function getActionClassAttribute(): string
    {
        $classes = [
            'demande_soumise' => 'bg-blue-100 text-blue-800',
            'demande_modifiee' => 'bg-yellow-100 text-yellow-800',
            'demande_annulee' => 'bg-gray-100 text-gray-800',
            'demande_approuvee' => 'bg-green-100 text-green-800',
            'demande_refusee' => 'bg-red-100 text-red-800',
            'solde_ajuste' => 'bg-purple-100 text-purple-800',
            'relance_envoyee' => 'bg-indigo-100 text-indigo-800',
            'rappel_automatique' => 'bg-pink-100 text-pink-800',
        ];

        return $classes[$this->action] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Obtenir l'icône pour l'action
     */
    public function getActionIconAttribute(): string
    {
        $icons = [
            'demande_soumise' => 'fas fa-paper-plane',
            'demande_modifiee' => 'fas fa-edit',
            'demande_annulee' => 'fas fa-times-circle',
            'demande_approuvee' => 'fas fa-check-circle',
            'demande_refusee' => 'fas fa-times-circle',
            'solde_ajuste' => 'fas fa-calculator',
            'relance_envoyee' => 'fas fa-envelope',
            'rappel_automatique' => 'fas fa-bell',
        ];

        return $icons[$this->action] ?? 'fas fa-history';
    }

    /**
     * Formater la date d'action
     */
    public function getDateFormattedAttribute(): string
    {
        return $this->date_action->format('d/m/Y H:i');
    }

    /**
     * Formater la date d'action relative (il y a...)
     */
    public function getDateRelativeAttribute(): string
    {
        return $this->date_action->diffForHumans();
    }

    /**
     * Obtenir les détails complets de l'action
     */
    public function getDetailsAttribute(): array
    {
        return [
            'id' => $this->id,
            'action' => $this->action,
            'action_label' => $this->action_label,
            'action_class' => $this->action_class,
            'action_icon' => $this->action_icon,
            'commentaire' => $this->commentaire,
            'date_action' => $this->date_action,
            'date_formatted' => $this->date_formatted,
            'date_relative' => $this->date_relative,
            'effectue_par_nom' => $this->effectuePar->name ?? 'Système',
            'effectue_par_email' => $this->effectuePar->email ?? null,
            'demande_id' => $this->demande_conge_id,
        ];
    }

    /**
     * Filtrer par action
     */
    public function scopeParAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Filtrer par utilisateur
     */
    public function scopeParUtilisateur($query, $userId)
    {
        return $query->where('effectue_par', $userId);
    }

    /**
     * Filtrer par période
     */
    public function scopePeriode($query, $debut, $fin = null)
    {
        if (!$fin) {
            $fin = now();
        }

        return $query->whereBetween('date_action', [$debut, $fin]);
    }

    /**
     * Obtenir les actions récentes
     */
    public static function getRecentActions($limit = 10)
    {
        return static::with(['demande', 'effectuePar'])
            ->latest('date_action')
            ->limit($limit)
            ->get();
    }

    /**
     * Créer une entrée d'historique
     */
    public static function creerHistorique($demandeId, $action, $userId = null, $commentaire = null): self
    {
        return static::create([
            'demande_conge_id' => $demandeId,
            'action' => $action,
            'effectue_par' => $userId,
            'commentaire' => $commentaire,
            'date_action' => now(),
        ]);
    }

    /**
     * Obtenir l'historique complet d'une demande
     */
    public static function getHistoriqueDemande($demandeId)
    {
        return static::with('effectuePar')
            ->where('demande_conge_id', $demandeId)
            ->orderBy('date_action', 'asc')
            ->get();
    }

    /**
     * Obtenir le dernier statut d'une demande
     */
    public static function getDernierStatut($demandeId): ?string
    {
        $actionsStatut = [
            'demande_soumise',
            'demande_approuvee',
            'demande_refusee',
            'demande_annulee'
        ];

        $historique = static::where('demande_conge_id', $demandeId)
            ->whereIn('action', $actionsStatut)
            ->latest('date_action')
            ->first();

        return $historique ? $historique->action : null;
    }

    /**
     * Vérifier si une action spécifique a été effectuée
     */
    public static function actionExistante($demandeId, $action): bool
    {
        return static::where('demande_conge_id', $demandeId)
            ->where('action', $action)
            ->exists();
    }

    /**
     * Obtenir le nombre d'actions par type
     */
    public static function getStatsActions($debut = null, $fin = null)
    {
        $query = static::query();

        if ($debut && $fin) {
            $query->whereBetween('date_action', [$debut, $fin]);
        }

        return $query->select('action', DB::raw('count(*) as total'))
            ->groupBy('action')
            ->orderBy('total', 'desc')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->action => $item->total];
            });
    }
}
