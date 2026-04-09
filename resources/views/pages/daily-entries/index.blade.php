@php
    use App\Helpers\UserHelper;
    $canManage = auth()->user()->hasRole(['admin', 'super-admin', 'manager', 'directeur-general'])
        || auth()->user()->subordinates()->exists();
@endphp

@extends('layaout')
@section('title', 'Feuilles de Temps — S' . $semaine . ' ' . $annee)

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
/* ── Design System ── */
:root {
    --ts-blue:     #2563EB;
    --ts-blue-lt:  #EFF6FF;
    --ts-green:    #059669;
    --ts-green-lt: #ECFDF5;
    --ts-amber:    #022335;
    --ts-amber-lt: #FFFBEB;
    --ts-red:      #244584;
    --ts-red-lt:   #FEF2F2;
    --ts-slate:    #475569;
    --ts-slate-lt: #F8FAFC;
    --ts-border:   #E2E8F0;
    --ts-shadow:   0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.04);
    --ts-shadow-md:0 4px 16px rgba(0,0,0,.08);
    --ts-radius:   12px;
    --ts-radius-sm:8px;
    font-family: 'DM Sans', sans-serif;
}

/* ── Layout ── */
.ts-page { background: #F1F5F9; min-height: 100vh; padding: 0 0 48px; }
.ts-topbar {
    background: #fff;
    border-bottom: 1px solid var(--ts-border);
    padding: 16px 32px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    position: sticky;
    top: 0;
    z-index: 50;
}
.ts-topbar__title { font-size: 18px; font-weight: 600; color: #0F172A; display: flex; align-items: center; gap: 8px; }
.ts-topbar__title i { color: var(--ts-blue); }

/* ── Week Nav ── */
.week-nav {
    display: flex;
    align-items: center;
    gap: 6px;
    background: var(--ts-slate-lt);
    border: 1px solid var(--ts-border);
    border-radius: 40px;
    padding: 4px;
}
.week-nav__btn {
    width: 32px; height: 32px;
    border-radius: 50%;
    border: none;
    background: transparent;
    color: var(--ts-slate);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    transition: background .15s, color .15s;
    text-decoration: none;
}
.week-nav__btn:hover { background: #E2E8F0; color: #0F172A; }
.week-nav__btn.disabled { opacity: .3; pointer-events: none; }
.week-nav__label {
    font-size: 13px; font-weight: 600;
    color: #0F172A;
    padding: 0 12px;
    white-space: nowrap;
}
.week-nav__badge {
    background: var(--ts-blue);
    color: #fff;
    font-size: 10px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 20px;
}

/* ── Stats Bar ── */
.stats-bar {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 12px;
    padding: 20px 32px;
}
.stat-card {
    background: #fff;
    border: 1px solid var(--ts-border);
    border-radius: var(--ts-radius);
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    box-shadow: var(--ts-shadow);
    transition: transform .15s;
}
.stat-card:hover { transform: translateY(-2px); }
.stat-icon {
    width: 42px; height: 42px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.stat-icon.blue  { background: var(--ts-blue-lt);  color: var(--ts-blue); }
.stat-icon.green { background: var(--ts-green-lt); color: var(--ts-green); }
.stat-icon.amber { background: var(--ts-amber-lt); color: var(--ts-amber); }
.stat-icon.red   { background: var(--ts-red-lt);   color: var(--ts-red); }
.stat-icon.slate { background: #F1F5F9;             color: var(--ts-slate); }
.stat-value { font-size: 22px; font-weight: 600; color: #0F172A; line-height: 1; }
.stat-label { font-size: 12px; color: var(--ts-slate); margin-top: 2px; }

/* ── Alert Banner ── */
.alert-missing {
    margin: 0 32px 16px;
    background: #FEF3C7;
    border: 1px solid #FCD34D;
    border-radius: var(--ts-radius);
    padding: 14px 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 14px;
}
.alert-missing__icon { font-size: 20px; }
.alert-missing__days {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-top: 8px;
}
.day-chip {
    background: #FFF;
    border: 1px solid #FCD34D;
    border-radius: 6px;
    padding: 3px 10px;
    font-size: 12px;
    font-weight: 500;
    color: #92400E;
}
.alert-missing a.btn-fill-now {
    margin-left: auto;
    background: var(--ts-amber);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 8px 18px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;
    transition: background .15s;
}
.alert-missing a.btn-fill-now:hover { background: #244585; }

/* ── Main Content ── */
.ts-main { padding: 0 32px; }

/* ── Week Grid (5 colonnes jours) ── */
.week-grid {
    display: grid;
    grid-template-columns: 220px repeat(5, 1fr);
    gap: 0;
    background: #fff;
    border: 1px solid var(--ts-border);
    border-radius: var(--ts-radius);
    overflow: hidden;
    box-shadow: var(--ts-shadow);
}
.week-grid__header {
    background: #F8FAFC;
    border-bottom: 1px solid var(--ts-border);
    font-size: 12px;
    font-weight: 600;
    color: var(--ts-slate);
    text-transform: uppercase;
    letter-spacing: .5px;
    padding: 12px 16px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
}
.week-grid__header.col-label { align-items: flex-start; }
.week-grid__header .day-num {
    font-size: 20px;
    font-weight: 700;
    color: #0F172A;
    line-height: 1;
}
.week-grid__header .day-num.today {
    background: var(--ts-blue);
    color: #fff;
    width: 32px; height: 32px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
}
.week-grid__row { display: contents; }
.week-grid__col-label {
    border-right: 1px solid var(--ts-border);
    padding: 14px 16px;
    border-top: 1px solid var(--ts-border);
    display: flex;
    align-items: center;
    gap: 10px;
}
.user-avatar {
    width: 36px; height: 36px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 700; color: #fff;
    flex-shrink: 0;
}
.user-name { font-size: 13px; font-weight: 600; color: #0F172A; }
.user-role { font-size: 11px; color: var(--ts-slate); }
.week-grid__cell {
    border-top: 1px solid var(--ts-border);
    border-right: 1px solid var(--ts-border);
    padding: 10px 12px;
    min-height: 80px;
    position: relative;
    transition: background .15s;
}
.week-grid__cell:last-child { border-right: none; }
.week-grid__cell.weekend { background: #FAFAFA; }
.week-grid__cell.missing { background: #FFF8F0; }
.week-grid__cell.today-col { background: #EFF6FF; }

/* ── Entry Pill ── */
.entry-pill {
    display: block;
    border-radius: 8px;
    padding: 7px 10px;
    font-size: 12px;
    text-decoration: none;
    transition: opacity .15s, transform .15s;
    margin-bottom: 4px;
    cursor: pointer;
    border: none;
    width: 100%;
    text-align: left;
}
.entry-pill:hover { opacity: .85; transform: scale(1.02); }
.entry-pill.submitted { background: #DBEAFE; color: #1E40AF; }
.entry-pill.validated { background: #D1FAE5; color: #065F46; }
.entry-pill.rejected  { background: #FEE2E2; color: #7F1D1D; }
.entry-pill.missing   { background: #FEF3C7; color: #78350F; border: 1px dashed #FCD34D; }
.entry-pill__hours { font-weight: 700; font-family: 'DM Mono', monospace; font-size: 13px; }
.entry-pill__acts  { font-size: 10px; opacity: .8; }

.cell-add-btn {
    position: absolute; bottom: 6px; right: 6px;
    width: 24px; height: 24px;
    background: var(--ts-blue-lt);
    color: var(--ts-blue);
    border-radius: 6px;
    border: 1px dashed #93C5FD;
    font-size: 14px;
    display: flex; align-items: center; justify-content: center;
    text-decoration: none;
    opacity: 0;
    transition: opacity .15s;
}
.week-grid__cell:hover .cell-add-btn { opacity: 1; }

/* ── Table vue liste (fallback/mobile) ── */
.entries-table-wrap {
    background: #fff;
    border: 1px solid var(--ts-border);
    border-radius: var(--ts-radius);
    overflow: hidden;
    box-shadow: var(--ts-shadow);
}
.entries-table { width: 100%; border-collapse: collapse; font-size: 14px; }
.entries-table thead { background: #F8FAFC; }
.entries-table th {
    padding: 12px 16px;
    font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .5px;
    color: var(--ts-slate);
    border-bottom: 1px solid var(--ts-border);
    white-space: nowrap;
}
.entries-table td {
    padding: 14px 16px;
    border-bottom: 1px solid #F1F5F9;
    vertical-align: middle;
}
.entries-table tr:last-child td { border-bottom: none; }
.entries-table tr:hover td { background: #FAFBFC; }
.entries-table tr.row-selected td { background: #EFF6FF; }

/* ── Status Badge ── */
.badge-ts {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 10px; border-radius: 20px;
    font-size: 11px; font-weight: 600;
}
.badge-ts.soumis  { background: #DBEAFE; color: #1E40AF; }
.badge-ts.validé  { background: #D1FAE5; color: #065F46; }
.badge-ts.refusé  { background: #FEE2E2; color: #7F1D1D; }
.badge-ts.manquant{ background: #FEF3C7; color: #78350F; }

/* ── Hours Progress ── */
.hours-bar-wrap { min-width: 120px; }
.hours-bar {
    height: 6px;
    background: #E2E8F0;
    border-radius: 3px;
    overflow: hidden;
    margin-bottom: 4px;
}
.hours-bar__fill { height: 100%; border-radius: 3px; transition: width .4s ease; }
.hours-bar__fill.green  { background: #10B981; }
.hours-bar__fill.amber  { background: #F59E0B; }
.hours-bar__fill.red    { background: #EF4444; }
.hours-label { font-size: 11px; color: var(--ts-slate); font-family: 'DM Mono', monospace; }

/* ── Action Buttons ── */
.btn-action {
    display: inline-flex; align-items: center; justify-content: center;
    width: 32px; height: 32px;
    border-radius: 8px;
    border: 1px solid var(--ts-border);
    background: #fff;
    color: var(--ts-slate);
    font-size: 13px;
    cursor: pointer;
    transition: all .15s;
    text-decoration: none;
}
.btn-action:hover { background: #F1F5F9; color: #0F172A; border-color: #CBD5E1; }
.btn-action.validate:hover { background: var(--ts-green-lt); color: var(--ts-green); border-color: #6EE7B7; }
.btn-action.reject:hover   { background: var(--ts-red-lt);   color: var(--ts-red);   border-color: #FCA5A5; }
.btn-action.delete:hover   { background: #FFF0F0; color: var(--ts-red); border-color: #FCA5A5; }

/* ── Bulk Bar ── */
.bulk-bar {
    background: #EFF6FF;
    border: 1px solid #BFDBFE;
    border-radius: var(--ts-radius);
    padding: 12px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 16px;
    display: none;
}
.bulk-bar.visible { display: flex; }

/* ── Weekly Validation Panel (manager view) ── */
.weekly-panel {
    background: #fff;
    border: 1px solid var(--ts-border);
    border-radius: var(--ts-radius);
    margin-bottom: 20px;
    overflow: hidden;
    box-shadow: var(--ts-shadow);
}
.weekly-panel__head {
    background: #F8FAFC;
    border-bottom: 1px solid var(--ts-border);
    padding: 14px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}
.weekly-panel__head h5 { font-size: 14px; font-weight: 600; color: #0F172A; margin: 0; }
.weekly-panel__body { padding: 16px 20px; }

.collab-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid #F1F5F9;
    flex-wrap: wrap;
}
.collab-row:last-child { border-bottom: none; }
.collab-info { display: flex; align-items: center; gap: 10px; }
.collab-days { display: flex; gap: 6px; }
.collab-day {
    width: 32px; height: 32px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 600;
}
.collab-day.ok      { background: #D1FAE5; color: #065F46; }
.collab-day.missing { background: #FEF3C7; color: #78350F; }
.collab-day.weekend { background: #F1F5F9; color: #94A3B8; }
.collab-day.future  { background: #F8FAFC; color: #CBD5E1; }
.collab-day.rejected{ background: #FEE2E2; color: #7F1D1D; }

/* ── Buttons ── */
.btn-ts {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 9px 18px;
    border-radius: 9px;
    font-size: 13px; font-weight: 600;
    border: none; cursor: pointer;
    transition: all .15s;
    text-decoration: none;
}
.btn-ts.primary { background: var(--ts-blue); color: #fff; }
.btn-ts.primary:hover { background: #1D4ED8; }
.btn-ts.success { background: var(--ts-green); color: #fff; }
.btn-ts.success:hover { background: #047857; }
.btn-ts.danger  { background: var(--ts-red); color: #fff; }
.btn-ts.danger:hover  { background: #B91C1C; }
.btn-ts.outline { background: #fff; color: var(--ts-slate); border: 1px solid var(--ts-border); }
.btn-ts.outline:hover { background: #F8FAFC; }
.btn-ts.sm { padding: 6px 12px; font-size: 12px; border-radius: 7px; }

/* ── Empty State ── */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: var(--ts-slate);
}
.empty-state i { font-size: 48px; opacity: .3; margin-bottom: 16px; display: block; }
.empty-state h4 { font-size: 18px; font-weight: 600; color: #0F172A; margin: 0 0 8px; }
.empty-state p  { font-size: 14px; margin: 0 0 24px; }

/* ── Modal custom ── */
.ts-modal-overlay {
    position: fixed; inset: 0;
    background: rgba(15,23,42,.45);
    backdrop-filter: blur(4px);
    z-index: 1000;
    display: none;
    align-items: center; justify-content: center;
}
.ts-modal-overlay.open { display: flex; }
.ts-modal {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 24px 60px rgba(0,0,0,.18);
    padding: 32px;
    width: 100%;
    max-width: 500px;
    animation: modalIn .2s ease;
}
@keyframes modalIn { from { opacity:0; transform:scale(.96) translateY(-8px); } to { opacity:1; transform:scale(1) translateY(0); } }
.ts-modal h3 { font-size: 18px; font-weight: 700; margin: 0 0 6px; color: #0F172A; }
.ts-modal p  { font-size: 13px; color: var(--ts-slate); margin: 0 0 24px; }
.ts-input {
    width: 100%;
    border: 1.5px solid var(--ts-border);
    border-radius: 10px;
    padding: 11px 15px;
    font-size: 14px;
    font-family: 'DM Sans', sans-serif;
    outline: none;
    transition: border-color .15s;
    box-sizing: border-box;
}
.ts-input:focus { border-color: var(--ts-blue); box-shadow: 0 0 0 3px #DBEAFE; }

/* ── Responsive ── */
@media (max-width: 768px) {
    .stats-bar, .ts-main, .ts-topbar { padding-left: 16px; padding-right: 16px; }
    .week-grid { display: none; }
    .alert-missing { margin-left: 16px; margin-right: 16px; }
}
@media (min-width: 769px) {
    .entries-table-wrap { display: none; }
}
/* Show table on big screens too when grid is hidden via class */
.show-table .week-grid { display: none !important; }
.show-table .entries-table-wrap { display: block !important; }
</style>
@endpush

@section('content')
<div class="ts-page">

    {{-- ── Top Bar ── --}}
    <div class="ts-topbar">
        <div class="ts-topbar__title">
            <i class="fas fa-clock"></i>
            Feuilles de Temps
        </div>

        {{-- Navigation semaine --}}
        <div class="week-nav">
            <a href="{{ request()->fullUrlWithQuery(['semaine' => $semainePrecedente->isoWeek(), 'annee' => $semainePrecedente->isoWeekYear()]) }}"
               class="week-nav__btn" title="Semaine précédente">
                <i class="fas fa-chevron-left" style="font-size:11px;"></i>
            </a>
            <span class="week-nav__label">
                S{{ $semaine }} — {{ $dateDebut->format('d') }}→{{ $dateFin->format('d M Y') }}
            </span>
            @if($isSemaineActuelle)
                <span class="week-nav__badge">En cours</span>
            @endif
            <a href="{{ $isSemaineActuelle ? '#' : request()->fullUrlWithQuery(['semaine' => $semaineSuivante->isoWeek(), 'annee' => $semaineSuivante->isoWeekYear()]) }}"
               class="week-nav__btn {{ $isSemaineActuelle ? 'disabled' : '' }}" title="Semaine suivante">
                <i class="fas fa-chevron-right" style="font-size:11px;"></i>
            </a>
        </div>

        {{-- Actions --}}
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <button class="btn-ts outline sm" onclick="toggleView()">
                <i class="fas fa-table" id="view-icon"></i>
            </button>
            <a href="{{ route('daily-entries.create') }}" class="btn-ts primary sm">
                <i class="fas fa-plus"></i> Nouvelle saisie
            </a>
            <button class="btn-ts outline sm" data-toggle="modal" data-target="#exportModal">
                <i class="fas fa-download"></i> Exporter
            </button>
        </div>
    </div>

    {{-- ── Stats ── --}}
    <div class="stats-bar">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-clock"></i></div>
            <div>
                <div class="stat-value">{{ UserHelper::hoursToHoursMinutes($totalHours) }}</div>
                <div class="stat-label">Total heures</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon amber"><i class="fas fa-hourglass-half"></i></div>
            <div>
                <div class="stat-value">{{ $submittedCount }}</div>
                <div class="stat-label">En attente</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
            <div>
                <div class="stat-value">{{ $validatedCount }}</div>
                <div class="stat-label">Validées</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon red"><i class="fas fa-times-circle"></i></div>
            <div>
                <div class="stat-value">{{ $rejectedCount }}</div>
                <div class="stat-label">Refusées</div>
            </div>
        </div>
        @if($missingCount > 0)
        <div class="stat-card">
            <div class="stat-icon slate"><i class="fas fa-calendar-times"></i></div>
            <div>
                <div class="stat-value" style="color:var(--ts-amber);">{{ $missingCount }}</div>
                <div class="stat-label">Jours manquants</div>
            </div>
        </div>
        @endif
    </div>

    {{-- ── Alert jours manquants de l'utilisateur courant ── --}}
    @if(count($missingDays) > 0)
    <div class="alert-missing">
        <div class="alert-missing__icon">⚠️</div>
        <div style="flex:1;">
            <strong>{{ count($missingDays) }} jour(s) sans saisie cette semaine</strong>
            <div class="alert-missing__days">
                @foreach($missingDays as $day)
                    <span class="day-chip">{{ \Carbon\Carbon::parse($day)->translatedFormat('D d M') }}</span>
                @endforeach
            </div>
        </div>
        <a href="{{ route('daily-entries.create') }}" class="btn-fill-now">
            <i class="fas fa-edit"></i> Saisir maintenant
        </a>
    </div>
    @endif

    <div class="ts-main" id="ts-main">

        {{-- ── Panneau validation hebdomadaire (managers) ── --}}
        @if($canManage && $dailyEntries->isNotEmpty())
        @php
            $subIds = auth()->user()->subordinates()->pluck('id')->toArray();
            $collabsThisWeek = $dailyEntries->whereIn('user_id', $subIds)
                ->groupBy('user_id');
        @endphp
        @if($collabsThisWeek->isNotEmpty())
        <div class="weekly-panel">
            <div class="weekly-panel__head">
                <h5><i class="fas fa-users-check" style="color:var(--ts-blue);margin-right:6px;"></i>
                    Validation semaine S{{ $semaine }} — Vos collaborateurs
                </h5>
                @if($submittedCount > 0)
                <div style="display:flex;gap:8px;">
                    <button class="btn-ts success sm" id="validate-all-week-btn">
                        <i class="fas fa-check-double"></i> Tout valider ({{ $submittedCount }})
                    </button>
                </div>
                @endif
            </div>
            <div class="weekly-panel__body">
                @foreach($collabsThisWeek as $userId => $userEntries)
                @php
                    $collab     = $userEntries->first()->user;
                    $hasSubmitted = $userEntries->where('statut', 'soumis')->count() > 0;
                    $allValidated = $userEntries->where('statut','!=','validé')->count() === 0 && $userEntries->count() > 0;
                    $avatarColors = ['#4F46E5','#0891B2','#059669','#7C3AED','#DC2626','#D97706'];
                    $color = $avatarColors[$userId % count($avatarColors)];

                    // Présence jours de la semaine
                    $weekDays = [];
                    for ($d=0; $d<5; $d++) {
                        $day = $dateDebut->copy()->addDays($d);
                        $entry = $userEntries->first(fn($e) => $e->jour->format('Y-m-d') === $day->format('Y-m-d'));
                        $weekDays[] = ['date' => $day, 'entry' => $entry, 'future' => $day->isFuture()];
                    }
                @endphp
                <div class="collab-row">
                    <div class="collab-info">
                        <div class="user-avatar" style="background:{{ $color }};width:36px;height:36px;font-size:13px;">
                            {{ strtoupper(substr($collab->prenom ?? 'U', 0, 1)) }}
                        </div>
                        <div>
                            <div class="user-name">{{ $collab->prenom }} {{ $collab->nom }}</div>
                            <div class="user-role">{{ $collab->poste->intitule ?? 'Non défini' }}</div>
                        </div>
                    </div>

                    {{-- Présence jours L→V --}}
                    <div class="collab-days">
                        @foreach(['L','M','M','J','V'] as $i => $dayLabel)
                        @php $wd = $weekDays[$i]; @endphp
                        @if($wd['future'])
                            <div class="collab-day future" title="{{ $wd['date']->format('d/m') }}">{{ $dayLabel }}</div>
                        @elseif(!$wd['entry'])
                            <div class="collab-day missing" title="Manquant – {{ $wd['date']->format('d/m') }}">{{ $dayLabel }}</div>
                        @elseif($wd['entry']->statut === 'validé')
                            <div class="collab-day ok" title="Validé – {{ $wd['date']->format('d/m') }}">✓</div>
                        @elseif($wd['entry']->statut === 'refusé')
                            <div class="collab-day rejected" title="Refusé – {{ $wd['date']->format('d/m') }}">✗</div>
                        @else
                            <div class="collab-day ok" style="background:#DBEAFE;color:#1E40AF;" title="Soumis – {{ $wd['date']->format('d/m') }}">{{ $dayLabel }}</div>
                        @endif
                        @endforeach
                    </div>

                    {{-- Heures total --}}
                    <div style="font-family:'DM Mono',monospace;font-size:13px;color:#0F172A;font-weight:600;min-width:70px;text-align:center;">
                        {{ UserHelper::hoursToHoursMinutes($userEntries->sum('heures_reelles')) }}
                    </div>

                    {{-- Actions validation semaine --}}
                    <div style="display:flex;gap:6px;">
                        @if($hasSubmitted)
                        <button class="btn-ts success sm week-validate-btn"
                            data-user-id="{{ $userId }}"
                            data-user-name="{{ $collab->prenom }} {{ $collab->nom }}"
                            data-semaine="{{ $semaine }}"
                            data-annee="{{ $annee }}">
                            <i class="fas fa-check"></i> Valider
                        </button>
                        <button class="btn-ts danger sm week-reject-btn"
                            data-user-id="{{ $userId }}"
                            data-user-name="{{ $collab->prenom }} {{ $collab->nom }}"
                            data-semaine="{{ $semaine }}"
                            data-annee="{{ $annee }}">
                            <i class="fas fa-times"></i> Refuser
                        </button>
                        @elseif($allValidated)
                        <span class="badge-ts validé"><i class="fas fa-check-circle"></i> Semaine validée</span>
                        @else
                        <span class="badge-ts manquant"><i class="fas fa-clock"></i> En attente</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
        @endif

        {{-- ── Bulk Bar ── --}}
        @if($canManage)
        <div class="bulk-bar" id="bulk-bar">
            <span style="font-size:13px;color:#1E40AF;font-weight:600;">
                <i class="fas fa-check-square"></i>
                <span id="selected-count">0</span> sélectionnée(s)
            </span>
            <div style="display:flex;gap:8px;">
                <button class="btn-ts success sm" id="bulk-validate-btn" disabled>
                    <i class="fas fa-check-double"></i> Valider
                </button>
                <button class="btn-ts danger sm" id="bulk-reject-btn" disabled>
                    <i class="fas fa-times-circle"></i> Refuser
                </button>
                <button class="btn-ts outline sm" id="deselect-all-btn">
                    <i class="fas fa-minus-square"></i> Désélectionner
                </button>
            </div>
        </div>
        @endif

        {{-- ── Filters ── --}}
        <form action="{{ route('daily-entries.index') }}" method="GET"
              style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:16px;">
            <input type="hidden" name="semaine" value="{{ $semaine }}">
            <input type="hidden" name="annee" value="{{ $annee }}">

            @if($users->isNotEmpty())
            <select name="user" class="ts-input" style="width:auto;min-width:180px;padding:8px 14px;font-size:13px;">
                <option value="">— Tous les collaborateurs —</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('user') == $u->id ? 'selected' : '' }}>
                        {{ $u->prenom }} {{ $u->nom }}
                    </option>
                @endforeach
            </select>
            @endif

            <select name="statut" class="ts-input" style="width:auto;min-width:140px;padding:8px 14px;font-size:13px;">
                <option value="">— Tous statuts —</option>
                <option value="soumis"   {{ request('statut') === 'soumis'   ? 'selected' : '' }}>Soumis</option>
                <option value="validé"   {{ request('statut') === 'validé'   ? 'selected' : '' }}>Validé</option>
                <option value="refusé"   {{ request('statut') === 'refusé'   ? 'selected' : '' }}>Refusé</option>
                <option value="manquant" {{ request('statut') === 'manquant' ? 'selected' : '' }}>Manquant</option>
            </select>

            <button type="submit" class="btn-ts primary sm"><i class="fas fa-search"></i> Filtrer</button>
            @if(request()->hasAny(['statut','user']))
            <a href="{{ route('daily-entries.index', ['semaine' => $semaine, 'annee' => $annee]) }}"
               class="btn-ts outline sm"><i class="fas fa-times"></i> Reset</a>
            @endif
        </form>

        {{-- ── Empty State ── --}}
        @if($dailyEntries->isEmpty())
        <div class="entries-table-wrap">
            <div class="empty-state">
                <i class="fas fa-calendar-check"></i>
                <h4>Aucune feuille cette semaine</h4>
                <p>Commencez par saisir votre première feuille de temps.</p>
                <a href="{{ route('daily-entries.create') }}" class="btn-ts primary">
                    <i class="fas fa-plus"></i> Nouvelle saisie
                </a>
            </div>
        </div>

        {{-- ── Week Grid View ── --}}
        @else
        {{-- Grid semaine (desktop) --}}
        <div class="week-grid" id="week-grid-view">
            {{-- Headers --}}
            <div class="week-grid__header col-label">
                <span>Collaborateur</span>
            </div>
            @foreach(['Lundi','Mardi','Mercredi','Jeudi','Vendredi'] as $i => $dayName)
            @php $d = $dateDebut->copy()->addDays($i); @endphp
            <div class="week-grid__header {{ $d->isToday() ? 'today-col' : '' }}">
                <span>{{ strtoupper(substr($dayName,0,3)) }}</span>
                <span class="day-num {{ $d->isToday() ? 'today' : '' }}">{{ $d->format('d') }}</span>
            </div>
            @endforeach

            {{-- Rows --}}
            @foreach($dailyEntries->groupBy('user_id') as $userId => $userEntries)
            @php
                $rowUser = $userEntries->first()->user;
                $isMine  = $userId == auth()->id();
                $avatarColors = ['#4F46E5','#0891B2','#059669','#7C3AED','#DC2626','#D97706'];
                $color = $avatarColors[$userId % count($avatarColors)];
            @endphp
            <div class="week-grid__row">
                {{-- Colonne utilisateur --}}
                <div class="week-grid__col-label">
                    <div class="user-avatar" style="background:{{ $color }};">
                        {{ strtoupper(substr($rowUser->prenom ?? 'U', 0, 1)) }}
                    </div>
                    <div>
                        <div class="user-name">
                            {{ $rowUser->prenom }} {{ $rowUser->nom }}
                            @if($isMine) <span style="font-size:10px;background:#DBEAFE;color:#1E40AF;padding:1px 6px;border-radius:4px;font-weight:600;">Moi</span> @endif
                        </div>
                        <div class="user-role">{{ $rowUser->poste->intitule ?? '' }}</div>
                    </div>
                </div>

                {{-- Cellules jours --}}
                @for($d=0; $d<5; $d++)
                @php
                    $day      = $dateDebut->copy()->addDays($d);
                    $dayEntry = $userEntries->first(fn($e) => $e->jour->format('Y-m-d') === $day->format('Y-m-d'));
                    $classes  = ['week-grid__cell'];
                    if ($day->isToday()) $classes[] = 'today-col';
                    if ($dayEntry && $dayEntry->est_manquant) $classes[] = 'missing';
                @endphp
                <div class="{{ implode(' ', $classes) }}">
                    @if($dayEntry && !$dayEntry->est_manquant)
                        <a href="{{ route('daily-entries.show', $dayEntry) }}"
                           class="entry-pill {{ $dayEntry->statut === 'validé' ? 'validated' : ($dayEntry->statut === 'refusé' ? 'rejected' : 'submitted') }}">
                            <div class="entry-pill__hours">{{ UserHelper::hoursToHoursMinutes($dayEntry->heures_reelles) }}</div>
                            <div class="entry-pill__acts">{{ $dayEntry->timeEntries->count() }} activité(s)</div>
                        </a>
                    @elseif($dayEntry && $dayEntry->est_manquant)
                        <a href="{{ route('daily-entries.create', ['jour' => $day->format('Y-m-d')]) }}"
                           class="entry-pill missing">
                            <div class="entry-pill__hours">—</div>
                            <div class="entry-pill__acts">Non rempli</div>
                        </a>
                    @endif

                    @if(!$day->isFuture() && (!$dayEntry || $dayEntry->est_manquant) && $isMine)
                    <a href="{{ route('daily-entries.create', ['jour' => $day->format('Y-m-d')]) }}"
                       class="cell-add-btn" title="Ajouter la saisie du {{ $day->format('d/m') }}">
                        <i class="fas fa-plus" style="font-size:11px;"></i>
                    </a>
                    @endif
                </div>
                @endfor
            </div>
            @endforeach
        </div>

        {{-- ── Table View (toggle + mobile) ── --}}
        <div class="entries-table-wrap" id="table-view" style="margin-top:16px;">
            <table class="entries-table">
                <thead>
                    <tr>
                        @if($canManage)
                        <th style="width:40px;">
                            <input type="checkbox" id="select-all" style="width:15px;height:15px;cursor:pointer;">
                        </th>
                        @endif
                        <th>Date</th>
                        <th>Collaborateur</th>
                        <th>Heures</th>
                        <th>Activités</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dailyEntries as $entry)
                    @php
                        $isMine    = $entry->user_id === auth()->id();
                        $canMgr    = $canManage && !$isMine;
                        $isSubmitted = $entry->statut === 'soumis';
                        $pct = $entry->heures_theoriques > 0
                            ? min(($entry->heures_reelles / $entry->heures_theoriques) * 100, 100)
                            : 0;
                        $barClass = $pct >= 100 ? 'green' : ($pct >= 80 ? 'amber' : 'red');
                        $avatarColors = ['#4F46E5','#0891B2','#059669','#7C3AED','#DC2626','#D97706'];
                        $color = $avatarColors[$entry->user_id % count($avatarColors)];
                    @endphp
                    <tr data-id="{{ $entry->id }}" data-statut="{{ $entry->statut }}"
                        class="{{ $entry->est_manquant ? 'table-warning' : '' }}">

                        @if($canManage)
                        <td>
                            @if($canMgr && in_array($entry->statut, ['soumis','refusé']))
                            <input type="checkbox" class="entry-checkbox" value="{{ $entry->id }}"
                                   style="width:15px;height:15px;cursor:pointer;">
                            @endif
                        </td>
                        @endif

                        <td>
                            <strong>{{ $entry->jour->format('d/m/Y') }}</strong><br>
                            <small style="color:var(--ts-slate);">{{ $entry->jour->translatedFormat('l') }}</small>
                        </td>

                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div class="user-avatar" style="background:{{ $color }};width:36px;height:36px;font-size:13px;flex-shrink:0;">
                                    {{ strtoupper(substr($entry->user->prenom ?? 'U', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="user-name">{{ $entry->user->prenom }} {{ $entry->user->nom }}
                                        @if($isMine) <span style="font-size:10px;background:#DBEAFE;color:#1E40AF;padding:1px 6px;border-radius:4px;font-weight:600;">Moi</span> @endif
                                    </div>
                                    <div class="user-role">{{ $entry->user->poste->intitule ?? '' }}</div>
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="hours-bar-wrap">
                                <div class="hours-bar">
                                    <div class="hours-bar__fill {{ $barClass }}" style="width:{{ $pct }}%;"></div>
                                </div>
                                <div class="hours-label">
                                    {{ UserHelper::hoursToHoursMinutes($entry->heures_reelles) }}
                                    / {{ UserHelper::hoursToHoursMinutes($entry->heures_theoriques) }}
                                </div>
                            </div>
                        </td>

                        <td>
                            @if($entry->est_manquant)
                                <span style="color:var(--ts-amber);font-size:13px;font-weight:600;">Non rempli</span>
                            @else
                                <strong>{{ $entry->timeEntries->count() }}</strong>
                                <span style="font-size:12px;color:var(--ts-slate);"> activité(s)</span>
                                @if($entry->commentaire)
                                <br><small style="color:var(--ts-slate);">{{ Str::limit($entry->commentaire, 40) }}</small>
                                @endif
                            @endif
                        </td>

                        <td>
                            @switch($entry->statut)
                                @case('soumis')
                                    <span class="badge-ts soumis"><i class="fas fa-hourglass-half"></i> Soumise</span>
                                    @break
                                @case('validé')
                                    <span class="badge-ts validé"><i class="fas fa-check"></i> Validée</span>
                                    @break
                                @case('refusé')
                                    <span class="badge-ts refusé" title="{{ $entry->motif_refus }}">
                                        <i class="fas fa-times"></i> Refusée
                                    </span>
                                    @break
                                @case('manquant')
                                    <span class="badge-ts manquant"><i class="fas fa-exclamation"></i> Manquant</span>
                                    @break
                            @endswitch
                        </td>

                        <td>
                            <div style="display:flex;gap:5px;align-items:center;">
                                @if(!$entry->est_manquant)
                                <a href="{{ route('daily-entries.show', $entry) }}"
                                   class="btn-action" title="Voir"><i class="fas fa-eye"></i></a>
                                @endif

                                @if($entry->est_manquant && $isMine)
                                <a href="{{ route('daily-entries.create', ['jour' => $entry->jour->format('Y-m-d')]) }}"
                                   class="btn-action validate" title="Remplir cette feuille">
                                   <i class="fas fa-edit"></i>
                                </a>
                                @elseif($isMine && $isSubmitted && !$entry->est_manquant)
                                <a href="{{ route('daily-entries.edit', $entry) }}"
                                   class="btn-action" title="Modifier"><i class="fas fa-edit"></i></a>
                                @endif

                                @if($canMgr && $isSubmitted)
                                <button class="btn-action validate validate-btn"
                                    data-id="{{ $entry->id }}"
                                    data-name="{{ $entry->user->prenom }} {{ $entry->user->nom }}"
                                    data-date="{{ $entry->jour->format('d/m/Y') }}"
                                    title="Valider"><i class="fas fa-check"></i></button>
                                @endif

                                @if($canMgr && in_array($entry->statut, ['soumis','refusé']))
                                <button class="btn-action reject reject-btn"
                                    data-id="{{ $entry->id }}"
                                    data-name="{{ $entry->user->prenom }} {{ $entry->user->nom }}"
                                    data-date="{{ $entry->jour->format('d/m/Y') }}"
                                    data-motif="{{ $entry->motif_refus }}"
                                    title="Refuser"><i class="fas fa-times"></i></button>
                                @endif

                                @if($isMine || auth()->user()->hasRole(['admin','super-admin']))
                                <button class="btn-action delete delete-btn"
                                    data-id="{{ $entry->id }}"
                                    data-name="{{ $entry->user->prenom }} {{ $entry->user->nom }}"
                                    data-date="{{ $entry->jour->format('d/m/Y') }}"
                                    title="Supprimer"><i class="fas fa-trash"></i></button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Pagination --}}
            <div style="padding:16px 20px;display:flex;justify-content:space-between;align-items:center;border-top:1px solid #F1F5F9;">
                <span style="font-size:13px;color:var(--ts-slate);">
                    {{ $dailyEntries->firstItem() }}–{{ $dailyEntries->lastItem() }} / {{ $dailyEntries->total() }}
                </span>
                {{ $dailyEntries->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>
        @endif

    </div>{{-- /ts-main --}}
</div>{{-- /ts-page --}}

{{-- ── Modal Export ── --}}
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;box-shadow:0 24px 60px rgba(0,0,0,.15);">
            <div class="modal-header" style="border-bottom:1px solid #F1F5F9;padding:20px 24px;">
                <h5 class="modal-title" style="font-family:'DM Sans',sans-serif;font-weight:700;">
                    <i class="fas fa-download" style="color:var(--ts-blue);margin-right:8px;"></i>Exporter
                </h5>
                <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
            </div>
            <form action="{{ route('daily-entries.export') }}" method="GET">
                <div class="modal-body" style="padding:24px;">
                    <div class="form-group">
                        <label style="font-size:13px;font-weight:600;color:#374151;">Période</label>
                        <div class="row">
                            <div class="col-6">
                                <label class="text-muted" style="font-size:12px;">Du</label>
                                <input type="date" name="date_debut" class="ts-input" value="{{ $dateDebut->format('Y-m-d') }}">
                            </div>
                            <div class="col-6">
                                <label class="text-muted" style="font-size:12px;">Au</label>
                                <input type="date" name="date_fin" class="ts-input" value="{{ $dateFin->format('Y-m-d') }}">
                            </div>
                        </div>
                    </div>
                    <div class="form-group" style="margin-top:16px;">
                        <label style="font-size:13px;font-weight:600;color:#374151;">Format</label>
                        <select name="format" class="ts-input">
                            <option value="excel">Excel (.xlsx)</option>
                            <option value="pdf">PDF</option>
                            <option value="csv">CSV</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #F1F5F9;padding:16px 24px;gap:10px;">
                    <button type="button" class="btn-ts outline" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn-ts primary">
                        <i class="fas fa-download"></i> Exporter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Modal Motif Refus ── --}}
<div class="ts-modal-overlay" id="reject-modal">
    <div class="ts-modal">
        <h3>Refuser la feuille</h3>
        <p id="reject-modal-subtitle">Feuille de temps</p>
        <div class="form-group">
            <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">
                Motif du refus <span style="color:var(--ts-red);">*</span>
            </label>
            <textarea id="reject-motif" class="ts-input" rows="4"
                placeholder="Expliquez pourquoi cette feuille est refusée…"
                style="resize:vertical;"></textarea>
        </div>
        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;">
            <button class="btn-ts outline" onclick="closeRejectModal()">Annuler</button>
            <button class="btn-ts danger" id="reject-confirm-btn">
                <i class="fas fa-times"></i> Confirmer le refus
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const CSRF = '{{ csrf_token() }}';
let currentRejectId = null, currentRejectType = null, currentRejectData = null;

// ── Toggle Grid / Table ──────────────────────────────────────
let showingTable = false;
function toggleView() {
    showingTable = !showingTable;
    document.getElementById('ts-main').classList.toggle('show-table', showingTable);
    document.getElementById('view-icon').className = showingTable ? 'fas fa-th' : 'fas fa-table';
}

// ── Sélection multiple ───────────────────────────────────────
function getSelectedIds() {
    return [...document.querySelectorAll('.entry-checkbox:checked')].map(c => c.value);
}

function updateBulkBar() {
    const ids   = getSelectedIds();
    const count = ids.length;
    const bar   = document.getElementById('bulk-bar');
    if (!bar) return;
    document.getElementById('selected-count').textContent = count;
    bar.classList.toggle('visible', count > 0);
    ['bulk-validate-btn','bulk-reject-btn'].forEach(id => {
        const b = document.getElementById(id);
        if (b) b.disabled = count === 0;
    });
}

document.getElementById('select-all')?.addEventListener('change', function() {
    document.querySelectorAll('.entry-checkbox').forEach(cb => cb.checked = this.checked);
    document.querySelectorAll('.entry-checkbox').forEach(cb => cb.closest('tr').classList.toggle('row-selected', this.checked));
    updateBulkBar();
});

document.addEventListener('change', function(e) {
    if (!e.target.matches('.entry-checkbox')) return;
    e.target.closest('tr').classList.toggle('row-selected', e.target.checked);
    updateBulkBar();
});

document.getElementById('deselect-all-btn')?.addEventListener('click', () => {
    document.querySelectorAll('.entry-checkbox').forEach(cb => cb.checked = false);
    document.querySelectorAll('tr').forEach(r => r.classList.remove('row-selected'));
    if (document.getElementById('select-all')) document.getElementById('select-all').checked = false;
    updateBulkBar();
});

// ── Validation individuelle ──────────────────────────────────
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.validate-btn');
    if (!btn) return;

    Swal.fire({
        title: 'Valider cette feuille ?',
        html: `<strong>${btn.dataset.name}</strong> — ${btn.dataset.date}`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Valider',
        cancelButtonText: 'Annuler',
        confirmButtonColor: '#059669',
        customClass: { popup: 'swal-ts' }
    }).then(r => {
        if (!r.isConfirmed) return;
        fetch(`/daily-entries/${btn.dataset.id}/validate`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF}
        }).then(() => location.reload());
    });
});

// ── Refus individuel — modal custom ─────────────────────────
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.reject-btn');
    if (!btn) return;
    currentRejectId   = btn.dataset.id;
    currentRejectType = 'single';
    document.getElementById('reject-modal-subtitle').textContent =
        `${btn.dataset.name} — ${btn.dataset.date}`;
    document.getElementById('reject-motif').value = btn.dataset.motif || '';
    document.getElementById('reject-modal').classList.add('open');
});

function closeRejectModal() {
    document.getElementById('reject-modal').classList.remove('open');
    currentRejectId = null; currentRejectType = null; currentRejectData = null;
}

document.getElementById('reject-confirm-btn')?.addEventListener('click', function() {
    const motif = document.getElementById('reject-motif').value.trim();
    if (!motif) {
        document.getElementById('reject-motif').style.borderColor = '#DC2626';
        return;
    }

    if (currentRejectType === 'single') {
        fetch(`/daily-entries/${currentRejectId}/reject`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF},
            body: JSON.stringify({motif_refus: motif})
        }).then(r => r.json()).then(d => {
            closeRejectModal();
            if (d.success) location.reload();
            else Swal.fire('Erreur', d.message, 'error');
        });
    } else if (currentRejectType === 'bulk') {
        fetch('{{ route("daily-entries.bulk-reject") }}', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF},
            body: JSON.stringify({ids: currentRejectData, motif_refus: motif})
        }).then(r => r.json()).then(d => {
            closeRejectModal();
            if (d.success) location.reload();
        });
    } else if (currentRejectType === 'week') {
        fetch('{{ route("daily-entries.week-reject") }}', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF},
            body: JSON.stringify({...currentRejectData, motif_refus: motif})
        }).then(r => r.json()).then(d => {
            closeRejectModal();
            if (d.success) location.reload();
        });
    }
});

// Fermer modal au clic overlay
document.getElementById('reject-modal')?.addEventListener('click', function(e) {
    if (e.target === this) closeRejectModal();
});

// ── Validation groupée ───────────────────────────────────────
document.getElementById('bulk-validate-btn')?.addEventListener('click', () => {
    const ids = getSelectedIds();
    Swal.fire({
        title: 'Valider la sélection ?',
        html: `<strong>${ids.length}</strong> feuille(s) sélectionnée(s)`,
        icon: 'question', showCancelButton: true,
        confirmButtonText: 'Valider', cancelButtonText: 'Annuler',
        confirmButtonColor: '#059669'
    }).then(r => {
        if (!r.isConfirmed) return;
        fetch('{{ route("daily-entries.bulk-validate") }}', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF},
            body: JSON.stringify({ids})
        }).then(r => r.json()).then(() => location.reload());
    });
});

document.getElementById('bulk-reject-btn')?.addEventListener('click', () => {
    const ids = getSelectedIds();
    currentRejectType = 'bulk';
    currentRejectData = ids;
    document.getElementById('reject-modal-subtitle').textContent = `${ids.length} feuille(s) sélectionnée(s)`;
    document.getElementById('reject-motif').value = '';
    document.getElementById('reject-modal').classList.add('open');
});

// ── Validation/Refus semaine collaborateur ───────────────────
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.week-validate-btn');
    if (!btn) return;
    Swal.fire({
        title: `Valider la semaine S${btn.dataset.semaine} ?`,
        html: `Toutes les feuilles soumises de <strong>${btn.dataset.userName}</strong>`,
        icon: 'question', showCancelButton: true,
        confirmButtonText: 'Valider la semaine', cancelButtonText: 'Annuler',
        confirmButtonColor: '#059669'
    }).then(r => {
        if (!r.isConfirmed) return;
        fetch('{{ route("daily-entries.week-validate") }}', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF},
            body: JSON.stringify({user_id: btn.dataset.userId, semaine: btn.dataset.semaine, annee: btn.dataset.annee})
        }).then(r => r.json()).then(d => {
            if (d.success) { Swal.fire({icon:'success',title:'Validé !',text:d.message,timer:1800,showConfirmButton:false}).then(() => location.reload()); }
            else Swal.fire('Erreur', d.message, 'error');
        });
    });
});

document.addEventListener('click', function(e) {
    const btn = e.target.closest('.week-reject-btn');
    if (!btn) return;
    currentRejectType = 'week';
    currentRejectData = {user_id: btn.dataset.userId, semaine: btn.dataset.semaine, annee: btn.dataset.annee};
    document.getElementById('reject-modal-subtitle').textContent =
        `Semaine S${btn.dataset.semaine} — ${btn.dataset.userName}`;
    document.getElementById('reject-motif').value = '';
    document.getElementById('reject-modal').classList.add('open');
});

// ── Tout valider (toutes soumises de la semaine) ─────────────
document.getElementById('validate-all-week-btn')?.addEventListener('click', function() {
    const subIds = @json(auth()->user()->subordinates()->pluck('id'));
    Swal.fire({
        title: 'Tout valider ?',
        html: `Valider toutes les feuilles soumises de la semaine <strong>S{{ $semaine }}</strong> ?`,
        icon: 'warning', showCancelButton: true,
        confirmButtonText: 'Oui, tout valider', cancelButtonText: 'Annuler',
        confirmButtonColor: '#059669'
    }).then(r => {
        if (!r.isConfirmed) return;
        // Utiliser bulk-validate sur toutes les IDs soumises visibles
        const ids = [...document.querySelectorAll('[data-statut="soumis"]')].map(tr => tr.dataset.id);
        fetch('{{ route("daily-entries.bulk-validate") }}', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF},
            body: JSON.stringify({ids})
        }).then(r => r.json()).then(d => {
            Swal.fire({icon:'success',title:'Validé !',text:d.message,timer:1800,showConfirmButton:false})
                .then(() => location.reload());
        });
    });
});

// ── Suppression ──────────────────────────────────────────────
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.delete-btn');
    if (!btn) return;
    Swal.fire({
        title: 'Supprimer ?',
        html: `Feuille de <strong>${btn.dataset.name}</strong> du <strong>${btn.dataset.date}</strong>`,
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#DC2626',
        confirmButtonText: 'Supprimer', cancelButtonText: 'Annuler'
    }).then(r => {
        if (!r.isConfirmed) return;
        fetch(`/daily-entries/${btn.dataset.id}`, {
            method: 'DELETE',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF}
        }).then(() => location.reload());
    });
});
</script>
@endpush