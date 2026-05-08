<?php

namespace App\Services;

use App\Mail\AttestationSoumiseMail;
use App\Mail\AttestationRecuParSecretaireMail;
use App\Mail\AttestationRecuParDgMail;
use App\Models\DemandeAttestation;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class AttestationMailService
{
    public static function envoyerConfirmationSoumission(DemandeAttestation $demande): void
{
    $user = $demande->user;

    // Secrétaire : peut être multiple
    $secretaireEmails = config('cofima.email_secretaire');
    if (!is_array($secretaireEmails)) {
        $secretaireEmails = [$secretaireEmails];
    }
    $secretaireEmails = array_filter($secretaireEmails);

    // DG : peut être multiple
    $dgEmails = config('cofima.email_dg');
    if (!is_array($dgEmails)) {
        $dgEmails = [$dgEmails];
    }
    $dgEmails = array_filter($dgEmails);

    // 1. Employé
    Mail::to($user->email)->send(new AttestationSoumiseMail($demande));

    // 2. Secrétaire(s)
    foreach ($secretaireEmails as $email) {
        if ($email !== $user->email) {
            Mail::to($email)->send(new AttestationRecuParSecretaireMail($demande));
        }
    }

    // 3. DG(s)
    foreach ($dgEmails as $email) {
        if ($email !== $user->email && !in_array($email, $secretaireEmails)) {
            Mail::to($email)->send(new AttestationRecuParDgMail($demande));
        }
    }
}
}