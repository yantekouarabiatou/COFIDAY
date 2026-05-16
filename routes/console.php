<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Carbon\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Rappels quotidiens (congés, attestations, démissions) ────────────────────
Schedule::command('leaves:send-reminders')
    ->dailyAt('08:00')
    ->withoutOverlapping();

// ── Notification fin de mois ─────────────────────────────────────────────────
Schedule::command('notify:fin-de-mois')
    ->when(fn() => Carbon::now()->isLastOfMonth())
    ->dailyAt('08:00');

// ── Feuilles de temps manquantes ─────────────────────────────────────────────
Schedule::command('timesheets:generate-missing --days=1')
    ->weekdays()
    ->at('18:00')
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('timesheets:generate-missing --days=5')
    ->weekly()
    ->fridays()
    ->at('14:00')
    ->withoutOverlapping()
    ->runInBackground();
