<?php

namespace App\Services;

use App\Mail\DemissionSoumiseMail;
use App\Mail\DemissionRecuParSecretaireMail;
use App\Mail\DemissionRecuParDgMail;
use App\Mail\DemissionRecuParRhMail;
use App\Models\DemandeDemission;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class DemissionMailService
{
        public static function envoyerConfirmationSoumission(DemandeDemission $demande): void
    {
        $employe = $demande->user;

        $secretaireEmails = config('cofima.email_secretaire');
        $secretaireEmails = is_array($secretaireEmails) ? $secretaireEmails : [$secretaireEmails];

        $dgEmails = config('cofima.email_dg');
        $dgEmails = is_array($dgEmails) ? $dgEmails : [$dgEmails];

        $rhEmail = config('cofima.email_rh');

        // Employé
        Mail::to($employe->email)->send(new DemissionSoumiseMail($demande));

        // Secrétaires
        foreach ($secretaireEmails as $email) {
            if ($email !== $employe->email) {
                Mail::to($email)->send(new DemissionRecuParSecretaireMail($demande));
            }
        }

        // DG(s)
        foreach ($dgEmails as $email) {
            if ($email !== $employe->email && !in_array($email, $secretaireEmails)) {
                Mail::to($email)->send(new DemissionRecuParDgMail($demande));
            }
        }

        // RH
        if ($rhEmail && $rhEmail !== $employe->email && !in_array($rhEmail, $secretaireEmails) && !in_array($rhEmail, $dgEmails)) {
            Mail::to($rhEmail)->send(new DemissionRecuParRhMail($demande));
        }
    }
}