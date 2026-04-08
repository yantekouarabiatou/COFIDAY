<?php

namespace App\Mail;

use App\Models\DemandeDemission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

// ─────────────────────────────────────────────────────────────────────────────
// DemissionSoumiseMail — Confirme à l'employé que sa lettre de démission a été reçue
// ─────────────────────────────────────────────────────────────────────────────
class DemissionSoumiseMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public DemandeDemission $demande) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '[COFIMA] Confirmation de réception de votre lettre de démission');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.demission.soumise',
            with: ['demande' => $this->demande, 'employe' => $this->demande->user]
        );
    }
}
