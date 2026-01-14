@extends('layaout')

@section('title', 'Statistiques Globales - Admin')

@section('content')
    <section class="section">
        <!-- En-tête avec filtres -->
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

        <!-- Filtres -->
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
                            <div class="col-md-3" id="filtre-dates" style="display: none;">
                                <label>Date de début</label>
                                <input type="date" class="form-control" id="date-debut">
                            </div>
                            <div class="col-md-3" id="filtre-dates-fin" style="display: none;">
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
                        <div class="row mt-3" id="filtre-info" style="display: none;">
                            <div class="col-12">
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Filtres actifs:</strong> <span id="filtre-details"></span>
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

        <!-- Cartes statistiques globales -->
        <div class="row g-4 mb-5">
            <!-- Ligne 1 : 4 cartes -->
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                <div
                    class="card card-statistic border-0 shadow-lg hover-lift h-100 transition-all position-relative overflow-hidden">
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
                                    <i class="fas fa-spinner fa-spin me-2"></i>
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

            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                <div
                    class="card card-statistic border-0 shadow-lg hover-lift h-100 transition-all position-relative overflow-hidden">
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
                                    <i class="fas fa-spinner fa-spin me-2"></i>
                                </h2>
                            </div>
                        </div>
                        <div class="mt-3">
                            <span class="badge bg-light text-dark px-3 py-1 rounded-pill">
                                <i class="fas fa-chart-line me-1"></i>
                                <span id="stat-moyenne">--</span> moyenne
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                <div
                    class="card card-statistic border-0 shadow-lg hover-lift h-100 transition-all position-relative overflow-hidden">
                    <div class="card-icon-bg position-absolute top-0 end-0 opacity-10">
                        <i class="fas fa-folder fa-4x"></i>
                    </div>
                    <div class="card-body pt-4 pb-4 px-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-wrapper rounded-circle bg-gradient-info p-3 me-3">
                                <i class="fas fa-folder fa-lg text-white"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="card-title text-muted mb-1 fw-normal">Dossiers</h5>
                                <h2 id="stat-dossiers" class="mb-0 fw-bold">
                                    <i class="fas fa-spinner fa-spin me-2"></i>
                                </h2>
                            </div>
                        </div>
                        <div class="mt-3">
                            <span class="badge bg-light text-dark px-3 py-1 rounded-pill">
                                <i class="fas fa-bolt me-1"></i>
                                <span id="stat-dossiers-actifs">--</span> actifs
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                <div
                    class="card card-statistic border-0 shadow-lg hover-lift h-100 transition-all position-relative overflow-hidden">
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
                                    <i class="fas fa-spinner fa-spin me-2"></i>
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

            <!-- Ligne 2 : 2 cartes restantes -->
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                <div
                    class="card card-statistic border-0 shadow-lg hover-lift h-100 transition-all position-relative overflow-hidden">
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
                                    <i class="fas fa-spinner fa-spin me-2"></i>
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

            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                <div
                    class="card card-statistic border-0 shadow-lg hover-lift h-100 transition-all position-relative overflow-hidden">
                    <div class="card-icon-bg position-absolute top-0 end-0 opacity-10">
                        <i class="fas fa-calendar-check fa-4x"></i>
                    </div>
                    <div class="card-body pt-4 pb-4 px-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-wrapper rounded-circle bg-gradient-danger p-3 me-3">
                                <i class="fas fa-calendar-check fa-lg text-white"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="card-title text-muted mb-1 fw-normal">Validation</h5>
                                <h2 id="stat-validation" class="mb-0 fw-bold">
                                    <i class="fas fa-spinner fa-spin me-2"></i>
                                </h2>
                            </div>
                        </div>
                        <div class="mt-3">
                            <span class="badge bg-light text-dark px-3 py-1 rounded-pill">
                                <i class="fas fa-percentage me-1"></i>
                                <span id="stat-validation-taux">--</span> taux
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphiques principaux -->
        <div class="row">
            <!-- Évolution des heures -->
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

            <!-- Heures par jour de la semaine -->
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

        <!-- Graphiques secondaires -->
        <div class="row">
            <!-- Répartition par dossier -->
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

            <!-- Congés par type et statut -->
            <div class="col-lg-6">
                <div class="card modern-card">
                    <div class="card-header">
                        <h4><i class="fas fa-chart-pie text-warning"></i> Congés - Répartition</h4>
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

        <!-- Performance mensuelle et taux de validation -->
        <div class=" col-lg-12 justify-content-center">

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

        <!-- Soldes de congés -->
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

        <!-- Activités par heure -->
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
/* Cartes améliorées */
.card-statistic {
    border-radius: 16px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
    position: relative;
}

.card-statistic:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15) !important;
}

.card-statistic::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--gradient-start), var(--gradient-end));
    opacity: 0;
    transition: opacity 0.3s ease;
}

.card-statistic:hover::before {
    opacity: 1;
}

.card-statistic .icon-wrapper {
    transition: all 0.3s ease;
}

.card-statistic:hover .icon-wrapper {
    transform: scale(1.1);
}

.card-icon-bg {
    color: rgba(255, 255, 255, 0.05);
    transform: translate(20px, -20px);
}

/* Gradients pour chaque carte */
.card-statistic:nth-child(1) { --gradient-start: #667eea; --gradient-end: #764ba2; }
.card-statistic:nth-child(2) { --gradient-start: #11998e; --gradient-end: #38ef7d; }
.card-statistic:nth-child(3) { --gradient-start: #3abaf4; --gradient-end: #1572E8; }
.card-statistic:nth-child(4) { --gradient-start: #ffa426; --gradient-end: #f3545d; }
.card-statistic:nth-child(5) { --gradient-start: #6c757d; --gradient-end: #343a40; }
.card-statistic:nth-child(6) { --gradient-start: #fc544b; --gradient-end: #ffa426; }

.bg-gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; }
.bg-gradient-success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%) !important; }
.bg-gradient-info { background: linear-gradient(135deg, #3abaf4 0%, #1572E8 100%) !important; }
.bg-gradient-warning { background: linear-gradient(135deg, #ffa426 0%, #f3545d 100%) !important; }
.bg-gradient-secondary { background: linear-gradient(135deg, #6c757d 0%, #343a40 100%) !important; }
.bg-gradient-danger { background: linear-gradient(135deg, #fc544b 0%, #ffa426 100%) !important; }

/* Badges améliorés */
.badge.rounded-pill {
    font-weight: 500;
    letter-spacing: 0.3px;
}

/* Tables améliorées */
.table-hover tbody tr {
    transition: all 0.2s ease;
}

.table-hover tbody tr:hover {
    background-color: rgba(var(--bs-primary-rgb), 0.05);
    transform: translateX(4px);
}

.table > thead > tr > th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 0.5px;
    color: #6c757d;
    border-bottom-width: 2px;
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card-statistic {
    animation: fadeInUp 0.5s ease forwards;
    animation-delay: calc(var(--animation-order) * 0.1s);
    opacity: 0;
}

/* Ordre d'animation pour les cartes */
.card-statistic:nth-child(1) { --animation-order: 1; }
.card-statistic:nth-child(2) { --animation-order: 2; }
.card-statistic:nth-child(3) { --animation-order: 3; }
.card-statistic:nth-child(4) { --animation-order: 4; }
.card-statistic:nth-child(5) { --animation-order: 5; }
.card-statistic:nth-child(6) { --animation-order: 6; }

/* Graphiques */
.card canvas {
    transition: opacity 0.3s ease;
}

.card:hover canvas {
    opacity: 0.9;
}

/* Progress bar pour les soldes */
.progress-taux {
    height: 8px;
    border-radius: 4px;
    overflow: hidden;
    background-color: #e9ecef;
    position: relative;
}

.progress-taux-bar {
    height: 100%;
    border-radius: 4px;
    transition: width 0.6s ease;
    background: linear-gradient(90deg, #3abaf4, #1572E8);
}

.progress-taux-text {
    position: absolute;
    top: -20px;
    right: 0;
    font-size: 0.75rem;
    font-weight: 600;
    color: #6c757d;
}

/* Responsive */
@media (max-width: 768px) {
    .card-statistic .icon-wrapper {
        width: 40px;
        height: 40px;
        padding: 8px;
    }

    .card-statistic .icon-wrapper i {
        font-size: 1rem;
    }

    .card-body {
        padding: 1rem !important;
    }
}

/* Effet de brillance sur les cartes */
.card-statistic::after {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(
        45deg,
        transparent 30%,
        rgba(255, 255, 255, 0.1) 50%,
        transparent 70%
    );
    transform: rotate(45deg);
    transition: all 0.5s ease;
    opacity: 0;
}

.card-statistic:hover::after {
    opacity: 1;
    transform: rotate(45deg) translate(20%, 20%);
}

/* Ombre portée dynamique */
.shadow-lg {
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08) !important;
}

.hover-lift:hover {
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15) !important;
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

        // Configuration Chart.js
        Chart.defaults.font.family = "'Nunito', sans-serif";
        Chart.defaults.plugins.legend.labels.usePointStyle = true;

        $(document).ready(function () {
            // Initialiser Select2
            $('#filtre-employe').select2({
                placeholder: "Rechercher un employé...",
                allowClear: true,
                width: '100%'
            });

            loadEmployes();
            loadStats();

            // Gestion du filtre période
            $('#filtre-periode').change(function () {
                const periode = $(this).val();
                if (periode === 'personnalise') {
                    $('#filtre-dates, #filtre-dates-fin').show();
                } else {
                    $('#filtre-dates, #filtre-dates-fin').hide();
                }
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
                        select.append(`<option value="${emp.id}">${emp.nom_complet}</option>`);
                    });

                    // Réinitialiser Select2
                    select.trigger('change.select2');
                }
            });
        }

        function applyFilters() {
            currentFilters = {
                periode: $('#filtre-periode').val(),
                user_id: $('#filtre-employe').val() || null,
                date_debut: $('#date-debut').val() || null,
                date_fin: $('#date-fin').val() || null
            };

            loadStats();
            updateFilterInfo();
        }

        function resetFilters() {
            $('#filtre-periode').val('mois');
            $('#filtre-employe').val('').trigger('change.select2');
            $('#date-debut, #date-fin').val('');
            $('#filtre-dates, #filtre-dates-fin').hide();
            $('#filtre-info').hide();

            currentFilters = {
                periode: 'mois',
                user_id: null,
                date_debut: null,
                date_fin: null
            };

            loadStats();
        }

        function updateFilterInfo() {
            const periode = currentFilters.periode;
            const employe = $('#filtre-employe option:selected').text();
            const hasFilters = currentFilters.user_id || periode !== 'mois';

            if (hasFilters) {
                let details = [];

                if (periode === 'jour') details.push('Aujourd\'hui');
                else if (periode === 'semaine') details.push('Cette semaine');
                else if (periode === 'mois') details.push('Ce mois');
                else if (periode === 'annee') details.push('Cette année');
                else if (periode === 'personnalise') {
                    details.push(`Du ${currentFilters.date_debut} au ${currentFilters.date_fin}`);
                }

                if (currentFilters.user_id) {
                    details.push(`Employé: ${employe}`);
                }

                $('#filtre-details').text(details.join(' • '));
                $('#filtre-info').show();
            } else {
                $('#filtre-info').hide();
            }
        }

        function refreshStats() {
            loadStats();
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Statistiques actualisées',
                showConfirmButton: false,
                timer: 2000
            });
        }

        function loadStats() {
            $.ajax({
                url: '{{ route("admin.stats.data") }}',
                method: 'GET',
                data: currentFilters,
                success: function (response) {
                    console.log('Stats data:', response);
                    const stats = response.stats;

                    updateGlobalStats(stats.totaux);
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
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: 'Impossible de charger les statistiques'
                    });
                }
            });
        }

        function updateGlobalStats(totaux) {
            $('#stat-employes').text(totaux.total_employes);
            $('#stat-employes-actifs').text(totaux.employes_actifs + ' actifs');

            $('#stat-heures').text(totaux.total_heures + 'h');
            $('#stat-moyenne').text(totaux.moyenne_heures_employe ?
                'Moyenne: ' + totaux.moyenne_heures_employe + 'h/employé' : '');

            $('#stat-dossiers').text(totaux.total_dossiers);
            $('#stat-dossiers-actifs').text(totaux.dossiers_actifs + ' actifs');

            $('#stat-conges').text(totaux.total_conges);
            $('#stat-conges-cours').text(totaux.conges_en_cours + ' en cours');

            $('#stat-clients').text(totaux.total_clients);

            // Calculer le taux de validation si disponible
            if (totaux.total_conges > 0) {
                $('#stat-validation').text(totaux.conges_en_cours);
                $('#stat-validation-taux').text('En cours: ' + totaux.conges_en_cours);
            }
        }

        function updateClassementHeures(data) {
            const tbody = $('#classement-heures');

            if (!data || data.length === 0) {
                tbody.html('<tr><td colspan="6" class="text-center py-3 text-muted">Aucune donnée</td></tr>');
                return;
            }

            let html = '';
            data.forEach((emp, index) => {
                let badgeClass = '';
                if (emp.rang === 1) badgeClass = 'badge-rang-1';
                else if (emp.rang === 2) badgeClass = 'badge-rang-2';
                else if (emp.rang === 3) badgeClass = 'badge-rang-3';

                html += `
                    <tr onclick="viewEmployeDetails(${emp.id})" style="cursor: pointer;">
                        <td>
                            <span class="badge badge-pill ${badgeClass || 'badge-secondary'}">${emp.rang}</span>
                        </td>
                        <td>
                            <strong>${emp.nom_complet}</strong><br>
                            <small class="text-muted">${emp.email}</small>
                        </td>
                        <td class="text-center">
                            <strong class="text-primary">${emp.total_heures}h</strong>
                        </td>
                        <td class="text-center">
                            <small class="text-muted">${emp.moyenne_jour}h/j</small>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-info">${emp.nombre_dossiers}</span>
                        </td>
                    </tr>
                `;
            });

            tbody.html(html);
        }

        function updateClassementConges(data) {
            const tbody = $('#classement-conges');

            if (!data || data.length === 0) {
                tbody.html('<tr><td colspan="6" class="text-center py-3 text-muted">Aucune donnée</td></tr>');
                return;
            }

            let html = '';
            data.forEach((emp, index) => {
                let badgeClass = '';
                if (emp.rang === 1) badgeClass = 'badge-rang-1';
                else if (emp.rang === 2) badgeClass = 'badge-rang-2';
                else if (emp.rang === 3) badgeClass = 'badge-rang-3';

                html += `
                    <tr onclick="viewEmployeDetails(${emp.id})" style="cursor: pointer;">
                        <td>
                            <span class="badge badge-pill ${badgeClass || 'badge-secondary'}">${emp.rang}</span>
                        </td>
                        <td>
                            <strong>${emp.nom_complet}</strong><br>
                            <small class="text-muted">${emp.email}</small>
                        </td>
                        <td class="text-center">
                            <strong class="text-warning">${emp.nombre_conges}</strong>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-success">${emp.jours_approuves}j</span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-warning">${emp.total_jours}j</span>
                        </td>
                    </tr>
                `;
            });

            tbody.html(html);
        }

        function updateSoldesConges(data) {
            const tbody = $('#soldes-conges');

            if (!data.employes || data.employes.length === 0) {
                tbody.html('<tr><td colspan="7" class="text-center py-3 text-muted">Aucune donnée</td></tr>');
                return;
            }

            let html = '';
            data.employes.forEach((emp, index) => {
                let badgeClass = '';
                if (index === 0) badgeClass = 'badge-rang-1';
                else if (index === 1) badgeClass = 'badge-rang-2';
                else if (index === 2) badgeClass = 'badge-rang-3';

                const pourcentageColor = emp.pourcentage_pris > 80 ? 'danger' :
                    emp.pourcentage_pris > 50 ? 'warning' : 'success';

                html += `
                    <tr>
                        <td>
                            <span class="badge badge-pill ${badgeClass || 'badge-secondary'}">${index + 1}</span>
                        </td>
                        <td>
                            <strong>${emp.nom_complet}</strong>
                        </td>
                        <td class="text-center">
                            <strong class="text-info">${emp.jours_acquis}</strong>
                        </td>
                        <td class="text-center">
                            <strong class="text-warning">${emp.jours_pris}</strong>
                        </td>
                        <td class="text-center">
                            <strong class="text-success">${emp.jours_restants}</strong>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-secondary">${emp.jours_reportes}</span>
                        </td>
                        <td>
                            <div class="progress-taux position-relative">
                                <div class="progress-taux-bar bg-${pourcentageColor}"
                                     style="width: ${emp.pourcentage_pris}%"></div>
                                <div class="progress-taux-text">${emp.pourcentage_pris}%</div>
                            </div>
                        </td>
                    </tr>
                `;
            });

            tbody.html(html);
        }

        function updateChartEvolution(data) {
            destroyChart('chartEvolution');

            const ctx = document.getElementById('chartEvolution');
            charts.evolution = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: 'Heures travaillées',
                            data: data.heures,
                            borderColor: '#6777ef',
                            backgroundColor: 'rgba(103, 119, 239, 0.1)',
                            tension: 0.4,
                            fill: true,
                            borderWidth: 3,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Employés actifs',
                            data: data.employes,
                            borderColor: '#47c363',
                            backgroundColor: 'rgba(71, 195, 99, 0.1)',
                            tension: 0.4,
                            borderWidth: 2,
                            borderDash: [5, 5],
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    stacked: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.datasetIndex === 0) {
                                        label += context.parsed.y + ' heures';
                                    } else {
                                        label += context.parsed.y + ' employés';
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Heures'
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Employés'
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                        }
                    }
                }
            });
        }

        function updateChartDossiers(data) {
            destroyChart('chartDossiers');

            const ctx = document.getElementById('chartDossiers');
            charts.dossiers = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.dossiers,
                    datasets: [{
                        label: 'Heures travaillées',
                        data: data.heures,
                        backgroundColor: '#47c363',
                        borderRadius: 6
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                afterLabel: function (context) {
                                    return `Intervenants: ${data.intervenants[context.dataIndex]}`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Heures'
                            }
                        }
                    }
                }
            });
        }

        function updateChartConges(data) {
            destroyChart('chartCongesTypes');
            destroyChart('chartCongesStatuts');

            // Graphique des types de congés
            const ctxTypes = document.getElementById('chartCongesTypes');
            const colorsTypes = ['#6777ef', '#ffa426', '#47c363', '#fc544b', '#3abaf4', '#f3545d'];

            charts.congesTypes = new Chart(ctxTypes, {
                type: 'doughnut',
                data: {
                    labels: data.types.labels,
                    datasets: [{
                        data: data.types.counts,
                        backgroundColor: colorsTypes,
                        borderWidth: 3,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' },
                        title: {
                            display: true,
                            text: 'Par type'
                        }
                    }
                }
            });

            // Graphique des statuts de congés
            const ctxStatuts = document.getElementById('chartCongesStatuts');
            const colorsStatuts = {
                'En_attente': '#3abaf4',
                'Approuve': '#47c363',
                'Refuse': '#fc544b',
                'Annule': '#95a5a6'
            };

            const bgColorsStatuts = data.statuts.labels.map(l =>
                colorsStatuts[l.replace(' ', '_')] || '#95a5a6'
            );

            charts.congesStatuts = new Chart(ctxStatuts, {
                type: 'pie',
                data: {
                    labels: data.statuts.labels,
                    datasets: [{
                        data: data.statuts.counts,
                        backgroundColor: bgColorsStatuts,
                        borderWidth: 3,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' },
                        title: {
                            display: true,
                            text: 'Par statut'
                        }
                    }
                }
            });
        }

        function updateChartMensuel(data) {
            destroyChart('chartMensuel');

            const ctx = document.getElementById('chartMensuel');
            charts.mensuel = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: 'Heures travaillées',
                            data: data.heures,
                            backgroundColor: '#3abaf4',
                            borderRadius: 6,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Jours de congés',
                            data: data.jours_conges,
                            backgroundColor: '#ffa426',
                            borderRadius: 6,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Heures'
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Jours'
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                        }
                    }
                }
            });
        }

        function updateChartValidation(data) {
            destroyChart('chartValidation');

            const ctx = document.getElementById('chartValidation').getContext('2d');

            const colors = {
                'Soumis': '#3abaf4',
                'Valide': '#95d5a2',
                'Refuse': '#fc544b',
                'Brouillon': '#47b363'
            };

            const bgColors = data.statuts.map(s => colors[s] || '#95a5a6');

            charts.validation = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: data.statuts,
                    datasets: [{
                        data: data.counts,
                        backgroundColor: bgColors,
                        borderWidth: 3,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,      // ← Très important pour forcer la hauteur personnalisée
                    aspectRatio: 2.5,                // ← Ratio largeur/hauteur (1.5 = plus large que haut)
                    layout: {
                        padding: {
                            top: 10,
                            bottom: 20,
                            left: 10,
                            right: 10
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: {
                                    size: 12           // ← Légende plus petite
                                },
                                padding: 15
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const percentage = data.pourcentages[context.dataIndex];
                                    return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                                }
                            }
                        }
                    },
                    cutout: '50%',                      // ← Option bonus : rend le donut plus fin et compac
                }
            });
        }
        function updateChartJoursSemaine(data) {
            destroyChart('chartJoursSemaine');

            const ctx = document.getElementById('chartJoursSemaine');
            charts.joursSemaine = new Chart(ctx, {
                type: 'radar',
                data: {
                    labels: data.jours,
                    datasets: [{
                        label: 'Heures travaillées',
                        data: data.heures,
                        backgroundColor: 'rgba(58, 186, 244, 0.2)',
                        borderColor: '#3abaf4',
                        borderWidth: 2,
                        pointBackgroundColor: '#3abaf4',
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        r: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 20
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }

        function updateChartActivitesHeure(data) {
            destroyChart('chartActivitesHeure');

            const ctx = document.getElementById('chartActivitesHeure');
            charts.activitesHeure = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.map(item => item.heure),
                    datasets: [
                        {
                            label: 'Nombre d\'activités',
                            data: data.map(item => item.nombre_activites),
                            backgroundColor: '#6777ef',
                            borderRadius: 4,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Heures totales',
                            data: data.map(item => item.total_heures),
                            backgroundColor: '#47c363',
                            borderRadius: 4,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Activités'
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Heures'
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                        }
                    }
                }
            });
        }

        function destroyChart(chartId) {
            const chartKey = chartId.replace('chart', '').toLowerCase();
            if (charts[chartKey]) {
                charts[chartKey].destroy();
                delete charts[chartKey];
            }
        }

        function viewEmployeDetails(userId) {
            $.ajax({
                url: `/admin/statistiques/employe/${userId}`,
                method: 'GET',
                success: function (data) {
                    Swal.fire({
                        title: '<strong>' + data.user.nom_complet + '</strong>',
                        html: `
                            <div class="text-left">
                                <p><strong>Email:</strong> ${data.user.email}</p>
                                <p><strong>Poste:</strong> ${data.user.poste}</p>
                                <p><strong>Type de contrat:</strong> ${data.user.type_contrat}</p>
                                <hr>

                                <h6><i class="fas fa-clock text-primary"></i> Heures travaillées</h6>
                                <div class="row mb-3">
                                    <div class="col-4">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h4 class="text-primary">${data.heures.total}h</h4>
                                                <small>Total</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h4 class="text-success">${data.heures.mois}h</h4>
                                                <small>Ce mois</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h4 class="text-info">${data.heures.annee}h</h4>
                                                <small>Cette année</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <h6><i class="fas fa-folder text-success"></i> Dossiers</h6>
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h4 class="text-success">${data.dossiers.total}</h4>
                                                <small>Total dossiers</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h4 class="text-warning">${data.dossiers.actifs}</h4>
                                                <small>Dossiers actifs</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <h6><i class="fas fa-umbrella-beach text-warning"></i> Congés</h6>
                                <div class="row mb-3">
                                    <div class="col-4">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h4 class="text-warning">${data.conges.total_demandes}</h4>
                                                <small>Demandes</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h4 class="text-danger">${data.conges.jours_pris}j</h4>
                                                <small>Jours pris</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h4 class="${data.conges.en_attente > 0 ? 'text-warning' : 'text-muted'}">${data.conges.en_attente}</h4>
                                                <small>En attente</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <h6><i class="fas fa-balance-scale text-info"></i> Solde congés</h6>
                                <div class="row mb-3">
                                    <div class="col-3">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h4 class="text-info">${data.conges.jours_acquis}</h4>
                                                <small>Acquis</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h4 class="text-warning">${data.conges.jours_pris}</h4>
                                                <small>Pris</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h4 class="text-success">${data.conges.jours_restants}</h4>
                                                <small>Restants</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h4 class="text-secondary">${data.conges.jours_reportes}</h4>
                                                <small>Reportés</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <h6><i class="fas fa-check-circle text-success"></i> Validation</h6>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h4 class="text-success">${data.validation.feuilles_validees}</h4>
                                                <small>Feuilles validées</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h4 class="text-warning">${data.validation.feuilles_en_attente}</h4>
                                                <small>En attente</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `,
                        width: 700,
                        showCloseButton: true,
                        showCancelButton: true,
                        confirmButtonText: '<i class="fas fa-filter"></i> Filtrer sur cet employé',
                        cancelButtonText: 'Fermer',
                        confirmButtonColor: '#6777ef',
                        cancelButtonColor: '#95a5a6'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('#filtre-employe').val(userId).trigger('change.select2');
                            applyFilters();
                        }
                    });
                },
                error: function (xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: 'Impossible de charger les détails de l\'employé'
                    });
                }
            });
        }
    </script>
@endpush
