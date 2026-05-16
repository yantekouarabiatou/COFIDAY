<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DemissionReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $destinataire;
    public $demandes;

    public function __construct($destinataire, $demandes)
    {
        $this->destinataire = $destinataire;
        $this->demandes     = $demandes;
    }

    public function build()
    {
        return $this->subject('[Rappel] ' . $this->demandes->count() . ' démission(s) en attente de traitement')
                    ->view('emails.reminder-demission');
    }
}
