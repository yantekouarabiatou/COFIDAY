<?php
namespace App\Mail;
use App\Models\DemandeAttestation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

// ─────────────────────────────────────────────────────────────────────────────
// 5. AttestationRefuseeMail
// ─────────────────────────────────────────────────────────────────────────────
class AttestationRefuseeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public DemandeAttestation $demande, public ?string $motifRefus = null) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '[COFIMA] Votre demande d\'attestation n\'a pas abouti');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.attestation.refusee', with: ['demande' => $this->demande, 'employe' => $this->demande->user, 'motifRefus' => $this->motifRefus]);
    }
}
