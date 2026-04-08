<?php
namespace App\Mail;
use App\Models\DemandeAttestation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;


class AttestationAutreNotifRHMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public DemandeAttestation $demande) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '[COFIMA Secrétaire] Nouvelle demande d\'attestation format spécifique — ' . $this->demande->user->username);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.attestation.autre_rh', with: ['demande' => $this->demande, 'employe' => $this->demande->user]);
    }
}
