@php
use App\Helpers\UserHelper;
@endphp

@extends('layaout')

@section('title', 'Statistiques Globales - Admin')

@section('content')
    <section class="section">

        {{-- ===== EN-TÊTE ===== --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card gradient-card">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h4 class="text-white mb-2">
                                    <i class="fas fa-chart-line"></i> Statistiques Globales
                                </h4>
                                <p class="text-white mb-0 opacity-75">Tableau de bord administrateur</p>
                            </div>
                            <div class="col-md-6 text-right">
                                <button class="btn btn-light rounded-pill mr-2" onclick="refreshStats()">
                                    <i class="fas fa-sync-alt"></i> Actualiser
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== FILTRES ===== --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card modern-card">
                    <div class="card-header">
                        <h5><i class="fas fa-filter"></i> Filtres</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <label>Période</label>
                                <select class="form-control" id="filtre-periode">
                                    <option value="jour">Aujourd'hui</option>
                                    <option value="semaine">Cette semaine</option>
                                    <option value="mois" selected>Ce mois</option>
                                    <option value="annee">Cette année</option>
                                    <option value="personnalise">Personnalisée</option>
                                </select>
                            </div>
                            <div class="col-md-3" id="filtre-dates" style="display:none;">
                                <label>Date de début</label>
                                <input type="date" class="form-control" id="date-debut">
                            </div>
                            <div class="col-md-3" id="filtre-dates-fin" style="display:none;">
                                <label>Date de fin</label>
                                <input type="date" class="form-control" id="date-fin">
                            </div>
                            <div class="col-md-3">
                                <label>Employé</label>
                                <select class="form-control select2" id="filtre-employe">
                                    <option value="">Tous les employés</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>&nbsp;</label>
                                <button class="btn btn-primary btn-block" onclick="applyFilters()">
                                    <i class="fas fa-search"></i> Appliquer
                                </button>
                            </div>
                        </div>
                        <div class="row mt-3" id="filtre-info" style="display:none;">
                            <div class="col-12">
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Filtres actifs :</strong> <span id="filtre-details"></span>
                                    <button class="btn btn-sm btn-light float-right" onclick="resetFilters()">
                                        <i class="fas fa-times"></i> Réinitialiser
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== CARTES STATS GÉNÉRALES ===== --}}
        <div class="row g-4 mb-4">

            {{-- Employés --}}
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mt-4">
                <div class="card card-statistic border-0 shadow-lg hover-lift h-100 position-relative overflow-hidden">
                    <div class="card-icon-bg position-absolute top-0 end-0 opacity-10">
                        <i class="fas fa-users fa-4x"></i>
                    </div>
                    <div class="card-body pt-4 pb-4 px-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-wrapper rounded-circle bg-gradient-primary p-3 me-3">
                                <i class="fas fa-users fa-lg text-white"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="card-title text-muted mb-1 fw-normal">Employés</h5>
                                <h2 id="stat-employes" class="mb-0 fw-bold">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </h2>
                            </div>
                        </div>
                        <div class="mt-3">
                            <span class="badge bg-light text-dark px-3 py-1 rounded-pill">
                                <i class="fas fa-user-check me-1"></i>
                                <span id="stat-employes-actifs">--</span> actifs
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Heures --}}
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mt-4">
                <div class="card card-statistic border-0 shadow-lg hover-lift h-100 position-relative overflow-hidden">
                    <div class="card-icon-bg position-absolute top-0 end-0 opacity-10">
                        <i class="fas fa-clock fa-4x"></i>
                    </div>
                    <div class="card-body pt-4 pb-4 px-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-wrapper rounded-circle bg-gradient-success p-3 me-3">
                                <i class="fas fa-clock fa-lg text-white"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="card-title text-muted mb-1 fw-normal">Heures</h5>
                                <h2 id="stat-heures" class="mb-0 fw-bold">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </h2>
                            </div>
                        </div>
                        <div class="mt-3">
                            <span class="badge bg-light text-dark px-3 py-1 rounded-pill">
                                <i class="fas fa-chart-line me-1"></i>
                                <span id="stat-moyenne">--</span> moy.
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Congés --}}
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mt-4">
                <div class="card card-statistic border-0 shadow-lg hover-lift h-100 position-relative overflow-hidden">
                    <div class="card-icon-bg position-absolute top-0 end-0 opacity-10">
                        <i class="fas fa-umbrella-beach fa-4x"></i>
                    </div>
                    <div class="card-body pt-4 pb-4 px-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-wrapper rounded-circle bg-gradient-warning p-3 me-3">
                                <i class="fas fa-umbrella-beach fa-lg text-white"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="card-title text-muted mb-1 fw-normal">Congés</h5>
                                <h2 id="stat-conges" class="mb-0 fw-bold">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </h2>
                            </div>
                        </div>
                        <div class="mt-3">
                            <span class="badge bg-light text-dark px-3 py-1 rounded-pill">
                                <i class="fas fa-running me-1"></i>
                                <span id="stat-conges-cours">--</span> en cours
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Clients --}}
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mt-4">
                <div class="card card-statistic border-0 shadow-lg hover-lift h-100 position-relative overflow-hidden">
                    <div class="card-icon-bg position-absolute top-0 end-0 opacity-10">
                        <i class="fas fa-user-tie fa-4x"></i>
                    </div>
                    <div class="card-body pt-4 pb-4 px-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-wrapper rounded-circle bg-gradient-secondary p-3 me-3">
                                <i class="fas fa-user-tie fa-lg text-white"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="card-title text-muted mb-1 fw-normal">Clients</h5>
                                <h2 id="stat-clients" class="mb-0 fw-bold">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </h2>
                            </div>
                        </div>
                        <div class="mt-3">
                            <span class="badge bg-light text-dark px-3 py-1 rounded-pill">
                                <i class="fas fa-handshake me-1"></i> Partenaires
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== SECTION DOSSIERS ===== --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card modern-card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-0"><i class="fas fa-folder-open text-info mr-2"></i> Dossiers</h5>
                            <small class="text-muted">Vue globale du système + résultat selon les filtres actifs</small>
                        </div>
                        <span class="badge badge-info px-3 py-2" id="badge-periode-dossiers">Ce mois</span>
                    </div>
                    <div class="card-body">
                        <div class="row">

                            {{-- Colonne gauche : stats globales (fixes) --}}
                            <div class="col-md-6">
                                <div class="dossier-section-label">
                                    <i class="fas fa-globe-europe mr-1"></i>
                                    Système complet
                                    <span class="text-muted small ml-1">(tous les dossiers, sans filtre)</span>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-6">
                                        <div class="dossier-stat-card dossier-global">
                                            <div class="dossier-stat-icon bg-gradient-info">
                                                <i class="fas fa-folder fa-lg text-white"></i>
                                            </div>
                                            <div class="dossier-stat-body">
                                                <div class="dossier-stat-label">Total dossiers</div>
                                                <div class="dossier-stat-value" id="stat-total-dossiers-global">
                                                    <i class="fas fa-spinner fa-spin"></i>
                                                </div>
                                                <div class="dossier-stat-sub">dans le système</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="dossier-stat-card dossier-global">
                                            <div class="dossier-stat-icon bg-gradient-success">
                                                <i class="fas fa-folder-open fa-lg text-white"></i>
                                            </div>
                                            <div class="dossier-stat-body">
                                                <div class="dossier-stat-label">Dossiers actifs</div>
                                                <div class="dossier-stat-value" id="stat-dossiers-actifs-global">
                                                    <i class="fas fa-spinner fa-spin"></i>
                                                </div>
                                                <div class="dossier-stat-sub">ouverts / en cours</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Séparateur vertical --}}
                            <div class="col-md-1 d-none d-md-flex align-items-center justify-content-center">
                                <div class="dossier-separator"></div>
                            </div>

                            {{-- Colonne droite : stats filtrées --}}
                            <div class="col-md-5">
                                <div class="dossier-section-label">
                                    <i class="fas fa-filter mr-1"></i>
                                    Selon les filtres
                                    <span class="text-muted small ml-1" id="label-periode-dossiers"></span>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-6">
                                        <div class="dossier-stat-card dossier-filtre">
                                            <div class="dossier-stat-icon bg-gradient-primary">
                                                <i class="fas fa-calendar-alt fa-lg text-white"></i>
                                            </div>
                                            <div class="dossier-stat-body">
                                                <div class="dossier-stat-label">Sur la période</div>
                                                <div class="dossier-stat-value text-danger" id="stat-total-dossiers">
                                                    <i class="fas fa-spinner fa-spin"></i>
                                                </div>
                                                <div class="dossier-stat-sub">dossiers concernés</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="dossier-stat-card dossier-filtre">
                                            <div class="dossier-stat-icon bg-gradient-warning">
                                                <i class="fas fa-tasks fa-lg text-white"></i>
                                            </div>
                                            <div class="dossier-stat-body">
                                                <div class="dossier-stat-label">Actifs filtrés</div>
                                                <div class="dossier-stat-value text-warning" id="stat-dossiers-actifs">
                                                    <i class="fas fa-spinner fa-spin"></i>
                                                </div>
                                                <div class="dossier-stat-sub">ouverts / en cours</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== GRAPHIQUES PRINCIPAUX ===== --}}
        <div class="row">
            <div class="col-lg-8">
                <div class="card modern-card">
                    <div class="card-header">
                        <h4><i class="fas fa-chart-area text-primary"></i> Évolution des Heures</h4>
                        <small class="text-muted">Tendance sur la période</small>
                    </div>
                    <div class="card-body">
                        <canvas id="chartEvolution" height="80"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card modern-card">
                    <div class="card-header">
                        <h4><i class="fas fa-calendar-week text-info"></i> Heures par Jour de Semaine</h4>
                        <small class="text-muted">Distribution hebdomadaire</small>
                    </div>
                    <div class="card-body">
                        <canvas id="chartJoursSemaine" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== GRAPHIQUES SECONDAIRES ===== --}}
        <div class="row">
            <div class="col-lg-6">
                <div class="card modern-card">
                    <div class="card-header">
                        <h4><i class="fas fa-chart-bar text-success"></i> Top 10 Dossiers</h4>
                        <small class="text-muted">Par nombre d'heures travaillées</small>
                    </div>
                    <div class="card-body">
                        <canvas id="chartDossiers" height="250"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card modern-card">
                    <div class="card-header">
                        <h4><i class="fas fa-chart-pie text-warning"></i> Congés — Répartition</h4>
                        <small class="text-muted">Par type et statut</small>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <canvas id="chartCongesTypes" height="200"></canvas>
                            </div>
                            <div class="col-md-6">
                                <canvas id="chartCongesStatuts" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== TAUX DE VALIDATION ===== --}}
        <div class="col-lg-12 justify-content-center">
            <div class="card modern-card">
                <div class="card-header">
                    <h4><i class="fas fa-check-circle text-success"></i> Taux de Validation</h4>
                    <small class="text-muted">Statut des feuilles de temps</small>
                </div>
                <div class="card-body">
                    <canvas id="chartValidation" height="200"></canvas>
                </div>
            </div>
        </div>

        {{-- ===== SOLDES CONGÉS ===== --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="card modern-card">
                    <div class="card-header">
                        <h4><i class="fas fa-balance-scale text-info"></i> Top 10 Soldes de Congés</h4>
                        <small class="text-muted">Jours restants pour l'année en cours</small>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="50">#</th>
                                        <th>Employé</th>
                                        <th class="text-center">Jours acquis</th>
                                        <th class="text-center">Jours pris</th>
                                        <th class="text-center">Jours restants</th>
                                        <th class="text-center">Jours reportés</th>
                                        <th class="text-center">Taux d'utilisation</th>
                                    </tr>
                                </thead>
                                <tbody id="soldes-conges">
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <i class="fas fa-spinner fa-spin"></i> Chargement...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== ACTIVITÉS PAR HEURE ===== --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="card modern-card">
                    <div class="card-header">
                        <h4><i class="fas fa-clock text-secondary"></i> Activités par Heure de la Journée</h4>
                        <small class="text-muted">Distribution horaire des activités</small>
                    </div>
                    <div class="card-body">
                        <canvas id="chartActivitesHeure" height="80"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </section>
@endsection

@push('styles')
<style>
/* ===== CARTE GRADIENT EN-TÊTE ===== */
.gradient-card {
    background: linear-gradient(135deg, #244584 0%, #4b79c8 100%);
    border: none;
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

/* ===== CARTES STATISTIQUES GÉNÉRALES ===== */
.card-statistic {
    border-radius: 16px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.card-statistic:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(0,0,0,.15) !important;
}
.card-statistic:hover .icon-wrapper {
    transform: scale(1.1);
}
.card-icon-bg {
    color: rgba(255,255,255,.05);
    transform: translate(20px,-20px);
}
.icon-wrapper { transition: all 0.3s ease; }

/* Gradients réutilisables */
.bg-gradient-primary   { background: linear-gradient(135deg,#667eea,#764ba2) !important; }
.bg-gradient-success   { background: linear-gradient(135deg,#11998e,#38ef7d) !important; }
.bg-gradient-info      { background: linear-gradient(135deg,#3abaf4,#1572E8) !important; }
.bg-gradient-warning   { background: linear-gradient(135deg,#ffa426,#f3545d) !important; }
.bg-gradient-secondary { background: linear-gradient(135deg,#6c757d,#343a40) !important; }
.bg-gradient-danger    { background: linear-gradient(135deg,#fc544b,#ffa426) !important; }

/* Animations d'entrée */
@keyframes fadeInUp {
    from { opacity:0; transform:translateY(20px); }
    to   { opacity:1; transform:translateY(0); }
}
.card-statistic {
    animation: fadeInUp 0.5s ease forwards;
    animation-delay: calc(var(--ao,1) * 0.1s);
    opacity: 0;
}
.card-statistic:nth-child(1) { --ao:1; }
.card-statistic:nth-child(2) { --ao:2; }
.card-statistic:nth-child(3) { --ao:3; }
.card-statistic:nth-child(4) { --ao:4; }

/* ===== SECTION DOSSIERS ===== */
.dossier-section-label {
    font-size: .78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .6px;
    color: #6c757d;
    border-bottom: 2px solid #f0f0f0;
    padding-bottom: 8px;
    margin-bottom: 0;
}

.dossier-stat-card {
    display: flex;
    align-items: center;
    gap: 14px;
    border-radius: 12px;
    padding: 16px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    margin-bottom: 8px;
}
.dossier-stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,.08);
}

/* Fond légèrement teinté selon le type */
.dossier-global { background: #f8f9ff; border: 1px solid #e8ecff; }
.dossier-filtre { background: #fff8f0; border: 1px solid #ffe8c0; }

.dossier-stat-icon {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.dossier-stat-body { flex: 1; min-width: 0; }
.dossier-stat-label {
    font-size: .73rem;
    font-weight: 600;
    color: #8a8fa3;
    text-transform: uppercase;
    letter-spacing: .4px;
    margin-bottom: 2px;
}
.dossier-stat-value {
    font-size: 1.9rem;
    font-weight: 800;
    line-height: 1;
    color: #2c3e50;
    margin-bottom: 2px;
}
.dossier-stat-sub {
    font-size: .72rem;
    color: #aab0bd;
}

/* Séparateur vertical */
.dossier-separator {
    width: 1px;
    height: 80%;
    background: linear-gradient(to bottom, transparent, #dee2e6, transparent);
}

/* ===== TABLES ===== */
.table-hover tbody tr { transition: all 0.2s ease; }
.table-hover tbody tr:hover {
    background: rgba(var(--bs-primary-rgb),.05);
    transform: translateX(4px);
}
.table > thead > tr > th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: .8rem;
    letter-spacing: .5px;
    color: #6c757d;
    border-bottom-width: 2px;
}

/* Progress bar soldes */
.progress-taux {
    height: 8px;
    border-radius: 4px;
    background: #e9ecef;
    position: relative;
}
.progress-taux-bar {
    height: 100%;
    border-radius: 4px;
    transition: width 0.6s ease;
    background: linear-gradient(90deg,#3abaf4,#1572E8);
}
.progress-taux-text {
    position: absolute;
    top: -20px;
    right: 0;
    font-size: .75rem;
    font-weight: 600;
    color: #6c757d;
}

/* Responsive */
@media (max-width:768px) {
    .dossier-separator { display:none; }
    .dossier-stat-value { font-size: 1.5rem; }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
let charts = {};
let currentFilters = {
    periode: 'mois',
    user_id: null,
    date_debut: null,
    date_fin: null
};

Chart.defaults.font.family = "'Nunito', sans-serif";
Chart.defaults.plugins.legend.labels.usePointStyle = true;

$(document).ready(function () {
    $('#filtre-employe').select2({
        placeholder: "Rechercher un employé...",
        allowClear: true,
        width: '100%'
    });

    loadEmployes();
    loadStats();

    $('#filtre-periode').change(function () {
        const periode = $(this).val();
        if (periode === 'personnalise') {
            $('#filtre-dates, #filtre-dates-fin').show();
        } else {
            $('#filtre-dates, #filtre-dates-fin').hide();
        }
    });

    // Quand date-debut change → mettre le min de date-fin
    $('#date-debut').change(function () {
        const val = $(this).val();
        $('#date-fin').attr('min', val);

        // Si date-fin déjà saisie et < date-debut → la corriger automatiquement
        if ($('#date-fin').val() && $('#date-fin').val() < val) {
            $('#date-fin').val(val);
        }
    });

    // Quand date-fin change → mettre le max de date-debut
    $('#date-fin').change(function () {
        const val = $(this).val();
        $('#date-debut').attr('max', val);
    });
});

// ===== CHARGEMENT EMPLOYÉS =====
function loadEmployes() {
    $.ajax({
        url: '{{ route("admin.stats.employes") }}',
        method: 'GET',
        success: function (data) {
            const select = $('#filtre-employe');
            select.empty().append('<option value="">Tous les employés</option>');
            data.forEach(emp => {
                select.append(`<option value="${emp.id}">${emp.nom_complet}</option>`);
            });
            select.trigger('change.select2');
        }
    });
}

// ===== APPLIQUER LES FILTRES =====
function applyFilters() {
    const periode = $('#filtre-periode').val();

    // Validation dates personnalisées
    if (periode === 'personnalise') {
        const dateDebut = $('#date-debut').val();
        const dateFin   = $('#date-fin').val();

        if (!dateDebut || !dateFin) {
            Swal.fire({
                icon: 'warning',
                title: 'Dates manquantes',
                text: 'Veuillez saisir une date de début et une date de fin.',
                confirmButtonColor: '#6777ef'
            });
            return;
        }

        if (dateFin < dateDebut) {
            Swal.fire({
                icon: 'error',
                title: 'Période invalide',
                text: 'La date de fin ne peut pas être antérieure à la date de début.',
                confirmButtonColor: '#6777ef'
            }).then(() => {
                // Remettre date-fin = date-debut comme valeur minimale
                $('#date-fin').val(dateDebut).focus();
            });
            return; // On bloque l'envoi
        }
    }

    currentFilters = {
        periode:    periode,
        user_id:    $('#filtre-employe').val() || null,
        date_debut: $('#date-debut').val() || null,
        date_fin:   $('#date-fin').val() || null
    };

    loadStats();
    updateFilterInfo();
}

// ===== RÉINITIALISER =====
function resetFilters() {
    $('#filtre-periode').val('mois');
    $('#filtre-employe').val('').trigger('change.select2');
    $('#date-debut, #date-fin').val('');
    $('#filtre-dates, #filtre-dates-fin').hide();
    $('#filtre-info').hide();
    currentFilters = { periode: 'mois', user_id: null, date_debut: null, date_fin: null };
    loadStats();
}

// ===== LABEL INFO FILTRES =====
function updateFilterInfo() {
    const periode  = currentFilters.periode;
    const employe  = $('#filtre-employe option:selected').text();
    const hasFilters = currentFilters.user_id || periode !== 'mois';

    if (hasFilters) {
        let parts = [];
        const labels = {
            jour: "Aujourd'hui", semaine: 'Cette semaine',
            mois: 'Ce mois', annee: 'Cette année'
        };
        if (periode === 'personnalise') {
            parts.push(`Du ${currentFilters.date_debut} au ${currentFilters.date_fin}`);
        } else {
            parts.push(labels[periode] || periode);
        }
        if (currentFilters.user_id) parts.push(`Employé : ${employe}`);
        $('#filtre-details').text(parts.join(' • '));
        $('#filtre-info').show();
    } else {
        $('#filtre-info').hide();
    }
}

// ===== RAFRAÎCHIR =====
function refreshStats() {
    loadStats();
    Swal.fire({
        toast: true, position: 'top-end', icon: 'success',
        title: 'Statistiques actualisées', showConfirmButton: false, timer: 2000
    });
}

// ===== CHARGER TOUTES LES STATS =====
function loadStats() {
    $.ajax({
        url: '{{ route("admin.stats.data") }}',
        method: 'GET',
        data: currentFilters,
        success: function (response) {
            const stats = response.stats;
            const periode = response.periode;

            updateGlobalStats(stats.totaux, periode);
            updateClassementHeures(stats.classement_employes);
            updateClassementConges(stats.classement_conges);
            updateChartEvolution(stats.evolution_heures);
            updateChartDossiers(stats.repartition_dossiers);
            updateChartConges(stats.statistiques_conges);
            updateChartMensuel(stats.performance_mensuelle);
            updateChartValidation(stats.taux_validation);
            updateChartJoursSemaine(stats.heures_par_jour_semaine);
            updateSoldesConges(stats.soldes_conges);
            updateChartActivitesHeure(stats.activites_par_heure);
        },
        error: function (xhr) {
            console.error('Erreur:', xhr);
            Swal.fire({ icon: 'error', title: 'Erreur', text: 'Impossible de charger les statistiques' });
        }
    });
}

// ===== FORMATAGE HEURES =====
function decimalToHoursMinutes(decimal) {
    const h = Math.floor(decimal);
    const m = Math.round((decimal - h) * 60).toString().padStart(2, '0');
    return `${h}h ${m}`;
}

// ===== MISE À JOUR STATS GLOBALES =====
function updateGlobalStats(totaux, periode) {
    // Employés
    $('#stat-employes').text(totaux.total_employes);
    $('#stat-employes-actifs').text(totaux.employes_actifs);

    // Heures
    $('#stat-heures').text(decimalToHoursMinutes(totaux.total_heures));
    if (totaux.moyenne_heures_employe !== null) {
        $('#stat-moyenne').text(decimalToHoursMinutes(totaux.moyenne_heures_employe) + '/employé');
    } else {
        $('#stat-moyenne').text('—');
    }

    // Congés
    $('#stat-conges').text(totaux.total_conges);
    $('#stat-conges-cours').text(totaux.conges_en_cours);

    // Clients
    $('#stat-clients').text(totaux.total_clients);

    // ===== DOSSIERS : globaux (fixes) =====
    $('#stat-total-dossiers-global').text(totaux.total_dossiers_global);
    $('#stat-dossiers-actifs-global').text(totaux.dossiers_actifs_global);

    // ===== DOSSIERS : filtrés =====
    $('#stat-total-dossiers').text(totaux.total_dossiers);
    $('#stat-dossiers-actifs').text(totaux.dossiers_actifs);

    // Label période sur la carte dossiers
    const labels = {
        jour: "Aujourd'hui", semaine: 'Cette semaine',
        mois: 'Ce mois', annee: 'Cette année'
    };
    const periodeType = currentFilters.periode;
    let labelPeriode = labels[periodeType]
        || `Du ${periode.debut} au ${periode.fin}`;

    $('#badge-periode-dossiers').text(labelPeriode);
    $('#label-periode-dossiers').text(`(${periode.debut} → ${periode.fin})`);
}

// ===== CLASSEMENT HEURES =====
function updateClassementHeures(data) {
    const tbody = $('#classement-heures');
    if (!data || !data.length) {
        tbody.html('<tr><td colspan="6" class="text-center py-3 text-muted">Aucune donnée</td></tr>');
        return;
    }
    let html = '';
    data.forEach(emp => {
        const badgeClass = emp.rang === 1 ? 'badge-rang-1' : emp.rang === 2 ? 'badge-rang-2' : emp.rang === 3 ? 'badge-rang-3' : '';
        html += `
            <tr onclick="viewEmployeDetails(${emp.id})" style="cursor:pointer;">
                <td><span class="badge badge-pill ${badgeClass}">${emp.rang}</span></td>
                <td><strong>${emp.nom_complet}</strong><br><small class="text-muted">${emp.email}</small></td>
                <td class="text-center"><strong class="text-primary">${emp.total_heures}h</strong></td>
                <td class="text-center"><small class="text-muted">${emp.moyenne_jour}h/j</small></td>
                <td class="text-center"><span class="badge badge-info">${emp.nombre_dossiers}</span></td>
            </tr>`;
    });
    tbody.html(html);
}

// ===== CLASSEMENT CONGÉS =====
function updateClassementConges(data) {
    const tbody = $('#classement-conges');
    if (!data || !data.length) {
        tbody.html('<tr><td colspan="5" class="text-center py-3 text-muted">Aucune donnée</td></tr>');
        return;
    }
    let html = '';
    data.forEach(emp => {
        const badgeClass = emp.rang === 1 ? 'badge-rang-1' : emp.rang === 2 ? 'badge-rang-2' : emp.rang === 3 ? 'badge-rang-3' : '';
        html += `
            <tr onclick="viewEmployeDetails(${emp.id})" style="cursor:pointer;">
                <td><span class="badge badge-pill ${badgeClass}">${emp.rang}</span></td>
                <td><strong>${emp.nom_complet}</strong><br><small class="text-muted">${emp.email}</small></td>
                <td class="text-center"><strong class="text-warning">${emp.nombre_conges}</strong></td>
                <td class="text-center"><span class="badge badge-success">${emp.jours_approuves}j</span></td>
                <td class="text-center"><span class="badge badge-warning">${emp.total_jours}j</span></td>
            </tr>`;
    });
    tbody.html(html);
}

// ===== SOLDES CONGÉS =====
function updateSoldesConges(data) {
    const tbody = $('#soldes-conges');
    if (!data.employes || !data.employes.length) {
        tbody.html('<tr><td colspan="7" class="text-center py-3 text-muted">Aucune donnée</td></tr>');
        return;
    }
    let html = '';
    data.employes.forEach((emp, i) => {
        const badgeClass = i === 0 ? 'badge-rang-1' : i === 1 ? 'badge-rang-2' : i === 2 ? 'badge-rang-3' : '';
        const color = emp.pourcentage_pris > 80 ? 'danger' : emp.pourcentage_pris > 50 ? 'warning' : 'success';
        html += `
            <tr>
                <td><span class="badge badge-pill ${badgeClass}">${i + 1}</span></td>
                <td><strong>${emp.nom_complet}</strong></td>
                <td class="text-center"><strong class="text-info">${emp.jours_acquis}</strong></td>
                <td class="text-center"><strong class="text-warning">${emp.jours_pris}</strong></td>
                <td class="text-center"><strong class="text-success">${emp.jours_restants}</strong></td>
                <td class="text-center"><span class="badge badge-secondary">${emp.jours_reportes}</span></td>
                <td>
                    <div class="progress-taux position-relative">
                        <div class="progress-taux-bar bg-${color}" style="width:${emp.pourcentage_pris}%"></div>
                        <div class="progress-taux-text">${emp.pourcentage_pris}%</div>
                    </div>
                </td>
            </tr>`;
    });
    tbody.html(html);
}

// ===== GRAPHIQUES =====
function destroyChart(id) {
    const key = id.replace('chart','').charAt(0).toLowerCase() + id.replace('chart','').slice(1);
    if (charts[key]) { charts[key].destroy(); delete charts[key]; }
}

function updateChartEvolution(data) {
    destroyChart('chartEvolution');
    charts.Evolution = new Chart(document.getElementById('chartEvolution'), {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: 'Heures travaillées',
                    data: data.heures,
                    borderColor: '#6777ef', backgroundColor: 'rgba(103,119,239,.1)',
                    tension: 0.4, fill: true, borderWidth: 3, yAxisID: 'y'
                },
                {
                    label: 'Employés actifs',
                    data: data.employes,
                    borderColor: '#47c363', backgroundColor: 'rgba(71,195,99,.1)',
                    tension: 0.4, borderWidth: 2, borderDash: [5,5], yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'top' } },
            scales: {
                x: { grid: { display: false } },
                y: { type: 'linear', position: 'left', title: { display: true, text: 'Heures' } },
                y1: { type: 'linear', position: 'right', title: { display: true, text: 'Employés' }, grid: { drawOnChartArea: false } }
            }
        }
    });
}

function updateChartDossiers(data) {
    destroyChart('chartDossiers');
    charts.Dossiers = new Chart(document.getElementById('chartDossiers'), {
        type: 'bar',
        data: {
            labels: data.dossiers,
            datasets: [{ label: 'Heures travaillées', data: data.heures, backgroundColor: '#47c363', borderRadius: 6 }]
        },
        options: {
            indexAxis: 'y', responsive: true,
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true, title: { display: true, text: 'Heures' } } }
        }
    });
}

function updateChartConges(data) {
    destroyChart('chartCongesTypes');
    destroyChart('chartCongesStatuts');
    const colors = ['#6777ef','#ffa426','#47c363','#fc544b','#3abaf4','#f3545d'];
    charts.CongesTypes = new Chart(document.getElementById('chartCongesTypes'), {
        type: 'doughnut',
        data: { labels: data.types.labels, datasets: [{ data: data.types.counts, backgroundColor: colors, borderWidth: 3, borderColor: '#fff' }] },
        options: { responsive: true, plugins: { legend: { position: 'bottom' }, title: { display: true, text: 'Par type' } } }
    });
    const colorStatuts = { 'En_attente': '#3abaf4', 'Approuve': '#47c363', 'Refuse': '#fc544b', 'Annule': '#95a5a6' };
    charts.CongesStatuts = new Chart(document.getElementById('chartCongesStatuts'), {
        type: 'pie',
        data: {
            labels: data.statuts.labels,
            datasets: [{ data: data.statuts.counts, backgroundColor: data.statuts.labels.map(l => colorStatuts[l.replace(' ','_')] || '#95a5a6'), borderWidth: 3, borderColor: '#fff' }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' }, title: { display: true, text: 'Par statut' } } }
    });
}

function updateChartMensuel(data) {
    destroyChart('chartMensuel');
    if (!document.getElementById('chartMensuel')) return;
    charts.Mensuel = new Chart(document.getElementById('chartMensuel'), {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [
                { label: 'Heures travaillées', data: data.heures, backgroundColor: '#3abaf4', borderRadius: 6, yAxisID: 'y' },
                { label: 'Jours de congés', data: data.jours_conges, backgroundColor: '#ffa426', borderRadius: 6, yAxisID: 'y1' }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'top' } },
            scales: {
                x: { grid: { display: false } },
                y: { type: 'linear', position: 'left', title: { display: true, text: 'Heures' } },
                y1: { type: 'linear', position: 'right', title: { display: true, text: 'Jours' }, grid: { drawOnChartArea: false } }
            }
        }
    });
}

function updateChartValidation(data) {
    destroyChart('chartValidation');
    const colorMap = { 'Soumis': '#3abaf4', 'Valide': '#95d5a2', 'Refuse': '#fc544b', 'Brouillon': '#47b363' };
    charts.Validation = new Chart(document.getElementById('chartValidation').getContext('2d'), {
        type: 'pie',
        data: {
            labels: data.statuts,
            datasets: [{ data: data.counts, backgroundColor: data.statuts.map(s => colorMap[s] || '#95a5a6'), borderWidth: 3, borderColor: '#fff' }]
        },
        options: {
            responsive: true, maintainAspectRatio: false, aspectRatio: 2.5,
            layout: { padding: { top: 10, bottom: 20, left: 10, right: 10 } },
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 12 }, padding: 15 } },
                tooltip: { callbacks: { label: (ctx) => `${ctx.label}: ${ctx.parsed} (${data.pourcentages[ctx.dataIndex]}%)` } }
            },
            cutout: '50%'
        }
    });
}

function updateChartJoursSemaine(data) {
    destroyChart('chartJoursSemaine');
    charts.JoursSemaine = new Chart(document.getElementById('chartJoursSemaine'), {
        type: 'radar',
        data: {
            labels: data.jours,
            datasets: [{ label: 'Heures travaillées', data: data.heures, backgroundColor: 'rgba(58,186,244,.2)', borderColor: '#3abaf4', borderWidth: 2, pointBackgroundColor: '#3abaf4', pointRadius: 4 }]
        },
        options: { responsive: true, scales: { r: { beginAtZero: true, ticks: { stepSize: 20 } } }, plugins: { legend: { display: false } } }
    });
}

function updateChartActivitesHeure(data) {
    destroyChart('chartActivitesHeure');
    charts.ActivitesHeure = new Chart(document.getElementById('chartActivitesHeure'), {
        type: 'bar',
        data: {
            labels: data.map(i => i.heure),
            datasets: [
                { label: "Nombre d'activités", data: data.map(i => i.nombre_activites), backgroundColor: '#6777ef', borderRadius: 4, yAxisID: 'y' },
                { label: 'Heures totales', data: data.map(i => i.total_heures), backgroundColor: '#47c363', borderRadius: 4, yAxisID: 'y1' }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'top' } },
            scales: {
                x: { grid: { display: false } },
                y: { type: 'linear', position: 'left', title: { display: true, text: 'Activités' } },
                y1: { type: 'linear', position: 'right', title: { display: true, text: 'Heures' }, grid: { drawOnChartArea: false } }
            }
        }
    });
}

// ===== DÉTAILS EMPLOYÉ =====
function viewEmployeDetails(userId) {
    $.ajax({
        url: `/admin/statistiques/employe/${userId}`,
        method: 'GET',
        success: function (data) {
            Swal.fire({
                title: `<strong>${data.user.nom_complet}</strong>`,
                html: `
                    <div class="text-left">
                        <p><strong>Email :</strong> ${data.user.email}</p>
                        <p><strong>Poste :</strong> ${data.user.poste}</p>
                        <p><strong>Contrat :</strong> ${data.user.type_contrat}</p>
                        <hr>
                        <h6><i class="fas fa-clock text-primary"></i> Heures</h6>
                        <div class="row mb-3">
                            <div class="col-4"><div class="card bg-light"><div class="card-body text-center"><h4 class="text-primary">${data.heures.total}h</h4><small>Total</small></div></div></div>
                            <div class="col-4"><div class="card bg-light"><div class="card-body text-center"><h4 class="text-success">${data.heures.mois}h</h4><small>Ce mois</small></div></div></div>
                            <div class="col-4"><div class="card bg-light"><div class="card-body text-center"><h4 class="text-info">${data.heures.annee}h</h4><small>Cette année</small></div></div></div>
                        </div>
                        <h6><i class="fas fa-folder text-success"></i> Dossiers</h6>
                        <div class="row mb-3">
                            <div class="col-6"><div class="card bg-light"><div class="card-body text-center"><h4 class="text-success">${data.dossiers.total}</h4><small>Total</small></div></div></div>
                            <div class="col-6"><div class="card bg-light"><div class="card-body text-center"><h4 class="text-warning">${data.dossiers.actifs}</h4><small>Actifs</small></div></div></div>
                        </div>
                        <h6><i class="fas fa-umbrella-beach text-warning"></i> Congés</h6>
                        <div class="row mb-3">
                            <div class="col-4"><div class="card bg-light"><div class="card-body text-center"><h4 class="text-warning">${data.conges.total_demandes}</h4><small>Demandes</small></div></div></div>
                            <div class="col-4"><div class="card bg-light"><div class="card-body text-center"><h4 class="text-danger">${data.conges.jours_pris}j</h4><small>Jours pris</small></div></div></div>
                            <div class="col-4"><div class="card bg-light"><div class="card-body text-center"><h4 class="${data.conges.en_attente > 0 ? 'text-warning' : 'text-muted'}">${data.conges.en_attente}</h4><small>En attente</small></div></div></div>
                        </div>
                        <h6><i class="fas fa-balance-scale text-info"></i> Solde congés</h6>
                        <div class="row">
                            <div class="col-3"><div class="card bg-light"><div class="card-body text-center"><h4 class="text-info">${data.conges.jours_acquis}</h4><small>Acquis</small></div></div></div>
                            <div class="col-3"><div class="card bg-light"><div class="card-body text-center"><h4 class="text-warning">${data.conges.jours_pris}</h4><small>Pris</small></div></div></div>
                            <div class="col-3"><div class="card bg-light"><div class="card-body text-center"><h4 class="text-success">${data.conges.jours_restants}</h4><small>Restants</small></div></div></div>
                            <div class="col-3"><div class="card bg-light"><div class="card-body text-center"><h4 class="text-secondary">${data.conges.jours_reportes}</h4><small>Reportés</small></div></div></div>
                        </div>
                    </div>`,
                width: 700,
                showCloseButton: true,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-filter"></i> Filtrer sur cet employé',
                cancelButtonText: 'Fermer',
                confirmButtonColor: '#6777ef',
                cancelButtonColor: '#95a5a6'
            }).then(result => {
                if (result.isConfirmed) {
                    $('#filtre-employe').val(userId).trigger('change.select2');
                    applyFilters();
                }
            });
        },
        error: function () {
            Swal.fire({ icon: 'error', title: 'Erreur', text: "Impossible de charger les détails de l'employé" });
        }
    });
}
</script>
@endpush