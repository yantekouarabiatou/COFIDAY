@extends('layaout')

@section('title','Mon Tableau de bord')

@section('content')
<section class="section">

    {{-- En-tête personnalisé --}}
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

    {{-- Cartes de statistiques principales --}}
    <div class="row">

        {{-- Congés en cours --}}
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
            <div class="card card-statistic-1 hover-card">
                <div class="card-icon bg-warning">
                    <i class="fas fa-umbrella-beach"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header"><h4>Congés en cours</h4></div>
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

        {{-- Attestations ce mois --}}
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
            <div class="card card-statistic-1 hover-card">
                <div class="card-icon bg-primary">
                    <i class="fas fa-file-signature"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header"><h4>Attestations (Ce mois)</h4></div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0" id="mes-attestations-mois">
                                <i class="fas fa-spinner fa-spin"></i>
                            </h4>
                            <span class="badge badge-pill" id="attestations-percent">--</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Attestations totales --}}
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
            <div class="card card-statistic-1 hover-card">
                <div class="card-icon bg-info">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header"><h4>Attestations Totales</h4></div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0" id="mes-attestations-totales">
                                <i class="fas fa-spinner fa-spin"></i>
                            </h4>
                            <span class="badge badge-pill badge-secondary">Cumul</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Certificats générés --}}
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
            <div class="card card-statistic-1 hover-card">
                <div class="card-icon bg-success">
                    <i class="fas fa-certificate"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header"><h4>Certificats de travail</h4></div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0" id="mes-certificats">
                                <i class="fas fa-spinner fa-spin"></i>
                            </h4>
                            <span class="badge badge-pill badge-success">Générés</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Statistiques rapides --}}
    <div class="row">
        <div class="col-12">
            <div class="card modern-card">
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-3 text-center border-right">
                            <i class="fas fa-umbrella-beach fa-2x mb-2 text-warning"></i>
                            <h6 class="mb-1">Congés (7 jours)</h6>
                            <h4 class="font-weight-bold text-warning" id="conges-semaine">-</h4>
                        </div>
                        <div class="col-md-3 text-center border-right">
                            <i class="fas fa-calendar-check fa-2x mb-2 text-success"></i>
                            <h6 class="mb-1">Congés (Ce mois)</h6>
                            <h4 class="font-weight-bold text-success" id="conges-mois">-</h4>
                        </div>
                        <div class="col-md-3 text-center border-right">
                            <i class="fas fa-file-signature fa-2x mb-2 text-primary"></i>
                            <h6 class="mb-1">Attestations (Ce mois)</h6>
                            <h4 class="font-weight-bold text-primary" id="attestations-mois">-</h4>
                        </div>
                        <div class="col-md-3 text-center">
                            <i class="fas fa-hourglass-half fa-2x mb-2 text-danger"></i>
                            <h6 class="mb-1">En attente</h6>
                            <h4 class="font-weight-bold text-danger" id="total-en-attente">-</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Graphique attestations (30 jours) --}}
    <div class="row">
        <div class="col-12">
            <div class="card modern-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0"><i class="fas fa-chart-area text-primary"></i> Mes Attestations (30 derniers jours)</h4>
                        <small class="text-muted">Évolution des demandes d'attestations</small>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="chartAttestations" height="80"></canvas>

                    <div class="row mt-4 pt-3 border-top">
                        <div class="col-md-4 text-center">
                            <div class="stats-box">
                                <i class="fas fa-file-signature text-primary fa-2x mb-2"></i>
                                <h4 class="mb-0" id="total-attestations-30j">0</h4>
                                <p class="text-muted mb-0">Total 30 jours</p>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="stats-box">
                                <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                                <h4 class="mb-0" id="attestations-approuvees">0</h4>
                                <p class="text-muted mb-0">Approuvées</p>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="stats-box">
                                <i class="fas fa-clock text-danger fa-2x mb-2"></i>
                                <h4 class="mb-0" id="attestations-en-attente">0</h4>
                                <p class="text-muted mb-0">En attente</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Graphiques en camembert --}}
    <div class="row">

        {{-- Attestations par type --}}
        <div class="col-lg-6 col-md-12">
            <div class="card modern-card">
                <div class="card-header">
                    <h4><i class="fas fa-chart-pie text-primary"></i> Mes Attestations par Type</h4>
                    <small class="text-muted">Répartition annuelle</small>
                </div>
                <div class="card-body">
                    <canvas id="chartMesAttestations" height="250"></canvas>
                </div>
            </div>
        </div>

        {{-- Congés par type --}}
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

    {{-- Tableau des demandes récentes + congés à venir --}}
    <div class="row">

        {{-- Demandes récentes (attestations, congés, certificats) --}}
        <div class="col-lg-6 col-md-12">
            <div class="card modern-card">
                <div class="card-header">
                    <h4><i class="fas fa-history text-primary"></i> Mes Dernières Demandes</h4>
                    <small class="text-muted">Attestations · Congés · Certificats</small>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Nature</th>
                                    <th class="text-center">Statut</th>
                                </tr>
                            </thead>
                            <tbody id="demandes-recentes-body">
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

        {{-- Congés à venir --}}
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

    {{-- Récapitulatif --}}
    <div class="row">
        <div class="col-12">
            <div class="card modern-card">
                <div class="card-header">
                    <h4><i class="fas fa-table text-primary"></i> Mon Récapitulatif</h4>
                    <small class="text-muted">Semaine vs mois en cours</small>
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
.hover-card {
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}
.card-statistic-1 { position: relative; overflow: hidden; }
.card-statistic-1 .card-icon {
    width: 80px; height: 80px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 2.5rem; color: white; margin-right: 15px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}
.card-statistic-1 .card-wrap { flex: 1; }
.card-statistic-1 .card-body h4 { font-size: 2rem; font-weight: bold; color: #34395e; }
.card-statistic-1 .badge-pill { padding: 5px 12px; font-size: 0.85rem; }

.gradient-card {
    background: linear-gradient(135deg, #244584 0%, #4b79c8 100%);
    border: none;
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}
.border-right { border-right: 1px solid #e3e6f0; }

.modern-card {
    border: none; border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}
.modern-card:hover { box-shadow: 0 5px 20px rgba(0,0,0,0.12); }
.modern-card .card-header {
    background-color: #f8f9fa;
    border-bottom: 2px solid #e3e6f0;
    padding: 1.25rem;
}
.modern-card .card-header h4 { margin-bottom: 0; font-weight: 600; color: #2c3e50; }

.stats-box { padding: 15px; border-radius: 8px; transition: all 0.3s ease; }
.stats-box:hover { background-color: #f8f9fa; transform: scale(1.05); }

.badge-success-gradient { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; }
.badge-danger-gradient  { background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%); color: white; }

@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.5} }
.fa-spinner { animation: pulse 1.5s ease-in-out infinite; }

.table-hover tbody tr:hover { background-color: #f8f9fa; cursor: pointer; transition: all 0.3s ease; }
.thead-light th { font-weight: 600; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px; }

.badge-statut { padding: 5px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
.badge-soumis    { background-color: #3abaf4; color: white; }
.badge-valide    { background-color: #47c363; color: white; }
.badge-refuse    { background-color: #fc544b; color: white; }
.badge-annule    { background-color: #6c757d; color: white; }
.badge-acceptee  { background-color: #47c363; color: white; }

.conge-item {
    padding: 12px; margin-bottom: 10px; border-radius: 8px;
    background-color: #f8f9fa; border-left: 4px solid #6777ef;
    transition: all 0.3s ease;
}
.conge-item:hover { background-color: #e9ecef; transform: translateX(5px); }
.conge-type  { font-weight: 600; color: #34395e; }
.conge-dates { font-size: 0.9rem; color: #6c757d; }

@media (max-width: 768px) {
    .border-right { border-right: none; border-bottom: 1px solid #e3e6f0; margin-bottom: 15px; padding-bottom: 15px; }
    .card-statistic-1 .card-icon { width: 60px; height: 60px; font-size: 2rem; }
}
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
let chartAttestations, chartMesAttestations, chartMesConges;

Chart.defaults.font.family = "'Nunito', sans-serif";
Chart.defaults.plugins.legend.labels.usePointStyle = true;

$(document).ready(function () {
    loadDashboardData();
    setInterval(loadDashboardData, 180000);
});

function refreshDashboard() {
    loadDashboardData();
    Swal.fire({
        toast: true, position: 'top-end', icon: 'success',
        title: 'Données actualisées avec succès',
        showConfirmButton: false, timer: 2000, timerProgressBar: true
    });
}

function loadDashboardData() {
    $.ajax({
        url: '{{ route("dashboard.data") }}',
        method: 'GET',
        success: function (data) {
            updatePersonalStats(data);
            updateQuickStats(data);
            updateAttestationsChart(data);
            updateMesAttestationsChart(data.mesAttestationsParType);
            updateMesCongesChart(data.mesCongesParType);
            updateDemandesRecentes(data.demandesRecentes);
            updateCongesAVenir(data.mesCongesAVenir);
            updateStatsTable(data);
        },
        error: function (xhr) {
            console.error('Erreur de chargement:', xhr);
            Swal.fire({
                icon: 'error', title: 'Erreur de chargement',
                text: 'Impossible de charger les données du dashboard',
                confirmButtonColor: '#6777ef'
            });
        }
    });
}

// ── Mise à jour des cartes de statistiques ──────────────────────────────────

function updatePersonalStats(data) {
    const t = data.totals;

    if (data.user) {
        $('#user-name').text(data.user.name);
    }

    $('#mes-conges').text(t.mes_conges_en_cours);
    $('#conges-badge').text(t.mes_conges_en_cours > 0 ? 'En cours' : 'Aucun');

    $('#mes-attestations-mois').text(t.attestations_approuvees_mois);
    $('#mes-attestations-totales').text(t.attestations_totales);
    $('#mes-certificats').text(t.certificats_generes);

    updatePercentage('#attestations-percent', data.percentages.attestations);
}

function updateQuickStats(data) {
    const w = data.weekly;
    const m = data.monthly;
    const t = data.totals;

    $('#conges-semaine').text(w.conges);
    $('#conges-mois').text(m.conges);
    $('#attestations-mois').text(m.attestations);
    $('#total-en-attente').text(t.attestations_en_attente + t.conges_en_attente);
}

function updatePercentage(selector, value) {
    const $el = $(selector);
    const num = parseInt(value);
    $el.removeClass('badge-success-gradient badge-danger-gradient badge-secondary');

    if (value === '0') {
        $el.addClass('badge-secondary').html('<i class="fas fa-minus"></i> 0%');
    } else if (num > 0) {
        $el.addClass('badge-success-gradient').html('<i class="fas fa-arrow-up"></i> ' + value + '%');
    } else {
        $el.addClass('badge-danger-gradient').html('<i class="fas fa-arrow-down"></i> ' + value + '%');
    }
}

// ── Graphique attestations (30 jours) ───────────────────────────────────────

function updateAttestationsChart(data) {
    const ctx = document.getElementById('chartAttestations');
    if (!ctx) return;
    if (chartAttestations) chartAttestations.destroy();

    const att = data.last30daysAttestations;
    const total = att.attestations.reduce((a, b) => a + b, 0);

    chartAttestations = new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
            labels: att.dates,
            datasets: [{
                label: 'Attestations',
                data: att.attestations,
                borderColor: '#6777ef',
                backgroundColor: 'rgba(103,119,239,0.1)',
                tension: 0.4, fill: true, borderWidth: 3,
                pointRadius: 4, pointHoverRadius: 6,
                pointBackgroundColor: '#6777ef',
                pointBorderColor: '#fff', pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    mode: 'index', intersect: false,
                    backgroundColor: 'rgba(0,0,0,0.8)', padding: 12,
                    callbacks: { label: ctx => ctx.parsed.y + ' attestation(s)' }
                }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: 'rgba(0,0,0,0.05)' } },
                x: { grid: { display: false } }
            }
        }
    });

    $('#total-attestations-30j').text(total);
    $('#attestations-approuvees').text(data.totals.attestations_approuvees_mois);
    $('#attestations-en-attente').text(data.totals.attestations_en_attente);
}

// ── Graphique attestations par type (camembert) ─────────────────────────────

function updateMesAttestationsChart(data) {
    const ctx = document.getElementById('chartMesAttestations');
    if (!ctx) return;
    if (chartMesAttestations) chartMesAttestations.destroy();

    if (!data.types || data.types.length === 0) {
        $(ctx).parent().html('<div class="text-center py-4 text-muted"><i class="fas fa-file-alt fa-3x mb-3"></i><p>Aucune attestation cette année</p></div>');
        return;
    }

    chartMesAttestations = new Chart(ctx.getContext('2d'), {
        type: 'pie',
        data: {
            labels: data.types,
            datasets: [{
                data: data.counts,
                backgroundColor: ['#6777ef','#3abaf4','#ffa426','#fc544b','#47c363','#cd201f','#36b9cc'],
                borderWidth: 2, borderColor: '#fff'
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: true,
            plugins: {
                legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true } },
                tooltip: { callbacks: { label: ctx => ctx.label + ': ' + ctx.parsed + ' attestation(s)' } }
            }
        }
    });
}

// ── Graphique congés par type (donut) ───────────────────────────────────────

function updateMesCongesChart(data) {
    const ctx = document.getElementById('chartMesConges');
    if (!ctx) return;
    if (chartMesConges) chartMesConges.destroy();

    if (!data.types || data.types.length === 0) {
        $(ctx).parent().html('<div class="text-center py-4 text-muted"><i class="fas fa-calendar-times fa-3x mb-3"></i><p>Aucun congé cette année</p></div>');
        return;
    }

    const colors = ['#6777ef','#ffa426','#47c363','#fc544b','#3abaf4','#e83e8c'];

    chartMesConges = new Chart(ctx.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: data.types,
            datasets: [{
                data: data.counts,
                backgroundColor: colors.slice(0, data.types.length),
                borderWidth: 3, borderColor: '#fff'
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: true,
            plugins: {
                legend: { position: 'bottom', labels: { padding: 15, font: { size: 12 } } },
                tooltip: { callbacks: { label: ctx => ctx.label + ': ' + ctx.parsed + ' congé(s)' } }
            }
        }
    });
}

// ── Tableau des demandes récentes ───────────────────────────────────────────

function updateDemandesRecentes(demandes) {
    const tbody = $('#demandes-recentes-body');

    if (!demandes || demandes.length === 0) {
        tbody.html('<tr><td colspan="3" class="text-center py-3 text-muted">Aucune demande récente</td></tr>');
        return;
    }

    const iconMap = {
        attestation: { icon: 'fa-file-signature', color: '#6777ef' },
        conge:       { icon: 'fa-umbrella-beach', color: '#ffa426' },
        demission:   { icon: 'fa-certificate',    color: '#47c363' },
    };

    const statutMap = {
        en_attente: { cls: 'badge-soumis',   label: '<i class="fas fa-clock"></i> En attente' },
        approuve:   { cls: 'badge-valide',   label: '<i class="fas fa-check-circle"></i> Approuvé' },
        acceptee:   { cls: 'badge-acceptee', label: '<i class="fas fa-check-circle"></i> Acceptée' },
        refuse:     { cls: 'badge-refuse',   label: '<i class="fas fa-times-circle"></i> Refusé' },
        refusee:    { cls: 'badge-refuse',   label: '<i class="fas fa-times-circle"></i> Refusée' },
        annule:     { cls: 'badge-annule',   label: '<i class="fas fa-ban"></i> Annulé' },
    };

    let html = '';
    demandes.forEach(d => {
        const ico = iconMap[d.type] || { icon: 'fa-file', color: '#6c757d' };
        const st  = statutMap[d.statut] || { cls: 'badge-secondary', label: d.statut };

        html += `
            <tr>
                <td>${d.date}</td>
                <td>
                    <i class="fas ${ico.icon} mr-1" style="color:${ico.color}"></i>
                    ${d.nature}
                </td>
                <td class="text-center">
                    <span class="badge badge-statut ${st.cls}">${st.label}</span>
                </td>
            </tr>`;
    });

    tbody.html(html);
}

// ── Congés à venir ──────────────────────────────────────────────────────────

function updateCongesAVenir(conges) {
    const container = $('#conges-a-venir-list');

    if (!conges || conges.length === 0) {
        container.html('<div class="text-center py-3 text-muted"><i class="fas fa-calendar-check fa-2x mb-2"></i><p>Aucun congé prévu</p></div>');
        return;
    }

    let html = '';
    conges.forEach(c => {
        let icon  = 'fa-umbrella-beach';
        let color = '#6777ef';
        const up  = (c.type || '').toUpperCase();

        if (up.includes('MALADIE'))                              { icon = 'fa-medkit';         color = '#fc544b'; }
        else if (up.includes('MATERNIT') || up.includes('PATERNIT')) { icon = 'fa-baby';      color = '#ffa426'; }
        else if (up.includes('NON') && up.includes('PAY'))      { icon = 'fa-plane-departure'; color = '#95a5a6'; }
        else if (up.includes('PAY') || up.includes('RÉMUNÉR'))  { icon = 'fa-umbrella-beach'; color = '#47c363'; }

        const joursVal  = parseFloat(c.jours);
        const joursDisp = joursVal % 1 === 0 ? Math.floor(joursVal) : joursVal.toFixed(1);

        html += `
            <div class="conge-item" style="border-left-color:${color}">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="conge-type">
                            <i class="fas ${icon}" style="color:${color}"></i> ${c.type}
                        </div>
                        <div class="conge-dates">
                            <i class="fas fa-calendar"></i> ${c.debut} — ${c.fin}
                        </div>
                    </div>
                    <span class="badge badge-primary badge-pill">${joursDisp} jour(s)</span>
                </div>
            </div>`;
    });

    container.html(html);
}

// ── Tableau récapitulatif ───────────────────────────────────────────────────

function updateStatsTable(data) {
    const w = data.weekly;
    const m = data.monthly;
    const p = data.percentages;

    const rows = [
        {
            name: 'Congés demandés', icon: 'fa-umbrella-beach', color: '#ffa426',
            week: w.conges, month: m.conges, percent: p.conges
        },
        {
            name: 'Attestations', icon: 'fa-file-signature', color: '#6777ef',
            week: w.attestations, month: m.attestations, percent: p.attestations
        },
        {
            name: 'Certificats générés', icon: 'fa-certificate', color: '#47c363',
            week: '—', month: m.certificats, percent: '0'
        },
    ];

    let html = '';
    rows.forEach(row => {
        const pos   = parseInt(row.percent) >= 0;
        const bcls  = pos ? 'badge-success-gradient' : 'badge-danger-gradient';
        const arrow = pos ? 'fa-arrow-up' : 'fa-arrow-down';

        html += `
            <tr>
                <td><i class="fas ${row.icon}" style="color:${row.color}"></i> ${row.name}</td>
                <td class="text-center"><strong>${row.week}</strong></td>
                <td class="text-center"><strong>${row.month}</strong></td>
                <td class="text-center">
                    <span class="badge ${bcls}">
                        <i class="fas ${arrow}"></i> ${Math.abs(parseInt(row.percent) || 0)}%
                    </span>
                </td>
            </tr>`;
    });

    $('#stats-table-body').html(html);
}
</script>
@endpush
