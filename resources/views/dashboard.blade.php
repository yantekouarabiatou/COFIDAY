@php
use App\Helpers\UserHelper;
@endphp

@extends('layaout')

@section('title','Mon Tableau de bord')

@section('content')
<section class="section">
    <!-- En-tête personnalisé -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card gradient-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-white">
                            <h4 class="mb-1">Bonjour, <span id="user-name">{{ auth()->user()->prenom }} {{ auth()->user()->nom }}</span> 👋</h4>
                            <p class="mb-0 text-white">Voici un aperçu de votre activité</p>
                        </div>
                        <button class="btn btn-light rounded-pill" onclick="refreshDashboard()">
                            <i class="fas fa-sync-alt"></i> Actualiser
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cartes de statistiques personnelles -->
    <div class="row">
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
            <div class="card card-statistic-1 hover-card">
                <div class="card-icon bg-primary">
                    <i class="fas fa-folder"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Mes Dossiers</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0" id="mes-dossiers">
                                <i class="fas fa-spinner fa-spin"></i>
                            </h4>
                            <span class="badge badge-pill badge-info" id="dossiers-actifs-badge">0 actifs</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
            <div class="card card-statistic-1 hover-card">
                <div class="card-icon bg-success">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Heures (Ce mois)</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0" id="mes-heures-mois">
                                <i class="fas fa-spinner fa-spin"></i>
                            </h4>
                            <span class="badge badge-pill" id="heures-percent">--</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
            <div class="card card-statistic-1 hover-card">
                <div class="card-icon bg-info">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Heures Totales</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0" id="mes-heures-totales">
                                <i class="fas fa-spinner fa-spin"></i>
                            </h4>
                            <span class="badge badge-pill badge-secondary">Cumul</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
            <div class="card card-statistic-1 hover-card">
                <div class="card-icon bg-warning">
                    <i class="fas fa-umbrella-beach"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Mes Congés en cours</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0" id="mes-conges">
                                <i class="fas fa-spinner fa-spin"></i>
                            </h4>
                            <span class="badge badge-pill badge-warning" id="conges-badge">En cours</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques rapides personnelles -->
    <div class="row">
        <div class="col-12">
            <div class="card modern-card">
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-3 text-center border-right">
                            <i class="fas fa-calendar-week fa-2x mb-2"></i>
                            <h6 class="mb-1">Heures (5 jours)</h6>
                            <h4 class="font-weight-bold " id="heures-semaine">-</h4>
                        </div>
                        <div class="col-md-3 text-center border-right">
                            <i class="fas fa-folder-open fa-2x mb-2 text-info"></i>
                            <h6 class="mb-1">Dossiers travaillés</h6>
                            <h4 class="font-weight-bold text-info" id="dossiers-semaine">-</h4>
                        </div>
                        <div class="col-md-3 text-center border-right">
                            <i class="fas fa-calendar-check fa-2x mb-2 text-success"></i>
                            <h6 class="mb-1">Congés (Ce mois)</h6>
                            <h4 class="font-weight-bold text-success" id="conges-mois">-</h4>
                        </div>
                        <div class="col-md-3 text-center">
                            <i class="fas fa-briefcase fa-2x mb-2 text-warning"></i>
                            <h6 class="mb-1">Dossiers actifs</h6>
                            <h4 class="font-weight-bold text-warning" id="dossiers-actifs">-</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphique principal - Mes heures -->
    <div class="row">
        <div class="col-12">
            <div class="card modern-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0"><i class="fas fa-chart-area text-primary"></i> Mon Activité (30 derniers jours)</h4>
                        <small class="text-muted">Évolution quotidienne de mes heures travaillées</small>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="chartMesHeures" height="80"></canvas>
                    
                    <div class="row mt-4 pt-3 border-top">
                        <div class="col-md-4 text-center">
                            <div class="stats-box">
                                <i class="fas fa-clock text-primary fa-2x mb-2"></i>
                                <h4 class="mb-0" id="total-heures-30j">0h</h4>
                                <p class="text-muted mb-0">Total 30 jours</p>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="stats-box">
                                <i class="fas fa-calendar-day text-success fa-2x mb-2"></i>
                                <h4 class="mb-0" id="moyenne-jour">0h</h4>
                                <p class="text-muted mb-0">Moyenne / jour</p>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="stats-box">
                                <i class="fas fa-fire text-danger fa-2x mb-2"></i>
                                <h4 class="mb-0" id="max-jour">0h</h4>
                                <p class="text-muted mb-0">Maximum / jour</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mes dossiers et congés -->
    <div class="row">
        <!-- Mes dossiers les plus actifs -->
        <div class="col-lg-6 col-md-12">
            <div class="card modern-card">
                <div class="card-header">
                    <h4><i class="fas fa-fire text-danger"></i> Mes Dossiers les Plus Actifs</h4>
                    <small class="text-muted">Par nombre d'heures ce mois</small>
                </div>
                <div class="card-body">
                    <canvas id="chartMesDossiers" height="250"></canvas>
                </div>
            </div>
        </div>

        <!-- Mes congés par type -->
        <div class="col-lg-6 col-md-12">
            <div class="card modern-card">
                <div class="card-header">
                    <h4><i class="fas fa-chart-pie text-warning"></i> Répartition de Mes Congés</h4>
                    <small class="text-muted">Par type (année en cours)</small>
                </div>
                <div class="card-body">
                    <canvas id="chartMesConges" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Répartition de mes heures par dossier -->
    <div class="row">
        <div class="col-12">
            <div class="card modern-card">
                <div class="card-header">
                    <h4><i class="fas fa-chart-bar text-info"></i> Répartition de Mes Heures par Dossier</h4>
                    <small class="text-muted">Top 10 des dossiers (mois en cours)</small>
                </div>
                <div class="card-body">
                    <canvas id="chartHeuresParDossier" height="120"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Mes dernières activités -->
    <div class="row">
        <!-- Mes daily entries récentes -->
        <div class="col-lg-6 col-md-12">
            <div class="card modern-card">
                <div class="card-header">
                    <h4><i class="fas fa-history text-primary"></i> Mes Dernières Saisies</h4>
                    <small class="text-muted">7 derniers jours</small>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>Date</th>
                                    <th class="text-center">Heures</th>
                                    <th class="text-center">Statut</th>
                                </tr>
                            </thead>
                            <tbody id="daily-entries-body">
                                <tr>
                                    <td colspan="3" class="text-center py-3">
                                        <i class="fas fa-spinner fa-spin"></i> Chargement...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mes congés à venir -->
        <div class="col-lg-6 col-md-12">
            <div class="card modern-card">
                <div class="card-header">
                    <h4><i class="fas fa-calendar-alt text-success"></i> Mes Congés à Venir</h4>
                    <small class="text-muted">Prochains 30 jours</small>
                </div>
                <div class="card-body">
                    <div id="conges-a-venir-list">
                        <div class="text-center py-3">
                            <i class="fas fa-spinner fa-spin"></i> Chargement...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau récapitulatif personnel -->
    <div class="row">
        <div class="col-12">
            <div class="card modern-card">
                <div class="card-header">
                    <h4><i class="fas fa-table text-primary"></i> Mon Récapitulatif</h4>
                    <small class="text-muted">Comparaison de mes performances</small>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th><i class="fas fa-list"></i> Indicateur</th>
                                    <th class="text-center"><i class="fas fa-calendar-week"></i> Cette semaine</th>
                                    <th class="text-center"><i class="fas fa-calendar-alt"></i> Ce mois</th>
                                    <th class="text-center"><i class="fas fa-chart-line"></i> Évolution</th>
                                </tr>
                            </thead>
                            <tbody id="stats-table-body">
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                                        <p class="mt-2 mb-0">Chargement des données...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Cartes stylisées */
.hover-card {
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.card-statistic-1 {
    position: relative;
    overflow: hidden;
}

.card-statistic-1 .card-icon {
    width: 80px;
    height: 80px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    color: white;
    margin-right: 15px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}

.card-statistic-1 .card-wrap {
    flex: 1;
}

.card-statistic-1 .card-body h3 {
    font-size: 2rem;
    font-weight: bold;
    color: #34395e;
}

.card-statistic-1 .badge-pill {
    padding: 5px 12px;
    font-size: 0.85rem;
}

/* Carte gradient */
.gradient-card {
    background: linear-gradient(135deg, #244584 0%, #4b79c8 100%);
    border: none;
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

.border-right {
    border-right: 1px solid #e3e6f0;
}

/* Cartes modernes */
.modern-card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.modern-card:hover {
    box-shadow: 0 5px 20px rgba(0,0,0,0.12);
}

.modern-card .card-header {
    background-color: #f8f9fa;
    border-bottom: 2px solid #e3e6f0;
    padding: 1.25rem;
}

.modern-card .card-header h4 {
    margin-bottom: 0;
    font-weight: 600;
    color: #2c3e50;
}

/* Stats box */
.stats-box {
    padding: 15px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.stats-box:hover {
    background-color: #f8f9fa;
    transform: scale(1.05);
}

/* Badges personnalisés */
.badge-success-gradient {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    color: white;
}

.badge-danger-gradient {
    background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
    color: white;
}

.badge-warning-gradient {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

/* Animation loading */
@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.fa-spinner {
    animation: pulse 1.5s ease-in-out infinite;
}

/* Table moderne */
.table-hover tbody tr:hover {
    background-color: #f8f9fa;
    cursor: pointer;
    transition: all 0.3s ease;
}

.thead-light th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
}

/* Badge de statut */
.badge-statut {
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-soumis {
    background-color: #3abaf4;
    color: white;
}

.badge-valide {
    background-color: #47c363;
    color: white;
}

.badge-refuse {
    background-color: #fc544b;
    color: white;
}

/* Congé item */
.conge-item {
    padding: 12px;
    margin-bottom: 10px;
    border-radius: 8px;
    background-color: #f8f9fa;
    border-left: 4px solid #6777ef;
    transition: all 0.3s ease;
}

.conge-item:hover {
    background-color: #e9ecef;
    transform: translateX(5px);
}

.conge-type {
    font-weight: 600;
    color: #34395e;
}

.conge-dates {
    font-size: 0.9rem;
    color: #6c757d;
}

/* Responsive */
@media (max-width: 768px) {
    .border-right {
        border-right: none;
        border-bottom: 1px solid #e3e6f0;
        margin-bottom: 15px;
        padding-bottom: 15px;
    }
    
    .card-statistic-1 .card-icon {
        width: 60px;
        height: 60px;
        font-size: 2rem;
    }
}
</style>
@endsection

@push('scripts')
// Remplacez tout le contenu de @push('scripts') dans dashboard.blade.php

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
let chartMesHeures, chartMesDossiers, chartMesConges, chartHeuresParDossier;

// Configuration Chart.js globale
Chart.defaults.font.family = "'Nunito', sans-serif";
Chart.defaults.plugins.legend.labels.usePointStyle = true;

/**
 * Convertir les heures décimales en format "Xh Ymin"
 * Équivalent de UserHelper::hoursToHoursMinutes()
 */
function hoursToHoursMinutes(decimal) {
    if (!decimal || decimal <= 0) {
        return '0h 0min';
    }
    
    const hours = Math.floor(decimal);
    let minutes = Math.round((decimal - hours) * 60);
    
    // Gérer les minutes à 60
    if (minutes >= 60) {
        hours++;
        minutes -= 60;
    }
    
    return `${hours}h ${minutes}min`;
}

/**
 * Version courte sans les 0min
 */
function hoursToHoursMinutesShort(decimal) {
    if (!decimal || decimal <= 0) {
        return '0h';
    }
    
    let hours = Math.floor(decimal);
    let minutes = Math.round((decimal - hours) * 60);
    
    if (minutes >= 60) {
        hours++;
        minutes -= 60;
    }
    
    if (minutes === 0) {
        return `${hours}h`;
    }
    
    return `${hours}h ${minutes}min`;
}

// Charger les données au chargement de la page
$(document).ready(function() {
    loadDashboardData();
    
    // Actualiser toutes les 3 minutes
    setInterval(loadDashboardData, 180000);
});

function refreshDashboard() {
    loadDashboardData();
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'Données actualisées avec succès',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true
    });
}

function loadDashboardData() {
    $.ajax({
        url: '{{ route("dashboard.data") }}',
        method: 'GET',
        success: function(data) {
            console.log('Dashboard data:', data);
            updatePersonalStats(data);
            updateQuickStats(data);
            updateMesHeuresChart(data.last30days);
            updateMesDossiersChart(data.mesDossiersActifs);
            updateMesCongesChart(data.mesCongesParType);
            updateHeuresParDossierChart(data.mesHeuresParDossier);
            updateDailyEntries(data.mesDailyEntries);
            updateCongesAVenir(data.mesCongesAVenir);
            updateStatsTable(data);
        },
        error: function(xhr) {
            console.error('Erreur de chargement:', xhr);
            console.error('Response:', xhr.responseText);
            Swal.fire({
                icon: 'error',
                title: 'Erreur de chargement',
                text: 'Impossible de charger les données du dashboard',
                confirmButtonColor: '#6777ef'
            });
        }
    });
}

function updatePersonalStats(data) {
    const totals = data.totals;
    const percentages = data.percentages;
    
    // Nom de l'utilisateur
    if (data.user) {
        $('#user-name').text(data.user.name);
    }
    
    // Affichage direct avec format heures/minutes
    $('#mes-dossiers').text(totals.mes_dossiers);
    $('#mes-heures-mois').text(hoursToHoursMinutesShort(totals.heures_mois));
    $('#mes-heures-totales').text(hoursToHoursMinutesShort(totals.heures_totales));
    $('#mes-conges').text(totals.mes_conges_en_cours);
    
    // Badges
    $('#dossiers-actifs-badge').text(totals.dossiers_actifs + ' actifs');
    $('#conges-badge').text(totals.mes_conges_en_cours > 0 ? 'En cours' : 'Aucun');
    
    // Pourcentage d'évolution des heures
    updatePercentage('#heures-percent', percentages.heures);
}

function updateQuickStats(data) {
    const weekly = data.weekly;
    const monthly = data.monthly;
    const totals = data.totals;
    
    // Afficher avec format heures/minutes
    $('#heures-semaine').html(hoursToHoursMinutesShort(weekly.heures));
    $('#dossiers-semaine').text(weekly.dossiers_travailles);
    $('#conges-mois').text(monthly.conges);
    $('#dossiers-actifs').text(totals.dossiers_actifs);
}

function updatePercentage(selector, value) {
    const $element = $(selector);
    const numValue = parseInt(value);
    
    $element.removeClass('badge-success-gradient badge-danger-gradient badge-warning-gradient badge-secondary');
    
    if (value === '0') {
        $element.addClass('badge-secondary');
        $element.html('<i class="fas fa-minus"></i> 0%');
    } else if (numValue > 0) {
        $element.addClass('badge-success-gradient');
        $element.html('<i class="fas fa-arrow-up"></i> ' + value + '%');
    } else {
        $element.addClass('badge-danger-gradient');
        $element.html('<i class="fas fa-arrow-down"></i> ' + value + '%');
    }
}

function updateMesHeuresChart(data) {
    const ctx = document.getElementById('chartMesHeures');
    if (!ctx) return;
    
    if (chartMesHeures) chartMesHeures.destroy();
    
    const heuresData = data.heures;
    const totalHeures = heuresData.reduce((a, b) => a + b, 0);
    const moyenneHeures = totalHeures / heuresData.length;
    const maxHeures = Math.max(...heuresData);
    
    chartMesHeures = new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
            labels: data.dates,
            datasets: [{
                label: 'Mes heures',
                data: heuresData,
                borderColor: '#6777ef',
                backgroundColor: 'rgba(103, 119, 239, 0.1)',
                tension: 0.4,
                fill: true,
                borderWidth: 3,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: '#6777ef',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    callbacks: {
                        label: (context) => hoursToHoursMinutes(context.parsed.y)
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0, 0, 0, 0.05)' },
                    ticks: {
                        callback: (value) => hoursToHoursMinutesShort(value)
                    }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
    
    // Totaux avec format heures/minutes
    $('#total-heures-30j').text(hoursToHoursMinutes(totalHeures));
    $('#moyenne-jour').text(hoursToHoursMinutes(moyenneHeures));
    $('#max-jour').text(hoursToHoursMinutes(maxHeures));
}

function updateMesDossiersChart(data) {
    const ctx = document.getElementById('chartMesDossiers');
    if (!ctx) return;
    
    if (chartMesDossiers) chartMesDossiers.destroy();
    
    if (!data.names || data.names.length === 0) {
        $(ctx).parent().html('<div class="text-center py-4 text-muted"><i class="fas fa-inbox fa-3x mb-3"></i><p>Aucun dossier actif ce mois</p></div>');
        return;
    }
    
    chartMesDossiers = new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: data.names,
            datasets: [{
                label: 'Heures',
                data: data.heures,
                backgroundColor: [
                    '#6777ef', '#3abaf4', '#ffa426', '#fc544b', '#47c363'
                ],
                borderRadius: 8,
                barThickness: 40
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (context) => hoursToHoursMinutes(context.parsed.x)
                    }
                }
            },
            scales: {
                x: { 
                    beginAtZero: true,
                    grid: { color: 'rgba(0, 0, 0, 0.05)' },
                    ticks: {
                        callback: (value) => hoursToHoursMinutesShort(value)
                    }
                },
                y: { grid: { display: false } }
            }
        }
    });
}

function updateMesCongesChart(data) {
    const ctx = document.getElementById('chartMesConges');
    if (!ctx) return;
    
    if (chartMesConges) chartMesConges.destroy();
    
    if (!data.types || data.types.length === 0) {
        $(ctx).parent().html('<div class="text-center py-4 text-muted"><i class="fas fa-calendar-times fa-3x mb-3"></i><p>Aucun congé cette année</p></div>');
        return;
    }
    
    const colors = ['#6777ef', '#ffa426', '#47c363', '#fc544b', '#3abaf4', '#e83e8c'];
    
    chartMesConges = new Chart(ctx.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: data.types,
            datasets: [{
                data: data.counts,
                backgroundColor: colors.slice(0, data.types.length),
                borderWidth: 3,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 15, font: { size: 12 } }
                },
                tooltip: {
                    callbacks: {
                        label: (context) => context.label + ': ' + context.parsed + ' congé(s)'
                    }
                }
            }
        }
    });
}

function updateHeuresParDossierChart(data) {
    const ctx = document.getElementById('chartHeuresParDossier');
    if (!ctx) return;
    
    if (chartHeuresParDossier) chartHeuresParDossier.destroy();
    
    if (!data.dossiers || data.dossiers.length === 0) {
        $(ctx).parent().html('<div class="text-center py-4 text-muted"><i class="fas fa-folder-open fa-3x mb-3"></i><p>Aucune activité ce mois</p></div>');
        return;
    }
    
    chartHeuresParDossier = new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: data.dossiers,
            datasets: [{
                label: 'Heures',
                data: data.heures,
                backgroundColor: '#3abaf4',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (context) => hoursToHoursMinutes(context.parsed.y)
                    }
                }
            },
            scales: {
                y: { 
                    beginAtZero: true,
                    grid: { color: 'rgba(0, 0, 0, 0.05)' },
                    ticks: {
                        callback: (value) => hoursToHoursMinutesShort(value)
                    }
                },
                x: { 
                    grid: { display: false },
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            }
        }
    });
}

function updateDailyEntries(entries) {
    const tbody = $('#daily-entries-body');
    
    if (!entries || entries.length === 0) {
        tbody.html('<tr><td colspan="3" class="text-center py-3 text-muted">Aucune saisie récente</td></tr>');
        return;
    }
    
    let html = '';
    entries.forEach(entry => {
        let statutBadge = '';
        let statutClass = '';
        
        if (entry.statut === 'valide') {
            statutClass = 'badge-valide';
            statutBadge = '<i class="fas fa-check-circle"></i> Validé';
        } else if (entry.statut === 'refuse') {
            statutClass = 'badge-refuse';
            statutBadge = '<i class="fas fa-times-circle"></i> Refusé';
        } else {
            statutClass = 'badge-soumis';
            statutBadge = '<i class="fas fa-clock"></i> Soumis';
        }
        
        const weekend = entry.is_weekend ? '<i class="fas fa-mug-hot text-warning ml-1" title="Week-end"></i>' : '';
        const holiday = entry.is_holiday ? '<i class="fas fa-calendar-day text-danger ml-1" title="Jour férié"></i>' : '';
        
        // Afficher avec format heures/minutes
        const heuresReelles = hoursToHoursMinutes(entry.heures_reelles);
        const heuresTheoriques = hoursToHoursMinutes(entry.heures_theoriques);
        
        html += `
            <tr>
                <td>${entry.jour} ${weekend} ${holiday}</td>
                <td class="text-center">
                    <strong>${heuresReelles}</strong>
                    ${entry.heures_theoriques > 0 ? '<small class="text-muted">/ ' + heuresTheoriques + '</small>' : ''}
                </td>
                <td class="text-center">
                    <span class="badge badge-statut ${statutClass}">${statutBadge}</span>
                </td>
            </tr>
        `;
    });
    
    tbody.html(html);
}

function updateCongesAVenir(conges) {
    const container = $('#conges-a-venir-list');
    
    if (!conges || conges.length === 0) {
        container.html('<div class="text-center py-3 text-muted"><i class="fas fa-calendar-check fa-2x mb-2"></i><p>Aucun congé prévu</p></div>');
        return;
    }
    
    let html = '';
    conges.forEach(conge => {
        let typeIcon = 'fa-umbrella-beach';
        let typeColor = '#6777ef';
        
        // Identifier le type basé sur le libellé
        const typeUpper = (conge.type || '').toUpperCase();
        
        if (typeUpper.includes('MALADIE') || typeUpper.includes('SICK')) {
            typeIcon = 'fa-medkit';
            typeColor = '#fc544b';
        } else if (typeUpper.includes('MATERNITÉ') || typeUpper.includes('MATERNITE') || typeUpper.includes('PATERNITÉ')) {
            typeIcon = 'fa-baby';
            typeColor = '#ffa426';
        } else if (typeUpper.includes('PAYÉ') || typeUpper.includes('PAYE') || typeUpper.includes('RÉMUNÉRÉ')) {
            typeIcon = 'fa-umbrella-beach';
            typeColor = '#47c363';
        } else if (typeUpper.includes('NON') && (typeUpper.includes('PAYÉ') || typeUpper.includes('PAYE'))) {
            typeIcon = 'fa-plane-departure';
            typeColor = '#95a5a6';
        }
        
        // Afficher avec décimales si nécessaire
        const joursValue = parseFloat(conge.jours);
        const joursDisplay = joursValue % 1 === 0 
            ? Math.floor(joursValue) 
            : joursValue.toFixed(1);
        
        html += `
            <div class="conge-item" style="border-left-color: ${typeColor}">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="conge-type">
                            <i class="fas ${typeIcon}" style="color: ${typeColor}"></i>
                            ${conge.type}
                        </div>
                        <div class="conge-dates">
                            <i class="fas fa-calendar"></i> ${conge.debut} - ${conge.fin}
                        </div>
                    </div>
                    <div>
                        <span class="badge badge-primary badge-pill">${joursDisplay} jour(s)</span>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.html(html);
}

function updateStatsTable(data) {
    const weekly = data.weekly;
    const monthly = data.monthly;
    const percentages = data.percentages;
    
    const rows = [
        { 
            name: 'Heures travaillées', 
            icon: 'fa-clock', 
            color: '#6777ef', 
            week: hoursToHoursMinutes(weekly.heures),
            month: hoursToHoursMinutes(monthly.heures),
            percent: percentages.heures 
        },
        { 
            name: 'Dossiers travaillés', 
            icon: 'fa-folder', 
            color: '#3abaf4', 
            week: weekly.dossiers_travailles, 
            month: monthly.dossiers_travailles, 
            percent: percentages.dossiers 
        },
        { 
            name: 'Congés', 
            icon: 'fa-umbrella-beach', 
            color: '#ffa426', 
            week: 0, 
            month: monthly.conges, 
            percent: '0' 
        }
    ];
    
    let html = '';
    rows.forEach(row => {
        const isPositive = parseInt(row.percent) >= 0;
        const badgeClass = isPositive ? 'badge-success-gradient' : 'badge-danger-gradient';
        const icon = isPositive ? 'fa-arrow-up' : 'fa-arrow-down';
        
        html += `
            <tr>
                <td>
                    <i class="fas ${row.icon}" style="color: ${row.color};"></i>
                    ${row.name}
                </td>
                <td class="text-center"><strong>${row.week}</strong></td>
                <td class="text-center"><strong>${row.month}</strong></td>
                <td class="text-center">
                    <span class="badge ${badgeClass}">
                        <i class="fas ${icon}"></i>
                        ${Math.abs(parseInt(row.percent))}%
                    </span>
                </td>
            </tr>
        `;
    });
    
    $('#stats-table-body').html(html);
}


</script>
@endpush