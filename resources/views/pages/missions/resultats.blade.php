@extends('layaout')

@section('title', 'Résultats Analyse')

@section('content')

@php
use App\Helpers\UserHelper;
@endphp
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-chart-bar"></i> Résultats de l'analyse</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item">
                <a href="{{ route('missions.analyse') }}">Missions</a>
            </div>
            <div class="breadcrumb-item active">Résultats</div>
        </div>
    </div>

    <div class="section-body">
        <!-- Résumé du dossier -->
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h4><i class="fas fa-folder"></i> {{ $dossier->nom }}</h4>
                        <div class="card-header-action">
                            <span class="badge badge-light">Réf: {{ $dossier->reference }}</span>
                            <span class="badge badge-info">Client: {{ $dossier->client->nom }}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Description:</strong> {{ $dossier->description ?? 'Non renseignée' }}</p>
                                <p><strong>Période analysée:</strong>
                                    {{ $request->date_debut ? date('d/m/Y', strtotime($request->date_debut)) : 'Début' }}
                                    au
                                    {{ $request->date_fin ? date('d/m/Y', strtotime($request->date_fin)) : 'Aujourd\'hui' }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Heures théoriques:</strong> {{ UserHelper::hoursToHoursMinutes($stats['heure_theorique']) }}</p>
                                <p><strong>Heures réelles:</strong> {{ UserHelper::hoursToHoursMinutes($stats['total_heures']) }}</p>
                                <p><strong>Surplus/Déficit:</strong>
                                    <span class="{{ $stats['surplus'] >= 0 ? 'text-danger' : 'text-success' }}">
                                        {{ $stats['surplus'] }}h ({{ $stats['surplus_pourcentage'] }}%)
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="row">
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>Personnels impliqués</h4></div>
                        <div class="card-body">{{ $stats['total_personnels'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-success">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>Total heures</h4></div>
                        <div class="card-body">{{ UserHelper::hoursToHoursMinutes($stats['total_heures']) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-{{ $stats['surplus'] > 0 ? 'danger' : 'info' }}">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>Heures Surplementaires</h4></div>
                        <div class="card-body">{{ UserHelper::hoursToHoursMinutes($stats['surplus']) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-warning">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>Moyenne/pers</h4></div>
                        <div class="card-body">{{ UserHelper::hoursToHoursMinutes($stats['moyenne_par_personnel']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tableau des personnels -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Personnels ayant travaillé sur ce dossier</h4>
                        <div class="card-header-action">
                            <button class="btn btn-primary" data-toggle="modal" data-target="#exportModal">
                                <i class="fas fa-file-export"></i> Exporter
                            </button>
                            <a href="{{ route('missions.analyse') }}" class="btn btn-info">
                                <i class="fas fa-redo"></i> Nouvelle analyse
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(count($personnelsParUser) > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Personnel</th>
                                        <th>Poste</th>
                                        <th>Heures sur dossier</th>
                                        <th>Charge totale</th>
                                        <th>Autres missions</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($personnelsParUser as $personnel)
                                        @php
                                            // Déterminer le statut
                                            $statutClasse = 'success';
                                            $statutIcon = '🟢';
                                            $statutText = 'Disponible';

                                            if ($personnel['charge_totale']['ecart'] > 10) {
                                                $statutClasse = 'danger';
                                                $statutIcon = '🔴';
                                                $statutText = 'Surcharge';
                                            } elseif ($personnel['charge_totale']['ecart'] > 5) {
                                                $statutClasse = 'warning';
                                                $statutIcon = '🟠';
                                                $statutText = 'Charge élevée';
                                            }

                                            $autresMissionsCount = count($personnel['autres_missions']);
                                        @endphp

                                        <tr>
                                            <td>
                                                <strong>{{ $personnel['user']->prenom }} {{ $personnel['user']->nom }}</strong><br>
                                                <small>{{ $personnel['user']->email ?? 'N/A' }}</small>
                                            </td>
                                            <td>{{ $personnel['user']->poste->intitule ?? 'Non défini' }}</td>
                                            <td>
                                                <span class="badge badge-info">{{ UserHelper::hoursToHoursMinutes($personnel['total_heures']) }}</span>
                                            </td>
                                            <td>
                                                {{ UserHelper::hoursToHoursMinutes($personnel['charge_totale']['heures_reelles']) }} /
                                                {{ UserHelper::hoursToHoursMinutes($personnel['charge_totale']['heures_theoriques']) }}<br>
                                                <small class="text-{{ $personnel['charge_totale']['ecart'] >= 0 ? 'danger' : 'success' }}">
                                                    {{ $personnel['charge_totale']['ecart'] >= 0 ? '+' : '' }}{{ UserHelper::hoursToHoursMinutes($personnel['charge_totale']['ecart']) }}
                                                </small>
                                            </td>
                                            <td>
                                                @if($autresMissionsCount > 0)
                                                    <span class="badge badge-warning">{{ $autresMissionsCount }} mission(s)</span>
                                                    <button class="btn btn-sm btn-outline-info mt-1"
                                                            data-toggle="collapse"
                                                            data-target="#missions-{{ $personnel['user']->id }}">
                                                        Voir détails
                                                    </button>
                                                @else
                                                    <span class="badge badge-success">Aucune autre mission</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $statutClasse }}">
                                                    {{ $statutIcon }} {{ $statutText }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('missions.utilisateur.dossier', ['user' => $personnel['user']->id, 'dossier' => $dossier->id]) }}"
                                                   class="btn btn-sm btn-info" title="Voir détails">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>

                                        <!-- Détails des autres missions -->
                                        @if($autresMissionsCount > 0)
                                            <tr class="collapse-row collapse" id="missions-{{ $personnel['user']->id }}">
                                                <td colspan="7">
                                                    <div class="p-3 bg-light">
                                                        <h6><i class="fas fa-tasks"></i> Autres missions en cours</h6>
                                                        <div class="row mt-2">
                                                            @foreach($personnel['autres_missions'] as $dossierId => $missions)
                                                                @php
                                                                    $dossierMission = $missions->first()->dossier;
                                                                    $totalHeures = $missions->sum('heures_reelles');
                                                                @endphp
                                                                <div class="col-md-4 mb-2">
                                                                    <div class="card border">
                                                                        <div class="card-body p-2">
                                                                            <h6 class="mb-1">{{ $dossierMission->nom }}</h6>
                                                                            <small class="text-muted">Réf: {{ $dossierMission->reference }}</small><br>
                                                                            <span class="badge badge-secondary">{{ UserHelper::hoursToHoursMinutes($totalHeures) }}</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Aucun personnel n'a travaillé sur ce dossier pendant la période sélectionnée.
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphiques -->
        @if(count($personnelsParUser) > 0)
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Répartition des heures</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="heuresChart" height="250"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Statut des personnels</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="statutChart" height="250"></canvas>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</section>

<!-- Modal Export -->
<div class="modal fade" id="exportModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Exporter les résultats</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('missions.filtrer') }}" method="POST">
                    @csrf
                    <input type="hidden" name="dossier_id" value="{{ $dossier->id }}">
                    <input type="hidden" name="date_debut" value="{{ $request->date_debut }}">
                    <input type="hidden" name="date_fin" value="{{ $request->date_fin }}">

                    <div class="form-group">
                        <label>Sélectionnez le format d'export</label>
                        <select name="export_format" class="form-control" required>
                            <option value="pdf">PDF (Rapport complet)</option>
                            <option value="excel">Excel (.xlsx)</option>
                            <option value="csv">CSV (.csv)</option>
                            <option value="json">JSON (.json)</option>
                        </select>
                    </div>

                    <div class="text-right">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Exporter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Données pour les graphiques
    const personnels = @json($personnelsParUser);

    // Préparer les données pour le graphique des heures
    const labels = [];
    const heuresData = [];
    const autresHeuresData = [];

    Object.values(personnels).forEach(p => {
        labels.push((p.user.prenom + ' ' + p.user.nom).substring(0, 15) + '...');
        heuresData.push(p.total_heures);
        autresHeuresData.push(p.charge_totale.heures_reelles - p.total_heures);
    });

    // Graphique des heures
    if (document.getElementById('heuresChart')) {
        const ctx1 = document.getElementById('heuresChart').getContext('2d');
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Heures sur ce dossier',
                    data: heuresData,
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }, {
                    label: 'Autres heures',
                    data: autresHeuresData,
                    backgroundColor: 'rgba(255, 99, 132, 0.8)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Heures'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Personnels'
                        }
                    }
                }
            }
        });
    }

    // Graphique des statuts
    if (document.getElementById('statutChart')) {
        const statutCounts = {
            disponible: 0,
            charge_elevee: 0,
            surcharge: 0
        };

        Object.values(personnels).forEach(p => {
            if (p.charge_totale.ecart > 10) {
                statutCounts.surcharge++;
            } else if (p.charge_totale.ecart > 5) {
                statutCounts.charge_elevee++;
            } else {
                statutCounts.disponible++;
            }
        });

        const ctx2 = document.getElementById('statutChart').getContext('2d');
        new Chart(ctx2, {
            type: 'pie',
            data: {
                labels: ['Disponible', 'Charge élevée', 'Surcharge'],
                datasets: [{
                    data: [statutCounts.disponible, statutCounts.charge_elevee, statutCounts.surcharge],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(255, 205, 86, 0.8)',
                        'rgba(255, 99, 132, 0.8)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 205, 86, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
});
</script>
@endpush
