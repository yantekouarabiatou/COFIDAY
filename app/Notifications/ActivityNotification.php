<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ActivityNotification extends Notification
{
    use Queueable;

    protected $model;
    protected $action;
    protected $customMessage;
    protected $modelClass;
    protected $modelId;

    public function __construct($model, string $action = 'created', ?string $customMessage = null)
    {
        $this->model = $model;
        $this->action = $action;
        $this->customMessage = $customMessage;
        $this->modelClass = get_class($model);
        $this->modelId = $model->id ?? null;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $causer = auth()->user()?->prenom . ' ' . auth()->user()?->nom ?? 'Système';
        $ref = $this->getReference();

        $message = $this->customMessage ?? match ($this->action) {
            'created'       => "$causer a créé $ref",
            'updated'       => "$causer a modifié $ref",
            'deleted'       => "$causer a supprimé $ref",
            'approuve'      => "$causer a approuvé $ref",
            'refuse'        => "$causer a refusé $ref",
            'annule'        => "$causer a annulé $ref",
            'demande_soumise' => "$causer a soumis $ref",
            'demande_modifiee' => "$causer a modifié $ref",
            'solde_ajuste'  => "$causer a ajusté le solde pour $ref",
            'valide'        => "$causer a validé $ref",
            'assign'        => "$causer vous a assigné $ref",
            'unassign'      => "$causer vous a retiré $ref",
            'replanifie'    => "$causer a replanifié $ref",
            'cloture'       => "$causer a clôturé $ref",
            'relance'       => "$causer a relancé $ref",
            'soumis'    => "$causer a soumis une feuille de temps pour le {$this->model->jour->format('d/m/Y')}",
            'validé'    => "$causer a validé la feuille de temps du {$this->model->jour->format('d/m/Y')}",
            'refusé'    => "$causer a refusé la feuille de temps du {$this->model->jour->format('d/m/Y')}",
            'règle créée'  => "$causer a créé une règle de congé",
            'règle modifiée' => "$causer a modifié une règle de congé",
            'règle supprimée' => "$causer a supprimé une règle de congé",
            'solde créé'   => "$causer a créé un solde de congés pour $ref",
            'solde modifié' => "$causer a modifié un solde de congés pour $ref",
            'solde supprimé' => "$causer a supprimé un solde de congés pour $ref",
            'type créé'    => "$causer a créé un type de congé",
            'type modifié'  => "$causer a modifié un type de congé",
            'type supprimé' => "$causer a supprimé un type de congé",
            'historique créé' => "$causer a ajouté une entrée d'historique pour $ref",
            // Congés
            'crée'   => "$causer a soumis une demande de congé",
            'approuveé'  => "$causer a approuvé votre demande de congé",
            'refuseé'    => "$causer a refusé votre demande de congé",
            default         => "$causer a effectué une action sur $ref",
        };

        // STRUCTURE CRITIQUE : Doit correspondre à ce que la navbar attend
        return [
            'message'    => $message,
            'url'        => $this->getUrl(),
            'icon'       => $this->getIcon(),
            'color'      => $this->getColor(),
            'action'     => $this->action,
            'model_type' => class_basename($this->modelClass),
            'model_id'   => $this->modelId,
            'causer'     => $causer,
            'created_at' => now()->toDateTimeString(),
        ];
    }

    private function getReference(): string
    {
        // Pour les modèles supprimés, on utilise les propriétés sauvegardées
        if (!$this->model || !$this->model->exists) {
            return class_basename($this->modelClass) . ' #' . $this->modelId;
        }

        // Gestion des congés
        if ($this->model instanceof \App\Models\DemandeConge) {
            $userName = $this->model->user->prenom . ' ' . $this->model->user->nom ?? 'Utilisateur';
            $type = $this->model->typeConge->libelle ?? 'Congé';
            $dates = \Carbon\Carbon::parse($this->model->date_debut)->format('d/m') . ' - ' .
                \Carbon\Carbon::parse($this->model->date_fin)->format('d/m');
            return "demande de $type pour $userName ($dates)";
        }

        // Gestion des soldes
        if ($this->model instanceof \App\Models\SoldeConge) {
            $userName = $this->model->user->prenom . ' ' . $this->model->user->nom ?? 'Utilisateur';
            return "solde de congés $userName ({$this->model->annee})";
        }

        // Gestion des types de congés
        if ($this->model instanceof \App\Models\TypeConge) {
            return "type de congé '{$this->model->libelle}'";
        }

        // Gestion des historiques
        if ($this->model instanceof \App\Models\HistoriqueConge) {
            return "historique #{$this->model->id}";
        }

        // Gestion des utilisateurs
        if ($this->model instanceof \App\Models\User) {
            return "utilisateur {$this->model->prenom} {$this->model->nom}";
        }

        // Gestion des paramètres
        if ($this->model instanceof \App\Models\CompanySetting) {
            return "paramètre '{$this->model->cle}'";
        }

         // Gestion des paramètres
        if ($this->model instanceof \App\Models\RegleConge) {
            return "Règles de conges '{$this->model->id}'";
        }

        // Fallback générique
        return $this->model->Reference
            ?? $this->model->nom
            ?? $this->model->libelle
            ?? $this->model->titre
            ?? $this->model->intitule
            ?? class_basename($this->model) . ' #' . $this->model->id;
    }

    private function getUrl(): ?string
    {
        // Pour les modèles supprimés, pas d'URL
        if (!$this->model || !$this->model->exists || in_array($this->action, ['deleted', 'refuse', 'annule'])) {
            return null;
        }

        // Routes pour la gestion des congés
        return match (true) {
            // Congés
            $this->model instanceof \App\Models\DemandeConge => route('conges.show', $this->model),

            // Soldes
            $this->model instanceof \App\Models\SoldeConge => route('conges.solde.user', $this->model->user),

            // Types de congés
            $this->model instanceof \App\Models\TypeConge => route('types-conges.show', $this->model),

            // Utilisateurs
            $this->model instanceof \App\Models\User => route('users.show', $this->model),

            $this->model instanceof \App\Models\DemandeAttestation => route('attestations.show', $this->model),
            $this->model instanceof \App\Models\DemandeDemission => route('demissions.show', $this->model),


            // Historiques (redirige vers la demande)
            $this->model instanceof \App\Models\HistoriqueConge =>
            $this->model->demandeConge
                ? route('conges.show', $this->model->demandeConge)
                : route('conges.index'),

            // Paramètres
            $this->model instanceof \App\Models\CompanySetting => route('settings.index'),

            default => null,
        };
    }

    private function getIcon(): string
    {
        return match ($this->action) {
            'created', 'demande_soumise'  => 'fas fa-plus-circle',
            'updated', 'demande_modifiee' => 'fas fa-edit',
            'deleted', 'refuse'           => 'fas fa-trash',
            'approuve', 'valide'          => 'fas fa-check-circle',
            'annule'                      => 'fas fa-ban',
            'solde_ajuste'                => 'fas fa-wallet',
            'assign'                      => 'fas fa-user-tag',
            'unassign'                    => 'fas fa-user-minus',
            'replanifie'                  => 'fas fa-calendar-alt',
            'cloture'                     => 'fas fa-lock',
            'relance'                     => 'fas fa-bell',
            default                       => 'fas fa-bell',
        };
    }

    private function getColor(): string
    {
        return match ($this->action) {
            'created', 'demande_soumise', 'approuve', 'valide' => 'bg-success',
            'updated', 'demande_modifiee', 'solde_ajuste', 'replanifie' => 'bg-warning',
            'deleted', 'refuse'   => 'bg-danger',
            'annule'              => 'bg-secondary',
            'assign', 'unassign'  => 'bg-info',
            'cloture'             => 'bg-dark',
            'relance'             => 'bg-primary',
            default               => 'bg-primary',
        };
    }
}
