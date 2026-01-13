@extends('layaout')

@section('title', 'Tableau de bord - Gestion des Congés')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-tachometer-alt"></i> Tableau de bord - Congés</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">Dashboard</div>
            <div class="breadcrumb-item">Gestion des congés</div>
        </div>
    </div>

    <div class="section-body">
        <!-- Alertes urgentes -->
        @if($demandesUrgentes->isNotEmpty())
        <div class="alert alert-warning alert-dismissible show fade">
            <div class="alert-body">
                <button class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Attention !</strong> Vous avez {{ $demandesUrgentes->count() }} demande(s) urgente(s) en attente de validation depuis plus de 3 jours.
            </div>
        </div>
        @endif

        <!-- Statistiques globales -->
        <div class="row">
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Total demandes</h4>
                        </div>
                        <div class="card-body">
                            {{ $stats['total_demandes'] }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>En attente</h4>
                        </div>
                        <div class="card-body">
                            {{ $stats['en_attente'] }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Approuvées</h4>
                        </div>
                        <div class="card-body">
                            {{ $stats['approuvees'] }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-danger">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Refusées</h4>
                        </div>
                        <div class="card-body">
                            {{ $stats['refusees'] }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Première ligne : Demandes urgentes et Congés en cours -->
        <div class="row">
            <!-- Demandes urgentes -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-exclamation-circle text-warning"></i> Demandes urgentes</h4>
                        <div class="card-header-action">
                            <a href="{{ route('conges.index') }}?statut=en_attente" class="btn btn-warning btn-sm">
                                <i class="fas fa-list"></i> Voir toutes
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($demandesUrgentes->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Employé</th>
                                            <th>Type</th>
                                            <th>Dates</th>
                                            <th>Jours</th>
                                            <th>Depuis</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($demandesUrgentes as $demande)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($demande->user->photo)
                                                        <img alt="image" src="{{ asset('storage/' . $demande->user->photo) }}"
                                                             class="rounded-circle mr-2" width="30" height="30">
                                                    @else
                                                        <div class="avatar bg-primary text-white rounded-circle mr-2"
                                                             style="width: 30px; height: 30px; line-height: 30px; text-align: center;">
                                                            {{ strtoupper(substr($demande->user->prenom, 0, 1) . substr($demande->user->nom, 0, 1)) }}
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <strong>{{ $demande->user->prenom }} {{ $demande->user->nom }}</strong>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge" style="background-color: {{ optional($demande->typeConge)->couleur ?? '#3B82F6' }}; color: white;">
                                                    {{ optional($demande->typeConge)->libelle ?? 'Type inconnu' }}
                                                </span>
                                            </td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($demande->date_debut)->format('d/m') }}
                                                <i class="fas fa-arrow-right mx-1"></i>
                                                {{ \Carbon\Carbon::parse($demande->date_fin)->format('d/m') }}
                                            </td>
                                            <td>
                                                <span class="badge badge-info">{{ $demande->nombre_jours }}j</span>
                                            </td>
                                            <td>
                                                <span class="text-danger">
                                                    {{ $demande->created_at->diffForHumans() }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <form action="{{ route('conges.traiter', $demande) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <input type="hidden" name="action" value="approuve">
                                                        <button type="submit" class="btn btn-success btn-sm" title="Approuver">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-danger btn-sm refuse-btn"
                                                            data-demande-id="{{ $demande->id }}"
                                                            title="Refuser">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                    <a href="{{ route('conges.show', $demande) }}"
                                                       class="btn btn-info btn-sm" title="Voir détails">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                <h5>Aucune demande urgente</h5>
                                <p class="text-muted">Toutes les demandes sont traitées dans les délais.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Congés en cours -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-plane text-primary"></i> Congés en cours</h4>
                        <div class="card-header-action">
                            <span class="badge badge-primary">{{ $congesEnCours->count() }} personne(s) absente(s)</span>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($congesEnCours->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Employé</th>
                                            <th>Type</th>
                                            <th>Période</th>
                                            <th>Jours restants</th>
                                            <th>Retour le</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($congesEnCours as $conge)
                                        @php
                                            $dateFin = \Carbon\Carbon::parse($conge->date_fin);
                                            $joursRestants = max(0, now()->diffInDays($dateFin, false));
                                            $pourcentage = $conge->nombre_jours > 0 ?
                                                (($conge->nombre_jours - $joursRestants) / $conge->nombre_jours) * 100 : 0;
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($conge->user->photo)
                                                        <img alt="image" src="{{ asset('storage/' . $conge->user->photo) }}"
                                                             class="rounded-circle mr-2" width="30" height="30">
                                                    @else
                                                        <div class="avatar bg-primary text-white rounded-circle mr-2"
                                                             style="width: 30px; height: 30px; line-height: 30px; text-align: center;">
                                                            {{ strtoupper(substr($conge->user->prenom, 0, 1) . substr($conge->user->nom, 0, 1)) }}
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <strong>{{ $conge->user->prenom }} {{ $conge->user->nom }}</strong><br>
                                                        <small class="text-muted">{{ $conge->user->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge" style="background-color: {{ optional($conge->typeConge)->couleur ?? '#3B82F6' }}; color: white;">
                                                    {{ optional($conge->typeConge)->libelle ?? 'Type inconnu' }}
                                                </span>
                                            </td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($conge->date_debut)->format('d/m') }}
                                                <i class="fas fa-arrow-right mx-1"></i>
                                                {{ \Carbon\Carbon::parse($conge->date_fin)->format('d/m') }}
                                            </td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar
                                                        @if($pourcentage < 30) bg-success
                                                        @elseif($pourcentage < 70) bg-warning
                                                        @else bg-danger
                                                        @endif"
                                                        role="progressbar"
                                                        style="width: {{ $pourcentage }}%"
                                                        aria-valuenow="{{ $pourcentage }}"
                                                        aria-valuemin="0"
                                                        aria-valuemax="100">
                                                        {{ $joursRestants }}j
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <strong>{{ $dateFin->format('d/m/Y') }}</strong><br>
                                                <small class="text-muted">
                                                    {{ $dateFin->diffForHumans() }}
                                                </small>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <h5>Tout le monde est présent</h5>
                                <p class="text-muted">Aucun congé en cours actuellement.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Deuxième ligne : Graphiques et Statistiques -->
        <div class="row">
            <!-- Graphique des demandes par mois -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-chart-bar"></i> Évolution des demandes par mois</h4>
                        <div class="card-header-action">
                            <select id="chart-year" class="form-control form-control-sm" style="width: auto; display: inline-block;">
                                @for($i = now()->year - 2; $i <= now()->year; $i++)
                                    <option value="{{ $i }}" {{ $i == now()->year ? 'selected' : '' }}>
                                        {{ $i }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="demandesChart" height="120"></canvas>
                    </div>
                </div>
            </div>

            <!-- Statistiques par type -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-chart-pie"></i> Répartition par type</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="typeChart" height="250"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Troisième ligne : Prochains congés et Soldes critiques -->
        <div class="row">
            <!-- Prochains congés programmés -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-calendar-plus text-success"></i> Prochains congés (15 jours)</h4>
                        <div class="card-header-action">
                            <a href="{{ route('conges.calendrier') }}" class="btn btn-success btn-sm">
                                <i class="fas fa-calendar-alt"></i> Calendrier
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($prochainsConges->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Employé</th>
                                            <th>Type</th>
                                            <th>Début</th>
                                            <th>Fin</th>
                                            <th>Dans</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($prochainsConges as $conge)
                                        @php
                                            $dateDebut = \Carbon\Carbon::parse($conge->date_debut);
                                            $dans = $dateDebut->diffForHumans();
                                            $isProche = $dateDebut->diffInDays(now()) <= 3;
                                        @endphp
                                        <tr class="{{ $isProche ? 'table-warning' : '' }}">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($conge->user->photo)
                                                        <img alt="image" src="{{ asset('storage/' . $conge->user->photo) }}"
                                                             class="rounded-circle mr-2" width="30" height="30">
                                                    @else
                                                        <div class="avatar bg-primary text-white rounded-circle mr-2"
                                                             style="width: 30px; height: 30px; line-height: 30px; text-align: center;">
                                                            {{ strtoupper(substr($conge->user->prenom, 0, 1) . substr($conge->user->nom, 0, 1)) }}
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <strong>{{ $conge->user->prenom }} {{ $conge->user->nom }}</strong>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge" style="background-color: {{ optional($conge->typeConge)->couleur ?? '#3B82F6' }}; color: white;">
                                                    {{ optional($conge->typeConge)->libelle ?? 'Type inconnu' }}
                                                </span>
                                            </td>
                                            <td>
                                                {{ $dateDebut->format('d/m/Y') }}<br>
                                                <small class="text-muted">{{ $dateDebut->locale('fr')->dayName }}</small>
                                            </td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($conge->date_fin)->format('d/m/Y') }}
                                            </td>
                                            <td>
                                                @if($isProche)
                                                    <span class="badge badge-warning">{{ $dans }}</span>
                                                @else
                                                    <span class="text-muted">{{ $dans }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('conges.show', $conge) }}"
                                                   class="btn btn-sm btn-info" title="Voir détails">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <h5>Aucun congé à venir</h5>
                                <p class="text-muted">Pas de congé programmé dans les 15 prochains jours.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Soldes critiques -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-exclamation-triangle text-danger"></i> Soldes critiques</h4>
                        <div class="card-header-action">
                            <a href="{{ route('conges.index') }}" class="btn btn-danger btn-sm">
                                <i class="fas fa-wallet"></i> Gérer
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($soldesCritiques->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Employé</th>
                                            <th>Jours acquis</th>
                                            <th>Jours pris</th>
                                            <th>Jours restants</th>
                                            <th>Utilisation</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($soldesCritiques as $solde)
                                        @php
                                            $tauxUtilisation = $solde->jours_acquis > 0 ?
                                                round(($solde->jours_pris / $solde->jours_acquis) * 100, 1) : 0;
                                            $isTresCritique = $solde->jours_restants < 5;
                                        @endphp
                                        <tr class="{{ $isTresCritique ? 'table-danger' : 'table-warning' }}">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($solde->user->photo)
                                                        <img alt="image" src="{{ asset('storage/' . $solde->user->photo) }}"
                                                             class="rounded-circle mr-2" width="30" height="30">
                                                    @else
                                                        <div class="avatar bg-primary text-white rounded-circle mr-2"
                                                             style="width: 30px; height: 30px; line-height: 30px; text-align: center;">
                                                            {{ strtoupper(substr($solde->user->prenom, 0, 1) . substr($solde->user->nom, 0, 1)) }}
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <strong>{{ $solde->user->prenom }} {{ $solde->user->nom }}</strong><br>
                                                        <small class="text-muted">{{ $solde->annee }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-success">{{ $solde->jours_acquis }}</span>
                                            </td>
                                            <td>
                                                <span class="badge badge-warning">{{ $solde->jours_pris }}</span>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $isTresCritique ? 'danger' : 'warning' }}">
                                                    {{ $solde->jours_restants }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar
                                                        @if($tauxUtilisation < 50) bg-success
                                                        @elseif($tauxUtilisation < 80) bg-warning
                                                        @else bg-danger
                                                        @endif"
                                                        role="progressbar"
                                                        style="width: {{ min($tauxUtilisation, 100) }}%"
                                                        aria-valuenow="{{ $tauxUtilisation }}"
                                                        aria-valuemin="0"
                                                        aria-valuemax="100">
                                                        {{ $tauxUtilisation }}%
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="{{ route('conges.solde.user', $solde->user) }}"
                                                   class="btn btn-sm btn-info" title="Voir solde">
                                                    <i class="fas fa-wallet"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                <h5>Aucun solde critique</h5>
                                <p class="text-muted">Tous les soldes sont dans des limites acceptables.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Quatrième ligne : Actions rapides et Export -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-bolt"></i> Actions rapides</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <a href="{{ route('conges.create') }}" class="btn btn-primary btn-lg btn-block mb-3">
                                    <i class="fas fa-plus-circle fa-2x mb-2"></i><br>
                                    Nouvelle demande
                                </a>
                            </div>
                            <div class="col-md-4 text-center">
                                <a href="{{ route('conges.calendrier') }}" class="btn btn-success btn-lg btn-block mb-3">
                                    <i class="fas fa-calendar-alt fa-2x mb-2"></i><br>
                                    Voir calendrier
                                </a>
                            </div>

                            <div class="col-md-4 text-center">
                            <a href="{{ route('conges.solde') }}"
                            class="btn btn-warning btn-lg btn-block mb-4 text-center">
                                <i class="fas fa-file-export fa-2x mb-2"></i><br>
                                Mes soldes et demandes
                            </a>
                        </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal pour refus -->
<div class="modal fade" id="refuseModal" tabindex="-1" role="dialog" aria-labelledby="refuseModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="refuseModalLabel">Refuser la demande</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="refuseForm" method="POST">
                @csrf
                <input type="hidden" name="action" value="refuse">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="refuseComment">Motif du refus <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="refuseComment" name="commentaire"
                                  rows="4" placeholder="Expliquez le motif du refus..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">Confirmer le refus</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.css">
<style>
    .card-statistic-1 .card-icon {
        width: 60px;
        height: 60px;
        line-height: 60px;
        font-size: 1.5rem;
    }

    .card-statistic-1 .card-body {
        font-size: 1.5rem;
        font-weight: bold;
    }

    .avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
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

    .btn-lg {
        padding: 20px 10px;
    }

    .btn-lg i {
        display: block;
        margin-bottom: 10px;
    }

    .table td, .table th {
        vertical-align: middle;
    }

    /* Badges personnalisés */
    .badge {
        font-weight: 500;
        padding: 0.35em 0.65em;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Données pour les graphiques
    const statsData = @json($stats);
    const chartData = @json($chartData ?? []);
    const typeData = @json($typeData ?? []);

    // 1. Graphique des demandes par mois
    initDemandesChart();

    // 2. Graphique de répartition par type
    initTypeChart();

    // 3. Gestion des refus
    document.querySelectorAll('.refuse-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const demandeId = this.dataset.demandeId;
            const form = document.getElementById('refuseForm');
            form.action = `/conges/${demandeId}/traiter`;
            $('#refuseModal').modal('show');
        });
    });

    // 4. Export des données
    document.getElementById('export-btn').addEventListener('click', function() {
        Swal.fire({
            title: 'Exporter les données',
            text: 'Choisissez le format d\'export',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Excel',
            cancelButtonText: 'PDF',
            showDenyButton: true,
            denyButtonText: 'CSV'
        }).then((result) => {
            if (result.isConfirmed) {
                exportData('excel');
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                exportData('pdf');
            } else if (result.isDenied) {
                exportData('csv');
            }
        });
    });

    // 5. Filtre année pour le graphique
    document.getElementById('chart-year').addEventListener('change', function() {
        // Recharger les données pour l'année sélectionnée
        loadChartData(this.value);
    });

    // Fonctions
    function initDemandesChart() {
        const ctx = document.getElementById('demandesChart').getContext('2d');

        const labels = chartData.months || ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
        const dataEnAttente = chartData.en_attente || Array(12).fill(0);
        const dataApprouvees = chartData.approuvees || Array(12).fill(0);
        const dataRefusees = chartData.refusees || Array(12).fill(0);

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'En attente',
                        data: dataEnAttente,
                        backgroundColor: '#ffc107',
                        borderColor: '#ffc107',
                        borderWidth: 1
                    },
                    {
                        label: 'Approuvées',
                        data: dataApprouvees,
                        backgroundColor: '#28a745',
                        borderColor: '#28a745',
                        borderWidth: 1
                    },
                    {
                        label: 'Refusées',
                        data: dataRefusees,
                        backgroundColor: '#dc3545',
                        borderColor: '#dc3545',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        stacked: true,
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Nombre de demandes'
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });
    }

    function initTypeChart() {
        const ctx = document.getElementById('typeChart').getContext('2d');

        const labels = typeData.labels || ['Payés', 'Maladie', 'Maternité', 'Sans solde'];
        const data = typeData.data || [40, 25, 15, 20];
        const backgroundColors = typeData.colors || ['#3B82F6', '#EF4444', '#8B5CF6', '#6B7280'];

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: backgroundColors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    function loadChartData(year) {
        // AJAX pour charger les données de l'année sélectionnée
        fetch(`/api/conges/stats/${year}`)
            .then(response => response.json())
            .then(data => {
                // Mettre à jour les graphiques avec les nouvelles données
                console.log('Données chargées pour', year, data);
                // Ici, tu devrais recréer les graphiques avec les nouvelles données
            })
            .catch(error => console.error('Erreur:', error));
    }

    function exportData(format) {
        const year = document.getElementById('chart-year').value;
        let url = '';

        switch(format) {
            case 'excel':
                url = `/conges/export/excel?annee=${year}`;
                break;
            case 'pdf':
                url = `/conges/export/pdf?annee=${year}`;
                break;
            case 'csv':
                url = `/conges/export/csv?annee=${year}`;
                break;
        }

        // Ouvrir dans un nouvel onglet pour le téléchargement
        window.open(url, '_blank');

        Swal.fire({
            icon: 'success',
            title: 'Export lancé',
            text: `Le téléchargement du fichier ${format.toUpperCase()} va commencer.`,
            timer: 2000,
            showConfirmButton: false
        });
    }

    // Mise à jour en temps réel (optionnel)
    function startLiveUpdates() {
        // Toutes les 30 secondes, vérifier les nouvelles demandes
        setInterval(() => {
            fetch('/api/conges/stats/live')
                .then(response => response.json())
                .then(data => {
                    if (data.hasNewRequests) {
                        // Afficher une notification
                        showNotification('Nouvelle demande de congé', 'Une nouvelle demande nécessite votre attention.');
                    }
                })
                .catch(error => console.error('Erreur live update:', error));
        }, 30000); // 30 secondes
    }

    function showNotification(title, message) {
        if (Notification.permission === "granted") {
            new Notification(title, { body: message });
        } else if (Notification.permission !== "denied") {
            Notification.requestPermission().then(permission => {
                if (permission === "granted") {
                    new Notification(title, { body: message });
                }
            });
        }

        // Notification SweetAlert
        Swal.fire({
            title: title,
            text: message,
            icon: 'info',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
    }

    // Démarrer les mises à jour en temps réel si admin
    @if(auth()->user()->hasRole('admin|manager'))
    if (Notification.permission === "default") {
        Notification.requestPermission();
    }
    // startLiveUpdates(); // Décommente pour activer
    @endif
});
</script>
@endpush
