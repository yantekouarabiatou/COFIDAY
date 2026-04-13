<?php

namespace App\Mail;

use App\Models\DemandeDemission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DemissionRecuParSecretaireMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public DemandeDemission $demande) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '[COFIMA] Nouvelle demande de démission - ' . $this->demande->user->name);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.demission.recu-secretaire',
            with: [
                'demande' => $this->demande,
                'employe' => $this->demande->user
            ]
        );
    }
}
