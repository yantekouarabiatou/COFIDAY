<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LeaveReminderFinal extends Mailable
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
        return $this->subject('[Rappel] Demandes de congé en attente de validation finale')
                    ->view('emails.reminder-final');
    }
}
