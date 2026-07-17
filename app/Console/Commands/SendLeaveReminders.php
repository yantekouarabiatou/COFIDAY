<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Console\Commands\Concerns\ResolvesValidateurs;
use App\Models\DemandeConge;
use App\Models\User;
use App\Mail\LeaveReminderManager;
use App\Mail\LeaveReminderFinal;

class SendLeaveReminders extends Command
{
    use ResolvesValidateurs;

    protected $signature   = 'leaves:send-reminders';
    protected $description = 'Rappels quotidiens : congés en_attente (managers + Admin/DG/RH/Secrétariat) et pre_approuve (Admin/DG/RH/Secrétariat)';

    public function handle()
    {
        // ── 1. Congés en_attente → managers + Admin/DG/RH/Secrétariat ────────
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

            $this->envoyerAuxValidateurs(
                fn($dest) => new LeaveReminderManager($dest, $congesEnAttente),
                'Congés [niveau 1 - Admin/DG/RH/Secrétariat]',
                $congesEnAttente->count()
            );
        } else {
            $this->line('Aucun congé en_attente.');
        }

        // ── 2. Congés pre_approuve → Admin/DG/RH/Secrétariat ──────────────────
        $congesPreApprouves = DemandeConge::with(['user', 'typeConge', 'superieurHierarchique'])
            ->where('statut', 'pre_approuve')
            ->get();

        if ($congesPreApprouves->isNotEmpty()) {
            $this->envoyerAuxValidateurs(
                fn($dest) => new LeaveReminderFinal($dest, $congesPreApprouves),
                'Congés [niveau 2 - Admin/DG/RH/Secrétariat]',
                $congesPreApprouves->count()
            );
        } else {
            $this->line('Aucun congé pre_approuve.');
        }

        $this->info('Rappels congés traités. (Attestations/certificats gérés par attestations:send-reminders)');
        return Command::SUCCESS;
    }

    private function envoyerAuxValidateurs(callable $mailFactory, string $label, int $count): void
    {
        foreach ($this->getValidateurs() as $dest) {
            Mail::to($dest->email)->send($mailFactory($dest));
            $this->info("{$label} → {$dest->email} ({$count} demande(s))");
        }
    }
}
