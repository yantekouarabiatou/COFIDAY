@extends('layaout')

@section('title', 'Feuille de Temps — ' . $dailyEntry->jour->format('d/m/Y'))

@section('content')

@php
    $statut      = $dailyEntry->statut;
    $statutColor = $statut === 'validé' ? '#10b981' : ($statut === 'refusé' ? '#ef4444' : '#3b82f6');
    $statutBg    = $statut === 'validé' ? '#d1fae5' : ($statut === 'refusé' ? '#fee2e2' : '#dbeafe');
    $statutText  = $statut === 'validé' ? '#065f46' : ($statut === 'refusé' ? '#991b1b' : '#1e40af');

    $pct         = $dailyEntry->heures_theoriques > 0
        ? min(($dailyEntry->heures_reelles / $dailyEntry->heures_theoriques) * 100, 100)
        : 0;

    $heuresSup   = max(0, $dailyEntry->heures_reelles - $dailyEntry->heures_theoriques);
    $canManage   = auth()->user()->hasRole(['manager','admin'])
                    && $dailyEntry->user_id !== auth()->id();
@endphp

<style>
/* ── Fonts ─────────────────────────────────────────────────────── */
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&family=DM+Mono:wght@400;500&display=swap');

/* ── Root tokens ────────────────────────────────────────────────── */
:root {
    --ff-body: 'DM Sans', sans-serif;
    --ff-mono: 'DM Mono', monospace;
    --c-bg:    #f8f7f4;
    --c-card:  #ffffff;
    --c-border:#e8e5df;
    --c-text:  #1a1816;
    --c-muted: #78716c;
    --c-orange:#244584;
    --c-orange-light: #e8edf7;
    --c-orange-mid:   #3a5fa8;
    --radius-lg: 16px;
    --radius-md: 10px;
    --radius-sm: 6px;
    --shadow-sm: 0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
    --shadow-md: 0 4px 16px rgba(0,0,0,.08), 0 2px 6px rgba(0,0,0,.04);
    --shadow-lg: 0 12px 40px rgba(0,0,0,.10), 0 4px 12px rgba(0,0,0,.05);
}

/* ── Page wrapper ───────────────────────────────────────────────── */
.fte-page { font-family: var(--ff-body); color: var(--c-text); }

/* ── Hero band ──────────────────────────────────────────────────── */
.fte-hero {
    background: linear-gradient(135deg, #244584 0%, #4b79c8 100%);
    border-radius: var(--radius-lg);
    padding: 2rem 2.5rem;
    margin-bottom: 1.75rem;
    position: relative;
    overflow: hidden;
}
.fte-hero::before {
    content: '';
    position: absolute; inset: 0;
    background: radial-gradient(ellipse 60% 80% at 90% 50%, rgba(36,69,132,.18) 0%, transparent 70%);
    pointer-events: none;
}
.fte-hero-avatar {
    width: 56px; height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--c-orange), #3a5fa8);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; font-weight: 600; color: #fff;
    flex-shrink: 0;
    box-shadow: 0 0 0 3px rgba(36,69,132,.25);
}
.fte-hero h2 { font-size: 1.3rem; font-weight: 600; color: #fff; margin: 0; }
.fte-hero .fte-meta { font-size: .82rem; color: rgba(255,255,255,.55); margin-top: .2rem; }
.fte-statut-pill {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .35rem .9rem;
    border-radius: 999px;
    font-size: .78rem; font-weight: 600; letter-spacing: .03em; text-transform: uppercase;
    background: {{ $statutBg }}; color: {{ $statutText }};
}
.fte-statut-dot {
    width: 7px; height: 7px; border-radius: 50%;
    background: {{ $statutColor }};
    box-shadow: 0 0 0 2px {{ $statutColor }}40;
}
.fte-date-badge {
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.12);
    color: rgba(255,255,255,.8);
    border-radius: var(--radius-sm);
    padding: .3rem .8rem;
    font-size: .82rem; font-family: var(--ff-mono);
}

/* ── Action bar ─────────────────────────────────────────────────── */
.fte-actions {
    display: flex; flex-wrap: wrap; gap: .6rem;
    margin-bottom: 1.75rem;
}
.fte-btn {
    display: inline-flex; align-items: center; gap: .45rem;
    padding: .5rem 1.1rem;
    border-radius: var(--radius-md);
    font-size: .85rem; font-weight: 500;
    border: none; cursor: pointer;
    transition: all .18s ease;
    text-decoration: none;
    white-space: nowrap;
}
.fte-btn:hover { transform: translateY(-1px); text-decoration: none; }
.fte-btn-primary   { background: var(--c-orange); color: #fff; box-shadow: 0 2px 8px rgba(36,69,132,.35); }
.fte-btn-primary:hover { background: #1a3a6e; color: #fff; box-shadow: 0 4px 14px rgba(36,69,132,.4); }
.fte-btn-success   { background: #10b981; color: #fff; box-shadow: 0 2px 8px rgba(16,185,129,.3); }
.fte-btn-success:hover { background: #059669; color: #fff; }
.fte-btn-danger    { background: #ef4444; color: #fff; box-shadow: 0 2px 8px rgba(239,68,68,.3); }
.fte-btn-danger:hover  { background: #dc2626; color: #fff; }
.fte-btn-warning   { background: #f59e0b; color: #fff; box-shadow: 0 2px 8px rgba(245,158,11,.3); }
.fte-btn-warning:hover { background: #d97706; color: #fff; }
.fte-btn-ghost {
    background: transparent; color: var(--c-muted);
    border: 1.5px solid var(--c-border);
}
.fte-btn-ghost:hover { background: var(--c-bg); color: var(--c-text); }
.fte-btn-info { background: #3b82f6; color: #fff; box-shadow: 0 2px 8px rgba(59,130,246,.3); }
.fte-btn-info:hover { background: #2563eb; color: #fff; }

/* ── KPI cards ──────────────────────────────────────────────────── */
.fte-kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.75rem; }
.fte-kpi {
    background: var(--c-card);
    border: 1px solid var(--c-border);
    border-radius: var(--radius-lg);
    padding: 1.25rem 1.4rem;
    box-shadow: var(--shadow-sm);
    position: relative; overflow: hidden;
    transition: box-shadow .2s, transform .2s;
}
.fte-kpi:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }
.fte-kpi-icon {
    width: 38px; height: 38px; border-radius: var(--radius-md);
    display: flex; align-items: center; justify-content: center;
    font-size: .95rem; margin-bottom: .85rem;
}
.fte-kpi-label { font-size: .75rem; font-weight: 500; color: var(--c-muted); text-transform: uppercase; letter-spacing: .05em; margin-bottom: .25rem; }
.fte-kpi-value { font-size: 1.55rem; font-weight: 600; color: var(--c-text); font-family: var(--ff-mono); line-height: 1; }
.fte-kpi-sub   { font-size: .75rem; color: var(--c-muted); margin-top: .3rem; }
.fte-kpi-accent { position: absolute; bottom: 0; left: 0; right: 0; height: 3px; border-radius: 0 0 var(--radius-lg) var(--radius-lg); }

/* Progress ring */
.fte-ring { width: 56px; height: 56px; flex-shrink: 0; }
.fte-ring-track { fill: none; stroke: #f3f4f6; stroke-width: 6; }
.fte-ring-fill  { fill: none; stroke-width: 6; stroke-linecap: round;
    transform: rotate(-90deg); transform-origin: 50% 50%;
    transition: stroke-dashoffset 1s cubic-bezier(.4,0,.2,1); }

/* ── Section card ───────────────────────────────────────────────── */
.fte-card {
    background: var(--c-card);
    border: 1px solid var(--c-border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    margin-bottom: 1.5rem;
    overflow: hidden;
}
.fte-card-head {
    padding: 1.1rem 1.5rem;
    border-bottom: 1px solid var(--c-border);
    display: flex; align-items: center; gap: .7rem;
}
.fte-card-head-icon {
    width: 32px; height: 32px;
    background: var(--c-orange-light);
    border-radius: var(--radius-sm);
    display: flex; align-items: center; justify-content: center;
    color: var(--c-orange); font-size: .85rem;
}
.fte-card-head h5 { margin: 0; font-size: .95rem; font-weight: 600; }
.fte-card-body { padding: 1.5rem; }

/* ── Alert boxes ────────────────────────────────────────────────── */
.fte-alert {
    border-radius: var(--radius-md);
    padding: 1rem 1.25rem;
    display: flex; gap: .85rem; align-items: flex-start;
    margin-bottom: 1.25rem;
}
.fte-alert-icon { font-size: 1.1rem; margin-top: .1rem; flex-shrink: 0; }
.fte-alert-comment { background: #fffbeb; border: 1px solid #fde68a; }
.fte-alert-comment .fte-alert-icon { color: #d97706; }
.fte-alert-refuse  { background: #fef2f2; border: 1px solid #fecaca; }
.fte-alert-refuse .fte-alert-icon  { color: #ef4444; }
.fte-alert-title  { font-size: .82rem; font-weight: 600; margin-bottom: .2rem; }
.fte-alert-body   { font-size: .88rem; line-height: 1.55; }

/* ── Activities table ───────────────────────────────────────────── */
.fte-table { width: 100%; border-collapse: collapse; font-size: .875rem; }
.fte-table th {
    padding: .75rem 1rem;
    font-size: .72rem; font-weight: 600; text-transform: uppercase;
    letter-spacing: .06em; color: var(--c-muted);
    border-bottom: 1px solid var(--c-border);
    text-align: left; white-space: nowrap;
}
.fte-table td {
    padding: .85rem 1rem;
    border-bottom: 1px solid #f5f4f1;
    vertical-align: middle;
}
.fte-table tbody tr:last-child td { border-bottom: none; }
.fte-table tbody tr:hover td { background: #fafaf9; }
.fte-table tfoot td {
    padding: .85rem 1rem;
    border-top: 2px solid var(--c-border);
    font-weight: 600;
}
.fte-badge-time {
    display: inline-block;
    background: #f3f4f6; color: var(--c-text);
    border-radius: var(--radius-sm);
    padding: .2rem .55rem;
    font-family: var(--ff-mono); font-size: .82rem;
}
.fte-dossier-name { font-weight: 600; }
.fte-dossier-ref  { font-size: .75rem; color: var(--c-muted); font-family: var(--ff-mono); }
.fte-num-badge {
    width: 24px; height: 24px; border-radius: 50%;
    background: var(--c-orange-light); color: var(--c-orange);
    display: inline-flex; align-items: center; justify-content: center;
    font-size: .72rem; font-weight: 600;
}

/* ── Chart & legend ─────────────────────────────────────────────── */
.fte-legend-item {
    display: flex; align-items: center; gap: .65rem;
    padding: .65rem 0;
    border-bottom: 1px solid #f5f4f1;
}
.fte-legend-item:last-child { border-bottom: none; }
.fte-legend-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.fte-legend-name { font-weight: 500; font-size: .88rem; }
.fte-legend-client { font-size: .75rem; color: var(--c-muted); }
.fte-legend-hours { font-family: var(--ff-mono); font-size: .85rem; font-weight: 500; margin-left: auto; }

/* ── Validated by banner ────────────────────────────────────────── */
.fte-validated-banner {
    background: linear-gradient(135deg, #d1fae5, #ecfdf5);
    border: 1px solid #6ee7b7;
    border-radius: var(--radius-md);
    padding: .85rem 1.25rem;
    display: flex; align-items: center; gap: .75rem;
    margin-bottom: 1.5rem;
    font-size: .875rem;
}

/* ── Animations ─────────────────────────────────────────────────── */
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(14px); }
    to   { opacity: 1; transform: translateY(0); }
}
.fte-hero     { animation: fadeUp .4s ease both; }
.fte-actions  { animation: fadeUp .4s .08s ease both; }
.fte-kpi      { animation: fadeUp .4s ease both; }
.fte-kpi:nth-child(1) { animation-delay: .12s; }
.fte-kpi:nth-child(2) { animation-delay: .18s; }
.fte-kpi:nth-child(3) { animation-delay: .24s; }
.fte-kpi:nth-child(4) { animation-delay: .30s; }
.fte-kpi:nth-child(5) { animation-delay: .36s; }
.fte-card { animation: fadeUp .4s .28s ease both; }

@media (max-width: 767px) {
    .fte-hero { padding: 1.5rem; }
    .fte-hero h2 { font-size: 1.1rem; }
    .fte-kpis { grid-template-columns: repeat(2, 1fr); }
    .fte-btn  { padding: .45rem .85rem; font-size: .8rem; }
}
</style>

<section class="section fte-page">
    <div class="section-header">
        <h1><i class="fas fa-clock"></i> Feuille de Temps</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item">
                <a href="{{ route('daily-entries.index') }}">Feuilles</a>
            </div>
            <div class="breadcrumb-item active">Détails</div>
        </div>
    </div>

    <div class="section-body">

        {{-- ─── Hero ──────────────────────────────────────────────── --}}
        <div class="fte-hero">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="fte-hero-avatar">
                        {{ strtoupper(substr($dailyEntry->user->prenom ?? 'U', 0, 1)) }}
                    </div>
                    <div>
                        <h2>{{ $dailyEntry->user->prenom }} {{ $dailyEntry->user->nom }}</h2>
                        <div class="fte-meta">
                            {{ $dailyEntry->user->poste->intitule ?? 'Poste non défini' }}
                            &nbsp;·&nbsp;
                            {{ $dailyEntry->user->email }}
                            &nbsp;·&nbsp;
                            Créée le {{ $dailyEntry->created_at->format('d/m/Y à H:i') }}
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="fte-date-badge">
                        <i class="fas fa-calendar-alt mr-1"></i>
                        {{ $dailyEntry->jour->format('d/m/Y') }}
                        &nbsp;—&nbsp;
                        {{ ucfirst($dailyEntry->jour->translatedFormat('l')) }}
                    </div>
                    <div class="fte-statut-pill">
                        <span class="fte-statut-dot"></span>
                        {{ ucfirst($dailyEntry->statut) }}
                    </div>
                </div>
            </div>
        </div>

        {{-- ─── Action bar ────────────────────────────────────────── --}}
        <div class="fte-actions">
            <a href="{{ route('daily-entries.index') }}" class="fte-btn fte-btn-ghost">
                <i class="fas fa-arrow-left"></i> Retour
            </a>

            @if($dailyEntry->user_id == auth()->id() && $dailyEntry->statut == 'soumis')
                <a href="{{ route('daily-entries.edit', $dailyEntry) }}" class="fte-btn fte-btn-warning">
                    <i class="fas fa-edit"></i> Modifier
                </a>
            @endif

            @if($canManage && $dailyEntry->statut == 'soumis')
                <button type="button" class="fte-btn fte-btn-success" id="validate-btn">
                    <i class="fas fa-check"></i> Valider
                </button>
            @endif

            @if($canManage && in_array($dailyEntry->statut, ['soumis', 'refusé']))
                <button type="button" class="fte-btn fte-btn-danger" id="reject-btn"
                        data-motif="{{ $dailyEntry->motif_refus }}">
                    <i class="fas fa-times"></i>
                    {{ $dailyEntry->statut === 'refusé' ? 'Modifier le motif' : 'Refuser' }}
                </button>
            @endif

            <a href="{{ route('daily-entries.pdf', $dailyEntry) }}" target="_blank"
               class="fte-btn fte-btn-info">
                <i class="fas fa-file-pdf"></i> PDF
            </a>

            <a href="{{ route('daily-entries.create') }}" class="fte-btn fte-btn-primary">
                <i class="fas fa-plus"></i> Nouvelle saisie
            </a>
        </div>

        {{-- ─── Bannière validée ──────────────────────────────────── --}}
        @if($dailyEntry->statut === 'validé' && $dailyEntry->valide_le)
            <div class="fte-validated-banner">
                <i class="fas fa-check-circle text-success" style="font-size:1.2rem;"></i>
                <span>
                    Feuille validée le <strong>{{ $dailyEntry->valide_le->format('d/m/Y à H:i') }}</strong>
                    @if($dailyEntry->valide_par_user)
                        par <strong>{{ $dailyEntry->valide_par_user->prenom }} {{ $dailyEntry->valide_par_user->nom }}</strong>
                    @endif
                </span>
            </div>
        @endif

        {{-- ─── Alertes commentaire / refus ──────────────────────── --}}
        @if($dailyEntry->commentaire)
            <div class="fte-alert fte-alert-comment">
                <i class="fas fa-comment fte-alert-icon"></i>
                <div>
                    <div class="fte-alert-title">Commentaire</div>
                    <div class="fte-alert-body">{{ $dailyEntry->commentaire }}</div>
                </div>
            </div>
        @endif

        @if($dailyEntry->statut === 'refusé' && $dailyEntry->motif_refus)
            <div class="fte-alert fte-alert-refuse">
                <i class="fas fa-exclamation-triangle fte-alert-icon"></i>
                <div>
                    <div class="fte-alert-title">Motif du refus</div>
                    <div class="fte-alert-body">{{ $dailyEntry->motif_refus }}</div>
                    @if($dailyEntry->valide_par_user)
                        <div style="font-size:.75rem;color:#b91c1c;margin-top:.4rem;">
                            Refusé par {{ $dailyEntry->valide_par_user->prenom }}
                            le {{ $dailyEntry->valide_le->format('d/m/Y à H:i') }}
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- ─── KPIs ──────────────────────────────────────────────── --}}
        @php
            $hT = floor($dailyEntry->heures_theoriques);
            $mT = round(($dailyEntry->heures_theoriques - $hT) * 60);
            $hR = floor($dailyEntry->heures_reelles);
            $mR = round(($dailyEntry->heures_reelles - $hR) * 60);
            $hS = floor($heuresSup);
            $mS = round(($heuresSup - $hS) * 60);
            $circumference = 2 * pi() * 22; // r=22
            $offset = $circumference - ($pct / 100) * $circumference;
            $ringColor = $pct >= 100 ? '#10b981' : ($pct >= 80 ? '#f59e0b' : '#ef4444');
        @endphp

        <div class="fte-kpis">

            {{-- Heures théoriques --}}
            <div class="fte-kpi">
                <div class="fte-kpi-icon" style="background:#eff6ff;color:#3b82f6;">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="fte-kpi-label">Théoriques</div>
                <div class="fte-kpi-value">{{ $hT }}h{{ $mT > 0 ? $mT.'m' : '' }}</div>
                <div class="fte-kpi-sub">Durée contractuelle</div>
                <div class="fte-kpi-accent" style="background:#3b82f6;"></div>
            </div>

            {{-- Heures réelles --}}
            <div class="fte-kpi">
                <div class="fte-kpi-icon" style="background:#f0fdf4;color:#10b981;">
                    <i class="fas fa-stopwatch"></i>
                </div>
                <div class="fte-kpi-label">Réelles</div>
                <div class="fte-kpi-value">{{ $hR }}h{{ $mR > 0 ? $mR.'m' : '' }}</div>
                <div class="fte-kpi-sub">Heures saisies</div>
                <div class="fte-kpi-accent" style="background:#10b981;"></div>
            </div>

            {{-- Taux de remplissage --}}
            <div class="fte-kpi" style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
                <div style="flex:1;min-width:100px;">
                    <div class="fte-kpi-label">Taux de remplissage</div>
                    <div class="fte-kpi-value" style="color:{{ $ringColor }};">
                        {{ number_format($pct, 1) }}%
                    </div>
                    <div class="fte-kpi-sub">
                        {{ $pct >= 100 ? 'Objectif atteint' : 'En cours' }}
                    </div>
                </div>
                <svg class="fte-ring" viewBox="0 0 50 50">
                    <circle class="fte-ring-track" cx="25" cy="25" r="22"/>
                    <circle class="fte-ring-fill"
                            cx="25" cy="25" r="22"
                            stroke="{{ $ringColor }}"
                            stroke-dasharray="{{ $circumference }}"
                            stroke-dashoffset="{{ $offset }}"
                            id="ring-fill"/>
                </svg>
                <div class="fte-kpi-accent" style="background:{{ $ringColor }};"></div>
            </div>

            {{-- Heures sup --}}
            @if($heuresSup > 0)
            <div class="fte-kpi">
                <div class="fte-kpi-icon" style="background:#edf1fb;color:#3a5fa8;">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <div class="fte-kpi-label">Heures sup.</div>
                <div class="fte-kpi-value" style="color:#3a5fa8;">
                    +{{ $hS }}h{{ $mS > 0 ? $mS.'m' : '' }}
                </div>
                <div class="fte-kpi-sub">Au-delà du contrat</div>
                <div class="fte-kpi-accent" style="background:#3a5fa8;"></div>
            </div>
            @endif

            {{-- Activités --}}
            <div class="fte-kpi">
                <div class="fte-kpi-icon" style="background:#faf5ff;color:#8b5cf6;">
                    <i class="fas fa-tasks"></i>
                </div>
                <div class="fte-kpi-label">Activités</div>
                <div class="fte-kpi-value">{{ $dailyEntry->timeEntries->count() }}</div>
                <div class="fte-kpi-sub">Entrées saisies</div>
                <div class="fte-kpi-accent" style="background:#8b5cf6;"></div>
            </div>

        </div>

        {{-- ─── Activités ──────────────────────────────────────────── --}}
        <div class="fte-card">
            <div class="fte-card-head">
                <div class="fte-card-head-icon"><i class="fas fa-list-ul"></i></div>
                <h5>Activités réalisées</h5>
                <span class="ml-auto" style="font-size:.78rem;color:var(--c-muted);">
                    {{ $dailyEntry->timeEntries->count() }} entrée{{ $dailyEntry->timeEntries->count() > 1 ? 's' : '' }}
                </span>
            </div>
            <div class="fte-card-body p-0">
                <div class="table-responsive">
                    <table class="fte-table">
                        <thead>
                            <tr>
                                <th style="width:40px;">#</th>
                                <th>Dossier</th>
                                <th>Client</th>
                                <th class="text-center">Début</th>
                                <th class="text-center">Fin</th>
                                <th class="text-center">Durée</th>
                                <th>Travaux réalisés</th>
                                <th>Rendu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dailyEntry->timeEntries as $index => $item)
                            <tr>
                                <td><span class="fte-num-badge">{{ $index + 1 }}</span></td>
                                <td>
                                    <div class="fte-dossier-name">{{ $item->dossier->nom }}</div>
                                    <div class="fte-dossier-ref">{{ $item->dossier->reference ?? '—' }}</div>
                                </td>
                                <td>
                                    <div style="font-weight:500;font-size:.88rem;">
                                        {{ $item->dossier->client->nom ?? '—' }}
                                    </div>
                                    <div style="font-size:.75rem;color:var(--c-muted);">
                                        {{ $item->dossier->type_dossier }}
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="fte-badge-time">
                                        {{ \Carbon\Carbon::parse($item->heure_debut)->format('H:i') }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="fte-badge-time">
                                        {{ \Carbon\Carbon::parse($item->heure_fin)->format('H:i') }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="fte-badge-time" style="background:var(--c-orange-light);color:var(--c-orange);">
                                        {{ \Carbon\Carbon::parse($item->heure_fin)->diff(\Carbon\Carbon::parse($item->heure_debut))->format('%hh%Imin') }}
                                    </span>
                                </td>
                                <td style="font-size:.875rem;max-width:200px;">
                                    @if($item->travaux)
                                        {{ $item->travaux }}
                                    @else
                                        <span style="color:var(--c-muted);font-style:italic;">—</span>
                                    @endif
                                </td>
                                <td style="font-size:.875rem;max-width:200px;">
                                    @if($item->rendu)
                                        {{ $item->rendu }}
                                    @else
                                        <span style="color:var(--c-muted);font-style:italic;">—</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-right" style="color:var(--c-muted);font-size:.82rem;">
                                    Total heures saisies
                                </td>
                                <td class="text-center">
                                    <span class="fte-badge-time"
                                          style="background:var(--c-text);color:#fff;font-weight:600;font-size:.88rem;">
                                        {{ $hR }}h{{ $mR > 0 ? $mR.'m' : '' }}
                                    </span>
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- ─── Répartition ────────────────────────────────────────── --}}
        @php
            $hoursByDossier = $dailyEntry->timeEntries
                ->groupBy('dossier_id')
                ->map(fn($e) => $e->sum('heures_reelles'))
                ->sortDesc();

            $colorPalette = ['#244584','#3b82f6','#10b981','#f59e0b','#8b5cf6','#ef4444','#06b6d4'];
            $labels = []; $chartData = []; $chartColors = []; $ci = 0;
            foreach($hoursByDossier as $did => $hrs) {
                $d = $dailyEntry->timeEntries->firstWhere('dossier_id', $did)->dossier;
                $labels[]     = $d->nom;
                $chartData[]  = $hrs;
                $chartColors[]= $colorPalette[$ci % count($colorPalette)];
                $ci++;
            }
        @endphp

        <div class="fte-card">
            <div class="fte-card-head">
                <div class="fte-card-head-icon"><i class="fas fa-chart-pie"></i></div>
                <h5>Répartition par dossier</h5>
            </div>
            <div class="fte-card-body">
                <div class="row align-items-center">
                    <div class="col-md-6 d-flex justify-content-center">
                        <div style="max-width:280px;width:100%;">
                            <canvas id="timeChart" height="280"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6 mt-3 mt-md-0">
                        @foreach($hoursByDossier as $did => $hrs)
                            @php
                                $d     = $dailyEntry->timeEntries->firstWhere('dossier_id', $did)->dossier;
                                $hh    = floor($hrs);
                                $mm    = round(($hrs - $hh) * 60);
                                $cidx  = array_search($hrs, $chartData);
                                $col   = $chartColors[$cidx % count($chartColors)] ?? '#244584';
                                $pcDos = $dailyEntry->heures_reelles > 0
                                    ? round(($hrs / $dailyEntry->heures_reelles) * 100, 1)
                                    : 0;
                            @endphp
                            <div class="fte-legend-item">
                                <span class="fte-legend-dot" style="background:{{ $col }};"></span>
                                <div style="flex:1;min-width:0;">
                                    <div class="fte-legend-name">{{ $d->nom }}</div>
                                    <div class="fte-legend-client">{{ $d->client->nom ?? '—' }} · {{ $pcDos }}%</div>
                                </div>
                                <span class="fte-legend-hours">{{ $hh }}h{{ $mm > 0 ? $mm.'m' : '' }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /section-body --}}
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function () {

    // ── Doughnut chart ───────────────────────────────────────────────
    const ctx = document.getElementById('timeChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: @json($labels),
            datasets: [{
                data: @json($chartData),
                backgroundColor: @json($chartColors),
                borderWidth: 2,
                borderColor: '#fff',
                hoverBorderColor: '#fff',
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            cutout: '70%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            const pct   = Math.round((ctx.raw / total) * 100);
                            return ` ${ctx.raw}h (${pct}%)`;
                        }
                    }
                }
            }
        }
    });

    // ── Validation ───────────────────────────────────────────────────
    $('#validate-btn').on('click', function () {
        Swal.fire({
            title: 'Valider cette feuille ?',
            text: 'Cette action est réversible si nécessaire.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fas fa-check mr-1"></i> Oui, valider',
            cancelButtonText: 'Annuler'
        }).then(result => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: '{{ route("daily-entries.validate", $dailyEntry) }}',
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: () => Swal.fire({ icon:'success', title:'Validée !', timer:1500, showConfirmButton:false })
                    .then(() => location.reload()),
                error: xhr => Swal.fire('Erreur', xhr.responseJSON?.message || 'Impossible de valider.', 'error')
            });
        });
    });

    // ── Refus ────────────────────────────────────────────────────────
    $('#reject-btn').on('click', function () {
        const motif = $(this).data('motif') || '';
        Swal.fire({
            title: 'Motif du refus',
            input: 'textarea',
            inputLabel: 'Veuillez indiquer le motif',
            inputPlaceholder: 'Ex : heures insuffisantes, activités non conformes…',
            inputValue: motif,
            inputAttributes: { rows: 4 },
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fas fa-times mr-1"></i> Refuser',
            cancelButtonText: 'Annuler',
            inputValidator: v => !v?.trim() && 'Le motif est obligatoire.'
        }).then(result => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: '{{ route("daily-entries.reject", $dailyEntry) }}',
                method: 'POST',
                data: { _token: '{{ csrf_token() }}', motif_refus: result.value },
                success: res => {
                    if (res.success) {
                        Swal.fire({ icon:'success', title:'Refusée', text:res.message, timer:1500, showConfirmButton:false })
                            .then(() => location.reload());
                    } else {
                        Swal.fire('Erreur', res.message, 'error');
                    }
                },
                error: xhr => Swal.fire('Erreur', xhr.responseJSON?.message || 'Impossible de refuser.', 'error')
            });
        });
    });
});
</script>
@endpush
