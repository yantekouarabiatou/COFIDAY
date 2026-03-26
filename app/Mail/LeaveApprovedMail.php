<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\DemandeConge;
use App\Models\SoldeConge;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Mail\Mailables\Attachment;

use Illuminate\Support\Collection;

class LeaveApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public DemandeConge $demande;
    public Collection   $soldes;
    public array        $anneesPrelevees;
    public string       $dateRepriseFormatee;
    public string       $numeroNote;
    public ?string      $commentaire;
    public function __construct(DemandeConge $demande, Collection $soldes, array $anneesPrelevees, string $dateRepriseFormatee, string $numeroNote, ?string $commentaire = null)
    {
        $this->demande = $demande;
        $this->soldes = $soldes;
        $this->anneesPrelevees = $anneesPrelevees;
        $this->dateRepriseFormatee = $dateRepriseFormatee;
        $this->numeroNote = $numeroNote;
        $this->commentaire = $commentaire;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre demande de congé a été approuvée'
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.leave_approved',
            with: [
                'demande' => $this->demande,
                'commentaire' => $this->commentaire,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $logoPath   = public_path('storage/photos/logo-cofima-bon.jpg');
        $logoBase64 = file_exists($logoPath)
            ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        // ── Construction de $soldesParAnnee ──────────────────────────────────
        $soldesParAnnee = $this->soldes->keyBy('annee')->map(fn($s) => $s->jours_restants)->toArray();

        $pdf = Pdf::loadView('pdfs.recapitulatif_approbation', [
            'demande'             => $this->demande,
            'soldes'              => $this->soldes,
            'anneesPrelevees'     => $this->anneesPrelevees,
            'soldesParAnnee'      => $soldesParAnnee, // ← Ajout ici
            'dateRepriseFormatee' => $this->dateRepriseFormatee,
            'numeroNote'          => $this->numeroNote,
            'logoBase64'          => $logoBase64,
        ]);

        $nomFichier = 'approbation_conge_'
            . ($this->demande->user->nom ?? $this->demande->user->name)
            . '_' . now()->format('Y')
            . '.pdf';

        return [
            Attachment::fromData(
                fn () => $pdf->output(),
                $nomFichier
            )->withMime('application/pdf'),
        ];
    }
}
