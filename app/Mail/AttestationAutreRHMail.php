<?php
// ══════════════════════════════════════════════════════════════════════════════
// FICHIER : app/Mail/  — À diviser en fichiers séparés dans votre projet
// Contient les 5 classes Mail du module Démission + Attestation
// ══════════════════════════════════════════════════════════════════════════════

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

// ─────────────────────────────────────────────────────────────────────────────
// 2. DemissionRefuseeMail  — Notifie l'employé du refus
// ─────────────────────────────────────────────────────────────────────────────
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

// ─────────────────────────────────────────────────────────────────────────────
// 3. CertificatTravailMail  — Envoyé à l'employé + secrétaire après approbation
// ─────────────────────────────────────────────────────────────────────────────
class CertificatTravailMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public DemandeDemission $demande) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '[COFIMA] Votre certificat de travail — Réf. ' . $this->demande->certificat_reference);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.demission.certificat', with: ['demande' => $this->demande, 'employe' => $this->demande->user]);
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// 4. AttestationApprouvéeMail  — Employé + secrétaire reçoivent l'attestation
// ─────────────────────────────────────────────────────────────────────────────
class AttestationApprouvéeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public DemandeAttestation $demande) {}

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
        return new Content(view: 'emails.attestation.approuvee', with: ['demande' => $this->demande, 'employe' => $this->demande->user]);
    }
}

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

// ─────────────────────────────────────────────────────────────────────────────
// 6. AttestationAutreRHMail  — Notifie le RH pour rédaction manuelle
// ─────────────────────────────────────────────────────────────────────────────
class AttestationAutreRHMail extends Mailable
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
