<?php
namespace App\Mail;

use App\Models\DemandeDemission;
use App\Models\DemandeAttestation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;


class DemissionRefuseeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public DemandeDemission $demande, public ?string $motifRefus = null) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '[COFIMA] Votre démission ne peut être acceptée pour le moment');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.demission.refusee', with: ['demande' => $this->demande, 'employe' => $this->demande->user, 'motifRefus' => $this->motifRefus]);
    }
}
