<?php

namespace App\Mail;

use App\Models\DemandeDemission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

// ─────────────────────────────────────────────────────────────────────────────
// 3. CertificatTravailMail  — Envoyé à l'employé + secrétaire après approbation
// ─────────────────────────────────────────────────────────────────────────────
class CertificatTravailMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public DemandeDemission $demande) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '[COFIMA] Votre certificat de travail — Réf. ' . $this->demande->certificat_reference);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.demission.certificat', with: ['demande' => $this->demande, 'employe' => $this->demande->user]);
    }
}
