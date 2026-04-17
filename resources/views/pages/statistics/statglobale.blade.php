@extends('layaout')

@section('title', 'Statistiques Globales — Administration')

@section('content')
<div class="stats-wrapper">

    {{-- ═══ EN-TÊTE ═══ --}}
    <div class="stats-header">
        <div class="header-content">
            <div class="header-left">
                <div class="header-icon-ring">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div>
                    <h1 class="header-title">Statistiques Globales</h1>
                    <p class="header-sub">Tableau de bord administrateur · <span id="periode-label">Ce mois</span></p>
                </div>
            </div>
            <div class="header-right">
                <button class="btn-header" onclick="refreshStats()">
                    <i class="fas fa-sync-alt" id="refresh-icon"></i> Actualiser
                </button>
            </div>
        </div>
        <div class="header-line"></div>
    </div>

    {{-- ═══ FILTRES ═══ --}}
    <div class="filters-panel">
        <div class="filters-title"><i class="fas fa-sliders-h"></i> Filtres</div>
        <div class="filters-row">
            <div class="filter-group">
                <label class="filter-label">Période</label>
                <select class="filter-select" id="filtre-periode">
                    <option value="jour">Aujourd'hui</option>
                    <option value="semaine">Cette semaine</option>
                    <option value="mois" selected>Ce mois</option>
                    <option value="annee">Cette année</option>
                    <option value="personnalise">Personnalisée</option>
                </select>
            </div>
            <div class="filter-group custom-date" id="filtre-dates" style="display:none;">
                <label class="filter-label">Début</label>
                <input type="date" class="filter-select" id="date-debut">
            </div>
            <div class="filter-group custom-date" id="filtre-dates-fin" style="display:none;">
                <label class="filter-label">Fin</label>
                <input type="date" class="filter-select" id="date-fin">
            </div>
            <div class="filter-group filter-employe">
                <label class="filter-label">Employé</label>
                <select class="filter-select select2" id="filtre-employe">
                    <option value="">Tous les employés</option>
                </select>
            </div>
            <div class="filter-group filter-action">
                <label class="filter-label">&nbsp;</label>
                <button class="btn-apply" onclick="applyFilters()">
                    <i class="fas fa-search"></i> Appliquer
                </button>
            </div>
        </div>
        <div id="filtre-info" style="display:none;" class="filter-active-bar">
            <i class="fas fa-info-circle"></i>
            <strong>Filtres actifs :</strong> <span id="filtre-details"></span>
            <button class="btn-reset" onclick="resetFilters()"><i class="fas fa-times"></i> Réinitialiser</button>
        </div>
    </div>

    {{-- ═══ CARTES KPI (sans les heures) ═══ --}}
    <div class="kpi-grid">

        {{-- Employés --}}
        <div class="kpi-card kpi-blue" style="animation-delay:.05s">
            <div class="kpi-bg-icon"><i class="fas fa-users"></i></div>
            <div class="kpi-inner">
                <div class="kpi-icon"><i class="fas fa-users"></i></div>
                <div class="kpi-info">
                    <span class="kpi-label">Employés</span>
                    <span class="kpi-value" id="stat-employes"><i class="fas fa-spinner fa-spin"></i></span>
                    <span class="kpi-sub"><span id="stat-employes-actifs">--</span> actifs sur la période</span>
                </div>
            </div>
        </div>

        {{-- Congés --}}
        <div class="kpi-card kpi-amber" style="animation-delay:.10s">
            <div class="kpi-bg-icon"><i class="fas fa-calendar-alt"></i></div>
            <div class="kpi-inner">
                <div class="kpi-icon"><i class="fas fa-calendar-alt"></i></div>
                <div class="kpi-info">
                    <span class="kpi-label">Congés</span>
                    <span class="kpi-value" id="stat-conges"><i class="fas fa-spinner fa-spin"></i></span>
                    <span class="kpi-sub"><span id="stat-conges-cours">--</span> en cours actuellement</span>
                </div>
            </div>
        </div>

        {{-- Attestations --}}
        <div class="kpi-card kpi-violet" style="animation-delay:.15s">
            <div class="kpi-bg-icon"><i class="fas fa-file-alt"></i></div>
            <div class="kpi-inner">
                <div class="kpi-icon"><i class="fas fa-file-alt"></i></div>
                <div class="kpi-info">
                    <span class="kpi-label">Attestations</span>
                    <span class="kpi-value" id="stat-attestations"><i class="fas fa-spinner fa-spin"></i></span>
                    <span class="kpi-sub"><span id="stat-attestations-validees">--</span> approuvées</span>
                </div>
            </div>
        </div>

        {{-- Certificats --}}
        <div class="kpi-card kpi-rose" style="animation-delay:.20s">
            <div class="kpi-bg-icon"><i class="fas fa-certificate"></i></div>
            <div class="kpi-inner">
                <div class="kpi-icon"><i class="fas fa-certificate"></i></div>
                <div class="kpi-info">
                    <span class="kpi-label">Certificats de travail</span>
                    <span class="kpi-value" id="stat-certificats"><i class="fas fa-spinner fa-spin"></i></span>
                    <span class="kpi-sub"><span id="stat-certificats-acceptes">--</span> acceptés</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ GRAPHIQUES ═══ --}}
    <div class="charts-grid">
        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-title-wrap">
                    <span class="chart-dot dot-violet"></span>
                    <h3 class="chart-title">Attestations par type</h3>
                </div>
                <span class="chart-badge">Donut</span>
            </div>
            <div class="chart-body">
                <div id="no-attest-types" class="chart-empty" style="display:none;">
                    <i class="fas fa-chart-pie"></i>
                    <p>Aucune donnée</p>
                </div>
                <canvas id="chartAttestationsTypes"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-title-wrap">
                    <span class="chart-dot dot-blue"></span>
                    <h3 class="chart-title">Évolution des demandes</h3>
                </div>
                <span class="chart-badge">Ligne</span>
            </div>
            <div class="chart-body">
                <div id="no-evolution" class="chart-empty" style="display:none;">
                    <i class="fas fa-chart-line"></i>
                    <p>Aucune donnée</p>
                </div>
                <canvas id="chartEvolutionDemandes"></canvas>
            </div>
        </div>
    </div>

    {{-- ═══ TABLEAU DERNIÈRES DEMANDES ═══ --}}
    <div class="table-card">
        <div class="table-card-header">
            <div class="chart-title-wrap">
                <span class="chart-dot dot-teal"></span>
                <h3 class="chart-title">Dernières attestations &amp; certificats</h3>
            </div>
            <span class="table-count" id="table-count"></span>
        </div>
        <div class="table-responsive">
            <table class="stats-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Employé</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th>Référence</th>
                    </tr>
                </thead>
                <tbody id="table-demandes">
                    <tr>
                        <td colspan="5" class="table-loading">
                            <i class="fas fa-spinner fa-spin"></i> Chargement en cours…
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div><!-- /.stats-wrapper -->
@endsection

@push('styles')
<style>
/* ═══════════════════════════════════════════════
   THEME CLAIR (LIGHT)
═══════════════════════════════════════════════ */
@import url('https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:wght@300;400;500&display=swap');

:root {
    --c-bg:        #f8f9fc;
    --c-surface:   #ffffff;
    --c-surface2:  #f1f3f8;
    --c-border:    #e9edf2;
    --c-text:      #1e293b;
    --c-muted:     #64748b;

    --c-blue:      #3b7fff;
    --c-blue-glow: rgba(59,127,255,.12);
    --c-teal:      #0ecfc5;
    --c-teal-glow: rgba(14,207,197,.12);
    --c-amber:     #f5a623;
    --c-amber-glow:rgba(245,166,35,.12);
    --c-violet:    #a259ff;
    --c-violet-glow:rgba(162,89,255,.12);
    --c-rose:      #ff5277;
    --c-rose-glow: rgba(255,82,119,.12);

    --radius:      14px;
    --shadow:      0 8px 30px rgba(0,0,0,.05);
    --trans:       .22s cubic-bezier(.4,0,.2,1);
}

.stats-wrapper {
    font-family: 'DM Sans', sans-serif;
    background: var(--c-bg);
    min-height: 100vh;
    padding: 32px 28px 60px;
    color: var(--c-text);
}

/* HEADER */
.stats-header { margin-bottom: 28px; }
.header-content { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px; }
.header-left { display:flex; align-items:center; gap:18px; }
.header-icon-ring {
    width:52px; height:52px; border-radius:50%;
    background: linear-gradient(135deg, var(--c-blue), var(--c-violet));
    display:grid; place-items:center;
    font-size:20px; color:#fff;
    box-shadow: 0 0 0 8px var(--c-blue-glow);
}
.header-title {
    font-family:'Syne',sans-serif;
    font-size:1.6rem; font-weight:700; margin:0;
    color: var(--c-text);
    background: none;
    -webkit-text-fill-color: initial;
}
.header-sub { margin:0; color:var(--c-muted); font-size:.85rem; }
.btn-header {
    background: var(--c-surface);
    border: 1px solid var(--c-border);
    color: var(--c-text);
    padding:10px 20px; border-radius:50px;
    cursor:pointer; font-size:.85rem; font-family:inherit;
    transition: var(--trans);
    display:inline-flex; align-items:center; gap:8px;
}
.btn-header:hover { background:var(--c-blue); border-color:var(--c-blue); color:#fff; box-shadow:0 4px 12px var(--c-blue-glow); }
.header-line { height:1px; background:var(--c-border); margin-top:20px; }

/* FILTRES */
.filters-panel {
    background: var(--c-surface);
    border: 1px solid var(--c-border);
    border-radius: var(--radius);
    padding: 22px 24px 20px;
    margin-bottom: 28px;
    box-shadow: var(--shadow);
}
.filters-title {
    font-family:'Syne',sans-serif;
    font-size:.8rem; letter-spacing:.12em; text-transform:uppercase;
    color:var(--c-muted); margin-bottom:16px;
    display:flex; align-items:center; gap:8px;
}
.filters-row { display:flex; flex-wrap:wrap; gap:14px; align-items:flex-end; }
.filter-group { display:flex; flex-direction:column; gap:6px; min-width:150px; flex:1; }
.filter-group.filter-employe { flex:2; min-width:200px; }
.filter-group.filter-action { flex:0 0 auto; min-width:120px; }
.filter-label { font-size:.75rem; color:var(--c-muted); text-transform:uppercase; letter-spacing:.06em; }
.filter-select {
    background: var(--c-surface2);
    border: 1px solid var(--c-border);
    border-radius: 8px;
    color: var(--c-text);
    padding: 10px 14px;
    font-size:.9rem; font-family:inherit;
    transition: var(--trans); outline:none;
    width:100%;
}
.filter-select:focus { border-color:var(--c-blue); box-shadow:0 0 0 3px var(--c-blue-glow); }
.btn-apply {
    background: linear-gradient(135deg, var(--c-blue), var(--c-violet));
    border:none; border-radius:8px; color:#fff;
    padding:10px 18px; cursor:pointer; font-family:inherit;
    font-size:.9rem; font-weight:500; width:100%;
    transition:var(--trans); display:flex; align-items:center; justify-content:center; gap:6px;
}
.btn-apply:hover { filter:brightness(1.05); box-shadow:0 4px 14px var(--c-blue-glow); transform:translateY(-1px); }
.filter-active-bar {
    margin-top:14px; padding:10px 16px; border-radius:8px;
    background: rgba(59,127,255,.08); border:1px solid rgba(59,127,255,.2);
    font-size:.85rem; display:flex; align-items:center; gap:8px; flex-wrap:wrap;
    color: #1e40af;
}
.btn-reset {
    margin-left:auto; background:transparent; border:1px solid var(--c-border);
    border-radius:6px; color:var(--c-muted); padding:4px 12px;
    cursor:pointer; font-size:.8rem; font-family:inherit; transition:var(--trans);
    display:flex; align-items:center; gap:4px;
}
.btn-reset:hover { color:var(--c-text); border-color:var(--c-text); }

/* KPI GRID (4 cartes) */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 28px;
}
.kpi-card {
    position:relative; overflow:hidden;
    background: var(--c-surface);
    border: 1px solid var(--c-border);
    border-radius: var(--radius);
    padding: 24px 20px;
    transition: var(--trans);
    animation: kpiFadeUp .4s ease both;
    box-shadow: var(--shadow);
}
.kpi-card:hover { transform:translateY(-4px); box-shadow: 0 12px 28px rgba(0,0,0,.08); }

@keyframes kpiFadeUp {
    from { opacity:0; transform:translateY(16px); }
    to   { opacity:1; transform:translateY(0); }
}

.kpi-bg-icon {
    position:absolute; top:-10px; right:-10px;
    font-size:72px; opacity:.04;
    pointer-events:none; line-height:1;
}
.kpi-inner { display:flex; align-items:center; gap:16px; }
.kpi-icon {
    width:44px; height:44px; border-radius:12px;
    display:grid; place-items:center; font-size:18px;
    flex-shrink:0;
}
.kpi-info { display:flex; flex-direction:column; gap:2px; min-width:0; }
.kpi-label { font-size:.72rem; text-transform:uppercase; letter-spacing:.1em; color:var(--c-muted); }
.kpi-value { font-family:'Syne',sans-serif; font-size:1.8rem; font-weight:700; line-height:1; }
.kpi-sub { font-size:.75rem; color:var(--c-muted); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

/* Card colors (light theme) */
.kpi-blue  { border-top:3px solid var(--c-blue); }
.kpi-blue  .kpi-icon { background:rgba(59,127,255,.1); color:var(--c-blue); }
.kpi-blue  .kpi-value { color:var(--c-blue); }

.kpi-amber { border-top:3px solid var(--c-amber); }
.kpi-amber .kpi-icon { background:rgba(245,166,35,.1); color:var(--c-amber); }
.kpi-amber .kpi-value { color:var(--c-amber); }

.kpi-violet{ border-top:3px solid var(--c-violet); }
.kpi-violet .kpi-icon { background:rgba(162,89,255,.1); color:var(--c-violet); }
.kpi-violet .kpi-value { color:var(--c-violet); }

.kpi-rose  { border-top:3px solid var(--c-rose); }
.kpi-rose  .kpi-icon { background:rgba(255,82,119,.1); color:var(--c-rose); }
.kpi-rose  .kpi-value { color:var(--c-rose); }

/* CHARTS */
.charts-grid {
    display:grid; grid-template-columns:1fr 1fr;
    gap:20px; margin-bottom:28px;
}
@media (max-width:900px) { .charts-grid { grid-template-columns:1fr; } }

.chart-card {
    background:var(--c-surface);
    border:1px solid var(--c-border);
    border-radius:var(--radius);
    overflow:hidden;
    box-shadow: var(--shadow);
}
.chart-header {
    display:flex; align-items:center; justify-content:space-between;
    padding:18px 22px 14px;
    border-bottom:1px solid var(--c-border);
}
.chart-title-wrap { display:flex; align-items:center; gap:10px; }
.chart-dot { width:10px; height:10px; border-radius:50%; flex-shrink:0; }
.dot-violet { background:var(--c-violet); }
.dot-blue   { background:var(--c-blue); }
.dot-teal   { background:var(--c-teal); }
.chart-title { font-family:'Syne',sans-serif; font-size:.95rem; font-weight:600; margin:0; color:var(--c-text); }
.chart-badge {
    font-size:.68rem; text-transform:uppercase; letter-spacing:.1em;
    background:var(--c-surface2); border:1px solid var(--c-border);
    color:var(--c-muted); border-radius:20px; padding:3px 10px;
}
.chart-body { padding:20px 16px; position:relative; }
.chart-empty {
    display:flex; flex-direction:column; align-items:center;
    justify-content:center; padding:40px 0; color:var(--c-muted);
}
.chart-empty i { font-size:32px; margin-bottom:10px; opacity:.5; }

/* TABLE */
.table-card {
    background:var(--c-surface);
    border:1px solid var(--c-border);
    border-radius:var(--radius);
    overflow:hidden;
    box-shadow: var(--shadow);
}
.table-card-header {
    display:flex; align-items:center; justify-content:space-between;
    padding:18px 22px 14px;
    border-bottom:1px solid var(--c-border);
}
.table-count {
    font-size:.78rem; color:var(--c-muted);
    background:var(--c-surface2); border:1px solid var(--c-border);
    border-radius:20px; padding:4px 12px;
}
.stats-table {
    width:100%; border-collapse:collapse;
    font-size:.875rem;
}
.stats-table thead tr {
    background:var(--c-surface2);
}
.stats-table th {
    padding:12px 18px; text-align:left;
    font-size:.7rem; text-transform:uppercase; letter-spacing:.1em;
    color:var(--c-muted); font-weight:500; white-space:nowrap;
    border-bottom:1px solid var(--c-border);
}
.stats-table td {
    padding:13px 18px;
    border-bottom:1px solid var(--c-border);
    color:var(--c-text); vertical-align:middle;
}
.stats-table tbody tr { transition:background var(--trans); }
.stats-table tbody tr:hover { background:var(--c-surface2); }
.stats-table tbody tr:last-child td { border-bottom:none; }
.table-loading { text-align:center; padding:40px !important; color:var(--c-muted); }

/* BADGES (light) */
.badge-approuve  { display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:20px; font-size:.75rem; font-weight:500; background:#e0f7f5; color:#0d9488; border:1px solid #99f6e4; }
.badge-en_attente{ display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:20px; font-size:.75rem; font-weight:500; background:#fff3e3; color:#b45309; border:1px solid #fed7aa; }
.badge-refuse    { display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:20px; font-size:.75rem; font-weight:500; background:#ffe4e6; color:#e11d48; border:1px solid #fecdd3; }
.badge-acceptee  { display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:20px; font-size:.75rem; font-weight:500; background:#e0f2fe; color:#0369a1; border:1px solid #bae6fd; }
.badge-default   { display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:20px; font-size:.75rem; font-weight:500; background:var(--c-surface2); color:var(--c-muted); border:1px solid var(--c-border); }

.type-tag {
    display:inline-block; padding:3px 9px; border-radius:6px; font-size:.78rem;
    background:#f3e8ff; color:#7e22ce; border:1px solid #d8b4fe;
}
.type-tag-cert {
    background:#ffe4e6; color:#be123c; border-color:#fecdd3;
}

/* Select2 light override */
.select2-container--default .select2-selection--single {
    background: var(--c-surface2) !important;
    border: 1px solid var(--c-border) !important;
    border-radius: 8px !important;
    height: 42px !important;
    color: var(--c-text) !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered { color: var(--c-text) !important; line-height: 42px !important; padding-left: 14px; }
.select2-container--default .select2-selection--single .select2-selection__arrow { height: 42px !important; right: 8px; }
.select2-dropdown { background: var(--c-surface) !important; border: 1px solid var(--c-border) !important; }
.select2-results__option { color: var(--c-text) !important; }
.select2-results__option--highlighted { background: var(--c-blue) !important; color: white !important; }

@keyframes spin { to { transform:rotate(360deg); } }
#refresh-icon.spinning { animation: spin .7s linear infinite; }

@media (max-width:600px) {
    .stats-wrapper { padding:16px 12px 40px; }
    .header-title { font-size:1.2rem; }
    .kpi-grid { grid-template-columns:1fr 1fr; }
    .kpi-value { font-size:1.4rem; }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
/* ═══════════════════════════════════════════════
   CHART.JS — THEME CLAIR
═══════════════════════════════════════════════ */
Chart.defaults.color = '#64748b';
Chart.defaults.font.family = "'DM Sans', sans-serif";
Chart.defaults.borderColor = '#e9edf2';

const PALETTE = {
    blue:   '#3b7fff',
    teal:   '#0ecfc5',
    amber:  '#f5a623',
    violet: '#a259ff',
    rose:   '#ff5277',
};

let charts = {};
let currentFilters = { periode: 'mois', user_id: null, date_debut: null, date_fin: null };

$(document).ready(function () {
    $('#filtre-employe').select2({
        placeholder: 'Rechercher un employé…',
        allowClear: true,
        width: '100%',
    });

    loadEmployes();
    loadStats();

    $('#filtre-periode').change(function () {
        if ($(this).val() === 'personnalise') {
            $('#filtre-dates, #filtre-dates-fin').slideDown(160);
        } else {
            $('#filtre-dates, #filtre-dates-fin').slideUp(160);
        }
    });

    $('#date-debut').change(function () {
        $('#date-fin').attr('min', $(this).val());
        if ($('#date-fin').val() && $('#date-fin').val() < $(this).val()) {
            $('#date-fin').val($(this).val());
        }
    });
    $('#date-fin').change(function () {
        $('#date-debut').attr('max', $(this).val());
    });
});

function loadEmployes() {
    $.ajax({
        url: '{{ route("admin.stats.employes") }}',
        method: 'GET',
        success: function (data) {
            const select = $('#filtre-employe');
            select.empty().append('<option value="">Tous les employés</option>');
            data.forEach(emp => {
                select.append(`<option value="${emp.id}">${emp.nom_complet} — ${emp.email}</option>`);
            });
            select.trigger('change.select2');
        },
        error: function () {
            console.warn('Impossible de charger la liste des employés.');
        }
    });
}

function applyFilters() {
    const periode = $('#filtre-periode').val();

    if (periode === 'personnalise') {
        const debut = $('#date-debut').val(), fin = $('#date-fin').val();
        if (!debut || !fin) {
            return Swal.fire({ icon: 'warning', title: 'Dates manquantes', text: 'Veuillez saisir une plage de dates complète.', background: '#fff', color: '#1e293b' });
        }
        if (fin < debut) {
            return Swal.fire({ icon: 'error', title: 'Période invalide', text: 'La date de fin doit être postérieure à la date de début.', background: '#fff', color: '#1e293b' });
        }
    }

    currentFilters = {
        periode,
        user_id:    $('#filtre-employe').val() || null,
        date_debut: $('#date-debut').val() || null,
        date_fin:   $('#date-fin').val() || null,
    };

    loadStats();
    updateFilterInfo();
}

function resetFilters() {
    $('#filtre-periode').val('mois');
    $('#filtre-employe').val('').trigger('change.select2');
    $('#date-debut, #date-fin').val('');
    $('#filtre-dates, #filtre-dates-fin').slideUp(160);
    $('#filtre-info').slideUp(160);
    currentFilters = { periode: 'mois', user_id: null, date_debut: null, date_fin: null };
    updatePeriodeLabel('mois', null, null);
    loadStats();
}

function updateFilterInfo() {
    const labels = { jour: "Aujourd'hui", semaine: 'Cette semaine', mois: 'Ce mois', annee: 'Cette année', personnalise: 'Période personnalisée' };
    const parts = [];
    parts.push('<i class="fas fa-calendar-alt"></i> ' + (labels[currentFilters.periode] || currentFilters.periode));
    if (currentFilters.date_debut && currentFilters.date_fin) {
        parts.push(`du ${formatDate(currentFilters.date_debut)} au ${formatDate(currentFilters.date_fin)}`);
    }
    if (currentFilters.user_id) {
        const empText = $('#filtre-employe option:selected').text();
        parts.push('<i class="fas fa-user"></i> ' + empText);
    }
    $('#filtre-details').html(parts.join(' &nbsp;·&nbsp; '));
    $('#filtre-info').slideDown(200);
}

function updatePeriodeLabel(periode, debut, fin) {
    const map = { jour: "Aujourd'hui", semaine: 'Cette semaine', mois: 'Ce mois', annee: 'Cette année' };
    if (debut && fin) {
        $('#periode-label').text(`${formatDate(debut)} → ${formatDate(fin)}`);
    } else {
        $('#periode-label').text(map[periode] || periode);
    }
}

function formatDate(str) {
    if (!str) return '';
    const [y, m, d] = str.split('-');
    return `${d}/${m}/${y}`;
}

function refreshStats() {
    const icon = document.getElementById('refresh-icon');
    icon.classList.add('spinning');
    loadStats(() => {
        icon.classList.remove('spinning');
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Données actualisées', showConfirmButton: false, timer: 2000, background: '#fff', color: '#1e293b' });
    });
}

function loadStats(callback) {
    // Skeleton (sans heures)
    ['stat-employes','stat-conges','stat-attestations','stat-certificats'].forEach(id => {
        $(`#${id}`).html('<i class="fas fa-spinner fa-spin" style="font-size:.8em;opacity:.5"></i>');
    });

    $.ajax({
        url: '{{ route("admin.stats.data") }}',
        method: 'GET',
        data: currentFilters,
        success: function (response) {
            const s = response.stats;
            updateKPIs(s.totaux);
            renderChartDoughnut(s.repartition_attestations);
            renderChartLine(s.evolution_demandes);
            renderTable(s.dernieres_demandes);
            updatePeriodeLabel(currentFilters.periode, response.periode?.debut, response.periode?.fin);
            if (callback) callback();
        },
        error: function (xhr) {
            console.error(xhr);
            Swal.fire({ icon: 'error', title: 'Erreur de chargement', text: 'Impossible de récupérer les statistiques.', background: '#fff', color: '#1e293b' });
            if (callback) callback();
        }
    });
}

function updateKPIs(t) {
    animCount('stat-employes', t.total_employes);
    $('#stat-employes-actifs').text(t.employes_actifs);

    animCount('stat-conges', t.total_conges);
    $('#stat-conges-cours').text(t.conges_en_cours);

    animCount('stat-attestations', t.total_attestations);
    $('#stat-attestations-validees').text(t.attestations_approuvees);

    animCount('stat-certificats', t.total_certificats);
    $('#stat-certificats-acceptes').text(t.certificats_acceptes);
}

function animCount(id, target) {
    const el = document.getElementById(id);
    if (!el) return;
    let start = 0;
    const duration = 600;
    const step = (timestamp) => {
        if (!start) start = timestamp;
        const progress = Math.min((timestamp - start) / duration, 1);
        el.textContent = Math.floor(progress * target);
        if (progress < 1) requestAnimationFrame(step);
        else el.textContent = target;
    };
    requestAnimationFrame(step);
}

function renderChartDoughnut(data) {
    destroyChart('doughnut');
    if (!data.labels || !data.labels.length) {
        document.getElementById('chartAttestationsTypes').style.display = 'none';
        document.getElementById('no-attest-types').style.display = 'flex';
        return;
    }
    document.getElementById('chartAttestationsTypes').style.display = 'block';
    document.getElementById('no-attest-types').style.display = 'none';

    charts.doughnut = new Chart(document.getElementById('chartAttestationsTypes'), {
        type: 'doughnut',
        data: {
            labels: data.labels,
            datasets: [{
                data: data.counts,
                backgroundColor: [PALETTE.violet, PALETTE.blue, PALETTE.teal, PALETTE.rose],
                borderColor: '#fff',
                borderWidth: 3,
                hoverOffset: 8,
            }]
        },
        options: {
            responsive: true,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 16,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        font: { size: 12 },
                    }
                },
                tooltip: {
                    backgroundColor: '#fff',
                    borderColor: '#e9edf2',
                    borderWidth: 1,
                    padding: 12,
                    bodyColor: '#1e293b',
                    titleColor: '#0f172a',
                    callbacks: {
                        label: (ctx) => ` ${ctx.label}: ${ctx.parsed} demande${ctx.parsed > 1 ? 's' : ''}`,
                    }
                }
            },
            animation: { animateRotate: true, duration: 700 }
        }
    });
}

function renderChartLine(data) {
    destroyChart('line');
    if (!data.labels || !data.labels.length) {
        document.getElementById('chartEvolutionDemandes').style.display = 'none';
        document.getElementById('no-evolution').style.display = 'flex';
        return;
    }
    document.getElementById('chartEvolutionDemandes').style.display = 'block';
    document.getElementById('no-evolution').style.display = 'none';

    const makeGradient = (ctx, color) => {
        const g = ctx.createLinearGradient(0, 0, 0, 260);
        g.addColorStop(0, color + '30');
        g.addColorStop(1, color + '00');
        return g;
    };

    charts.line = new Chart(document.getElementById('chartEvolutionDemandes'), {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: 'Attestations',
                    data: data.attestations,
                    borderColor: PALETTE.violet,
                    backgroundColor: (ctx) => makeGradient(ctx.chart.ctx, PALETTE.violet),
                    fill: true,
                    tension: .4,
                    pointBackgroundColor: PALETTE.violet,
                    pointRadius: 4,
                    pointHoverRadius: 7,
                    borderWidth: 2,
                },
                {
                    label: 'Certificats',
                    data: data.certificats,
                    borderColor: PALETTE.rose,
                    backgroundColor: (ctx) => makeGradient(ctx.chart.ctx, PALETTE.rose),
                    fill: true,
                    tension: .4,
                    pointBackgroundColor: PALETTE.rose,
                    pointRadius: 4,
                    pointHoverRadius: 7,
                    borderWidth: 2,
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            scales: {
                x: {
                    grid: { color: '#e9edf2' },
                    ticks: { color: '#64748b' },
                },
                y: {
                    grid: { color: '#e9edf2' },
                    beginAtZero: true,
                    ticks: { precision: 0, color: '#64748b' },
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                    align: 'end',
                    labels: { usePointStyle: true, pointStyle: 'circle', padding: 16, color: '#1e293b' }
                },
                tooltip: {
                    backgroundColor: '#fff',
                    borderColor: '#e9edf2',
                    borderWidth: 1,
                    padding: 12,
                    bodyColor: '#1e293b',
                    titleColor: '#0f172a',
                }
            },
            animation: { duration: 700 }
        }
    });
}

function renderTable(demandes) {
    if (!demandes || !demandes.length) {
        $('#table-demandes').html('<tr><td colspan="5" class="table-loading" style="color:var(--c-muted)">Aucune demande sur cette période</td></tr>');
        $('#table-count').text('0 résultat');
        return;
    }

    $('#table-count').text(demandes.length + ' résultat' + (demandes.length > 1 ? 's' : ''));

    let html = '';
    demandes.forEach(d => {
        const isCert = d.type_label === 'Certificat de travail';
        const tagClass = isCert ? 'type-tag type-tag-cert' : 'type-tag';
        const badge = buildBadge(d.statut_badge);
        html += `
        <tr>
            <td><span class="${tagClass}">${escHtml(d.type_label)}</span></td>
            <td>
                <div style="display:flex;align-items:center;gap:8px">
                    <div style="width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,var(--c-blue),var(--c-violet));display:grid;place-items:center;font-size:.7rem;font-weight:700;color:#fff;flex-shrink:0">
                        ${getInitials(d.employe)}
                    </div>
                    <span>${escHtml(d.employe)}</span>
                </div>
            </td>
            <td style="color:var(--c-muted)">${escHtml(d.date)}</td>
            <td>${badge}</td>
            <td style="font-family:monospace;font-size:.8rem;color:var(--c-muted)">${escHtml(d.reference)}</td>
        </tr>`;
    });

    $('#table-demandes').html(html);
}

function buildBadge(statutBadge) {
    const map = {
        'approuve':   ['badge-approuve',  'fas fa-check-circle', 'Approuvé'],
        'en_attente': ['badge-en_attente','fas fa-clock',        'En attente'],
        'refuse':     ['badge-refuse',    'fas fa-times-circle', 'Refusé'],
        'acceptee':   ['badge-acceptee',  'fas fa-check',        'Accepté'],
    };
    for (const [key, [cls, icon, label]] of Object.entries(map)) {
        if (statutBadge && (statutBadge.includes(key) || statutBadge.toLowerCase().includes(label.toLowerCase()))) {
            return `<span class="${cls}"><i class="${icon}"></i>${label}</span>`;
        }
    }
    return `<span class="badge-default">${escHtml(statutBadge || '—')}</span>`;
}

function getInitials(name) {
    return (name || '').split(' ').slice(0,2).map(w => w[0] || '').join('').toUpperCase();
}

function escHtml(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function destroyChart(key) {
    if (charts[key]) { charts[key].destroy(); delete charts[key]; }
}
</script>
@endpush
