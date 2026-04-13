@extends('layaout')

@section('title', 'Détails du Personnel - ' . $user->prenom . ' ' . $user->nom)

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Détails du Personnel</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('missions.analyse') }}">Missions</a></div>
            <div class="breadcrumb-item active">{{ $user->prenom }} {{ $user->nom }}</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <!-- Colonne gauche : Profil + Charge -->
            <div class="col-lg-4 col-md-5">
                <!-- Carte Profil -->
                <div class="card card-primary">
                    <div class="card-header">
                        <h4>Profil</h4>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-4">
                            <!-- Avatar avec initiales si pas de photo -->
                            <div class="avatar avatar-xl mx-auto" style="width: 120px; height: 120px; font-size: 2.5rem;">
                                @if($user->photo && file_exists(public_path('storage/' . $user->photo)))
                                    <img src="{{ asset('storage/' . $user->photo) }}" alt="Photo de {{ $user->prenom }}" class="rounded-circle img-fluid">
                                @else
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center h-100">
                                        {{ Str::upper(substr($user->prenom, 0, 1) . substr($user->nom, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <h4 class="mb-1">{{ $user->prenom }} {{ $user->nom }}</h4>
                        <p class="text-muted mb-4">{{ $user->poste->intitule ?? 'Poste non défini' }}</p>

                        <div class="list-group list-group-flush">
                            <div class="list-group-item d-flex justify-content-between py-3">
                                <div><i class="fas fa-envelope mr-2 text-muted"></i> Email</div>
                                <div class="font-weight-medium">{{ $user->email ?? 'Non renseigné' }}</div>
                            </div>
                            <div class="list-group-item d-flex justify-content-between py-3">
                                <div><i class="fas fa-phone mr-2 text-muted"></i> Téléphone</div>
                                <div class="font-weight-medium">{{ $user->telephone ?? 'Non renseigné' }}</div>
                            </div>
                            <div class="list-group-item d-flex justify-content-between py-3">
                                <div><i class="fas fa-calendar-alt mr-2 text-muted"></i> Membre depuis</div>
                                <div class="font-weight-medium">{{ $user->created_at->format('d/m/Y') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charge de travail -->
                <div class="card">
                    <div class="card-header">
                        <h4>Charge de travail (30 derniers jours)</h4>
                    </div>
                    <div class="card-body">
                        <div class="row text-center mb-4">
                            <div class="col-6">
                                <h6 class="text-muted">Heures réelles</h6>

                                            @php
                                                $heures = floor($charge['heures_reelles']);
                                                $minutes = round(($charge['heures_reelles']- $heures) * 60);
                                            @endphp
                                <h4 class="text-dark mb-0">{{ $heures }}h {{ $minutes}} min</h4>
                            </div>
                            <div class="col-6">
                                <h6 class="text-muted">Heures théoriques</h6>
                                   @php
                                                $heures = floor($charge['heures_theoriques']);
                                                $minutes = round(($charge['heures_theoriques']- $heures) * 60);
                                    @endphp
                                <h4 class="text-info mb-0">{{ $heures }}h {{ $minutes}} min</h4>
                            </div>
                        </div>

                         {{ $heures }}h {{ $minutes }}min
                        @php
                            $pourcentage = floor($charge['heures_theoriques'] ) > 0
                                ? min(100, ($charge['heures_reelles'] / $charge['heures_theoriques']) * 100)
                                : 0;
                            $color = $pourcentage > 100 ? 'danger' : ($pourcentage >= 90 ? 'warning' : 'success');
                        @endphp

                        <div class="progress mb-3" style="height: 12px;">
                            <div class="progress-bar bg-{{ $color }}"
                                 style="width: {{ $pourcentage }}%"
                                 role="progressbar">
                            </div>
                        </div>

                        <div class="text-center">
                            <h5 class="mb-1">{{ round($pourcentage, 1) }}%</h5>
                            <small class="text-{{ $charge['ecart'] >= 0 ? 'danger' : 'success' }}">
                                Écart : {{ $charge['ecart'] >= 0 ? '+' : '' }}{{ $charge['ecart'] }}h
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Colonne droite : Missions + Graphique -->
            <div class="col-lg-8 col-md-7">
                <!-- Missions en cours -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Missions en cours</h4>
                        <span class="badge badge-lg badge-info">{{ count($missions) }} mission{{ count($missions) > 1 ? 's' : '' }}</span>
                    </div>
                    <div class="card-body p-0">
                        @if(count($missions) > 0)
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Mission</th>
                                            <th>Client</th>
                                            <th>Temps passé</th>
                                            <th>Dernière activité</th>
                                            <th>Statut</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($missions as $mission)
                                            @php
                                                $dossier = $mission['dossier'];
                                                $derniereActivite = \Carbon\Carbon::parse($mission['derniere_activite']);
                                                $joursDepuis = $derniereActivite->diffInDays(now());
                                                $statutCouleur = $joursDepuis > 30 ? 'danger' : ($joursDepuis > 7 ? 'warning' : 'success');
                                            @endphp
                                            <tr>
                                                <td>
                                                    <strong>{{ $dossier->nom }}</strong><br>
                                                    <small class="text-muted">Ref: {{ $dossier->reference }}</small>
                                                </td>
                                                <td>{{ $dossier->client->nom ?? 'N/A' }}</td>
                                                 @php
                                                $heures = floor(num: $mission['total_heures']);
                                                $minutes = round(($mission['total_heures'] - $heures) * 60);
                                               @endphp


                                                <td><span class="badge badge-primary badge-pill">{{ $heures }}h {{ $minutes }}min</span></td>
                                                <td>
                                                    {{ $derniereActivite->format('d/m/Y') }}<br>
                                                    <small class="text-{{ $statutCouleur }}">{{ $joursDepuis }} jour{{ $joursDepuis > 1 ? 's' : '' }}</small>
                                                </td>
                                                <td>{!! $dossier->statut_badge !!}</td>
                                                <td class="text-center">
                                                    <a href="{{ route('dossiers.show', $dossier->id) }}" class="btn btn-sm btn-outline-info" title="Voir dossier">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('missions.utilisateur.dossier', ['user' => $user->id, 'dossier' => $dossier->id]) }}"
                                                       class="btn btn-sm btn-outline-primary" title="Statistiques">
                                                        <i class="fas fa-chart-bar"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Aucune mission active pour cet utilisateur.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Graphique de répartition -->
                @if(count($missions) > 0)
                <div class="card mt-4">
                    <div class="card-header">
                        <h4>Répartition du temps par mission</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="missionChart" height="280"></canvas>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Activités récentes -->
        @if($timeEntries->count() > 0)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Dernières activités (10 plus récentes)</h4>
                    </div>
                    <div class="card-body">
                        <div class="activities">
                            @foreach($timeEntries->take(10) as $entry)
                            <div class="activity">
                                <div class="activity-icon bg-primary text-white">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="activity-detail">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <strong class="text-dark">
                                                {{ $entry->dailyEntry->jour->format('d/m/Y') }}
                                            </strong>
                                            <span class="text-muted ml-2">({{ $entry->plage }})</span>
                                             @php
                                                $heures = floor($entry->heures_reelles);
                                                $minutes = round(($entry->heures_reelles - $heures) * 60);
                                            @endphp


                                            <span class="ml-3 text-muted"> {{ $heures }}h {{ $minutes }}min</span>
                                        </div>
                                        <div class="dropdown">
                                            <a href="#" data-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></a>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a href="{{ route('dossiers.show', $entry->dossier_id) }}" class="dropdown-item">
                                                    <i class="fas fa-eye mr-2"></i>Voir le dossier
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="mb-0">
                                        <strong>{{ $entry->dossier->nom }}</strong>
                                        <br><small class="text-muted">{{ $entry->travaux }}</small>
                                    </p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    @if(count($missions) > 0)
    const ctx = document.getElementById('missionChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut', // Plus moderne que pie
        data: {
            labels: [
                @foreach($missions as $mission)
                    "{{ Str::limit($mission['dossier']->nom, 25) }}",
                @endforeach
            ],
            datasets: [{
                data: [
                    @foreach($missions as $mission)
                        {{ $mission['total_heures'] }},
                    @endforeach
                ],
                backgroundColor: [
                    '#4361ee', '#3f37c9', '#4895ef', '#4cc9f0',
                    '#7209b7', '#b5179e', '#f72585', '#f94144',
                    '#f3722c', '#f8961e', '#f9c74f', '#90be6d'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        padding: 20,
                        font: { size: 12 }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: ${value}h (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
    @endif
});
</script>
@endpush

@push('styles')
<style>
    .avatar img, .avatar > div {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .activity {
        display: flex;
        margin-bottom: 1.8rem;
        align-items: flex-start;
    }
    .activity-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-right: 1rem;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .activity-detail {
        flex: 1;
        min-width: 0;
    }
    .activity-detail p {
        margin-bottom: 0;
        line-height: 1.4;
    }
    .badge-lg {
        font-size: 1rem;
        padding: 0.5em 1em;
    }
</style>
@endpush
