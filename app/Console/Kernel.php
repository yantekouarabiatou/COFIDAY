<?php

// Dans app/Console/Kernel.php — méthode schedule()

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // ── Générer les jours manquants + notifier ──────────────────────
        // Chaque jour ouvrable à 18h : génère les feuilles manquantes pour la journée
        $schedule->command('timesheets:generate-missing --days=1')
            ->weekdays()
            ->at('18:00')
            ->withoutOverlapping()
            ->runInBackground();

        // Rappel hebdomadaire le vendredi à 14h
        $schedule->command('timesheets:generate-missing --days=5')
            ->weekly()
            ->fridays()
            ->at('14:00')
            ->withoutOverlapping()
            ->runInBackground();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}