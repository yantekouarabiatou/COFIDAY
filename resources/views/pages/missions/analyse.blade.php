@extends('layaout')

@section('title', 'Analyse par Mission')

@section('content')

@php
use App\Helpers\UserHelper;
use Carbon\Carbon;

// Préparer les données pour les graphiques
$personnelsAvecHeures = [];
foreach ($personnels as $personnel) {
    $heures = $personnel->timeEntries->sum('heures_reelles');
    $personnelsAvecHeures[] = [
        'nom' => $personnel->prenom . ' ' . $personnel->nom,
        'nom_court' => $personnel->prenom . ' ' . substr($personnel->nom, 0, 1) . '.',
        'heures' => $heures,
    ];
}

// Données pour l'évolution (7 derniers jours)
$joursSemaine = [];
$heuresParJour = [];
for ($i = 6; $i >= 0; $i--) {
    $date = Carbon::now()->subDays($i);
    $joursSemaine[] = $date->locale('fr')->translatedFormat('D');
    $heuresParJour[] = rand(20, 100); // Données de démonstration
}

$chargeNormale = $personnels->filter(fn($p) => $p->timeEntries->sum('heures_reelles') <= 6)->count();
$chargeMoyenne = $personnels->filter(fn($p) => $p->timeEntries->sum('heures_reelles') > 6 && $p->timeEntries->sum('heures_reelles') <= 8)->count();
$surcharge = $personnels->filter(fn($p) => $p->timeEntries->sum('heures_reelles') > 8)->count();
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
                                                        data-client="{{ $dossier->client->nom }}"
                                                        @if($dossier->userCanAccess())
                                                            class="text-success font-weight-bold"
                                                            title="Vous avez accès à ce dossier"
                                                        @endif>
                                                    {{ $dossier->reference }} - {{ $dossier->nom }}
                                                    ({{ $dossier->client->nom }})
                                                    @if($dossier->userCanAccess())
                                                        <span class="badge badge-success ml-2">Accès autorisé</span>
                                                    @endif
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
                                <div class="col-md-12">
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

        <!-- Tableau détaillé des dossiers -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-table"></i> Détail des dossiers</h4>
                        <div class="card-header-action">
                            <div class="input-group">
                                <input type="text" class="form-control" id="searchDossier" placeholder="Rechercher...">
                                <div class="input-group-append">
                                    <button class="btn btn-primary"><i class="fas fa-search"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="dossierTable">
                                <thead>
                                    <tr>
                                        <th>Dossier</th>
                                        <th>Client</th>
                                        <th>Collaborateurs</th>
                                        <th>Heures théoriques</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dossiers as $dossier)
                                    <tr>
                                        <td>{{ $dossier->nom }}<br><small class="text-muted">{{ $dossier->reference }}</small></td>
                                        <td>{{ $dossier->client->nom }}</td>
                                        <td>
                                            <span class="badge badge-info">{{ $dossier->collaborateurs->count() }} collaborateur(s)</span>
                                            @if($dossier->userCanAccess())
                                                <span class="badge badge-success">Vous y avez accès</span>
                                            @endif
                                        </td>
                                        <td>{{ UserHelper::hoursToHoursMinutes($dossier->heure_theorique_sans_weekend) }}</td>
                                        <td>{!! $dossier->statut_badge !!}</td>
                                        <td>
                                            @if($dossier->userCanAccess())
                                                <form action="{{ route('missions.filtrer') }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    <input type="hidden" name="dossier_id" value="{{ $dossier->id }}">
                                                    <input type="hidden" name="date_debut" value="{{ date('Y-m-d', strtotime('-30 days')) }}">
                                                    <input type="hidden" name="date_fin" value="{{ date('Y-m-d') }}">
                                                    <button type="submit" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-chart-bar"></i> Analyser
                                                    </button>
                                                </form>
                                            @else
                                                <button class="btn btn-sm btn-secondary" disabled title="Accès non autorisé">
                                                    <i class="fas fa-lock"></i> Non autorisé
                                                </button>
                                            @endif
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

#dossierTable th {
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

    // ============================================
    // DONNÉES POUR LES GRAPHIQUES
    // ============================================

    // Données des personnels
    const personnelLabels = [
        @foreach($personnelsAvecHeures as $personnel)
            '{{ $personnel["nom_court"] }}',
        @endforeach
    ];

    const personnelValues = [
        @foreach($personnelsAvecHeures as $personnel)
            {{ $personnel['heures'] }},
        @endforeach
    ];

    // Données pour le graphique de statut
    const statutData = [
        {{ $chargeNormale }},
        {{ $chargeMoyenne }},
        {{ $surcharge }}
    ];

    // Données pour l'évolution
    const timelineLabels = @json($joursSemaine);
    const timelineData = @json($heuresParJour);

    // ============================================
    // INITIALISATION DES GRAPHIQUES
    // ============================================

    let personnelChartInstance = null;
    let statusChartInstance = null;
    let timelineChartInstance = null;

    // Graphique des personnels
    function createPersonnelChart(type = 'bar') {
        const ctx = document.getElementById('personnelChart');
        if (!ctx) return;

        if (personnelChartInstance) {
            personnelChartInstance.destroy();
        }

        personnelChartInstance = new Chart(ctx.getContext('2d'), {
            type: type,
            data: {
                labels: personnelLabels,
                datasets: [{
                    label: 'Heures travaillées',
                    data: personnelValues,
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

    // Graphique du statut
    function createStatusChart() {
        const ctx = document.getElementById('statusChart');
        if (!ctx) return;

        if (statusChartInstance) {
            statusChartInstance.destroy();
        }

        statusChartInstance = new Chart(ctx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Charge normale', 'Charge moyenne', 'Surcharge'],
                datasets: [{
                    data: statutData,
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
    }

    // Graphique d'évolution
    function createTimelineChart() {
        const ctx = document.getElementById('timelineChart');
        if (!ctx) return;

        if (timelineChartInstance) {
            timelineChartInstance.destroy();
        }

        timelineChartInstance = new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: timelineLabels,
                datasets: [{
                    label: 'Heures travaillées',
                    data: timelineData,
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
    }

    // Initialiser les graphiques
    createPersonnelChart('bar');
    createStatusChart();
    createTimelineChart();

    // ============================================
    // INTERACTIONS
    // ============================================

    // Changer le type de graphique
    $('#chartType').change(function() {
        createPersonnelChart($(this).val());
    });

    // Rafraîchir les graphiques
    $('#refreshChart').click(function() {
        // Recharger la page pour actualiser les données
        location.reload();
    });

    // Recherche dans le tableau
    $('#searchDossier').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#dossierTable tbody tr').filter(function() {
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
