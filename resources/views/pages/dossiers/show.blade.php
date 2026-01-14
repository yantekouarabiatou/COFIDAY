@php
use App\Helpers\UserHelper;
@endphp

@extends('layaout')

@section('title', 'Dossier - ' . $dossier->nom)

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-folder-open"></i> Dossier: {{ $dossier->nom }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('dossiers.index') }}">Dossiers</a></div>
            <div class="breadcrumb-item active">{{ $dossier->reference }}</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Informations du Dossier</h4>
                        <div class="card-header-action">
                            <a href="{{ route('dossiers.edit', $dossier) }}" class="btn btn-icon icon-left btn-warning">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                            <a href="{{ route('dossiers.index') }}" class="btn btn-icon icon-left btn-primary">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                            <button type="button" class="btn btn-icon icon-left btn-danger" data-toggle="modal" data-target="#deleteModal">
                                <i class="fas fa-trash"></i> Supprimer
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <!-- Colonne de gauche -->
                            <div class="col-md-8">
                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <h2 class="mb-0">{{ $dossier->nom }}</h2>
                                                <div class="mt-2">
                                                    {!! $dossier->type_dossier_badge !!}
                                                    {!! $dossier->statut_badge !!}
                                                    @if($dossier->en_retard)
                                                        <span class="badge badge-danger">
                                                            <i class="fas fa-exclamation-triangle"></i> En retard
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <h4 class="text-primary">{{ $dossier->reference }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Informations client -->
                                <div class="card card-primary mb-4">
                                    <div class="card-header">
                                        <h4><i class="fas fa-user"></i> Informations Client</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Client:</strong>
                                                    <a href="{{ route('clients.show', $dossier->client) }}">
                                                        {{ $dossier->client->nom }}
                                                    </a>
                                                </p>
                                                @if($dossier->client->contact_principal)
                                                <p><strong>Contact:</strong> {{ $dossier->client->contact_principal }}</p>
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                @if($dossier->client->telephone)
                                                <p><strong>Téléphone:</strong>
                                                    <a href="tel:{{ $dossier->client->telephone }}">{{ $dossier->client->telephone }}</a>
                                                </p>
                                                @endif
                                                @if($dossier->client->email)
                                                <p><strong>Email:</strong>
                                                    <a href="mailto:{{ $dossier->client->email }}">{{ $dossier->client->email }}</a>
                                                </p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Dates et durée -->
                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        <div class="info-box">
                                            <div class="info-box-icon bg-info">
                                                <i class="fas fa-calendar-plus"></i>
                                            </div>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Date d'ouverture</span>
                                                <span class="info-box-number">{{ $dossier->date_ouverture->format('d/m/Y') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-box">
                                            <div class="info-box-icon bg-warning">
                                                <i class="fas fa-calendar-check"></i>
                                            </div>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Clôture prévue</span>
                                                <span class="info-box-number">
                                                    @if($dossier->date_cloture_prevue)
                                                        {{ $dossier->date_cloture_prevue->format('d/m/Y') }}
                                                    @else
                                                        <span class="text-muted">Non définie</span>
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-box">
                                            <div class="info-box-icon bg-success">
                                                <i class="fas fa-calendar-alt"></i>
                                            </div>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Durée</span>
                                                <span class="info-box-number">{{ $dossier->duree }} jours</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Budget et frais -->
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="card card-success">
                                            <div class="card-header">
                                                <h4><i class="fas fa-money-bill-wave"></i> Budget</h4>
                                            </div>
                                            <div class="card-body">
                                                <h3 class="text-success">{{ $dossier->budget_formate }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card card-info">
                                            <div class="card-header">
                                                <h4><i class="fas fa-receipt"></i> Frais de dossier</h4>
                                            </div>
                                            <div class="card-body">
                                                <h3 class="text-info">{{ $dossier->frais_dossier_formate }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Budget et frais -->
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="card card-success">
                                            <div class="card-header">
                                                <h4><i class="fas fa-briefcase"></i> Heures théoriques sans weekend</h4>
                                            </div>
                                            <div class="card-body">
                                                <h3 class="text-success">{{ UserHelper::hoursToHoursMinutes($dossier->heure_theorique_sans_weekend) }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card card-info">
                                            <div class="card-header">
                                                <h4><i class="fas fa-calendar-week"></i> Heures théoriques avec weekend</h4>
                                            </div>
                                            <div class="card-body">
                                                <h3 class="text-info">{{ UserHelper::hoursToHoursMinutes($dossier->heure_theorique_avec_weekend) }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Description -->
                                @if($dossier->description)
                                <div class="card card-secondary mb-4">
                                    <div class="card-header">
                                        <h4><i class="fas fa-align-left"></i> Description</h4>
                                    </div>
                                    <div class="card-body">
                                        <p>{{ $dossier->description }}</p>
                                    </div>
                                </div>
                                @endif

                                <!-- Notes -->
                                @if($dossier->notes)
                                <div class="card card-warning mb-4">
                                    <div class="card-header">
                                        <h4><i class="fas fa-sticky-note"></i> Notes</h4>
                                    </div>
                                    <div class="card-body">
                                        <p>{{ $dossier->notes }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Colonne de droite -->
                            <div class="col-md-4">
                                <!-- Document attaché -->
                                @if($dossier->document)
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h4><i class="fas fa-file"></i> Document</h4>
                                    </div>
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="fas fa-file-pdf fa-3x text-danger"></i>
                                        </div>
                                        <p class="mb-2">{{ $dossier->document_name }}</p>

                                @endif     <a href="{{ $dossier->document_url }}"
                                           class="btn btn-primary btn-sm"
                                           target="_blank"
                                           download>
                                            <i class="fas fa-download"></i> Télécharger
                                        </a>
                                    </div>
                                </div>
                                @endif

                                <!-- Informations techniques -->
                                <div class="card">
                                    <div class="card-header">
                                        <h4><i class="fas fa-info-circle"></i> Informations Techniques</h4>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled">
                                            <li class="mb-2">
                                                <i class="fas fa-calendar-alt mr-2"></i>
                                                <strong>Créé le:</strong>
                                                {{ $dossier->created_at->format('d/m/Y H:i') }}
                                            </li>
                                            <li class="mb-2">
                                                <i class="fas fa-sync mr-2"></i>
                                                <strong>Modifié le:</strong>
                                                {{ $dossier->updated_at->format('d/m/Y H:i') }}
                                            </li>
                                            <li class="mb-2">
                                                <i class="fas fa-hashtag mr-2"></i>
                                                <strong>ID:</strong> {{ $dossier->id }}
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Actions rapides -->
                                <div class="card mt-4">
                                    <div class="card-header">
                                        <h4><i class="fas fa-bolt"></i> Actions Rapides</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="list-group">
                                            <a href="{{ route('dossiers.edit', $dossier) }}" class="list-group-item list-group-item-action">
                                                <i class="fas fa-edit mr-2"></i> Modifier le dossier
                                            </a>

                                            <a href="{{ route('clients.show', $dossier->client) }}" class="list-group-item list-group-item-action">
                                                <i class="fas fa-external-link-alt mr-2"></i> Voir le client
                                            </a>
                                            <a href="{{ route('dossiers.create') }}" class="list-group-item list-group-item-action">
                                                <i class="fas fa-plus mr-2"></i> Créer un autre dossier
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tableau détaillé des personnels -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-table"></i> Personnels ayant travaillé sur ce dossier</h4>
                        <div class="card-header-action">
                            <div class="input-group" style="width: 250px;">
                                <input type="text" class="form-control" id="searchPersonnel" placeholder="Rechercher personnel...">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" id="btnSearch"><i class="fas fa-search"></i></button>
                                </div>
                            </div>
                            <a href="{{ route('missions.analyse') }}" class="btn btn-info ml-2">
                                <i class="fas fa-chart-bar"></i> Analyse complète
                            </a>
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
                                        <th class="text-center">Total Heures</th>
                                        <th class="text-center">Nb Interventions</th>
                                        <th class="text-center">Charge</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        // Récupérer tous les personnels ayant travaillé sur ce dossier
                                        $personnels = $dossier->personnelsAvecTemps();
                                    @endphp

                                    @forelse($personnels as $personnelData)
                                        @php
                                            $personnel = $personnelData->user;
                                            $chargeJour = App\Models\TimeEntry::where('user_id', $personnel->id)
                                                ->where('dossier_id', $dossier->id)
                                                ->whereHas('dailyEntry', function($query) {
                                                    $query->whereDate('jour', today());
                                                })
                                                ->sum('heures_reelles');

                                            $chargeTotal = $personnelData->total_heures;
                                            $nbInterventions = $personnelData->nb_interventions;

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
                                                        @if($personnel->photo && Storage::exists($personnel->photo))
                                                            <img src="{{ asset('storage/'.$personnel->photo) }}" alt="Avatar" class="rounded-circle">
                                                        @else
                                                            <div class="avatar-initial rounded-circle bg-{{ $statut }}">
                                                                {{ strtoupper(substr($personnel->prenom, 0, 1) . substr($personnel->nom, 0, 1)) }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <div class="font-weight-bold">{{ $personnel->nom }}</div>
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
                                                    {{ number_format($chargeJour, 2) }}h
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="font-weight-bold">
                                                    {{ number_format($chargeTotal, 2) }}h
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
                                                <a href="{{ route('missions.utilisateur.dossier', ['user' => $personnel->id, 'dossier' => $dossier->id]) }}"
                                                   class="btn btn-sm btn-primary" title="Voir détails">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <div class="empty-state" data-height="200">
                                                    <div class="empty-state-icon">
                                                        <i class="fas fa-user-friends"></i>
                                                    </div>
                                                    <h2>Aucun personnel n'a encore travaillé sur ce dossier</h2>
                                                    <p class="lead">
                                                        Aucune entrée de temps n'a été enregistrée pour ce dossier.
                                                    </p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if($personnels->count() > 0)
                                <tfoot>
                                    <tr class="bg-light">
                                        <td colspan="2"><strong>Totaux</strong></td>
                                        <td class="text-center">
                                            <strong>{{ number_format($personnels->sum(fn($p) => App\Models\TimeEntry::where('user_id', $p->user->id)
                                                ->where('dossier_id', $dossier->id)
                                                ->whereHas('dailyEntry', function($query) {
                                                    $query->whereDate('jour', today());
                                                })
                                                ->sum('heures_reelles')), 2) }}h</strong>
                                        </td>
                                        <td class="text-center">
                                            <strong>{{ number_format($personnels->sum('total_heures'), 2) }}h</strong>
                                        </td>
                                        <td class="text-center">
                                            <strong>{{ $personnels->sum('nb_interventions') }}</strong>
                                        </td>
                                        <td colspan="2">
                                            <small class="text-muted">{{ $personnels->count() }} personnel(s) impliqué(s)</small>
                                        </td>
                                    </tr>
                                </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Temps passé sur le dossier -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-clock"></i> Historique des temps passés</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Personnel</th>
                                        <th>Heure début</th>
                                        <th>Heure fin</th>
                                        <th>Durée</th>
                                        <th>Travaux effectués</th>
                                        <th>Rendus</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($dossier->timeEntries()->with('user')->latest()->take(20)->get() as $entry)
                                    <tr>
                                        <td>{{ $entry->dailyEntry->jour->format('d/m/Y') }}</td>
                                        <td>{{ $entry->user->nom }}</td>
                                        <td>{{ $entry->heure_debut->format('H:i') }}</td>
                                        <td>{{ $entry->heure_fin->format('H:i') }}</td>
                                        <td>{{ number_format($entry->heures_reelles, 2) }}h</td>
                                        <td>
                                            <span title="{{ $entry->travaux }}">{{ Str::limit($entry->travaux, 50) }}</span>
                                        </td>
                                        <td>
                                            <span title="{{ $entry->rendu }}">{{ Str::limit($entry->rendu, 50) }}</span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            Aucune entrée de temps enregistrée pour ce dossier
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($dossier->timeEntries()->count() > 20)
                        <div class="text-center mt-3">
                            <a href="#" class="btn btn-outline-primary">
                                Voir tout l'historique ({{ $dossier->timeEntries()->count() }} entrées)
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal de suppression -->
<div class="modal fade" tabindex="-1" role="dialog" id="deleteModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmation de suppression</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Supprimer le dossier <strong>{{ $dossier->nom }}</strong> ?</p>
                <p class="text-danger">Cette action supprimera également tous les documents associés.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <form action="{{ route('dossiers.destroy', $dossier) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Détails Personnel -->
<div class="modal fade" tabindex="-1" role="dialog" id="detailModal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Détails du personnel</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Le contenu sera chargé via AJAX -->
                <div id="modalContent" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Chargement...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .info-box {
        min-height: 90px;
    }
    .info-box-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 70px;
        font-size: 30px;
    }
    .list-group-item {
        border-left: none;
        border-right: none;
    }
    .list-group-item:first-child {
        border-top: none;
    }
    .avatar-initial {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 12px;
    }
    .empty-state {
        height: 200px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    .empty-state-icon {
        font-size: 48px;
        color: #ddd;
        margin-bottom: 20px;
    }
    .progress {
        border-radius: 10px;
    }
    .progress-bar {
        border-radius: 10px;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Recherche dans le tableau des personnels
        $('#btnSearch').on('click', function() {
            const searchTerm = $('#searchPersonnel').val().toLowerCase();

            $('.personnel-row').each(function() {
                const rowText = $(this).text().toLowerCase();
                if (rowText.indexOf(searchTerm) > -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        $('#searchPersonnel').on('keyup', function(e) {
            if (e.key === 'Enter') {
                $('#btnSearch').click();
            }
        });

        // Modal de détails personnel
        $('button[data-target="#detailModal"]').on('click', function() {
            const personnelId = $(this).data('personnel');
            const dossierId = {{ $dossier->id }};

            $('#modalContent').html(`
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Chargement...</span>
                </div>
            `);

            $.ajax({
                url: '/api/personnel-details',
                method: 'GET',
                data: {
                    personnel_id: personnelId,
                    dossier_id: dossierId
                },
                success: function(response) {
                    $('#modalContent').html(response.html);
                },
                error: function() {
                    $('#modalContent').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            Impossible de charger les détails du personnel.
                        </div>
                    `);
                }
            });
        });

        // Trier par charge
        $('#sortCharge').on('change', function() {
            const rows = $('.personnel-row').get();

            rows.sort(function(a, b) {
                const chargeA = parseFloat($(a).data('charge'));
                const chargeB = parseFloat($(b).data('charge'));

                if ($(this).val() === 'desc') {
                    return chargeB - chargeA;
                } else {
                    return chargeA - chargeB;
                }
            }.bind(this));

            $.each(rows, function(index, row) {
                $('#personnelTable tbody').append(row);
            });
        });
    });
</script>
@endpush
