<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;

class MissingTimesheetReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected array $missingDays
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $daysList = collect($this->missingDays)
            ->map(fn($d) => Carbon::parse($d)->translatedFormat('l d F Y'))
            ->implode(', ');

        return (new MailMessage)
            ->subject('⏰ Rappel — Feuilles de temps manquantes')
            ->greeting("Bonjour {$notifiable->prenom},")
            ->line("Vous avez **" . count($this->missingDays) . " jour(s)** sans feuille de temps cette semaine :")
            ->line("**{$daysList}**")
            ->action('Saisir mes feuilles', route('daily-entries.create'))
            ->line("Pensez à remplir vos feuilles de temps avant la fin de la semaine pour faciliter la validation par votre supérieur.")
            ->salutation("L'équipe RH");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'         => 'missing_timesheet',
            'missing_days' => $this->missingDays,
            'count'        => count($this->missingDays),
            'message'      => count($this->missingDays) . " feuille(s) de temps manquante(s) cette semaine.",
        ];
    }
}