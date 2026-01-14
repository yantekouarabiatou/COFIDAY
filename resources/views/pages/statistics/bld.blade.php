<!-- Cartes statistiques globales -->
<div class="row g-4 mb-5">
    <!-- Ligne 1 : 4 cartes -->
    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
        <div class="card card-statistic border-0 shadow-lg hover-lift h-100 transition-all position-relative overflow-hidden">
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
        <div class="card card-statistic border-0 shadow-lg hover-lift h-100 transition-all position-relative overflow-hidden">
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
        <div class="card card-statistic border-0 shadow-lg hover-lift h-100 transition-all position-relative overflow-hidden">
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
        <div class="card card-statistic border-0 shadow-lg hover-lift h-100 transition-all position-relative overflow-hidden">
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
        <div class="card card-statistic border-0 shadow-lg hover-lift h-100 transition-all position-relative overflow-hidden">
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
        <div class="card card-statistic border-0 shadow-lg hover-lift h-100 transition-all position-relative overflow-hidden">
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

<!-- Espacement -->
<div class="my-5 py-3"></div>

<!-- Classements -->
<div class="row g-4 mb-5">
    <!-- Classement par heures -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-lg hover-lift h-100 transition-all">
            <div class="card-header bg-transparent border-bottom py-3 px-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-0"><i class="fas fa-trophy text-warning me-2"></i> Top Employés</h5>
                        <small class="text-muted">Classement par heures travaillées</small>
                    </div>
                    <span class="badge bg-primary rounded-pill px-3 py-2">Classement</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0 ps-4" style="width: 60px;">#</th>
                                <th class="border-0">Employé</th>
                                <th class="border-0 text-center" style="width: 120px;">Heures</th>
                                <th class="border-0 text-center" style="width: 100px;">Dossiers</th>
                            </tr>
                        </thead>
                        <tbody id="classement-heures">
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Chargement...</span>
                                    </div>
                                    <p class="mt-2 text-muted">Chargement des données...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Classement par congés -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-lg hover-lift h-100 transition-all">
            <div class="card-header bg-transparent border-bottom py-3 px-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-0"><i class="fas fa-plane-departure text-danger me-2"></i> Congés Employés</h5>
                        <small class="text-muted">Classement par jours de congés</small>
                    </div>
                    <span class="badge bg-warning rounded-pill px-3 py-2">Vacances</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0 ps-4" style="width: 60px;">#</th>
                                <th class="border-0">Employé</th>
                                <th class="border-0 text-center" style="width: 120px;">Congés</th>
                                <th class="border-0 text-center" style="width: 100px;">Jours</th>
                            </tr>
                        </thead>
                        <tbody id="classement-conges">
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div class="spinner-border text-warning" role="status">
                                        <span class="visually-hidden">Chargement...</span>
                                    </div>
                                    <p class="mt-2 text-muted">Chargement des données...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Espacement -->
<div class="my-5 py-3"></div>

<!-- Graphiques principaux -->
<div class="row g-4 mb-5">
    <!-- Évolution des heures -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-lg hover-lift h-100 transition-all">
            <div class="card-header bg-transparent border-bottom py-3 px-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-0"><i class="fas fa-chart-line text-primary me-2"></i> Évolution des Heures</h5>
                        <small class="text-muted">Tendance sur la période sélectionnée</small>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cog me-1"></i> Options
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="updateChartType('line')">Ligne</a></li>
                            <li><a class="dropdown-item" href="#" onclick="updateChartType('bar')">Barres</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body p-4">
                <canvas id="chartEvolution" height="100"></canvas>
            </div>
        </div>
    </div>

    <!-- Heures par jour de la semaine -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-lg hover-lift h-100 transition-all">
            <div class="card-header bg-transparent border-bottom py-3 px-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-0"><i class="fas fa-calendar-week text-info me-2"></i> Par Jour</h5>
                        <small class="text-muted">Distribution hebdomadaire</small>
                    </div>
                    <i class="fas fa-chart-radar text-info"></i>
                </div>
            </div>
            <div class="card-body p-4">
                <canvas id="chartJoursSemaine" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Espacement -->
<div class="my-5 py-3"></div>

<!-- Graphiques secondaires -->
<div class="row g-4 mb-5">
    <!-- Répartition par dossier -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-lg hover-lift h-100 transition-all">
            <div class="card-header bg-transparent border-bottom py-3 px-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-0"><i class="fas fa-chart-bar text-success me-2"></i> Top 10 Dossiers</h5>
                        <small class="text-muted">Par heures travaillées</small>
                    </div>
                    <span class="badge bg-success rounded-pill px-3 py-1">10</span>
                </div>
            </div>
            <div class="card-body p-4">
                <canvas id="chartDossiers" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Congés par type et statut -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-lg hover-lift h-100 transition-all">
            <div class="card-header bg-transparent border-bottom py-3 px-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-0"><i class="fas fa-chart-pie text-warning me-2"></i> Congés - Répartition</h5>
                        <small class="text-muted">Par type et statut</small>
                    </div>
                    <i class="fas fa-chart-pie text-warning"></i>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card border-light shadow-sm">
                            <div class="card-body text-center">
                                <h6 class="mb-3 text-muted">Par Type</h6>
                                <canvas id="chartCongesTypes" height="180"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-light shadow-sm">
                            <div class="card-body text-center">
                                <h6 class="mb-3 text-muted">Par Statut</h6>
                                <canvas id="chartCongesStatuts" height="180"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Espacement -->
<div class="my-5 py-3"></div>

<!-- Performance mensuelle et taux de validation -->
<div class="row g-4 mb-5">
    <div class="col-lg-6">
        <div class="card border-0 shadow-lg hover-lift h-100 transition-all">
            <div class="card-header bg-transparent border-bottom py-3 px-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-0"><i class="fas fa-calendar-alt text-primary me-2"></i> Performance Mensuelle</h5>
                        <small class="text-muted">Heures vs Jours de congés</small>
                    </div>
                    <span class="badge bg-primary rounded-pill px-3 py-1">Mois</span>
                </div>
            </div>
            <div class="card-body p-4">
                <canvas id="chartMensuel" height="200"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-lg hover-lift h-100 transition-all">
            <div class="card-header bg-transparent border-bottom py-3 px-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-0"><i class="fas fa-check-circle text-success me-2"></i> Taux de Validation</h5>
                        <small class="text-muted">Statut des feuilles de temps</small>
                    </div>
                    <span class="badge bg-success rounded-pill px-3 py-1">%</span>
                </div>
            </div>
            <div class="card-body p-4">
                <canvas id="chartValidation" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Espacement -->
<div class="my-5 py-3"></div>

<!-- Soldes de congés -->
<div class="row g-4 mb-5">
    <div class="col-lg-12">
        <div class="card border-0 shadow-lg hover-lift h-100 transition-all">
            <div class="card-header bg-transparent border-bottom py-3 px-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-0"><i class="fas fa-balance-scale text-info me-2"></i> Top 10 Soldes de Congés</h5>
                        <small class="text-muted">Jours restants pour l'année en cours</small>
                    </div>
                    <span class="badge bg-info rounded-pill px-3 py-1">2024</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0 ps-4" style="width: 60px;">#</th>
                                <th class="border-0">Employé</th>
                                <th class="border-0 text-center" style="width: 120px;">Jours acquis</th>
                                <th class="border-0 text-center" style="width: 120px;">Jours pris</th>
                                <th class="border-0 text-center" style="width: 120px;">Jours restants</th>
                                <th class="border-0 text-center" style="width: 120px;">Taux d'utilisation</th>
                            </tr>
                        </thead>
                        <tbody id="soldes-conges">
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="spinner-border text-info" role="status">
                                        <span class="visually-hidden">Chargement...</span>
                                    </div>
                                    <p class="mt-2 text-muted">Chargement des soldes...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Espacement -->
<div class="my-5 py-3"></div>

<!-- Activités par heure -->
<div class="row g-4 mb-5">
    <div class="col-lg-12">
        <div class="card border-0 shadow-lg hover-lift h-100 transition-all">
            <div class="card-header bg-transparent border-bottom py-3 px-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-0"><i class="fas fa-clock text-secondary me-2"></i> Activités par Heure</h5>
                        <small class="text-muted">Distribution horaire des activités (7h-20h)</small>
                    </div>
                    <span class="badge bg-secondary rounded-pill px-3 py-1">24h</span>
                </div>
            </div>
            <div class="card-body p-4">
                <canvas id="chartActivitesHeure" height="80"></canvas>
            </div>
        </div>
    </div>
</div>
