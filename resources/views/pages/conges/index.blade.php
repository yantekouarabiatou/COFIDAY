@extends('layaout')

@section('title', 'Gestion des Congés')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-calendar-check"></i> Gestion des Congés</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">
                <a href="{{ route('dashboard') }}">Dashboard</a>
            </div>
            <div class="breadcrumb-item">Congés</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">

                    <!-- Header -->
                    <div class="card-header">
                        <h4>Liste des demandes de congés</h4>
                        <div class="card-header-action">
                            <a href="{{ route('conges.create') }}" class="btn btn-primary btn-icon icon-left">
                                <i class="fas fa-plus"></i> Nouvelle demande
                            </a>
                            @role('admin|manager')
                            <a href="{{ route('conges.dashboard') }}" class="btn btn-info btn-icon icon-left ml-2">
                                <i class="fas fa-tachometer-alt"></i> Tableau de bord
                            </a>
                            @endrole
                            <a href="{{ route('conges.solde') }}" class="btn btn-warning btn-icon icon-left ml-2">
                                <i class="fas fa-chart-pie"></i> Mon solde
                            </a>

                            <a href="{{ route('conges.export.excel') }}?annee={{ date('Y') }}"
                            class="btn btn-success" title="Tous les congés">
                            <i class="fas fa-file-excel"></i> Tous
                        </a>

                        <!-- Export mes congés seulement -->
                        <a href="{{ route('conges.export.excel') }}?annee={{ date('Y') }}&user_id={{ auth()->id() }}"
                        class="btn btn-info" title="Mes congés seulement">
                            <i class="fas fa-user"></i> Mes congés
                        </a>
                        </div>

                </div>

                    <!-- Body -->
                    <div class="card-body">
                        <!-- Alertes -->
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible show fade">
                                <div class="alert-body">
                                    <button class="close" data-dismiss="alert">
                                        <span>&times;</span>
                                    </button>
                                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                                </div>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible show fade">
                                <div class="alert-body">
                                    <button class="close" data-dismiss="alert">
                                        <span>&times;</span>
                                    </button>
                                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                                </div>
                            </div>
                        @endif

                        <!-- Filtres -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Type de congé</label>
                                    <select id="type-filter" class="form-control select2">
                                        <option value="">Tous les types</option>
                                        @foreach($typesConges ?? [] as $type)
                                            <option value="{{ $type->id }}">{{ $type->libelle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Statut</label>
                                    <select id="status-filter" class="form-control select2">
                                        <option value="">Tous les statuts</option>
                                        <option value="en_attente">En attente</option>
                                        <option value="approuve">Approuvé</option>
                                        <option value="refuse">Refusé</option>
                                        <option value="annule">Annulé</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Recherche</label>
                                    <input type="text" id="search-input" class="form-control"
                                           placeholder="Utilisateur, motif...">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Date début (à partir de)</label>
                                    <input type="date" id="start-date" class="form-control">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Date fin (jusqu'à)</label>
                                    <input type="date" id="end-date" class="form-control">
                                </div>
                            </div>

                            <div class="col-md-3 text-right" style="padding-top: 30px; margin-left: auto;">
                                <button type="button" id="reset-filters"
                                        class="btn btn-outline-secondary btn-icon icon-left">
                                    <i class="fas fa-redo"></i> Réinitialiser
                                </button>
                            </div>
                        </div>

                        <!-- Tableau -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="conges-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        @role('admin|manager')
                                        <th>Utilisateur</th>
                                        @endrole
                                        <th>Type</th>
                                        <th>Dates</th>
                                        <th>Durée</th>
                                        <th>Statut</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($demandes as $demande)
                                        <tr
                                            data-type-id="{{ $demande->type_conge_id }}"
                                            data-status="{{ $demande->statut }}"
                                            data-user="{{ strtolower(($demande->user->prenom ?? '') . ' ' . ($demande->user->nom ?? '')) }}"
                                            data-start="{{ $demande->date_debut->format('Y-m-d') }}"
                                            data-end="{{ $demande->date_fin->format('Y-m-d') }}">
                                            <td>{{ $loop->iteration }}</td>

                                            @role('admin|manager')
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="mr-2">
                                                        @if($demande->user->photo)
                                                            <img src="{{ asset('storage/' . $demande->user->photo) }}"
                                                                 class="rounded-circle" width="30" height="30" alt="Photo">
                                                        @else
                                                            <div class="avatar bg-primary text-white rounded-circle"
                                                                 style="width: 30px; height: 30px; line-height: 30px; text-align: center;">
                                                                {{ strtoupper(substr($demande->user->prenom, 0, 1) . substr($demande->user->nom, 0, 1)) }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <strong>{{ $demande->user->prenom }} {{ $demande->user->nom }}</strong><br>
                                                        <small class="text-muted">{{ $demande->user->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            @endrole

                                            <td>
                                                <span class="badge badge-{{ $demande->typeConge->est_paye ? 'success' : 'warning' }}">
                                                    {{ $demande->typeConge->libelle }}
                                                </span>
                                                @if($demande->typeConge->est_paye)
                                                    <br><small class="text-muted">Payé</small>
                                                @else
                                                    <br><small class="text-muted">Non payé</small>
                                                @endif
                                            </td>

                                            <td>
                                                <strong>Début :</strong> {{ $demande->date_debut->format('d/m/Y') }}<br>
                                                <strong>Fin :</strong> {{ $demande->date_fin->format('d/m/Y') }}
                                            </td>

                                            <td class="text-center">
                                                <span class="badge badge-info" style="font-size: 1em;">
                                                    {{ $demande->nombre_jours }} jour(s)
                                                </span>
                                            </td>

                                            <td>
                                                @switch($demande->statut)
                                                    @case('en_attente')
                                                        <span class="badge badge-warning">
                                                            <i class="fas fa-clock"></i> En attente
                                                        </span>
                                                        @if($demande->created_at->diffInDays(now()) > 3)
                                                            <br><small class="text-danger">En attente depuis {{ $demande->created_at->diffInDays(now()) }} jours</small>
                                                        @endif
                                                        @break
                                                    @case('approuve')
                                                        <span class="badge badge-success">
                                                            <i class="fas fa-check-circle"></i> Approuvé
                                                        </span>
                                                        @if($demande->validePar)
                                                            <br><small class="text-muted">Par {{ $demande->validePar->name }}</small>
                                                        @endif
                                                        @break
                                                    @case('refuse')
                                                        <span class="badge badge-danger">
                                                            <i class="fas fa-times-circle"></i> Refusé
                                                        </span>
                                                        @if($demande->validePar)
                                                            <br><small class="text-muted">Par {{ $demande->validePar->name }}</small>
                                                        @endif
                                                        @break
                                                    @case('annule')
                                                        <span class="badge badge-secondary">
                                                            <i class="fas fa-ban"></i> Annulé
                                                        </span>
                                                        @break
                                                @endswitch
                                            </td>

                                            <td class="text-center">
                                                <div class="btn-group" role="group">

                                                    <!-- Voir -->
                                                    <a href="{{ route('conges.show', $demande) }}"
                                                    class="btn btn-info btn-sm" title="Voir détails">
                                                        <i class="fas fa-eye"></i>
                                                    </a>

                                                    <!-- Modifier -->
                                                    @if($demande->statut === 'en_attente' &&
                                                        (auth()->user()->hasRole('admin') || auth()->id() === $demande->user_id))
                                                        <a href="{{ route('conges.edit', $demande) }}"
                                                        class="btn btn-primary btn-sm" title="Modifier">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endif

                                                    <!-- Actions admin/manager -->
                                                    @role('admin|manager')
                                                        @if($demande->statut === 'en_attente' && $demande->superieur_hierarchique_id == auth()->id() && auth()->id() !== $demande->user_id)

                                                            <!-- Approuver -->
                                                            <form action="{{ route('conges.traiter', $demande) }}"
                                                                method="POST" class="d-inline approve-form">
                                                                @csrf
                                                                <input type="hidden" name="action" value="approuve">
                                                                <button type="button"
                                                                        class="btn btn-success btn-sm approve-btn"
                                                                        title="Approuver">
                                                                    <i class="fas fa-check"></i>
                                                                </button>
                                                            </form>

                                                            <!-- Refuser -->
                                                            <form action="{{ route('conges.traiter', $demande) }}"
                                                                method="POST" class="d-inline refuse-form">
                                                                @csrf
                                                                <input type="hidden" name="action" value="refuse">
                                                                <button type="button"
                                                                        class="btn btn-danger btn-sm refuse-btn"
                                                                        title="Refuser">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            </form>

                                                        @endif
                                                    @endrole

                                                    <!-- Annuler -->
                                                    @if($demande->statut === 'en_attente' &&
                                                        (auth()->user()->hasRole('admin') || auth()->id() === $demande->user_id))
                                                        <form action="{{ route('conges.annuler', $demande) }}"
                                                            method="POST" class="d-inline cancel-form">
                                                            @csrf
                                                            <button type="button"
                                                                    class="btn btn-warning btn-sm cancel-btn"
                                                                    title="Annuler">
                                                                <i class="fas fa-ban"></i>
                                                            </button>
                                                        </form>
                                                    @endif

                                                    <!-- Supprimer -->
                                                    @if(
                                                        $demande->statut !== 'approuve' &&
                                                        (
                                                            auth()->user()->hasRole('admin') ||
                                                            (
                                                                auth()->id() === $demande->user_id &&
                                                                in_array($demande->statut, ['en_attente', 'annule'])
                                                            )
                                                        )
                                                    )
                                                        <form action="{{ route('conges.destroy', $demande) }}"
                                                            method="POST" class="d-inline delete-form">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="button"
                                                                    class="btn btn-danger btn-sm delete-btn"
                                                                    title="Supprimer">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif

                                                </div>
                                            </td>

                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ auth()->user()->hasRole('admin') ? '8' : '7' }}"
                                                class="text-center py-4">
                                                <div class="empty-state">
                                                    <div class="empty-state-icon">
                                                        <i class="fas fa-calendar-times"></i>
                                                    </div>
                                                    <h2>Aucune demande de congé</h2>
                                                    <p class="lead">
                                                        Vous n'avez pas encore de demande de congé.
                                                    </p>
                                                    <a href="{{ route('conges.create') }}"
                                                       class="btn btn-primary mt-4">
                                                        <i class="fas fa-plus"></i> Créer une demande
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($demandes->hasPages())
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Affichage de {{ $demandes->firstItem() }} à {{ $demandes->lastItem() }}
                                sur {{ $demandes->total() }} demandes
                            </div>
                            <div>
                                {{ $demandes->links() }}
                            </div>
                        </div>
                        @endif

                        <!-- Statistiques -->
                        <div class="row mt-4">
                            <div class="col-md-3">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-primary">
                                        <i class="fas fa-calendar"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Total demandes</h4>
                                        </div>
                                        <div class="card-body">
                                            {{ $totalDemandes ?? $demandes->count() }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-warning">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>En attente</h4>
                                        </div>
                                        <div class="card-body">
                                            {{ $enAttente ?? $demandes->where('statut', 'en_attente')->count() }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-success">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Approuvés</h4>
                                        </div>
                                        <div class="card-body">
                                            {{ $approuves ?? $demandes->where('statut', 'approuve')->count() }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-danger">
                                        <i class="fas fa-times-circle"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Refusés</h4>
                                        </div>
                                        <div class="card-body">
                                            {{ $refuses ?? $demandes->where('statut', 'refuse')->count() }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/bundles/select2/dist/css/select2.min.css') }}">
<style>
    .badge {
        font-size: 0.85em;
        font-weight: 500;
        padding: 0.35em 0.65em;
    }
    .btn-group .btn-sm {
        margin: 0 2px;
        border-radius: 4px;
    }
    .avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }
    .empty-state {
        text-align: center;
        padding: 40px 20px;
    }
    .empty-state-icon {
        font-size: 4rem;
        color: #6c757d;
        margin-bottom: 20px;
    }
    .empty-state h2 {
        font-size: 1.5rem;
        margin-bottom: 10px;
    }
    .empty-state p {
        color: #6c757d;
        margin-bottom: 20px;
    }
    .pagination {
        margin-bottom: 0;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('assets/bundles/select2/dist/js/select2.full.min.js') }}"></script>
<script>
$(document).ready(function() {
    // Initialiser Select2
    $('.select2').select2({
        placeholder: "Sélectionner...",
        allowClear: true
    });

    /* ==========================
        CONFIRMATION SUPPRESSION
    =========================== */
    $('.delete-btn').on('click', function(e) {
        e.preventDefault();
        const form = $(this).closest('form');

        Swal.fire({
            title: 'Confirmer la suppression',
            text: 'Êtes-vous sûr de vouloir supprimer cette demande ? Cette action est irréversible.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    /* ==========================
        ANNULATION DE DEMANDE
    =========================== */
    $('.cancel-btn').on('click', function(e) {
        e.preventDefault();
        const form = $(this).closest('form');

        Swal.fire({
            title: 'Confirmer l\'annulation',
            text: 'Êtes-vous sûr de vouloir annuler cette demande ?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#f39c12',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Oui, annuler',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    /* ==========================
        APPROBATION DE DEMANDE
    =========================== */
    $('.approve-btn').on('click', function(e) {
        e.preventDefault();
        const form = $(this).closest('form');

        Swal.fire({
            title: 'Confirmer l\'approbation',
            text: 'Êtes-vous sûr de vouloir approuver cette demande de congé ?',
            icon: 'question',
            input: 'textarea',
            inputLabel: 'Commentaire (optionnel)',
            inputPlaceholder: 'Ajouter un commentaire...',
            inputAttributes: {
                maxlength: 500
            },
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Oui, approuver',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                if (result.value) {
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'commentaire',
                        value: result.value
                    }).appendTo(form);
                }
                form.submit();
            }
        });
    });

    /* ==========================
        REFUS DE DEMANDE
    =========================== */
    $('.refuse-btn').on('click', function(e) {
        e.preventDefault();
        const form = $(this).closest('form');

        Swal.fire({
            title: 'Confirmer le refus',
            text: 'Êtes-vous sûr de vouloir refuser cette demande de congé ?',
            icon: 'question',
            input: 'textarea',
            inputLabel: 'Motif du refus (requis)',
            inputPlaceholder: 'Expliquez le motif du refus...',
            inputAttributes: {
                maxlength: 500,
                required: true
            },
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Oui, refuser',
            cancelButtonText: 'Annuler',
            preConfirm: (value) => {
                if (!value) {
                    Swal.showValidationMessage('Veuillez indiquer le motif du refus');
                }
                return value;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'commentaire',
                    value: result.value
                }).appendTo(form);
                form.submit();
            }
        });
    });

    /* ==========================
        FILTRES
    =========================== */
    const typeFilter = $('#type-filter');
    const statusFilter = $('#status-filter');
    const searchInput = $('#search-input');
    const startDate = $('#start-date');
    const endDate = $('#end-date');
    const resetBtn = $('#reset-filters');

    function applyFilters() {
        const typeValue = typeFilter.val();
        const statusValue = statusFilter.val();
        const searchText = searchInput.val().toLowerCase();
        const startValue = startDate.val() ? new Date(startDate.val()) : null;
        const endValue = endDate.val() ? new Date(endDate.val()) : null;

        $('#conges-table tbody tr').each(function() {
            const rowType = $(this).data('type-id');
            const rowStatus = $(this).data('status');
            const rowUser = $(this).data('user');
            const rowStart = new Date($(this).data('start'));
            const rowEnd = new Date($(this).data('end'));

            let visible = true;

            // Filtre par type
            if (typeValue && rowType != typeValue) {
                visible = false;
            }

            // Filtre par statut
            if (statusValue && rowStatus !== statusValue) {
                visible = false;
            }

            // Filtre par recherche
            if (searchText && !rowUser.includes(searchText)) {
                visible = false;
            }

            // Filtre par dates
            if (startValue && rowEnd < startValue) {
                visible = false;
            }
            if (endValue && rowStart > endValue) {
                visible = false;
            }

            $(this).toggle(visible);
        });

        // Afficher/masquer le message vide
        const visibleRows = $('#conges-table tbody tr:visible').length;
        if (visibleRows === 0) {
            $('#conges-table tbody').append(`
                <tr id="no-results">
                    <td colspan="${$('#conges-table thead th').length}" class="text-center py-4">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="fas fa-search"></i>
                            </div>
                            <h2>Aucun résultat trouvé</h2>
                            <p class="lead">
                                Aucune demande ne correspond à vos critères de recherche.
                            </p>
                            <button type="button" id="reset-filters-btn" class="btn btn-primary mt-4">
                                <i class="fas fa-redo"></i> Réinitialiser les filtres
                            </button>
                        </div>
                    </td>
                </tr>
            `);

            $('#reset-filters-btn').on('click', function() {
                resetFilters();
            });
        } else {
            $('#no-results').remove();
        }
    }

    function resetFilters() {
        typeFilter.val('').trigger('change');
        statusFilter.val('').trigger('change');
        searchInput.val('');
        startDate.val('');
        endDate.val('');
        $('#conges-table tbody tr').show();
        $('#no-results').remove();
    }

    // Événements
    typeFilter.on('change', applyFilters);
    statusFilter.on('change', applyFilters);
    searchInput.on('input', applyFilters);
    startDate.on('change', applyFilters);
    endDate.on('change', applyFilters);
    resetBtn.on('click', resetFilters);

    // Empêcher la sélection de date de fin antérieure à date de début
    startDate.on('change', function() {
        endDate.attr('min', $(this).val());
        if (endDate.val() && new Date(endDate.val()) < new Date($(this).val())) {
            endDate.val('');
        }
    });

    /* ==========================
        TRI PAR COLONNES
    =========================== */
    $('#conges-table th').on('click', function() {
        const table = $(this).parents('table').eq(0);
        const rows = table.find('tr:gt(0)').toArray().sort(comparer($(this).index()));
        this.asc = !this.asc;
        if (!this.asc) {
            rows.reverse();
        }
        for (let i = 0; i < rows.length; i++) {
            table.append(rows[i]);
        }
    });

    function comparer(index) {
        return function(a, b) {
            const valA = $(a).children('td').eq(index).text().toUpperCase();
            const valB = $(b).children('td').eq(index).text().toUpperCase();
            return $.isNumeric(valA) && $.isNumeric(valB) ?
                valA - valB : valA.localeCompare(valB);
        };
    }
});
</script>
@endpush
@endsection
