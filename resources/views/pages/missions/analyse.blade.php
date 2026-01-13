@extends('layaout')

@section('title', 'Analyse par Mission')

@section('content')

@php
use App\Helpers\UserHelper;
@endphp
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-chart-pie"></i> Analyse des Personnels par Mission</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">Missions</div>
            <div class="breadcrumb-item">Analyse</div>
        </div>
    </div>

    <div class="section-body">
        <!-- Formulaire de filtrage -->
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h4><i class="fas fa-filter"></i> Filtres d'analyse</h4>
                        <div class="card-header-action">
                            <button class="btn btn-icon btn-sm" data-toggle="collapse" data-target="#filterCollapse">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body collapse show" id="filterCollapse">
                        <form action="{{ route('missions.filtrer') }}" method="POST" id="analyse-form">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fas fa-folder-open"></i> Mission / Dossier *</label>
                                        <select name="dossier_id" class="form-control select2" required>
                                            <option value="">Sélectionner un dossier</option>
                                            @foreach($dossiers as $dossier)
                                                <option value="{{ $dossier->id }}"
                                                        data-type="{{ $dossier->type_dossier }}"
                                                        data-client="{{ $dossier->client->nom }}">
                                                    {{ $dossier->reference }} - {{ $dossier->nom }}
                                                    ({{ $dossier->client->nom }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><i class="fas fa-calendar-alt"></i> Date début</label>
                                        <input type="date" name="date_debut" class="form-control"
                                               value="{{ date('Y-m-d', strtotime('-30 days')) }}">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><i class="fas fa-calendar-check"></i> Date fin</label>
                                        <input type="date" name="date_fin" class="form-control"
                                               value="{{ date('Y-m-d') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fas fa-file-export"></i> Format d'export</label>
                                        <select name="export_format" class="form-control">
                                            <option value="">Aucun export</option>
                                            <option value="pdf"><i class="far fa-file-pdf"></i> PDF (Rapport)</option>
                                            <option value="excel"><i class="far fa-file-excel"></i> Excel (.xlsx)</option>
                                            <option value="csv"><i class="fas fa-file-csv"></i> CSV (.csv)</option>
                                            <option value="json"><i class="fas fa-code"></i> JSON (.json)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fas fa-chart-line"></i> Type de graphique</label>
                                        <select id="chartType" class="form-control">
                                            <option value="bar">Barres</option>
                                            <option value="pie">Camembert</option>
                                            <option value="line">Courbes</option>
                                            <option value="doughnut">Anneau</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center mt-3">
                                <button type="submit" class="btn btn-primary btn-lg px-5">
                                    <i class="fas fa-chart-bar"></i> Analyser
                                </button>

                                <button type="button" class="btn btn-success btn-lg ml-2 px-5" id="btn-export">
                                    <i class="fas fa-file-export"></i> Exporter
                                </button>

                                <button type="reset" class="btn btn-secondary btn-lg ml-2 px-5">
                                    <i class="fas fa-redo"></i> Réinitialiser
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques globales -->
        <div class="row">
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card card-statistic-2">
                    <div class="card-icon shadow-primary bg-primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Total Personnels</h4>
                        </div>
                        <div class="card-body">
                            {{ $personnels->count() }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card card-statistic-2">
                    <div class="card-icon shadow-success bg-success">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Heures Totales</h4>
                        </div>
                        <div class="card-body">
                            @php
                                $totalHeures = $personnels->sum(function($p) {
                                    return $p->timeEntries->sum('heures_reelles');
                                });
                            @endphp
                            {{ UserHelper::hoursToHoursMinutes($totalHeures) }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card card-statistic-2">
                    <div class="card-icon shadow-warning bg-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Surcharges</h4>
                        </div>
                        <div class="card-body">
                            @php
                                $surcharges = $personnels->filter(function($p) {
                                    return $p->timeEntries->sum('heures_reelles') > 8;
                                })->count();
                            @endphp
                            {{ $surcharges }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card card-statistic-2">
                    <div class="card-icon shadow-info bg-info">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Taux Occupation</h4>
                        </div>
                        <div class="card-body">
                            @php
                                $tauxOccupation = $personnels->count() > 0
                                    ? round(($totalHeures / ($personnels->count() * 8)) * 100, 1)
                                    : 0;
                            @endphp
                            {{ $tauxOccupation }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphiques -->
        <div class="row">
            <div class="col-lg-8 col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-chart-bar"></i> Répartition des heures par personnel</h4>
                        <div class="card-header-action">
                            <button class="btn btn-sm btn-primary" id="refreshChart">
                                <i class="fas fa-sync-alt"></i> Actualiser
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="personnelChart" height="100"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-chart-pie"></i> Statut des charges</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="statusChart" height="200"></canvas>
                        <div class="mt-3">
                            <div class="mb-2">
                                <span class="badge badge-success">Charge normale</span>
                                <span class="float-right font-weight-bold">
                                    {{ $personnels->filter(fn($p) => $p->timeEntries->sum('heures_reelles') <= 6)->count() }}
                                </span>
                            </div>
                            <div class="mb-2">
                                <span class="badge badge-warning">Charge moyenne</span>
                                <span class="float-right font-weight-bold">
                                    {{ $personnels->filter(fn($p) => $p->timeEntries->sum('heures_reelles') > 6 && $p->timeEntries->sum('heures_reelles') <= 8)->count() }}
                                </span>
                            </div>
                            <div class="mb-2">
                                <span class="badge badge-danger">Heures Suplémentaires</span>
                                <span class="float-right font-weight-bold">
                                    {{ $personnels->filter(fn($p) => $p->timeEntries->sum('heures_reelles') > 8)->count() }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphique d'évolution temporelle -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-chart-line"></i> Évolution des heures travaillées</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="timelineChart" height="60"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tableau détaillé des personnels -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-table"></i> Détail des personnels</h4>
                        <div class="card-header-action">
                            <div class="input-group">
                                <input type="text" class="form-control" id="searchPersonnel" placeholder="Rechercher...">
                                <div class="input-group-append">
                                    <button class="btn btn-primary"><i class="fas fa-search"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="personnelTable">
                                <thead>
                                    <tr>
                                        <th>Personnel</th>
                                        <th>Poste</th>
                                        <th class="text-center">Heures Aujourd'hui</th>
                                        <th class="text-center">Total Période</th>
                                        <th class="text-center">Nb Interventions</th>
                                        <th class="text-center">Charge</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($personnels as $personnel)
                                        @php
                                            $chargeJour = $personnel->timeEntries
                                                ->filter(fn($t) => $t->dailyEntry && $t->dailyEntry->jour->isToday())
                                                ->sum('heures_reelles');

                                            $chargeTotal = $personnel->timeEntries->sum('heures_reelles');
                                            $nbInterventions = $personnel->timeEntries->count();

                                            $statut = 'success';
                                            $label = 'Normale';
                                            if ($chargeJour > 8) {
                                                $statut = 'danger';
                                                $label = 'Surcharge';
                                            } elseif ($chargeJour > 6) {
                                                $statut = 'warning';
                                                $label = 'Moyenne';
                                            }

                                            $progress = min(($chargeJour / 8) * 100, 100);
                                        @endphp
                                        <tr class="personnel-row" data-charge="{{ $chargeJour }}">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm mr-2">
                                                        @if($personnel->photo)
                                                            <img src="{{ asset('storage/'.$personnel->photo) }}" alt="Avatar">
                                                        @else
                                                            <div class="avatar-initial rounded-circle bg-{{ $statut }}">
                                                                {{ strtoupper(substr($personnel->nom, 0, 1)) }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <div class="font-weight-bold">{{ $personnel->full_name }}</div>
                                                        <small class="text-muted">{{ $personnel->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">
                                                    {{ $personnel->poste->intitule ?? 'Non défini' }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="font-weight-bold text-{{ $statut }}">
                                                    {{ UserHelper::hoursToHoursMinutes($chargeJour) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="font-weight-bold">
                                                    {{ UserHelper::hoursToHoursMinutes($chargeTotal) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-primary">
                                                    {{ $nbInterventions }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="progress mb-1" style="height: 20px;">
                                                    <div class="progress-bar bg-{{ $statut }}"
                                                         role="progressbar"
                                                         style="width: {{ $progress }}%"
                                                         aria-valuenow="{{ $progress }}"
                                                         aria-valuemin="0"
                                                         aria-valuemax="100">
                                                        {{ round($progress) }}%
                                                    </div>
                                                </div>
                                                <small class="text-{{ $statut }}">{{ $label }}</small>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-primary"
                                                        data-toggle="modal"
                                                        data-target="#detailModal"
                                                        data-personnel="{{ $personnel->id }}">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Détail -->
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-user-clock"></i> Détail des interventions
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalContent">
                <!-- Contenu chargé dynamiquement -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
<style>
.card-statistic-2 {
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transition: transform 0.3s;
}

.card-statistic-2:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
}

.card-icon {
    border-radius: 10px;
    font-size: 2.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.avatar-initial {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    font-weight: bold;
    color: white;
}

.table-hover tbody tr:hover {
    background-color: rgba(98, 89, 202, 0.1);
    cursor: pointer;
}

#personnelTable th {
    background-color: #6259ca;
    color: white;
    font-weight: 600;
}

.progress {
    border-radius: 10px;
}

.progress-bar {
    border-radius: 10px;
    font-weight: bold;
}

.select2-container--default .select2-selection--single {
    border-radius: 5px;
    height: 42px;
    padding: 6px;
}

.card-primary {
    border-top: 3px solid #6259ca;
}
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
$(document).ready(function() {
    // Initialiser Select2
    $('.select2').select2({
        placeholder: 'Sélectionner un dossier',
        allowClear: true
    });

    // Données pour les graphiques
    const personnelData = {
        labels: [
            @foreach($personnels as $personnel)
                '{{ $personnel->full_name }}',
            @endforeach
        ],
        values: [
            @foreach($personnels as $personnel)
                {{ $personnel->timeEntries->sum('heures_reelles') }},
            @endforeach
        ]
    };

    // Graphique des personnels
    let currentChartType = 'bar';
    let personnelChartInstance;

    function createPersonnelChart(type) {
        const ctx = document.getElementById('personnelChart').getContext('2d');

        if (personnelChartInstance) {
            personnelChartInstance.destroy();
        }

        personnelChartInstance = new Chart(ctx, {
            type: type,
            data: {
                labels: personnelData.labels,
                datasets: [{
                    label: 'Heures travaillées',
                    data: personnelData.values,
                    backgroundColor: [
                        '#6259ca', '#3abaf4', '#ffa426', '#fc544b',
                        '#47c363', '#6777ef', '#fdac41', '#e74c3c'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: type === 'pie' || type === 'doughnut',
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed.toFixed(2) + 'h';
                            }
                        }
                    }
                },
                scales: type === 'bar' || type === 'line' ? {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value + 'h';
                            }
                        }
                    }
                } : {}
            }
        });
    }

    createPersonnelChart('bar');

    $('#chartType').change(function() {
        createPersonnelChart($(this).val());
    });

    $('#refreshChart').click(function() {
        createPersonnelChart($('#chartType').val());
    });

    // Graphique du statut
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Charge normale', 'Charge moyenne', 'Surcharge'],
            datasets: [{
                data: [
                    {{ $personnels->filter(fn($p) => $p->timeEntries->sum('heures_reelles') <= 6)->count() }},
                    {{ $personnels->filter(fn($p) => $p->timeEntries->sum('heures_reelles') > 6 && $p->timeEntries->sum('heures_reelles') <= 8)->count() }},
                    {{ $personnels->filter(fn($p) => $p->timeEntries->sum('heures_reelles') > 8)->count() }}
                ],
                backgroundColor: ['#47c363', '#ffa426', '#fc544b'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Graphique d'évolution
    const timelineCtx = document.getElementById('timelineChart').getContext('2d');
    new Chart(timelineCtx, {
        type: 'line',
        data: {
            labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
            datasets: [{
                label: 'Heures travaillées',
                data: [45, 52, 48, 55, 50, 20, 15],
                borderColor: '#6259ca',
                backgroundColor: 'rgba(98, 89, 202, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Recherche
    $('#searchPersonnel').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#personnelTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // Export
    $('#btn-export').on('click', function() {
        $('#analyse-form').append('<input type="hidden" name="export" value="1">');
        $('#analyse-form').submit();
    });
});
</script>
@endpush
