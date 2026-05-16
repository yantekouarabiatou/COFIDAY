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
// 4. AttestationApprouvéeMail  — Employé + secrétaire reçoivent l'attestation
// ─────────────────────────────────────────────────────────────────────────────
class AttestationApprouvéeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public DemandeAttestation $demande,
        public bool $enAttentePrepRH = false
    ) {}

    public function envelope(): Envelope
    {
        $sujet = match ($this->demande->type) {
            'attestation_simple'    => 'Votre attestation de travail simple est disponible',
            'attestation_banque'    => 'Votre attestation de travail (usage bancaire) est disponible',
            'attestation_ambassade' => 'Votre attestation de travail (ambassade) est disponible',
            default                 => 'Votre attestation de travail est disponible',
        };
        return new Envelope(subject: '[COFIMA] ' . $sujet . ' — Réf. ' . $this->demande->numero_reference);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.attestation.approuvee',
            with: [
                'demande' => $this->demande,
                'employe' => $this->demande->user,
                'enAttentePrepRH' => $this->enAttentePrepRH,
            ]
        );
    }

    public function attachments(): array
    {
        if ($this->enAttentePrepRH) {
            return [];
        }

        $logoPath   = storage_path('app/public/photos/logo-cofima-bon.jpg');
        $logoBase64 = file_exists($logoPath)
            ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        $pdf = Pdf::loadView('pdfs.attestation_letter', [
            'demande'    => $this->demande,
            'employe'    => $this->demande->user,
            'logoBase64' => $logoBase64,
        ]);

        $nomFichier = 'attestation_' . ($this->demande->user->nom ?? 'attestation')
            . '_' . now()->format('Ymd')
            . '.pdf';

        return [
            Attachment::fromData(
                fn () => $pdf->output(),
                $nomFichier
            )->withMime('application/pdf'),
        ];
    }
}
