<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Models\DemandeConge;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Support\Facades\Log;
class RequestFinalValidationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */

    public DemandeConge $demande;
    public User $superieurhierarchique;
    public User $superieur;
    public ?String $commentaire;
    public function __construct(DemandeConge $demande, User $superieurhierarchique, User $superieur, ?String $commentaire = null)
    {
        $this->demande = $demande;
        $this->superieurhierarchique = $superieurhierarchique;
        $this->superieur = $superieur;
        $this->commentaire = $commentaire;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Demande de congé en attente de validation finale',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.request_final_validation',
            with: [
                'demande'                => $this->demande,
                'superieurHierarchique'  => $this->superieurhierarchique,
                'superieur'              => $this->superieur,
                'commentaire'            => $this->commentaire,
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
        // ── Génération du PDF à la volée ──────────────────────────────────
        $pdf = Pdf::loadView('pdfs.leave_request', [
            'leave'                  => $this->demande,
            'superieurHierarchique'  => $this->superieurhierarchique,
            'superieur'              => $this->superieur,
            'commentaire'            => $this->commentaire,
        ]);

        $nomFichier = 'lettre_conge_'
            . ($this->demande->user->nom ?? $this->demande->user->username)
            . '_' . $this->demande->id
            . '.pdf';

        return [
            Attachment::fromData(
                fn () => $pdf->output(),   // contenu binaire du PDF
                $nomFichier
            )->withMime('application/pdf'),
        ];
    }
}
