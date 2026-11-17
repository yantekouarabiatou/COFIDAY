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

class CertificatTravailMail extends Mailable
{
    use Queueable, SerializesModels;

    private string $logoBase64 = '';

    public function __construct(public DemandeDemission $demande, public $dateDepart = null)
    {
        $logoPath = storage_path('app/public/photos/logo-cofima-bon.jpg');
        if (file_exists($logoPath)) {
            $this->logoBase64 = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath));
        }
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: '[COFIMA] Votre certificat de travail  Réf. ' . $this->demande->numero_certificat);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.demission.certificat', with: [
            'demande'    => $this->demande,
            'employe'    => $this->demande->user,
            'dateDepart' => $this->dateDepart,
            'logoBase64' => $this->logoBase64,
        ]);
    }

    public function build()
    {
        return $this->view('emails.demission.certificat')
                    ->with([
                        'demande'    => $this->demande,
                        'employe'    => $this->demande->user,
                        'dateDepart' => $this->dateDepart,
                        'logoBase64' => $this->logoBase64,
                    ])
                    ->subject('[COFIMA] Votre certificat de travail  Réf. ' . $this->demande->numero_certificat);
    }

    public function attachments(): array
    {
        try {
            Log::info('Génération du PDF certificat de travail', [
                'demande_id'       => $this->demande->id,
                'numero_certificat' => $this->demande->numero_certificat,
            ]);

            $pdf = Pdf::loadView('emails.demission.certificat', [
                'demande'    => $this->demande,
                'employe'    => $this->demande->user,
                'dateDepart' => $this->dateDepart,
                'logoBase64' => $this->logoBase64,
            ]);

            $filename = 'Certificat_Travail_' . $this->demande->numero_certificat . '.pdf';

            Log::info('PDF généré avec succès', [
                'demande_id' => $this->demande->id,
                'filename'   => $filename,
            ]);

            return [
                Attachment::fromData(fn () => $pdf->output(), $filename)
                    ->withMime('application/pdf'),
            ];
        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération du PDF certificat', [
                'demande_id' => $this->demande->id,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);

            return [];
        }
    }
}
