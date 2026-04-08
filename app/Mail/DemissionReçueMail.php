<?php
namespace App\Mail;

use App\Models\DemandeDemission;
use App\Models\DemandeAttestation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

// ─────────────────────────────────────────────────────────────────────────────
// 1. DemissionReçueMail  — Notifie le DG/RH qu'une démission a été soumise
// ─────────────────────────────────────────────────────────────────────────────
class DemissionReçueMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public DemandeDemission $demande) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '[COFIMA] Nouvelle lettre de démission — ' . $this->demande->user->username);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.demission.recue', with: ['demande' => $this->demande, 'employe' => $this->demande->user]);
    }
}
