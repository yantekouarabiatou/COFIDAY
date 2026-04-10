<?php

namespace App\Mail;

use App\Models\DemandeDemission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

// ─────────────────────────────────────────────────────────────────────────────
// 3. CertificatTravailMail  — Envoyé à l'employé + secrétaire après approbation
// ─────────────────────────────────────────────────────────────────────────────
class CertificatTravailMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public DemandeDemission $demande, public $dateDepart = null) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '[COFIMA] Votre certificat de travail — Réf. ' . $this->demande->numero_certificat);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.demission.certificat', with: [
            'demande' => $this->demande,
            'employe' => $this->demande->user,
            'dateDepart' => $this->dateDepart
        ]);
    }

    public function build()
    {
        return $this->view('emails.demission.certificat')
                    ->with([
                        'demande' => $this->demande,
                        'employe' => $this->demande->user,
                        'dateDepart' => $this->dateDepart
                    ])
                    ->subject('[COFIMA] Votre certificat de travail — Réf. ' . $this->demande->numero_certificat);
    }

    public function attachments(): array
    {
        try {
            Log::info('Génération du PDF certificat de travail', [
                'demande_id' => $this->demande->id,
                'numero_certificat' => $this->demande->numero_certificat
            ]);

            $pdf = Pdf::loadView('emails.demission.certificat', [
                'demande' => $this->demande,
                'employe' => $this->demande->user,
                'dateDepart' => $this->dateDepart
            ]);

            $filename = 'Certificat_Travail_' . $this->demande->numero_certificat . '.pdf';

            Log::info('PDF généré avec succès', [
                'demande_id' => $this->demande->id,
                'filename' => $filename
            ]);

            return [
                Attachment::fromData(fn () => $pdf->output(), $filename)
                    ->withMime('application/pdf')
            ];
        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération du PDF certificat', [
                'demande_id' => $this->demande->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Pour le débogage, on retourne un tableau vide au lieu de planter
            // TODO: Remettre les pièces jointes après résolution du problème
            return [];
        }
    }
}
