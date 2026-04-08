<?php

namespace App\Mail;

use App\Models\DemandeAttestation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

// ─────────────────────────────────────────────────────────────────────────────
// AttestationSoumiseMail — Confirme à l'employé que sa demande a été reçue
// ─────────────────────────────────────────────────────────────────────────────
class AttestationSoumiseMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public DemandeAttestation $demande) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '[COFIMA] Confirmation de votre demande d\'attestation');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.attestation.soumise',
            with: ['demande' => $this->demande, 'employe' => $this->demande->user]
        );
    }

    public function attachments(): array
    {
        $pdf = Pdf::loadView('pdfs.attestation_request', [
            'demande' => $this->demande,
            'employe' => $this->demande->user,
        ]);

        $nomFichier = 'demande_attestation_' . $this->demande->numero_reference . '.pdf';

        return [
            Attachment::fromData(
                fn () => $pdf->output(),
                $nomFichier
            )->withMime('application/pdf'),
        ];
    }
}
