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

                    {{-- ── Header ──────────────────────────────────────────────────── --}}
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
                               class="btn btn-success ml-2" title="Tous les congés">
                                <i class="fas fa-file-excel"></i> Tous
                            </a>

                            @hasanyrole('directeur-general|rh')
                            <a href="{{ route('conges.validation-finale.index') }}" class="btn btn-warning btn-icon icon-left ml-2">
                                <i class="fas fa-check-double"></i> Validation finale
                                @php $nbPreApprouves = \App\Models\DemandeConge::where('statut', 'pre_approuve')->count(); @endphp
                                @if($nbPreApprouves > 0)
                                    <span class="badge badge-danger ml-1">{{ $nbPreApprouves }}</span>
                                @endif
                            </a>
                            @endhasanyrole

                            <a href="{{ route('conges.export.excel') }}?annee={{ date('Y') }}&user_id={{ auth()->id() }}"
                               class="btn btn-info ml-2" title="Mes congés seulement">
                                <i class="fas fa-user"></i> Mes congés
                            </a>
                        </div>
                    </div>

                    {{-- ── Body ────────────────────────────────────────────────────── --}}
                    <div class="card-body">

                        {{-- Alertes session --}}
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible show fade">
                                <div class="alert-body">
                                    <button class="close" data-dismiss="alert"><span>&times;</span></button>
                                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                                </div>
                            </div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible show fade">
                                <div class="alert-body">
                                    <button class="close" data-dismiss="alert"><span>&times;</span></button>
                                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                                </div>
                            </div>
                        @endif

                        {{-- ── Formulaire de filtres (server-side) ─────────────────── --}}
                        <form action="{{ route('conges.index') }}" method="GET" id="filterForm">
                            <div class="row align-items-end mb-4">

                                {{-- Type de congé --}}
                                <div class="col-md-2">
                                    <label class="small font-weight-bold text-muted mb-1">
                                        <i class="fas fa-tag mr-1"></i>Type
                                    </label>
                                    <select name="type_conge_id" class="form-control select2-filter">
                                        <option value="">— Tous les types —</option>
                                        @foreach($typesConges as $type)
                                            <option value="{{ $type->id }}"
                                                {{ request('type_conge_id') == $type->id ? 'selected' : '' }}>
                                                {{ $type->libelle }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Statut --}}
                                <div class="col-md-2">
                                    <label class="small font-weight-bold text-muted mb-1">
                                        <i class="fas fa-circle mr-1"></i>Statut
                                    </label>
                                    <select name="statut" class="form-control select2-filter">
                                        <option value="">— Tous —</option>
                                        <option value="en_attente"  {{ request('statut') === 'en_attente'  ? 'selected' : '' }}>En attente</option>
                                        <option value="pre_approuve"{{ request('statut') === 'pre_approuve'? 'selected' : '' }}>Pré-approuvé</option>
                                        <option value="approuve"    {{ request('statut') === 'approuve'    ? 'selected' : '' }}>Approuvé</option>
                                        <option value="refuse"      {{ request('statut') === 'refuse'      ? 'selected' : '' }}>Refusé</option>
                                        <option value="annule"      {{ request('statut') === 'annule'      ? 'selected' : '' }}>Annulé</option>
                                    </select>
                                </div>

                                {{-- Collaborateur (admin / manager uniquement) --}}
                                @if($isAdmin && $users->isNotEmpty())
                                <div class="col-md-3">
                                    <label class="small font-weight-bold text-muted mb-1">
                                        <i class="fas fa-user mr-1"></i>Collaborateur
                                    </label>
                                    <select name="user_id" id="userFilter" class="form-control select2-filter">
                                        <option value="">— Tous les collaborateurs —</option>
                                        @foreach($users as $u)
                                            <option value="{{ $u->id }}"
                                                {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                                {{ $u->prenom }} {{ $u->nom }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif

                                {{-- Recherche texte --}}
                                <div class="col-md-2">
                                    <label class="small font-weight-bold text-muted mb-1">
                                        <i class="fas fa-search mr-1"></i>Recherche
                                    </label>
                                    <input type="text"
                                           name="search"
                                           class="form-control"
                                           value="{{ request('search') }}"
                                           placeholder="Nom, motif…">
                                </div>

                                {{-- Date début --}}
                                <div class="col-md-2">
                                    <label class="small font-weight-bold text-muted mb-1">
                                        <i class="fas fa-calendar mr-1"></i>À partir du
                                    </label>
                                    <input type="date"
                                           name="date_debut"
                                           class="form-control"
                                           value="{{ request('date_debut') }}">
                                </div>

                                {{-- Date fin --}}
                                <div class="col-md-2">
                                    <label class="small font-weight-bold text-muted mb-1">
                                        <i class="fas fa-calendar mr-1"></i>Jusqu'au
                                    </label>
                                    <input type="date"
                                           name="date_fin"
                                           class="form-control"
                                           value="{{ request('date_fin') }}">
                                </div>

                                {{-- Boutons --}}
                                <div class="col-12 mt-3 d-flex align-items-center">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Rechercher
                                    </button>

                                    @if(request()->hasAny(['type_conge_id', 'statut', 'user_id', 'search', 'date_debut', 'date_fin']))
                                        <a href="{{ route('conges.index') }}"
                                           class="btn btn-outline-secondary ml-2">
                                            <i class="fas fa-times"></i> Réinitialiser
                                        </a>
                                        <span class="ml-3 text-muted small">
                                            <i class="fas fa-filter mr-1"></i>Filtres actifs
                                        </span>
                                    @endif
                                </div>

                            </div>
                        </form>

                        {{-- ── Tableau ──────────────────────────────────────────────── --}}
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="conges-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        @if($isAdmin)
                                        <th>Collaborateur</th>
                                        @endif
                                        <th>Type</th>
                                        <th>Dates</th>
                                        <th>Durée</th>
                                        <th>Statut</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($demandes as $demande)
                                        <tr>
                                            <td>{{ $loop->iteration + ($demandes->currentPage() - 1) * $demandes->perPage() }}</td>

                                            @if($isAdmin)
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="mr-2">
                                                        @if($demande->user->photo)
                                                            <img src="{{ asset('storage/' . $demande->user->photo) }}"
                                                                 class="rounded-circle" width="30" height="30" alt="Photo">
                                                        @else
                                                            <div class="avatar bg-primary text-white rounded-circle"
                                                                 style="width:30px;height:30px;line-height:30px;text-align:center;">
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
                                            @endif

                                            <td>
                                                <span class="badge badge-{{ $demande->typeConge->est_paye ? 'success' : 'warning' }}">
                                                    {{ $demande->typeConge->libelle }}
                                                </span>
                                                <br>
                                                <small class="text-muted">{{ $demande->typeConge->est_paye ? 'Payé' : 'Non payé' }}</small>
                                            </td>

                                            <td>
                                                <strong>Début :</strong> {{ $demande->date_debut->format('d/m/Y') }}<br>
                                                <strong>Fin :</strong> {{ $demande->date_fin->format('d/m/Y') }}
                                            </td>

                                            <td class="text-center">
                                                <span class="badge badge-info" style="font-size:1em;">
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
                                                            <br><small class="text-danger">Depuis {{ $demande->created_at->diffInDays(now()) }} jours</small>
                                                        @endif
                                                        @break
                                                    @case('pre_approuve')
                                                        <span class="badge badge-info">
                                                            <i class="fas fa-hourglass-half"></i> Pré-approuvé
                                                        </span>
                                                        @if($demande->validePar)
                                                            <br><small class="text-muted">Par {{ $demande->validePar->prenom }} {{ $demande->validePar->nom }}</small>
                                                        @endif
                                                        <br><small class="text-warning">En attente validation finale</small>
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

                                                    <a href="{{ route('conges.show', $demande) }}"
                                                       class="btn btn-info btn-sm" title="Voir détails">
                                                        <i class="fas fa-eye"></i>
                                                    </a>

                                                    @if($demande->statut === 'en_attente' &&
                                                        (auth()->user()->hasRole('admin') || auth()->id() === $demande->user_id))
                                                        <a href="{{ route('conges.edit', $demande) }}"
                                                           class="btn btn-primary btn-sm" title="Modifier">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endif

                                                    @role('admin|manager')
                                                        @if($demande->statut === 'en_attente' && $demande->superieur_hierarchique_id == auth()->id() && auth()->id() !== $demande->user_id)
                                                            <form action="{{ route('conges.traiter', $demande) }}" method="POST" class="d-inline approve-form">
                                                                @csrf
                                                                <input type="hidden" name="action" value="pre_approuve">
                                                                <button type="button" class="btn btn-success btn-sm approve-btn" title="Pré-approuver">
                                                                    <i class="fas fa-check"></i>
                                                                </button>
                                                            </form>
                                                            <form action="{{ route('conges.traiter', $demande) }}" method="POST" class="d-inline refuse-form">
                                                                @csrf
                                                                <input type="hidden" name="action" value="refuse">
                                                                <button type="button" class="btn btn-danger btn-sm refuse-btn" title="Refuser">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endrole

                                                    @if($demande->statut === 'en_attente' &&
                                                        (auth()->user()->hasRole('admin') || auth()->id() === $demande->user_id))
                                                        <form action="{{ route('conges.annuler', $demande) }}" method="POST" class="d-inline cancel-form">
                                                            @csrf
                                                            <button type="button" class="btn btn-warning btn-sm cancel-btn" title="Annuler">
                                                                <i class="fas fa-ban"></i>
                                                            </button>
                                                        </form>
                                                    @endif

                                                    @if($demande->statut !== 'approuve' &&
                                                        (auth()->user()->hasRole('admin') ||
                                                         (auth()->id() === $demande->user_id && in_array($demande->statut, ['en_attente', 'annule']))))
                                                        <form action="{{ route('conges.destroy', $demande) }}" method="POST" class="d-inline delete-form">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="button" class="btn btn-danger btn-sm delete-btn" title="Supprimer">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif

                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ $isAdmin ? 7 : 6 }}" class="text-center py-5">
                                                <div class="empty-state">
                                                    <div class="empty-state-icon">
                                                        <i class="fas fa-{{ request()->hasAny(['type_conge_id','statut','user_id','search','date_debut','date_fin']) ? 'search' : 'calendar-times' }}"></i>
                                                    </div>
                                                    <h2>Aucune demande trouvée</h2>
                                                    <p class="lead">
                                                        @if(request()->hasAny(['type_conge_id','statut','user_id','search','date_debut','date_fin']))
                                                            Aucune demande ne correspond à vos critères.
                                                        @else
                                                            Vous n'avez pas encore de demande de congé.
                                                        @endif
                                                    </p>
                                                    @if(request()->hasAny(['type_conge_id','statut','user_id','search','date_debut','date_fin']))
                                                        <a href="{{ route('conges.index') }}" class="btn btn-outline-secondary mt-3">
                                                            <i class="fas fa-times"></i> Réinitialiser les filtres
                                                        </a>
                                                    @else
                                                        <a href="{{ route('conges.create') }}" class="btn btn-primary mt-3">
                                                            <i class="fas fa-plus"></i> Créer une demande
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div class="d-flex flex-wrap justify-content-between align-items-center mt-3">
                            <div class="text-muted mb-2 mb-sm-0">
                                @if($demandes->total() > 0)
                                    Affichage de {{ $demandes->firstItem() }} à {{ $demandes->lastItem() }}
                                    sur {{ $demandes->total() }} demande{{ $demandes->total() > 1 ? 's' : '' }}
                                @endif
                            </div>
                            <div>
                                {{ $demandes->links('vendor.pagination.stisla') }}
                            </div>
                        </div>

                        {{-- ── Statistiques ─────────────────────────────────────────── --}}
                        <div class="row mt-4">
                            <div class="col-md-3">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-primary"><i class="fas fa-calendar"></i></div>
                                    <div class="card-wrap">
                                        <div class="card-header"><h4>Total demandes</h4></div>
                                        <div class="card-body">{{ $totalDemandes }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-warning"><i class="fas fa-clock"></i></div>
                                    <div class="card-wrap">
                                        <div class="card-header"><h4>En attente</h4></div>
                                        <div class="card-body">{{ $enAttente }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-success"><i class="fas fa-check-circle"></i></div>
                                    <div class="card-wrap">
                                        <div class="card-header"><h4>Approuvés</h4></div>
                                        <div class="card-body">{{ $approuves }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-danger"><i class="fas fa-times-circle"></i></div>
                                    <div class="card-wrap">
                                        <div class="card-header"><h4>Refusés</h4></div>
                                        <div class="card-body">{{ $refuses }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>{{-- /card-body --}}
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/bundles/select2/dist/css/select2.min.css') }}">
<style>
    .badge { font-size: .85em; font-weight: 500; padding: .35em .65em; }
    .btn-group .btn-sm { margin: 0 2px; border-radius: 4px; }
    .avatar { display: inline-flex; align-items: center; justify-content: center; font-weight: bold; }
    .empty-state { text-align: center; padding: 40px 20px; }
    .empty-state-icon { font-size: 4rem; color: #6c757d; margin-bottom: 20px; }
    .empty-state h2 { font-size: 1.5rem; margin-bottom: 10px; }
    .empty-state p { color: #6c757d; margin-bottom: 20px; }
    .pagination { margin-bottom: 0; }

    /* Select2 aligné sur Bootstrap */
    .select2-container .select2-selection--single {
        height: calc(1.5em + .75rem + 2px) !important;
        border: 1px solid #ced4da; border-radius: .25rem;
        padding: .375rem .75rem; font-size: 1rem; line-height: 1.5;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 1.5; padding-left: 0; color: #495057;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 100%; }
    .select2-container--default .select2-results__option--highlighted[aria-selected] { background-color: #4e73df; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('assets/bundles/select2/dist/js/select2.full.min.js') }}"></script>
<script>
$(document).ready(function () {

    // ── Select2 sur tous les selects du formulaire de filtre ────────
    $('.select2-filter').select2({
        allowClear: true,
        width: '100%',
        language: {
            noResults:  () => 'Aucun résultat',
            searching:  () => 'Recherche…',
        }
    });

    // Empêcher date_fin < date_debut
    $('input[name="date_debut"]').on('change', function () {
        $('input[name="date_fin"]').attr('min', $(this).val());
        if ($('input[name="date_fin"]').val() &&
            new Date($('input[name="date_fin"]').val()) < new Date($(this).val())) {
            $('input[name="date_fin"]').val('');
        }
    });

    // ── Suppression ─────────────────────────────────────────────────
    $('.delete-btn').on('click', function () {
        const form = $(this).closest('form');
        Swal.fire({
            title: 'Confirmer la suppression',
            text: 'Êtes-vous sûr ? Cette action est irréversible.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler'
        }).then(r => r.isConfirmed && form.submit());
    });

    // ── Annulation ──────────────────────────────────────────────────
    $('.cancel-btn').on('click', function () {
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
        }).then(r => r.isConfirmed && form.submit());
    });

    // ── Pré-approbation ─────────────────────────────────────────────
    $('.approve-btn').on('click', function () {
        const form = $(this).closest('form');
        Swal.fire({
            title: 'Confirmer la pré-approbation',
            text: 'La demande sera transmise au DG / RH pour validation finale.',
            icon: 'question',
            input: 'textarea',
            inputLabel: 'Commentaire (optionnel)',
            inputPlaceholder: 'Ajouter un commentaire…',
            inputAttributes: { maxlength: 500 },
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Oui, pré-approuver',
            cancelButtonText: 'Annuler'
        }).then(r => {
            if (r.isConfirmed) {
                if (r.value) $('<input>').attr({ type: 'hidden', name: 'commentaire', value: r.value }).appendTo(form);
                form.submit();
            }
        });
    });

    // ── Refus ────────────────────────────────────────────────────────
    $('.refuse-btn').on('click', function () {
        const form = $(this).closest('form');
        Swal.fire({
            title: 'Confirmer le refus',
            icon: 'question',
            input: 'textarea',
            inputLabel: 'Motif du refus (requis)',
            inputPlaceholder: 'Expliquez le motif…',
            inputAttributes: { maxlength: 500 },
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Oui, refuser',
            cancelButtonText: 'Annuler',
            preConfirm: v => { if (!v) Swal.showValidationMessage('Le motif est obligatoire'); return v; }
        }).then(r => {
            if (r.isConfirmed) {
                $('<input>').attr({ type: 'hidden', name: 'commentaire', value: r.value }).appendTo(form);
                form.submit();
            }
        });
    });

});
</script>
@endpush