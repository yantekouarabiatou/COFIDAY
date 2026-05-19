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
    protected $description = 'Rappels quotidiens : congés en_attente (managers) et pre_approuve (DG/RH/Admin)';

    public function handle()
    {
        // ── 1. Congés en_attente → managers ──────────────────────────────────
        $congesEnAttente = DemandeConge::with(['user', 'typeConge'])
            ->where('statut', 'en_attente')
            ->get();

        if ($congesEnAttente->isNotEmpty()) {
            $groupedByManager = $congesEnAttente->groupBy('superieur_hierarchique_id');

            foreach ($groupedByManager as $managerId => $demandes) {
                $manager = User::find($managerId);
                if ($manager && $manager->email) {
                    Mail::to($manager->email)->send(new LeaveReminderManager($manager, $demandes));
                    $this->info("Congés [manager] → {$manager->email} ({$demandes->count()} demande(s))");
                }
            }
        } else {
            $this->line('Aucun congé en_attente.');
        }

        // ── 2. Congés pre_approuve → DG / RH / Admin ─────────────────────────
        $congesPreApprouves = DemandeConge::with(['user', 'typeConge', 'superieurHierarchique'])
            ->where('statut', 'pre_approuve')
            ->get();

        if ($congesPreApprouves->isNotEmpty()) {
            $this->envoyerAuxValidateursDg(
                new LeaveReminderFinal(...[null, $congesPreApprouves]),
                function ($dest) use ($congesPreApprouves) {
                    return new LeaveReminderFinal($dest, $congesPreApprouves);
                },
                'Congés [DG]',
                $congesPreApprouves->count()
            );
        } else {
            $this->line('Aucun congé pre_approuve.');
        }

        $this->info('Rappels congés traités. (Attestations/certificats gérés par attestations:send-reminders)');
        return Command::SUCCESS;
    }

    /**
     * Envoie un mail à tous les utilisateurs ayant le rôle DG / RH / Admin.
     *
     * @param mixed    $unused         (non utilisé, gardé pour compatibilité)
     * @param callable $mailFactory    Closure(User $dest): Mailable
     * @param string   $label          Label pour les logs
     * @param int      $count          Nombre de demandes
     */
    private function envoyerAuxValidateursDg($unused, callable $mailFactory, string $label, int $count): void
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
