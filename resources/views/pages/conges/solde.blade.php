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
                                <i class="fas fa-right"></i> Retour
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Informations utilisateur -->
                        @if(isset($user) && $user->id != auth()->id())
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Vous visualisez le solde de <strong>{{ $user->prenom }} {{ $user->nom }}</strong>
                        </div>
                        @endif

                        <!-- Année courante -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h3 class="text-primary">Année {{ now()->year }}</h3>
                                <p class="text-muted">Solde de vos congés payés annuels (30 jours maximum)</p>
                            </div>
                        </div>

                        <!-- Cartes de statistiques -->
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
                                            @if($soldeCourant)
                                                {{ $soldeCourant->jours_acquis }}
                                            @else
                                                0
                                            @endif
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
                                            @if($soldeCourant)
                                                {{ $soldeCourant->jours_pris }}
                                            @else
                                                0
                                            @endif
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
                                            @if($soldeCourant)
                                                {{ $soldeCourant->jours_restants }}
                                            @else
                                                0
                                            @endif
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

                        <!-- Graphique de progression -->
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

                        <!-- Détails par année -->
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
                                                        <th class="text-center">Actions</th>
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
                                                                $taux = $solde->jours_acquis > 0 ?
                                                                        round(($solde->jours_pris / $solde->jours_acquis) * 100, 1) : 0;
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
                                                        <td class="text-center">
                                                            <button class="btn btn-sm btn-info btn-details"
                                                                    data-annee="{{ $solde->annee }}"
                                                                    data-toggle="tooltip"
                                                                    title="Voir les détails">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            @if(auth()->user()->hasRole('admin'))
                                                            <button class="btn btn-sm btn-warning btn-edit-solde"
                                                                    data-solde-id="{{ $solde->id }}"
                                                                    data-toggle="tooltip"
                                                                    title="Modifier le solde">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="7" class="text-center py-4">
                                                            <div class="empty-state">
                                                                <div class="empty-state-icon">
                                                                    <i class="fas fa-wallet"></i>
                                                                </div>
                                                                <h2>Aucun solde disponible</h2>
                                                                <p class="lead">
                                                                    Aucun solde de congés n'a été trouvé pour cet utilisateur.
                                                                </p>
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

                        <!-- Congés de l'année en cours -->
                        @if($soldeCourant)
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4><i class="fas fa-calendar-check"></i> Congés payés utilisés en {{ now()->year }}</h4>
                                    </div>
                                    <div class="card-body">
                                        @php
                                            $congesPayesAnnee = $demandesCongesPayes->where('statut', 'approuve');
                                            $totalJoursPris = $congesPayesAnnee->sum('nombre_jours');
                                        @endphp

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <h5>Récapitulatif</h5>
                                                <ul class="list-group">
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        Nombre de congés
                                                        <span class="badge badge-primary badge-pill">{{ $congesPayesAnnee->count() }}</span>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        Total jours pris
                                                        <span class="badge badge-danger badge-pill">{{ $totalJoursPris }}</span>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        Solde initial
                                                        <span class="badge badge-success badge-pill">{{ $soldeCourant->jours_acquis }}</span>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <h5>Répartition par type</h5>
                                                <canvas id="typeRepartitionChart" height="150"></canvas>
                                            </div>
                                        </div>

                                        @if($congesPayesAnnee->isNotEmpty())
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Période</th>
                                                        <th>Type</th>
                                                        <th class="text-center">Durée</th>
                                                        <th>Statut</th>
                                                        <th>Date validation</th>
                                                        <th class="text-center">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($congesPayesAnnee as $conge)
                                                    <tr>
                                                        <td>
                                                            {{ \Carbon\Carbon::parse($conge->date_debut)->format('d/m/Y') }}
                                                            au {{ \Carbon\Carbon::parse($conge->date_fin)->format('d/m/Y') }}
                                                        </td>
                                                        <td>
                                                            <span class="badge" style="background-color: {{ optional($conge->typeConge)->couleur ?? '#3B82F6' }}; color: white;">
                                                                {{ optional($conge->typeConge)->libelle ?? 'Type inconnu' }}
                                                            </span>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge badge-info">{{ $conge->nombre_jours }} jour(s)</span>
                                                        </td>
                                                        <td>
                                                            @if($conge->statut == 'approuve')
                                                                <span class="badge badge-success">Approuvé</span>
                                                            @elseif($conge->statut == 'en_attente')
                                                                <span class="badge badge-warning">En attente</span>
                                                            @else
                                                                <span class="badge badge-danger">{{ ucfirst($conge->statut) }}</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($conge->date_validation)
                                                                {{ \Carbon\Carbon::parse($conge->date_validation)->format('d/m/Y H:i') }}
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            <a href="{{ route('conges.show', $conge) }}"
                                                               class="btn btn-sm btn-info" title="Voir détails">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot>
                                                    <tr class="table-active">
                                                        <td colspan="2" class="text-right"><strong>Total :</strong></td>
                                                        <td class="text-center"><strong>{{ $totalJoursPris }} jour(s)</strong></td>
                                                        <td colspan="3"></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                        @else
                                        <div class="text-center py-4">
                                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                            <h5>Aucun congé payé utilisé cette année</h5>
                                            <p class="text-muted">Tous vos jours de congés payés sont disponibles.</p>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Prévision pour l'année prochaine -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4><i class="fas fa-calculator"></i> Projections et calculs</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h5>Prévision fin d'année</h5>
                                                @php
                                                    $regles = \App\Models\RegleConge::first();
                                                    $moisRestants = 12 - now()->month;
                                                    $joursAcquisMensuel = $regles ? $regles->jours_par_mois : 2.5;
                                                    $joursAcquisRestants = $joursAcquisMensuel * $moisRestants;
                                                    $soldePrevisionnel = ($soldeCourant->jours_restants ?? 0) + $joursAcquisRestants;
                                                    $joursReportables = $soldeCourant ? min($soldeCourant->jours_restants, $regles->limite_report ?? 10) : 0;
                                                @endphp

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
                                            </div>


                                        </div>

                                        <div class="mt-3 text-muted">
                                            <small>* Basé sur la règle de report (maximum {{ $regles->limite_report ?? 10 }} jours)</small>
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
                        @if(auth()->user()->hasRole('admin') && isset($user))
                        <button class="btn btn-warning" id="btn-adjust-solde">
                            <i class="fas fa-adjust"></i> Ajuster le solde
                        </button>
                        @endif
                        <a href="{{ route('conges.index') }}" class="btn btn-secondary">
                            <i class="fas fa-list"></i> Liste des congés
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal pour les détails d'une année -->
<div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalLabel">Détails du solde</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="detailsModalBody">
                <!-- Les détails seront chargés ici -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour ajuster le solde (admin) -->
@if(auth()->user()->hasRole('admin'))
<div class="modal fade" id="adjustModal" tabindex="-1" role="dialog" aria-labelledby="adjustModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adjustModalLabel">Ajuster le solde</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="adjustForm" action="{{ route('conges.ajuster-solde', isset($user) ? $user : auth()->user()) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Année</label>
                        <select name="annee" class="form-control" required>
                            @for($i = now()->year - 2; $i <= now()->year + 1; $i++)
                                <option value="{{ $i }}" {{ $i == now()->year ? 'selected' : '' }}>
                                    {{ $i }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jours acquis</label>
                        <input type="number" name="jours_acquis" class="form-control" step="0.5" min="0" max="50"
                               value="{{ $soldeCourant->jours_acquis ?? 25 }}" required>
                        <small class="form-text text-muted">Nombre de jours acquis pour l'année (max 50)</small>
                    </div>
                    <div class="form-group">
                        <label>Jours pris</label>
                        <input type="number" name="jours_pris" class="form-control" step="0.5" min="0"
                               value="{{ $soldeCourant->jours_pris ?? 0 }}" required>
                        <small class="form-text text-muted">Jours déjà utilisés</small>
                    </div>
                    <div class="form-group">
                        <label>Jours reportés</label>
                        <input type="number" name="jours_reportes" class="form-control" step="0.5" min="0"
                               value="{{ $soldeCourant->jours_reportes ?? 0 }}">
                        <small class="form-text text-muted">Jours reportés de l'année précédente</small>
                    </div>
                    <div class="form-group">
                        <label>Raison de l'ajustement</label>
                        <textarea name="raison" class="form-control" rows="3" placeholder="Expliquez la raison de cet ajustement..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.css">
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

    /* Styles pour l'impression */
    @media print {
        .card-header-action, .btn, .modal, .no-print {
            display: none !important;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
        }

        .table {
            border: 1px solid #dee2e6;
        }

        .table th, .table td {
            border: 1px solid #dee2e6;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Données pour les graphiques
    const soldesData = {!! $soldes->map(function($solde) {
    return [
        'annee' => $solde->annee,
        'jours_acquis' => $solde->jours_acquis,
        'jours_pris' => $solde->jours_pris,
        'jours_restants' => $solde->jours_restants,
        'jours_reportes' => $solde->jours_reportes ?? 0,
        'taux_utilisation' => $solde->jours_acquis > 0 ?
            round(($solde->jours_pris / $solde->jours_acquis) * 100) : 0
    ];
})->toJson() !!};


    const congesPayesData = {!! $demandesCongesPayes
    ->where('statut', 'approuve')
    ->map(function($conge) {
        return [
            'type' => optional($conge->typeConge)->libelle ?? 'Type inconnu',
            'couleur' => optional($conge->typeConge)->couleur ?? '#3B82F6',
            'jours' => $conge->nombre_jours
        ];
    })->toJson() !!};

    // 1. Graphique de progression du solde
    initSoldeChart();

    // 2. Graphique de répartition par type
    initRepartitionChart();

    // 3. Événements
    document.querySelectorAll('.btn-details').forEach(btn => {
        btn.addEventListener('click', showDetails);
    });

    @if(auth()->user()->hasRole('admin'))
    document.querySelectorAll('.btn-edit-solde').forEach(btn => {
        btn.addEventListener('click', editSolde);
    });

    document.getElementById('btn-adjust-solde').addEventListener('click', function() {
        $('#adjustModal').modal('show');
    });
    @endif

    // 4. Calculateur de congés
    document.getElementById('calc-date-debut').addEventListener('change', function() {
        const dateDebut = new Date(this.value);
        const dateFin = new Date(dateDebut);
        dateFin.setDate(dateFin.getDate() + 1);
        document.getElementById('calc-date-fin').valueAsDate = dateFin;
    });

    // 5. Formulaire d'ajustement (admin)
    @if(auth()->user()->hasRole('admin'))
    document.getElementById('adjustForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const joursAcquis = parseFloat(formData.get('jours_acquis'));
        const joursPris = parseFloat(formData.get('jours_pris'));

        if (joursPris > joursAcquis) {
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: 'Les jours pris ne peuvent pas dépasser les jours acquis.'
            });
            return;
        }

        Swal.fire({
            title: 'Confirmer l\'ajustement',
            text: 'Êtes-vous sûr de vouloir modifier ce solde de congés ?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Oui, modifier',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        });
    });
    @endif

    function initSoldeChart() {
    const ctx = document.getElementById('soldeChart').getContext('2d');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: soldesData.map(s => s.annee),
            datasets: [
                {
                    label: 'Jours pris',
                    data: soldesData.map(s => s.jours_pris),
                    backgroundColor: 'rgba(255, 193, 107, 0.85)',   // orange/jaune
                    borderColor: '#ffc107',
                    borderWidth: 1
                },
                {
                    label: 'Jours restants',
                    data: soldesData.map(s => s.jours_restants),
                    backgroundColor: 'rgba(40, 167, 69, 0.85)',     // vert
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
                    title: { display: true, text: 'Nombre de jours (sur 30 max)' },
                    max: 35   // petite marge visuelle
                }
            }
        }
    });
}  function initRepartitionChart() {
        const ctx = document.getElementById('typeRepartitionChart').getContext('2d');

        // Grouper par type
        const typeMap = {};
        congesPayesData.forEach(conge => {
            if (!typeMap[conge.type]) {
                typeMap[conge.type] = {
                    jours: 0,
                    couleur: conge.couleur
                };
            }
            typeMap[conge.type].jours += conge.jours;
        });

        const types = Object.keys(typeMap);
        const jours = Object.values(typeMap).map(t => t.jours);
        const couleurs = Object.values(typeMap).map(t => t.couleur);

        if (types.length === 0) {
            document.getElementById('typeRepartitionChart').parentElement.innerHTML = `
                <div class="text-center py-3">
                    <i class="fas fa-chart-pie fa-2x text-muted"></i>
                    <p class="mt-2 text-muted">Aucune donnée à afficher</p>
                </div>
            `;
            return;
        }

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: types,
                datasets: [{
                    data: jours,
                    backgroundColor: couleurs,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    }

    function showDetails(event) {
        const annee = event.target.closest('.btn-details').dataset.annee;
        const solde = soldesData.find(s => s.annee == annee);

        if (!solde) return;

        const modalBody = document.getElementById('detailsModalBody');
        modalBody.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h5>Année ${solde.annee}</h5>
                    <div class="list-group">
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <span>Jours acquis :</span>
                                <strong class="text-success">${solde.jours_acquis}</strong>
                            </div>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <span>Jours pris :</span>
                                <strong class="text-warning">${solde.jours_pris}</strong>
                            </div>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <span>Jours restants :</span>
                                <strong class="text-info">${solde.jours_restants}</strong>
                            </div>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <span>Jours reportés :</span>
                                <strong class="text-info">${solde.jours_reportes}</strong>
                            </div>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <span>Taux d'utilisation :</span>
                                <strong>${solde.taux_utilisation}%</strong>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <h5>Statistiques</h5>
                    <div class="progress mb-3" style="height: 25px;">
                        <div class="progress-bar bg-success" style="width: ${solde.jours_acquis > 0 ? (solde.jours_acquis / 30 * 100) : 0}%">
                            Acquis: ${solde.jours_acquis}/30
                        </div>
                    </div>
                    <div class="progress mb-3" style="height: 25px;">
                        <div class="progress-bar bg-warning" style="width: ${solde.taux_utilisation}%">
                            Pris: ${solde.jours_pris} jours
                        </div>
                    </div>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar bg-primary" style="width: ${solde.jours_acquis > 0 ? (solde.jours_restants / solde.jours_acquis * 100) : 0}%">
                            Restants: ${solde.jours_restants} jours
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('#detailsModal').modal('show');
    }

    function editSolde(event) {
        const soldeId = event.target.closest('.btn-edit-solde').dataset.soldeId;
        // À implémenter : formulaire d'édition
        alert('Édition du solde #' + soldeId + ' - À implémenter');
    }

    function calculerJours() {
        const dateDebut = document.getElementById('calc-date-debut').value;
        const dateFin = document.getElementById('calc-date-fin').value;

        if (!dateDebut || !dateFin) {
            Swal.fire({
                icon: 'warning',
                title: 'Dates manquantes',
                text: 'Veuillez sélectionner une date de début et une date de fin.'
            });
            return;
        }

        const start = new Date(dateDebut);
        const end = new Date(dateFin);

        if (end < start) {
            Swal.fire({
                icon: 'error',
                title: 'Dates invalides',
                text: 'La date de fin doit être postérieure à la date de début.'
            });
            return;
        }

        // Calcul des jours ouvrés (exclut weekends)
        let jours = 0;
        let current = new Date(start);

        while (current <= end) {
            const day = current.getDay();
            if (day !== 0 && day !== 6) { // Pas samedi (6) ni dimanche (0)
                jours++;
            }
            current.setDate(current.getDate() + 1);
        }

        document.getElementById('calc-nombre-jours').value = jours;

        const resultatDiv = document.getElementById('resultat-calcul');
        const resultatText = document.getElementById('resultat-text');

        resultatText.innerHTML = `
            Période : ${formatDate(start)} au ${formatDate(end)}<br>
            Nombre de jours ouvrés : <strong>${jours} jours</strong><br>
            ${jours === 0 ? 'Aucun jour ouvré dans cette période' : ''}
        `;

        resultatDiv.style.display = 'block';
    }

    function testerSolde() {
        const jours = parseInt(document.getElementById('calc-nombre-jours').value) || 0;
        const soldeRestant = {{ $soldeCourant->jours_restants ?? 0 }};

        if (jours === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Aucun jour',
                text: 'Veuillez d\'abord calculer le nombre de jours.'
            });
            return;
        }

        if (jours > soldeRestant) {
            Swal.fire({
                icon: 'error',
                title: 'Solde insuffisant',
                html: `
                    <div class="text-left">
                        <p>Demandé : <strong>${jours} jours</strong></p>
                        <p>Disponible : <strong>${soldeRestant} jours</strong></p>
                        <p class="text-danger">Manquant : <strong>${jours - soldeRestant} jours</strong></p>
                        <p>Vous pouvez :</p>
                        <ul>
                            <li>Réduire la durée de votre congé</li>
                            <li>Choisir un congé non payé</li>
                            <li>Attendre l'acquisition de nouveaux jours</li>
                        </ul>
                    </div>
                `,
                confirmButtonText: 'Compris'
            });
        } else {
            Swal.fire({
                icon: 'success',
                title: 'Solde suffisant',
                html: `
                    <div class="text-left">
                        <p>Demandé : <strong>${jours} jours</strong></p>
                        <p>Disponible : <strong>${soldeRestant} jours</strong></p>
                        <p class="text-success">Reste après congé : <strong>${soldeRestant - jours} jours</strong></p>
                        <p>Vous pouvez soumettre cette demande de congé.</p>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Créer la demande',
                cancelButtonText: 'Fermer'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "{{ route('conges.create') }}";
                }
            });
        }
    }

    function formatDate(date) {
        return date.toLocaleDateString('fr-FR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }
});
</script>
@endpush
