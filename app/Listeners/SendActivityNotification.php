<?php

namespace App\Listeners;

use App\Events\ModelActivityEvent;
use App\Notifications\ActivityNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class SendActivityNotification
{
    public function handle(ModelActivityEvent $event)
    {
        try {
            $model = $event->model;
            $action = $event->action;

            if (!$model) {
                return;
            }

            Log::info('Notification gestion du temps', [
                'model' => get_class($model),
                'id' => $model->getKey(),
                'action' => $action,
            ]);

            // 1. Admins reçoivent tout
            $admins = User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['super-admin', 'admin']);
            })->get();

            // 2. Utilisateurs concernés selon le modèle
            $specificUsers = $this->getSpecificRecipients($model);

            // Fusion
            $recipients = collect($admins)->merge($specificUsers);

            // 3. Exclure l'utilisateur courant seulement sur création (sauf admins)
            $currentUserId = auth()->id();
            if ($currentUserId && $this->shouldExcludeCurrentUser($action)) {
                $recipients = $recipients->filter(fn($user) => $user->id !== $currentUserId);
            }

            // Unicité
            $recipients = $recipients->unique('id')->values();

            if ($recipients->isEmpty()) {
                return;
            }

            foreach ($recipients as $user) {
                try {
                    $user->notify(new ActivityNotification($model, $action));
                    Log::info('Notification envoyée', ['user_id' => $user->id, 'action' => $action]);
                } catch (\Exception $e) {
                    Log::error('Échec notification', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

        } catch (\Exception $e) {
            Log::error('Erreur critique notification gestion temps', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function getSpecificRecipients($model): Collection
    {
        return match (true) {
            $model instanceof \App\Models\DemandeConge => $this->getDailyEntryRecipients($model),
            $model instanceof \App\Models\DailyEntry => $this->getDailyEntryRecipients($model),
            $model instanceof \App\Models\TimeEntry  => $this->getTimeEntryRecipients($model),
            $model instanceof \App\Models\Dossier    => $this->getDossierRecipients($model),
            $model instanceof \App\Models\Client     => collect(), // pas d'utilisateur spécifique
            default                                  => $this->getDefaultRecipients($model),
        };
    }

    private function getDailyEntryRecipients($dailyEntry): Collection
    {
        $users = collect();

        if ($dailyEntry->user) {
            $users->push($dailyEntry->user);
        }

        return $users->unique('id');
    }

    private function getTimeEntryRecipients($timeEntry): Collection
    {
        $users = collect();

        // Celui qui a saisi l'activité
        if ($timeEntry->user) {
            $users->push($timeEntry->user);
        }

        // Celui de la feuille parent (au cas où un manager modifie)
        if ($timeEntry->dailyEntry?->user && $timeEntry->dailyEntry->user_id !== $timeEntry->user_id) {
            $users->push($timeEntry->dailyEntry->user);
        }

        return $users->unique('id');
    }

    private function getDossierRecipients($dossier): Collection
    {
        // Tous les utilisateurs ayant saisi du temps sur ce dossier
        return $dossier->timeEntries->pluck('user')->filter()->unique('id');
    }

    private function getDefaultRecipients($model): Collection
    {
        $users = collect();

        if (property_exists($model, 'user_id') && $model->user_id) {
            $user = $model->user ?? User::find($model->user_id);
            if ($user) {
                $users->push($user);
            }
        }

        return $users;
    }

    private function shouldExcludeCurrentUser(string $action): bool
    {
        $currentUser = auth()->user();

        if (!$currentUser) {
            return false;
        }

        if ($currentUser->hasRole(['super-admin', 'admin'])) {
            return false; // admins reçoivent tout
        }

        return $action === 'created'; // les autres ne reçoivent pas sur leur propre création
    }
}
