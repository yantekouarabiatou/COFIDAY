@extends('layaout')

@section('title', 'Mon Solde de Congés')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-wallet"></i> Mon Solde de Congés</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('conges.index') }}">Congés</a></div>
            <div class="breadcrumb-item active">Mon Solde</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Solde de congés payés</h4>
                        <div class="card-header-action">
                            @if(auth()->user()->hasRole('admin') && isset($user) && $user->id != auth()->id())
                            <a href="{{ route('conges.solde') }}" class="btn btn-icon icon-left btn-primary">
                                <i class="fas fa-user"></i> Mon solde
                            </a>
                            @endif
                            <button type="button" class="btn btn-icon icon-left btn-info ml-2" onclick="history.back();">
                                <i class="fas fa-arrow-left"></i> Retour
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        {{-- Informations utilisateur --}}
                        @if(isset($user) && $user->id != auth()->id())
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Vous visualisez le solde de <strong>{{ $user->prenom }} {{ $user->nom }}</strong>
                        </div>
                        @endif

                        {{-- Année courante --}}
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h3 class="text-primary">Année {{ now()->year }}</h3>
                                <p class="text-muted">Solde de vos congés payés annuels (30 jours maximum)</p>
                            </div>
                        </div>

                        {{-- Cartes de statistiques --}}
                        <div class="row">
                            <div class="col-md-3">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-success">
                                        <i class="fas fa-gift"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Jours acquis</h4>
                                        </div>
                                        <div class="card-body">
                                            {{ $soldeCourant ? $soldeCourant->jours_acquis : 0 }}
                                        </div>
                                        <div class="card-footer">
                                            <small>Sur 30 jours maximum</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-warning">
                                        <i class="fas fa-plane"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Jours pris</h4>
                                        </div>
                                        <div class="card-body">
                                            {{ $soldeCourant ? $soldeCourant->jours_pris : 0 }}
                                        </div>
                                        <div class="card-footer">
                                            <small>Congés payés utilisés</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-primary">
                                        <i class="fas fa-coins"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Jours restants</h4>
                                        </div>
                                        <div class="card-body">
                                            {{ $soldeCourant ? $soldeCourant->jours_restants : 0 }}
                                        </div>
                                        <div class="card-footer">
                                            <small>Disponibles</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-info">
                                        <i class="fas fa-percentage"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Taux d'utilisation</h4>
                                        </div>
                                        <div class="card-body">
                                            @if($soldeCourant && $soldeCourant->jours_acquis > 0)
                                                {{ round(($soldeCourant->jours_pris / $soldeCourant->jours_acquis) * 100, 1) }}%
                                            @else
                                                0%
                                            @endif
                                        </div>
                                        <div class="card-footer">
                                            <small>Du solde acquis</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Graphique de progression --}}
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4><i class="fas fa-chart-line"></i> Progression du solde</h4>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="soldeChart" height="100"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Historique des soldes --}}
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4><i class="fas fa-history"></i> Historique des soldes</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Année</th>
                                                        <th class="text-center">Jours acquis</th>
                                                        <th class="text-center">Jours pris</th>
                                                        <th class="text-center">Jours restants</th>
                                                        <th class="text-center">Jours reportés*</th>
                                                        <th class="text-center">Taux utilisation</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($soldes as $solde)
                                                    <tr class="{{ $solde->annee == now()->year ? 'table-info' : '' }}">
                                                        <td>
                                                            <strong>{{ $solde->annee }}</strong>
                                                            @if($solde->annee == now()->year)
                                                                <span class="badge badge-primary">Année en cours</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge badge-success">{{ $solde->jours_acquis }}</span>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge badge-danger">{{ $solde->jours_pris }}</span>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge badge-primary">{{ $solde->jours_restants }}</span>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge badge-info">{{ $solde->jours_reportes ?? 0 }}</span>
                                                        </td>
                                                        <td class="text-center">
                                                            @php
                                                                $taux = $solde->jours_acquis > 0
                                                                    ? round(($solde->jours_pris / $solde->jours_acquis) * 100, 1)
                                                                    : 0;
                                                            @endphp
                                                            <div class="progress" style="height: 20px;">
                                                                <div class="progress-bar
                                                                    @if($taux < 50) bg-success
                                                                    @elseif($taux < 80) bg-warning
                                                                    @else bg-danger
                                                                    @endif"
                                                                    role="progressbar"
                                                                    style="width: {{ min($taux, 100) }}%"
                                                                    aria-valuenow="{{ $taux }}"
                                                                    aria-valuemin="0"
                                                                    aria-valuemax="100">
                                                                    {{ $taux }}%
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center py-4">
                                                            <div class="empty-state">
                                                                <div class="empty-state-icon">
                                                                    <i class="fas fa-wallet"></i>
                                                                </div>
                                                                <h2>Aucun solde disponible</h2>
                                                                <p class="lead">Aucun solde de congés n'a été trouvé pour cet utilisateur.</p>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="text-muted mt-2">
                                            <small>* Les jours reportés sont les jours non utilisés de l'année précédente qui ont été transférés</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Projections --}}
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4><i class="fas fa-calculator"></i> Projections et calculs</h4>
                                    </div>
                                    <div class="card-body">
                                        @php
                                            $regles               = \App\Models\RegleConge::first();
                                            $moisRestants         = 12 - now()->month;
                                            $joursAcquisMensuel   = $regles ? $regles->jours_par_mois : 2;
                                            $joursAcquisRestants  = $joursAcquisMensuel * $moisRestants;
                                            $soldePrevisionnel    = ($soldeCourant->jours_restants ?? 0) + $joursAcquisRestants;
                                            $limiteReport         = $regles->limite_report ?? 10;
                                            $joursReportables     = $soldeCourant
                                                ? min($soldeCourant->jours_restants, $limiteReport)
                                                : 0;
                                        @endphp
                                        <h5>Prévision fin d'année</h5>
                                        <div class="list-group">
                                            <div class="list-group-item">
                                                <div class="d-flex justify-content-between">
                                                    <span>Mois restants dans l'année :</span>
                                                    <strong>{{ $moisRestants }}</strong>
                                                </div>
                                            </div>
                                            <div class="list-group-item">
                                                <div class="d-flex justify-content-between">
                                                    <span>Jours qui seront acquis :</span>
                                                    <strong>{{ round($joursAcquisRestants, 1) }} jours</strong>
                                                </div>
                                            </div>
                                            <div class="list-group-item">
                                                <div class="d-flex justify-content-between">
                                                    <span>Solde prévisionnel au 31/12 :</span>
                                                    <strong class="text-info">{{ round($soldePrevisionnel, 1) }} jours</strong>
                                                </div>
                                            </div>
                                            <div class="list-group-item">
                                                <div class="d-flex justify-content-between">
                                                    <span>Jours reportables* :</span>
                                                    <strong class="text-success">{{ $joursReportables }} jours</strong>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-3 text-muted">
                                            <small>* Basé sur la règle de report (maximum {{ $limiteReport }} jours)</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer text-right">
                        <a href="{{ route('conges.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Nouvelle demande
                        </a>
                        <a href="{{ route('conges.index') }}" class="btn btn-secondary">
                            <i class="fas fa-list"></i> Liste des congés
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    .card-statistic-1 .card-header h4 {
        font-size: 0.9rem;
        font-weight: 600;
    }
    .card-statistic-1 .card-body {
        font-size: 1.5rem;
        font-weight: bold;
    }
    .card-statistic-1 .card-footer {
        background: transparent;
        border-top: 1px solid #e3e6f0;
        padding: 0.5rem 1.25rem;
        font-size: 0.8rem;
        color: #6c757d;
    }
    .empty-state {
        text-align: center;
        padding: 2rem;
    }
    .empty-state-icon {
        font-size: 3rem;
        color: #6c757d;
        margin-bottom: 1rem;
    }
    .list-group-item {
        border-left: none;
        border-right: none;
    }
    .progress {
        border-radius: 10px;
        overflow: hidden;
    }
    .progress-bar {
        font-weight: 500;
        font-size: 0.8rem;
        line-height: 20px;
    }
    @media print {
        .card-header-action, .btn, .modal, .no-print { display: none !important; }
        .card { border: none !important; box-shadow: none !important; }
        .table, .table th, .table td { border: 1px solid #dee2e6; }
    }
</style>
@endpush

@push('scripts')
{{-- Préparation des données PHP → JS (évite les conflits Blade/JS) --}}
@php
    $soldesJson = $soldes->map(function ($solde) {
        return [
            'annee'            => $solde->annee,
            'jours_acquis'     => $solde->jours_acquis,
            'jours_pris'       => $solde->jours_pris,
            'jours_restants'   => $solde->jours_restants,
            'jours_reportes'   => $solde->jours_reportes ?? 0,
            'taux_utilisation' => $solde->jours_acquis > 0
                ? round(($solde->jours_pris / $solde->jours_acquis) * 100)
                : 0,
        ];
    })->values()->toArray();

    $congesPayesJson = isset($demandesCongesPayes)
        ? $demandesCongesPayes
            ->where('statut', 'approuve')
            ->map(function ($conge) {
                return [
                    'type'    => optional($conge->typeConge)->libelle ?? 'Type inconnu',
                    'couleur' => optional($conge->typeConge)->couleur ?? '#3B82F6',
                    'jours'   => $conge->nombre_jours,
                ];
            })->values()->toArray()
        : [];

    $soldeRestant = $soldeCourant->jours_restants ?? 0;
@endphp

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Données PHP sérialisées proprement ────────────────────────────────────
    const soldesData      = @json($soldesJson);
    const congesPayesData = @json($congesPayesJson);
    const soldeRestant    = @json($soldeRestant);
    const routeCreate     = @json(route('conges.create'));

    // ── Graphique en barres : progression du solde ───────────────────────────
    (function initSoldeChart() {
        const canvas = document.getElementById('soldeChart');
        if (!canvas) return;

        if (!soldesData.length) {
            canvas.parentElement.innerHTML =
                '<div class="alert alert-warning text-center">Aucune donnée de solde disponible.</div>';
            return;
        }

        new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: soldesData.map(function (s) { return s.annee; }),
                datasets: [
                    {
                        label: 'Jours pris',
                        data: soldesData.map(function (s) { return s.jours_pris; }),
                        backgroundColor: 'rgba(255, 193, 107, 0.85)',
                        borderColor: '#ffc107',
                        borderWidth: 1
                    },
                    {
                        label: 'Jours restants',
                        data: soldesData.map(function (s) { return s.jours_restants; }),
                        backgroundColor: 'rgba(40, 167, 69, 0.85)',
                        borderColor: '#28a745',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    x: { stacked: true },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        title: { display: true, text: 'Nombre de jours' },
                        max: 35
                    }
                }
            }
        });
    })();

    // ── Graphique donut : répartition par type (optionnel) ───────────────────
    (function initRepartitionChart() {
        const canvas = document.getElementById('typeRepartitionChart');
        if (!canvas) return;

        if (!congesPayesData.length) {
            canvas.parentElement.innerHTML =
                '<div class="alert alert-warning text-center">Aucune donnée de congés payés.</div>';
            return;
        }

        var typeMap = {};
        congesPayesData.forEach(function (conge) {
            if (!typeMap[conge.type]) {
                typeMap[conge.type] = { jours: 0, couleur: conge.couleur };
            }
            typeMap[conge.type].jours += conge.jours;
        });

        var types    = Object.keys(typeMap);
        var jours    = types.map(function (t) { return typeMap[t].jours; });
        var couleurs = types.map(function (t) { return typeMap[t].couleur; });

        new Chart(canvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: types,
                datasets: [{ data: jours, backgroundColor: couleurs, borderWidth: 1 }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    })();

    // ── Boutons "Détails" ────────────────────────────────────────────────────
    document.querySelectorAll('.btn-details').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var annee = btn.dataset.annee;
            var solde = soldesData.find(function (s) { return s.annee == annee; });
            if (!solde) return;

            var modalBody = document.getElementById('detailsModalBody');
            if (!modalBody) { console.error('#detailsModalBody introuvable'); return; }

            var acquis100   = solde.jours_acquis > 0 ? Math.round(solde.jours_acquis / 30 * 100) : 0;
            var restants100 = solde.jours_acquis > 0 ? Math.round(solde.jours_restants / solde.jours_acquis * 100) : 0;

            modalBody.innerHTML =
                '<div class="row">' +
                    '<div class="col-md-6">' +
                        '<h5>Année ' + solde.annee + '</h5>' +
                        '<div class="list-group">' +
                            '<div class="list-group-item"><div class="d-flex justify-content-between"><span>Jours acquis :</span><strong class="text-success">' + solde.jours_acquis + '</strong></div></div>' +
                            '<div class="list-group-item"><div class="d-flex justify-content-between"><span>Jours pris :</span><strong class="text-warning">' + solde.jours_pris + '</strong></div></div>' +
                            '<div class="list-group-item"><div class="d-flex justify-content-between"><span>Jours restants :</span><strong class="text-info">' + solde.jours_restants + '</strong></div></div>' +
                            '<div class="list-group-item"><div class="d-flex justify-content-between"><span>Jours reportés :</span><strong class="text-info">' + solde.jours_reportes + '</strong></div></div>' +
                            '<div class="list-group-item"><div class="d-flex justify-content-between"><span>Taux d\'utilisation :</span><strong>' + solde.taux_utilisation + '%</strong></div></div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="col-md-6">' +
                        '<h5>Statistiques</h5>' +
                        '<div class="progress mb-3" style="height:25px;"><div class="progress-bar bg-success" style="width:' + acquis100 + '%">Acquis : ' + solde.jours_acquis + '/30</div></div>' +
                        '<div class="progress mb-3" style="height:25px;"><div class="progress-bar bg-warning" style="width:' + solde.taux_utilisation + '%">Pris : ' + solde.jours_pris + ' jours</div></div>' +
                        '<div class="progress" style="height:25px;"><div class="progress-bar bg-primary" style="width:' + restants100 + '%">Restants : ' + solde.jours_restants + ' jours</div></div>' +
                    '</div>' +
                '</div>';

            var modal = document.getElementById('detailsModal');
            if (!modal) return;
            if (typeof bootstrap !== 'undefined') {
                new bootstrap.Modal(modal).show();
            } else if (typeof $ !== 'undefined' && $.fn && $.fn.modal) {
                $(modal).modal('show');
            }
        });
    });

    // ── Admin : édition des soldes ───────────────────────────────────────────
    @if(auth()->user()->hasRole('admin'))
    document.querySelectorAll('.btn-edit-solde').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var soldeId = btn.dataset.soldeId;
            alert('Édition du solde #' + soldeId + ' – Fonctionnalité à implémenter');
        });
    });

    var adjustBtn = document.getElementById('btn-adjust-solde');
    if (adjustBtn) {
        adjustBtn.addEventListener('click', function () {
            var adjustModal = document.getElementById('adjustModal');
            if (!adjustModal) return;
            if (typeof bootstrap !== 'undefined') {
                new bootstrap.Modal(adjustModal).show();
            } else if (typeof $ !== 'undefined' && $.fn && $.fn.modal) {
                $(adjustModal).modal('show');
            }
        });
    }

    var adjustForm = document.getElementById('adjustForm');
    if (adjustForm) {
        adjustForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var joursAcquis = parseFloat(this.querySelector('[name="jours_acquis"]').value) || 0;
            var joursPris   = parseFloat(this.querySelector('[name="jours_pris"]').value)   || 0;

            if (joursPris > joursAcquis) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Les jours pris ne peuvent pas dépasser les jours acquis.'
                });
                return;
            }
            // this.submit();
        });
    }
    @endif

    // ── Calculateur de jours ouvrés ──────────────────────────────────────────
    var calcDateDebut   = document.getElementById('calc-date-debut');
    var calcDateFin     = document.getElementById('calc-date-fin');
    var calcNombreJours = document.getElementById('calc-nombre-jours');
    var resultatDiv     = document.getElementById('resultat-calcul');
    var resultatText    = document.getElementById('resultat-text');

    if (calcDateDebut && calcDateFin) {
        calcDateDebut.addEventListener('change', function () {
            var d = new Date(this.value);
            if (!isNaN(d)) {
                d.setDate(d.getDate() + 1);
                calcDateFin.valueAsDate = d;
            }
        });
    }

    var btnCalculer = document.getElementById('btn-calculer');
    if (btnCalculer) {
        btnCalculer.addEventListener('click', calculerJours);
    }

    var btnTester = document.getElementById('btn-tester-solde');
    if (btnTester) {
        btnTester.addEventListener('click', testerSolde);
    }

    function calculerJours() {
        if (!calcDateDebut || !calcDateFin || !calcNombreJours || !resultatDiv || !resultatText) {
            console.error('Éléments du calculateur manquants');
            return;
        }

        var dateDebut = calcDateDebut.value;
        var dateFin   = calcDateFin.value;

        if (!dateDebut || !dateFin) {
            Swal.fire({ icon: 'warning', title: 'Dates manquantes', text: 'Veuillez sélectionner une date de début et une date de fin.' });
            return;
        }

        var start = new Date(dateDebut);
        var end   = new Date(dateFin);

        if (end < start) {
            Swal.fire({ icon: 'error', title: 'Dates invalides', text: 'La date de fin doit être postérieure à la date de début.' });
            return;
        }

        var jours   = 0;
        var current = new Date(start);
        while (current <= end) {
            var day = current.getDay();
            if (day !== 0 && day !== 6) jours++;
            current.setDate(current.getDate() + 1);
        }

        calcNombreJours.value = jours;
        resultatText.innerHTML =
            'Période : ' + formatDate(start) + ' au ' + formatDate(end) + '<br>' +
            'Nombre de jours ouvrés : <strong>' + jours + ' jours</strong>' +
            (jours === 0 ? '<br>Aucun jour ouvré dans cette période' : '');
        resultatDiv.style.display = 'block';
    }

    function testerSolde() {
        var jours = parseInt(calcNombreJours ? calcNombreJours.value : 0) || 0;

        if (jours === 0) {
            Swal.fire({ icon: 'warning', title: 'Aucun jour', text: 'Veuillez d\'abord calculer le nombre de jours.' });
            return;
        }

        if (jours > soldeRestant) {
            Swal.fire({
                icon: 'error',
                title: 'Solde insuffisant',
                html:
                    '<div class="text-left">' +
                        '<p>Demandé : <strong>' + jours + ' jours</strong></p>' +
                        '<p>Disponible : <strong>' + soldeRestant + ' jours</strong></p>' +
                        '<p class="text-danger">Manquant : <strong>' + (jours - soldeRestant) + ' jours</strong></p>' +
                        '<p>Vous pouvez :</p>' +
                        '<ul><li>Réduire la durée de votre congé</li><li>Choisir un congé non payé</li><li>Attendre l\'acquisition de nouveaux jours</li></ul>' +
                    '</div>',
                confirmButtonText: 'Compris'
            });
        } else {
            Swal.fire({
                icon: 'success',
                title: 'Solde suffisant',
                html:
                    '<div class="text-left">' +
                        '<p>Demandé : <strong>' + jours + ' jours</strong></p>' +
                        '<p>Disponible : <strong>' + soldeRestant + ' jours</strong></p>' +
                        '<p class="text-success">Reste après congé : <strong>' + (soldeRestant - jours) + ' jours</strong></p>' +
                        '<p>Vous pouvez soumettre cette demande de congé.</p>' +
                    '</div>',
                showCancelButton: true,
                confirmButtonText: 'Créer la demande',
                cancelButtonText: 'Fermer'
            }).then(function (result) {
                if (result.isConfirmed) {
                    window.location.href = routeCreate;
                }
            });
        }
    }

    function formatDate(date) {
        return date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }
});
</script>
@endpush
