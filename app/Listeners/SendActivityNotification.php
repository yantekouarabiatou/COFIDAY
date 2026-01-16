<?php

namespace App\Listeners;

use App\Events\ModelActivityEvent;
use App\Notifications\ActivityNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Illuminate\Support\Arr;

class SendActivityNotification
{
    /**
     * Handle the event.
     */
    public function handle(ModelActivityEvent $event): void
    {
        try {
            $model = $event->model;
            $action = $event->action;

            if (!$model) {
                Log::warning('Modèle null dans ModelActivityEvent', ['event' => $event]);
                return;
            }

            Log::info('Début envoi notification activité', [
                'model_type' => get_class($model),
                'model_id'   => $model->getKey(),
                'action'     => $action,
                'auth_user'  => auth()->id() ?? 'guest',
            ]);

            // 1. Récupérer TOUS les admins (super-admin + admin) via Spatie
            $admins = User::whereHas('roles', function ($query) {
                $query->whereIn('name', ['super-admin', 'admin']);
            })->get();

            Log::info('Admins trouvés via Spatie Permission', ['count' => $admins->count()]);

            // 2. Utilisateurs spécifiques liés au modèle (créateur, responsable, etc.)
            $specificUsers = $this->getSpecificRecipients($model);

            // 3. Utilisateurs additionnels passés dans l'événement (optionnel)
            $additionalUsers = collect();
            if (!empty($event->additionalRecipients)) {
                $additionalUsers = User::whereIn('id', Arr::wrap($event->additionalRecipients))
                    ->get();
            }

            // 4. Fusion et déduplication
            $recipients = $admins
                ->merge($specificUsers)
                ->merge($additionalUsers)
                ->unique('id')
                ->values();

            Log::info('Destinataires finaux avant exclusion', ['count' => $recipients->count()]);

            // 5. Exclure l'utilisateur courant s'il n'est pas admin (pour les créations)
            $currentUserId = auth()->id();
            if ($currentUserId && $action === 'created') {
                $currentUser = auth()->user();

                $isAdmin = $currentUser && $currentUser->hasAnyRole(['super-admin', 'admin']);

                if (!$isAdmin) {
                    $recipients = $recipients->filter(fn($user) => $user->id !== $currentUserId);
                    Log::info('Utilisateur courant exclu (non-admin lors de la création)', [
                        'excluded_id' => $currentUserId
                    ]);
                }
            }

            if ($recipients->isEmpty()) {
                Log::info('Aucun destinataire après filtrage et exclusion');
                return;
            }

            // 6. Envoi des notifications
            $notificationCount = 0;
            foreach ($recipients as $user) {
                try {
                    $user->notify(new ActivityNotification($model, $action, $event->customMessage ?? null));
                    $notificationCount++;

                    Log::debug('Notification envoyée avec succès', [
                        'to_user_id' => $user->id,
                        'model'      => class_basename($model),
                        'action'     => $action
                    ]);
                } catch (\Exception $e) {
                    Log::error('Échec envoi notification', [
                        'user_id' => $user->id,
                        'error'   => $e->getMessage(),
                        'trace'   => $e->getTraceAsString()
                    ]);
                }
            }

            Log::info('Fin traitement notifications', ['envoyées' => $notificationCount]);

        } catch (\Exception $e) {
            Log::critical('Erreur critique dans SendActivityNotification', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Récupère les utilisateurs spécifiques à notifier selon le modèle
     */
    private function getSpecificRecipients($model): Collection
    {
        $users = collect();

        try {
            // Cas 1 : relation 'user' directe (ex: TimeEntry, DailyEntry, DemandeConge)
            if (method_exists($model, 'user') && $model->user) {
                $users->push($model->user);
            }

            // Cas 2 : champ user_id classique
            elseif (property_exists($model, 'user_id') && $model->user_id) {
                $user = User::find($model->user_id);
                if ($user) {
                    $users->push($user);
                }
            }

            // Cas 3 : si le modèle a un créateur (created_by)
            if (property_exists($model, 'created_by') && $model->created_by) {
                $creator = User::find($model->created_by);
                if ($creator) {
                    $users->push($creator);
                }
            }

            // Optionnel : notifier le manager du principal concerné
            if ($users->isNotEmpty()) {
                $mainUser = $users->first();
                if ($mainUser && $mainUser->manager_id) {
                    $manager = User::find($mainUser->manager_id);
                    if ($manager) {
                        $users->push($manager);
                    }
                }
            }

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des destinataires spécifiques', [
                'model'   => get_class($model),
                'error'   => $e->getMessage()
            ]);
        }

        return $users->unique('id');
    }
}
