<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AttestationReminderMail extends Mailable
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
        return $this->subject('[Rappel] ' . $this->demandes->count() . ' demande(s) d\'attestation en attente')
                    ->view('emails.reminder-attestation');
    }
}
