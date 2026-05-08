<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Models\DemandeConge;
use App\Models\User;
use App\Mail\LeaveReminderManager;
use App\Mail\LeaveReminderFinal;

class SendLeaveReminders extends Command
{
    protected $signature   = 'leaves:send-reminders';
    protected $description = 'Envoie des rappels aux managers et DG/RH sur les demandes en attente';

    public function handle()
    {
        // 1. Rappels pour les managers (demandes en_attente)
        $demandesEnAttente = DemandeConge::with(['user', 'typeConge'])
            ->where('statut', 'en_attente')
            ->get();

        if ($demandesEnAttente->isNotEmpty()) {
            // Regrouper par manager (superieur_hierarchique_id)
            $groupedByManager = $demandesEnAttente->groupBy('superieur_hierarchique_id');

            foreach ($groupedByManager as $managerId => $demandes) {
                $manager = User::find($managerId);
                if ($manager && $manager->email) {
                    Mail::to($manager->email)->send(new LeaveReminderManager($manager, $demandes));
                    $this->info("Rappel envoyé au manager : {$manager->email}");
                }
            }
        }

        // 2. Rappels pour le grand supérieur (DG/RH) : demandes pre_approuve
        $demandesPreApprouvees = DemandeConge::with(['user', 'typeConge', 'superieurHierarchique'])
            ->where('statut', 'pre_approuve')
            ->get();

        if ($demandesPreApprouvees->isNotEmpty()) {
            // Envoyer à un ou plusieurs destinataires (DG, RH)
            $destinataires = User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['directeur-general', 'rh', 'admin']);
            })->get();

            foreach ($destinataires as $destinataire) {
                if ($destinataire->email) {
                    Mail::to($destinataire->email)->send(new LeaveReminderFinal($destinataire, $demandesPreApprouvees));
                    $this->info("Rappel envoyé au grand supérieur : {$destinataire->email}");
                }
            }
        }

        $this->info('Tous les rappels ont été envoyés.');
    }
}