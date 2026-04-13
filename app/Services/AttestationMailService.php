<?php

namespace App\Services;

use App\Mail\AttestationSoumiseMail;
use App\Mail\AttestationRecuParSecretaireMail;
use App\Mail\AttestationRecuParDgMail;
use App\Models\DemandeAttestation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class AttestationMailService
{
    /**
     * Envoyer les emails lors de la soumission d'une demande d'attestation :
     * - Email à l'employé (confirmation de réception)
     * - Email à la secrétaire (notification avec pièce jointe)
     * - Email au Directeur Général (notification avec pièce jointe)
     */
    public static function envoyerConfirmationSoumission(DemandeAttestation $demande): void
    {
        $user = $demande->user;
        $emailSecretaire = self::getSecretaireEmail();
        $emailDG = self::getDgEmail();

        Log::info('AttestationMailService: Début envoi des emails', [
            'demande_id' => $demande->id,
            'employe' => $user->email,
            'secretaire' => $emailSecretaire,
            'dg' => $emailDG,
        ]);

        // 1. Email à l'employé (confirmation de réception)
        try {
            Mail::to($user->email)->send(new AttestationSoumiseMail($demande));
            Log::info('Email employé envoyé', ['email' => $user->email]);
        } catch (\Exception $e) {
            Log::error('Erreur envoi email employé', [
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
            // On ne relance pas l'exception pour ne pas bloquer les autres envois
        }

        // 2. Email à la secrétaire (si email valide et différent de l'employé)
        if ($emailSecretaire && $emailSecretaire !== $user->email) {
            try {
                Mail::to($emailSecretaire)->send(new AttestationRecuParSecretaireMail($demande));
                Log::info('Email secrétaire envoyé', ['email' => $emailSecretaire]);
            } catch (\Exception $e) {
                Log::error('Erreur envoi email secrétaire', [
                    'email' => $emailSecretaire,
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            Log::warning('Email secrétaire manquant ou identique à celui de l\'employé', [
                'secretaire' => $emailSecretaire,
            ]);
        }

        // 3. Email au Directeur Général (si email valide et différent des précédents)
        if ($emailDG && $emailDG !== $user->email && $emailDG !== $emailSecretaire) {
            try {
                Mail::to($emailDG)->send(new AttestationRecuParDgMail($demande));
                Log::info('Email DG envoyé', ['email' => $emailDG]);
            } catch (\Exception $e) {
                Log::error('Erreur envoi email DG', [
                    'email' => $emailDG,
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            Log::warning('Email DG manquant ou identique à employé/secrétaire', [
                'dg' => $emailDG,
            ]);
        }

        Log::info('AttestationMailService: Fin envoi des emails');
    }

    /**
     * Récupère l'email de la secrétaire (compte générique défini dans la config)
     */
    private static function getSecretaireEmail(): ?string
    {
        return config('cofima.email_secretaire', 'cofima@cofima.cc');
    }

    /**
     * Récupère l'email du Directeur Général (rôle ou poste, avec fallback config)
     */
    private static function getDgEmail(): ?string
    {
        // 1. Par rôle
        $dg = User::role('directeur-general')->value('email');
        if ($dg) {
            return $dg;
        }
        // 2. Par intitulé de poste
        $dg = User::whereHas('poste', function ($query) {
            $query->whereIn('intitule', [
                'DIRECTEUR GENERAL',
                'DIRECTEUR GÉNÉRAL',
                'DIRECTEUR GENERALE',
                'DG',
            ]);
        })->value('email');
        // 3. Fallback config
        return $dg ?: config('cofima.email_dg');
    }
}
