<?php

namespace App\Mail;

use App\Models\DemandeDemission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DemissionRecuParRhMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public DemandeDemission $demande) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '[COFIMA] Demande de démission pour suivi RH - ' . $this->demande->user->name);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.demission.recu-rh',
            with: [
                'demande' => $this->demande,
                'employe' => $this->demande->user
            ]
        );
    }
}
