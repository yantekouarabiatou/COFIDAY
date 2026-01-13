@extends('layaout')

@section('title', 'Feuille de Temps - ' . $dailyEntry->jour->format('d/m/Y'))

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-clock"></i> Feuille de Temps</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('daily-entries.index') }}">Feuilles</a></div>
            <div class="breadcrumb-item active">Détails</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Feuille du {{ $dailyEntry->jour->format('d/m/Y') }}</h4>
                        <div class="card-header-action">
                            @if($dailyEntry->user_id == auth()->id() && $dailyEntry->statut == 'soumis')
                            <a href="{{ route('daily-entries.edit', $dailyEntry) }}" class="btn btn-icon icon-left btn-warning">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                            @endif

                            @if(auth()->user()->hasRole('admin') && $dailyEntry->statut == 'soumis')
                            <button type="button" class="btn btn-icon icon-left btn-success" id="validate-btn">
                                <i class="fas fa-check"></i> Valider
                            </button>
                            <button type="button" class="btn btn-icon icon-left btn-danger" id="reject-btn">
                                <i class="fas fa-times"></i> Refuser
                            </button>
                            @endif

                            <a href="{{ route('daily-entries.pdf', $dailyEntry) }}" target="_blank" class="btn btn-info">
                                <i class="fas fa-file-pdf"></i> PDF
                            </a>

                            <a href="{{ route('daily-entries.index') }}" class="btn btn-icon icon-left btn-primary">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- En-tête -->
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center">
                                    <div class="avatar mr-3">
                                        <div class="avatar-title rounded-circle text-white d-flex align-items-center justify-content-center"
                                            style="width: 50px; height: 50px; background: linear-gradient(135deg, #667eea, #764ba2); font-weight: bold; font-size: 1.2rem;">
                                            {{ strtoupper(substr($dailyEntry->user->prenom, 0, 1)) }}
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="mb-0">{{ $dailyEntry->user->prenom }} {{ $dailyEntry->user->nom }}</h4>
                                        <p class="mb-1">
                                            <strong>{{ $dailyEntry->user->poste->intitule ?? 'Non spécifié' }}</strong>
                                            <span class="mx-2">•</span>
                                            {{ $dailyEntry->user->email }}
                                        </p>
                                        <p class="text-muted mb-0">
                                            Créée le {{ $dailyEntry->created_at->format('d/m/Y à H:i') }}
                                            @if($dailyEntry->valide_le)
                                                <span class="mx-2">|</span>
                                                Validée le {{ $dailyEntry->valide_le->format('d/m/Y à H:i') }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-right">
                                <div class="d-flex flex-column align-items-end">
                                    <span class="badge badge-{{
                                        $dailyEntry->statut == 'validé' ? 'success' :
                                        ($dailyEntry->statut == 'refusé' ? 'danger' : 'info')
                                    }} mb-2" style="font-size: 1rem; padding: 8px 16px;">
                                        {{ ucfirst($dailyEntry->statut) }}
                                    </span>

                                    <div class="text-muted">
                                        <i class="fas fa-calendar-alt mr-1"></i>
                                        {{ $dailyEntry->jour->translatedFormat('l') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Résumé -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-primary">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Heures théoriques</h4>
                                        </div>
                                        <div class="card-body">
                                            @php
                                                $heures = floor($dailyEntry->heures_theoriques);
                                                $minutes = round(($dailyEntry->heures_theoriques - $heures) * 60);
                                            @endphp

                                            {{ $heures }}h {{ $minutes }}min
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-info">
                                        <i class="fas fa-stopwatch"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Heures réelles</h4>
                                        </div>
                                        <div class="card-body">
                                            @php
                                                $heures = floor($dailyEntry->heures_reelles);
                                                $minutes = round(($dailyEntry->heures_reelles - $heures) * 60);
                                            @endphp

                                            {{ $heures }}h {{ $minutes }}min
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @php
                                $heuresSup = max(0, $dailyEntry->heures_reelles - $dailyEntry->heures_theoriques);

                                $hsHeures = floor($heuresSup);
                                $hsMinutes = round(($heuresSup - $hsHeures) * 60);
                            @endphp

                            @if($dailyEntry->heures_reelles > $dailyEntry->heures_theoriques)
                                <div class="col-md-3">
                                    <div class="card card-statistic-1">
                                        <div class="card-icon bg-success">
                                            <i class="fas fa-plus-circle"></i>
                                        </div>
                                        <div class="card-wrap">
                                            <div class="card-header">
                                                <h4>Heures supplémentaires</h4>
                                            </div>
                                            <div class="card-body">
                                                {{ $hsHeures }}h {{ $hsMinutes }}min
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="col-md-3">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-{{ $dailyEntry->heures_reelles >= $dailyEntry->heures_theoriques ? 'success' : 'danger' }}">
                                        <i class="fas fa-percentage"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Taux de remplissage</h4>
                                        </div>
                                        <div class="card-body">
                                            {{ number_format(($dailyEntry->heures_reelles / $dailyEntry->heures_theoriques) * 100, 1) }}%
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-warning">
                                        <i class="fas fa-tasks"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Activités</h4>
                                        </div>
                                        <div class="card-body">
                                            {{ $dailyEntry->timeEntries->count() }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Commentaire -->
                        @if($dailyEntry->commentaire)
                        <div class="alert alert-light mb-4">
                            <h5><i class="fas fa-comment mr-2"></i>Commentaire</h5>
                            <p class="mb-0">{{ $dailyEntry->commentaire }}</p>
                        </div>
                        @endif

                        <!-- Motif de refus -->
                        @if($dailyEntry->statut == 'refusé' && $dailyEntry->motif_refus)
                        <div class="alert alert-danger mb-4">
                            <h5><i class="fas fa-exclamation-triangle mr-2"></i>Motif du refus</h5>
                            <p class="mb-0">{{ $dailyEntry->motif_refus }}</p>
                            @if($dailyEntry->valide_par_user)
                            <small class="text-muted">
                                Refusé par {{ $dailyEntry->valide_par_user->prenom }} le {{ $dailyEntry->valide_le->format('d/m/Y à H:i') }}
                            </small>
                            @endif
                        </div>
                        @endif

                        <!-- Liste des activités -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h4><i class="fas fa-list"></i> Activités réalisées</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Dossier</th>
                                                <th>Client</th>
                                                <th>Heures</th>
                                                <th>Travaux réalisés</th>
                                                <th>Rendu</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($dailyEntry->timeEntries as $index => $entry)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>
                                                    <strong>{{ $entry->dossier->nom }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $entry->dossier->reference ?? 'Sans référence' }}</small>
                                                </td>
                                                <td>
                                                    {{ $entry->dossier->client->nom ?? 'Sans client' }}
                                                    <br>
                                                    <small class="text-muted">{{ $entry->dossier->type_dossier }}</small>
                                                </td>
                                                <td>
                                                    <span class="badge badge-light" style="font-size: 1rem;">
                                                        {{
                                                            \Carbon\Carbon::parse($entry->heure_fin)
                                                                ->diff(\Carbon\Carbon::parse($entry->heure_debut))
                                                                ->format('%hh %Imin')
                                                        }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($entry->travaux)
                                                        {{ $entry->travaux }}
                                                    @else
                                                        <span class="text-muted">Aucune description</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($entry->rendu)
                                                        {{ $entry->rendu }}
                                                    @else
                                                        <span class="text-muted">Aucune description</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-active">
                                                <td colspan="3" class="text-right"><strong>Total heures :</strong></td>
                                                <td>
                                                    <strong>
                                                        @php
                                                            $heures = floor($dailyEntry->heures_reelles);
                                                            $minutes = round(($dailyEntry->heures_reelles - $heures) * 60);
                                                        @endphp

                                                        {{ $heures }}h {{ $minutes }}min
                                                    </strong>
                                                </td>
                                                <td colspan="2"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Visualisation des heures -->
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-chart-bar"></i> Répartition des heures</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <canvas id="timeChart" height="150"></canvas>
                                    </div>
                                    <div class="col-md-4">
                                        <ul class="list-group">
                                            @php
                                                $hoursByDossier = $dailyEntry->timeEntries
                                                    ->groupBy('dossier_id')
                                                    ->map(function($entries) {
                                                        return $entries->sum('heures_reelles');
                                                    })
                                                    ->sortDesc();
                                            @endphp

                                            @foreach($hoursByDossier as $dossierId => $hours)
                                                @php
                                                    $dossier = $dailyEntry->timeEntries
                                                        ->firstWhere('dossier_id', $dossierId)
                                                        ->dossier;
                                                    $percentage = ($hours / $dailyEntry->heures_reelles) * 100;
                                                @endphp
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <strong>{{ $dossier->nom }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ $dossier->client->nom ?? 'Sans client' }}</small>
                                                    </div>
                                                    <span class="badge badge-primary badge-pill">
                                                        @php
                                                            $heures = floor($hours);
                                                            $minutes = round(($hours - $heures) * 60);
                                                        @endphp

                                                        {{ $heures }}h {{ $minutes }}min
                                                    </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions supplémentaires -->
                        <div class="text-center mt-4">
                            <div class="btn-group" role="group">
                                
                                <a href="{{ route('daily-entries.create') }}" class="btn btn-outline-success">
                                    <i class="fas fa-plus"></i> Nouvelle saisie
                                </a>
                                @if(auth()->user()->hasRole('responsable'))
                                <a href="{{ route('daily-entries.index', ['user' => $dailyEntry->user_id]) }}" class="btn btn-outline-info">
                                    <i class="fas fa-user-clock"></i> Voir toutes les feuilles de {{ $dailyEntry->user->prenom }}
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    .avatar-title {
        color: white;
        font-weight: bold;
    }
    .list-group-item {
        border-left: none;
        border-right: none;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Graphique des heures
    let ctx = document.getElementById('timeChart').getContext('2d');

    @php
        $labels = [];
        $data = [];
        $colors = [];
        $colorPalette = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796', '#5a5c69'];

        $hoursByDossier = $dailyEntry->timeEntries
            ->groupBy('dossier_id')
            ->map(function($entries) {
                return $entries->sum('heures_reelles');
            })
            ->sortDesc();

        $i = 0;
        foreach($hoursByDossier as $dossierId => $hours) {
            $dossier = $dailyEntry->timeEntries->firstWhere('dossier_id', $dossierId)->dossier;
            $labels[] = $dossier->nom . ' (' . $hours . 'h)';
            $data[] = $hours;
            $colors[] = $colorPalette[$i % count($colorPalette)];
            $i++;
        }
    @endphp

    let timeChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: @json($labels),
            datasets: [{
                data: @json($data),
                backgroundColor: @json($colors),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                position: 'bottom'
            },
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        let dataset = data.datasets[tooltipItem.datasetIndex];
                        let total = dataset.data.reduce(function(previousValue, currentValue) {
                            return previousValue + currentValue;
                        });
                        let currentValue = dataset.data[tooltipItem.index];
                        let percentage = Math.floor((currentValue / total) * 100);
                        return currentValue + 'h (' + percentage + '%)';
                    }
                }
            }
        }
    });

    // Validation
    $('#validate-btn').on('click', function() {
        Swal.fire({
            title: 'Valider cette feuille ?',
            text: 'Confirmez-vous la validation de cette feuille de temps ?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Oui, valider',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("daily-entries.validate", $dailyEntry) }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire(
                            'Validé !',
                            'La feuille a été validée avec succès.',
                            'success'
                        ).then(() => {
                            location.reload();
                        });
                    },
                    error: function() {
                        Swal.fire(
                            'Erreur !',
                            'Une erreur est survenue lors de la validation.',
                            'error'
                        );
                    }
                });
            }
        });
    });

    // Refus
    $('#reject-btn').on('click', function() {
        Swal.fire({
            title: 'Motif du refus',
            input: 'textarea',
            inputLabel: 'Veuillez indiquer le motif du refus',
            inputPlaceholder: 'Ex: Heures insuffisantes, activités non conformes...',
            showCancelButton: true,
            confirmButtonText: 'Refuser',
            cancelButtonText: 'Annuler',
            inputValidator: (value) => {
                if (!value) {
                    return 'Le motif du refus est obligatoire !';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("daily-entries.reject", $dailyEntry) }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        motif_refus: result.value
                    },
                    success: function(response) {
                        Swal.fire(
                            'Refusé !',
                            'La feuille a été refusée.',
                            'success'
                        ).then(() => {
                            location.reload();
                        });
                    },
                    error: function() {
                        Swal.fire(
                            'Erreur !',
                            'Une erreur est survenue.',
                            'error'
                        );
                    }
                });
            }
        });
    });
});
</script>
@endpush
