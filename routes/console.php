<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Carbon\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Rappels quotidiens congés ────────────────────────────────────────────────
Schedule::command('leaves:send-reminders')
    ->dailyAt('08:00')
    ->withoutOverlapping();

// ── Rappels attestations & certificats (2x/jour) ─────────────────────────────
Schedule::command('attestations:send-reminders')
    ->dailyAt('08:00')
    ->withoutOverlapping();

Schedule::command('attestations:send-reminders')
    ->dailyAt('14:00')
    ->withoutOverlapping();

// ── Congés : accrual mensuel progressif ──────────────────────────────────────
// Le 1er de chaque mois, recalcule jours_acquis de l'année en cours pour tous
// les salariés (2 j./mois écoulé depuis l'embauche), sans jamais toucher
// jours_pris ni les années antérieures.
Schedule::command('conges:recalculer-solde-annee-courante')
    ->monthlyOn(1, '00:30')
    ->withoutOverlapping()
    ->runInBackground();

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
