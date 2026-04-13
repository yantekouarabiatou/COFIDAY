<?php

namespace App\Services;

use App\Mail\DemissionSoumiseMail;
use App\Mail\DemissionRecuParSecretaireMail;
use App\Mail\DemissionRecuParDgMail;
use App\Mail\DemissionRecuParRhMail;
use App\Models\DemandeDemission;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class DemissionMailService
{
    /**
     * Envoyer les emails différenciés pour la soumission d'une demande de démission
     * - Employé : confirmation de réception
     * - Secrétaire : notification avec récapitulatif
     * - Directeur Général : notification pour validation
     * - RH : notification pour suivi
     */
    public static function envoyerConfirmationSoumission(DemandeDemission $demande): void
    {
        $employe = $demande->user;
        $emailSecretaire = self::getSecretaireEmail(); // depuis config, pas base
        $emailDG = self::getDgEmail();
        $emailRH = self::getRhEmail();

        Log::info('DemissionMailService: Début envoi emails différenciés', [
            'demande_id' => $demande->id,
            'employe' => $employe->email,
            'secretaire' => $emailSecretaire,
            'dg' => $emailDG,
            'rh' => $emailRH,
        ]);

        // 1. Email à l'employé
        try {
            Mail::to($employe->email)->send(new DemissionSoumiseMail($demande));
            Log::info('Email employé envoyé', ['email' => $employe->email]);
        } catch (\Exception $e) {
            Log::error('Erreur envoi email employé démission', [
                'email' => $employe->email,
                'error' => $e->getMessage(),
            ]);
        }

        // 2. Email à la secrétaire (compte générique)
        if ($emailSecretaire && $emailSecretaire !== $employe->email) {
            try {
                Mail::to($emailSecretaire)->send(new DemissionRecuParSecretaireMail($demande));
                Log::info('Email secrétaire envoyé', ['email' => $emailSecretaire]);
            } catch (\Exception $e) {
                Log::error('Erreur envoi email secrétaire démission', [
                    'email' => $emailSecretaire,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // 3. Email au DG
        if ($emailDG && $emailDG !== $employe->email && $emailDG !== $emailSecretaire) {
            try {
                Mail::to($emailDG)->send(new DemissionRecuParDgMail($demande));
                Log::info('Email DG envoyé', ['email' => $emailDG]);
            } catch (\Exception $e) {
                Log::error('Erreur envoi email DG démission', [
                    'email' => $emailDG,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // 4. Email au RH
        if ($emailRH && $emailRH !== $employe->email && $emailRH !== $emailSecretaire && $emailRH !== $emailDG) {
            try {
                Mail::to($emailRH)->send(new DemissionRecuParRhMail($demande));
                Log::info('Email RH envoyé', ['email' => $emailRH]);
            } catch (\Exception $e) {
                Log::error('Erreur envoi email RH démission', [
                    'email' => $emailRH,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('DemissionMailService: Fin envoi emails');
    }

    /**
     * Email de la secrétaire (uniquement depuis la configuration, pas depuis la base)
     */
    private static function getSecretaireEmail(): ?string
    {
        return config('cofima.email_secretaire', 'cofima@cofima.cc');
    }

    /**
     * Email du Directeur Général (rôle ou poste, fallback config)
     */
    private static function getDgEmail(): ?string
    {
        $dg = User::role('directeur-general')->value('email');
        if ($dg) {
            return $dg;
        }
        $dg = User::whereHas('poste', function ($q) {
            $q->whereIn('intitule', ['DIRECTEUR GENERAL', 'DIRECTEUR GÉNÉRAL', 'DIRECTEUR GENERALE', 'DG']);
        })->value('email');
        return $dg ?: config('cofima.email_dg');
    }

    /**
     * Email du Responsable RH (rôle ou poste, fallback config)
     */
    private static function getRhEmail(): ?string
    {
        $rh = User::role('rh')->value('email');
        if ($rh) {
            return $rh;
        }
        $rh = User::whereHas('poste', function ($q) {
            $q->whereIn('intitule', ['RH', 'Ressources Humaines', 'Responsable RH', 'Responsable Ressources Humaines']);
        })->value('email');
        return $rh ?: config('cofima.email_rh');
    }
}
