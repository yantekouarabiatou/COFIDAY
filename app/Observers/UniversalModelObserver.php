<?php

namespace App\Observers;

use App\Models\DemandeConge;
use App\Models\SoldeConge;
use App\Models\User;
use App\Models\LogActivite; // ← Important : importe ton modèle de log
use App\Events\ModelActivityEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class UniversalModelObserver
{
    /**
     * Handle model created event
     */
    public function created(Model $model)
    {
        $this->logAndNotify($model, 'created');
    }

    /**
     * Handle model updated event
     */
    public function updated(Model $model)
    {
        // Ne logger que si quelque chose a vraiment changé
        if ($model->isDirty()) {
            $this->logAndNotify($model, 'updated');
        }
    }

    /**
     * Handle model deleted event
     */
    public function deleted(Model $model)
    {
        $this->logAndNotify($model, 'deleted');
    }

    /**
     * Handle model restored event (soft delete)
     */
    public function restored(Model $model)
    {
        $this->logAndNotify($model, 'restored');
    }

    /**
     * Handle force deleted event
     */
    public function forceDeleted(Model $model)
    {
        $this->logAndNotify($model, 'forceDeleted');
    }

    /**
     * Fonction centrale : crée le log + déclenche la notification
     */
    private function logAndNotify(Model $model, string $action): void
    {

        if ($model instanceof LogActivite) {
            return; // ←←← ON NE LOG PAS LES LOGS !
        }
        try {
            // 1. Création du log dans la table log_activites
            $log = LogActivite::create([
                'user_id'        => auth()->id() ?? null, // Qui a fait l'action
                'action'         => $action,
                'model_type'     => get_class($model),
                'model_id'       => $model->getKey(),
                'old_values'     => $action === 'updated' ? json_encode($model->getOriginal()) : null,
                'new_values'     => $action === 'updated' ? json_encode($model->getAttributes()) : null,
                'ip_address'     => request()->ip(),
                'user_agent'     => request()->userAgent(),
                'custom_message' => $this->getCustomMessage($model, $action),
            ]);

            Log::info('Log activité créé avec succès', [
                'log_id'   => $log->id,
                'model'    => get_class($model),
                'model_id' => $model->getKey(),
                'action'   => $action
            ]);



            // 2. Déterminer message personnalisé et destinataires supplémentaires
            $customMessage = $this->getCustomMessage($model, $action);
            $additionalRecipients = $this->getAdditionalRecipients($model, $action);

            // 3. Déclencher l'événement (avec léger délai pour transaction DB)
            dispatch(function () use ($model, $action, $customMessage, $additionalRecipients) {
                event(new ModelActivityEvent(
                    $model,
                    $action,
                    $customMessage,
                    $additionalRecipients
                ));
            })->delay(now()->addSeconds(1)); // 1 seconde suffit largement

        } catch (\Exception $e) {
            Log::error('Échec création log activité', [
                'model'   => get_class($model),
                'action'  => $action,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Message personnalisé selon modèle et action
     */
    private function getCustomMessage($model, string $action): ?string
    {
        return match (true) {

            $model instanceof DemandeConge => match ($action) {
                'created' => "Nouvelle demande de congé soumise",
                'updated' => "Demande modifiée (Statut: {$model->statut})",
                'deleted' => "Demande de congé supprimée",
                default => null,
            },

            default => null,
        };
    }

    /**
     * Destinataires supplémentaires
     */
    private function getAdditionalRecipients($model, string $action): array
    {
        $recipients = [];

        // Cas feuille de temps
        if ($model instanceof DemandeConge) {
            // Si la feuille est soumise → notifier le manager/responsable de l'utilisateur
            if ($action === 'created' || ($action === 'updated' && $model->isDirty('statut') && $model->statut === 'soumis')) {
                if ($model->user && $model->user->manager_id) {
                    $recipients[] = $model->user->manager_id;
                }

                // Optionnel : notifier aussi tous les "Directeur Général" ou rôle spécifique
                 $directeurs = User::role(['Directeur Général', 'admin', 'super-admin'])
                  ->pluck('id')
                  ->toArray();
                 $recipients = array_merge($recipients, $directeurs);
            }

            // Si validée / refusée → notifier l'employé concerné
            if ($action === 'updated' && $model->isDirty('statut')) {
                if (in_array($model->statut, ['validé', 'refusé'])) {
                    $recipients[] = $model->user_id; // l'employé qui a fait la feuille
                }
            }
        }

        // Cas congé (déjà partiellement présent)
        if ($model instanceof DemandeConge) {
            if ($action === 'created' || ($action === 'updated' && $model->isDirty('statut') && $model->statut === 'en_attente')) {
                // Manager ou responsable des congés
                if ($model->user && $model->user->manager_id) {
                    $recipients[] = $model->user->manager_id;
                }
            }

            // Validation → notifier le demandeur
            if ($action === 'updated' && $model->isDirty('statut')) {
                if (in_array($model->statut, ['approuve', 'refuse'])) {
                    $recipients[] = $model->user_id;
                }
            }
        }

        return array_unique($recipients);
    }
}
