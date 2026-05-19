<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Models\DemandeAttestation;
use App\Models\DemandeDemission;
use App\Models\User;
use App\Mail\AttestationReminderMail;
use App\Mail\DemissionReminderMail;

class SendAttestationCertificatReminders extends Command
{
    protected $signature   = 'attestations:send-reminders';
    protected $description = 'Rappels automatiques (2x/jour) : attestations et certificats en attente → DG/RH/Admin';

    public function handle()
    {
        $hasPending = false;

        // ── Attestations en_attente → DG / RH / Admin ────────────────────────
        $attestations = DemandeAttestation::with('user')
            ->where('statut', 'en_attente')
            ->get();

        if ($attestations->isNotEmpty()) {
            $hasPending = true;
            $this->envoyerAuxValidateurs(
                fn($dest) => new AttestationReminderMail($dest, $attestations),
                'Attestations',
                $attestations->count()
            );
        } else {
            $this->line('Aucune attestation en_attente.');
        }

        // ── Démissions/Certificats en_attente → DG / RH / Admin ──────────────
        $demissions = DemandeDemission::with('user')
            ->where('statut', 'en_attente')
            ->get();

        if ($demissions->isNotEmpty()) {
            $hasPending = true;
            $this->envoyerAuxValidateurs(
                fn($dest) => new DemissionReminderMail($dest, $demissions),
                'Certificats/Démissions',
                $demissions->count()
            );
        } else {
            $this->line('Aucun certificat/démission en_attente.');
        }

        $this->info($hasPending ? 'Rappels attestations/certificats envoyés.' : 'Aucun rappel à envoyer.');
        return Command::SUCCESS;
    }

    private function envoyerAuxValidateurs(callable $mailFactory, string $label, int $count): void
    {
        $destinataires = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['directeur-general', 'rh', 'admin']);
        })->get();

        foreach ($destinataires as $dest) {
            if ($dest->email) {
                Mail::to($dest->email)->send($mailFactory($dest));
                $this->info("{$label} → {$dest->email} ({$count} demande(s))");
            }
        }
    }
}
