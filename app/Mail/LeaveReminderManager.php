<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LeaveReminderManager extends Mailable
{
    use Queueable, SerializesModels;

    public $manager;
    public $demandes;

    public function __construct($manager, $demandes)
    {
        $this->manager  = $manager;
        $this->demandes = $demandes;
    }

    public function build()
    {
        return $this->subject('[Rappel] Demandes de congé en attente de votre validation')
                    ->view('emails.reminder-manager');
    }
}