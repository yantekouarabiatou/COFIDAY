<?php

namespace App\Console\Commands;

use App\Models\DailyEntry;
use App\Models\User;
use App\Notifications\MissingTimesheetReminder;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateMissingTimesheets extends Command
{
    protected $signature   = 'timesheets:generate-missing {--days=5 : Nombre de jours ouvrables à vérifier dans le passé}';
    protected $description = 'Génère les feuilles manquantes et envoie des rappels aux collaborateurs.';

    public function handle(): int
    {
        $daysBack = (int) $this->option('days');
        $today    = Carbon::today();
        $from     = $today->copy()->subWeekdays($daysBack);

        $users = User::where('is_active', true)
            ->doesntHave('roles', 'and', fn($q) => $q->whereIn('name', ['admin', 'super-admin']))
            ->get();

        $totalMissing = 0;
        $notified     = 0;

        foreach ($users as $user) {
            $missing = DailyEntry::generateMissingDays($user, $from, $today->copy()->subDay());
            $totalMissing += $missing;

            // Envoyer un rappel si des jours manquants existent cette semaine
            $missingThisWeek = DailyEntry::getMissingDaysForWeek(
                $user->id,
                now()->isoWeek(),
                now()->isoWeekYear()
            );

            if (count($missingThisWeek) > 0) {
                try {
                    $user->notify(new MissingTimesheetReminder($missingThisWeek));
                    $notified++;
                } catch (\Exception $e) {
                    $this->warn("Erreur notification {$user->email}: {$e->getMessage()}");
                }
            }
        }

        $this->info("✅ {$totalMissing} feuilles manquantes générées — {$notified} utilisateurs notifiés.");
        return self::SUCCESS;
    }
}