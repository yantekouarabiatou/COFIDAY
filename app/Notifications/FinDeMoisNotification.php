<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class FinDeMoisNotification extends Notification
{
    public function __construct(public int $mois) {}

    /**
     * Détermine les canaux selon le rôle (admin/RH uniquement)
     */
    public function via($notifiable): array
    {
        // Adapter selon votre système de rôles
        if (! in_array($notifiable->role, ['admin', 'manager', 'rh'])) {
            return []; // Aucune diffusion
        }

        return ['mail', 'database'];
    }

    /**
     * Envoi de l'email via une vue personnalisée
     */
    public function toMail($notifiable)
    {
        // Préparez les données à passer à la vue
        $mois = $this->mois; // Par exemple, "Mars 2025" ou un numéro de mois
        // Vous pouvez générer des libellés de mois, dates de début/fin, etc.
        // Exemple simplifié :
        $debut_periode = now()->startOfMonth()->format('d/m/Y');
        $fin_periode = now()->endOfMonth()->format('d/m/Y');

        return (new MailMessage)
            ->subject('Rappel : validez les feuilles de temps')
            ->view('emails.fin_de_mois', [
                'mois'           => $mois,
                'prenom'         => $notifiable->prenom ?? null,
                'nom'            => $notifiable->name ?? $notifiable->nom ?? '',
                'debut_periode'  => $debut_periode,
                'fin_periode'    => $fin_periode,
                'statut'         => 'Soumis', // Vous pouvez calculer un vrai statut
                'url'            => url('/daily-entries'),
            ]);
    }

    /**
     * Notification en base de données
     */
    public function toArray($notifiable): array
    {
        return [
            'message' => 'Pensez à valider les feuilles de temps du mois.',
            'mois'    => $this->mois,
        ];
    }
}
