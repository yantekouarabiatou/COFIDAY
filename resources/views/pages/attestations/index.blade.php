@extends('layaout')
@section('title', 'Mes demandes d\'attestation')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-file-alt"></i> Attestations de travail</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Attestations</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">

                    <div class="card-header">
                        <h4>Liste des demandes</h4>
                        <div class="card-header-action">
                            <a href="{{ route('attestations.create') }}" class="btn btn-primary btn-icon icon-left">
                                <i class="fas fa-plus"></i> Nouvelle demande
                            </a>
                            @hasanyrole('directeur-general|rh|admin')
                                <a href="{{ route('attestations.validation.index') }}" class="btn btn-warning btn-icon icon-left ml-2">
                                    <i class="fas fa-check-double"></i> Validation
                                    @php $nb = \App\Models\DemandeAttestation::enAttente()->count(); @endphp
                                    @if($nb > 0)
                                        <span class="badge badge-danger ml-1">{{ $nb }}</span>
                                    @endif
                                </a>
                            @endhasanyrole
                        </div>
                    </div>

                    <div class="card-body">

                        {{-- Filtres --}}
                        <form action="{{ route('attestations.index') }}" method="GET">
                            <div class="row align-items-end mb-4">
                                <div class="col-md-3">
                                    <label class="small font-weight-bold text-muted mb-1">Type</label>
                                    <select name="type" class="form-control select2-filter">
                                        <option value="">— Tous les types —</option>
                                        <option value="attestation_simple"    {{ request('type') === 'attestation_simple'    ? 'selected' : '' }}>Simple</option>
                                        <option value="attestation_banque"    {{ request('type') === 'attestation_banque'    ? 'selected' : '' }}>Banque / Crédit</option>
                                        <option value="attestation_ambassade" {{ request('type') === 'attestation_ambassade' ? 'selected' : '' }}>Ambassade / Visa</option>
                                        <option value="attestation_autre"     {{ request('type') === 'attestation_autre'     ? 'selected' : '' }}>Format spécifique</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="small font-weight-bold text-muted mb-1">Statut</label>
                                    <select name="statut" class="form-control select2-filter">
                                        <option value="">— Tous —</option>
                                        <option value="en_attente" {{ request('statut') === 'en_attente' ? 'selected' : '' }}>En attente</option>
                                        <option value="approuve"   {{ request('statut') === 'approuve'   ? 'selected' : '' }}>Approuvée</option>
                                        <option value="refuse"     {{ request('statut') === 'refuse'     ? 'selected' : '' }}>Refusée</option>
                                    </select>
                                </div>
                                @if($isAdmin && $users->isNotEmpty())
                                    <div class="col-md-3">
                                        <label class="small font-weight-bold text-muted mb-1">Collaborateur</label>
                                        <select name="user_id" class="form-control select2-filter">
                                            <option value="">— Tous —</option>
                                            @foreach($users as $u)
                                                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                                    {{ $u->prenom }} {{ $u->nom }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                                <div class="col-md-2 mt-3 mt-md-0">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-search"></i> Filtrer
                                    </button>
                                </div>
                                @if(request()->hasAny(['type','statut','user_id']))
                                    <div class="col-md-2 mt-2 mt-md-0">
                                        <a href="{{ route('attestations.index') }}" class="btn btn-outline-secondary btn-block">
                                            <i class="fas fa-times"></i> Réinitialiser
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </form>

                        {{-- Tableau --}}
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        @if($isAdmin)<th>Collaborateur</th>@endif
                                        <th>Type</th>
                                        <th>Motif</th>
                                        <th>Statut</th>
                                        <th>Date</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($demandes as $demande)
                                        <tr>
                                            <td>{{ $loop->iteration + ($demandes->currentPage() - 1) * $demandes->perPage() }}</td>

                                            @if($isAdmin)
                                                <td>
                                                    <strong>{{ $demande->user->prenom }} {{ $demande->user->nom }}</strong><br>
                                                    <small class="text-muted">{{ $demande->user->poste->libelle ?? '' }}</small>
                                                </td>
                                            @endif

                                            <td>
                                                @switch($demande->type)
                                                    @case('attestation_simple')
                                                        <span class="badge badge-primary"><i class="fas fa-file-alt"></i> Simple</span>@break
                                                    @case('attestation_banque')
                                                        <span class="badge badge-success"><i class="fas fa-university"></i> Banque</span>@break
                                                    @case('attestation_ambassade')
                                                        <span class="badge badge-info"><i class="fas fa-passport"></i> Ambassade</span>@break
                                                    @case('attestation_autre')
                                                        <span class="badge badge-secondary"><i class="fas fa-ellipsis-h"></i> Spécifique</span>@break
                                                @endswitch
                                                @if($demande->destinataire)
                                                    <br><small class="text-muted">{{ $demande->destinataire }}</small>
                                                @endif
                                            </td>

                                            <td>
                                                <span title="{{ $demande->motif }}">
                                                    {{ Str::limit($demande->motif, 55) }}
                                                </span>
                                            </td>

                                            <td>{!! $demande->statut_badge !!}</td>

                                            <td>
                                                {{ $demande->created_at->format('d/m/Y') }}<br>
                                                <small class="text-muted">{{ $demande->created_at->diffForHumans() }}</small>
                                            </td>

                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <a href="{{ route('attestations.show', $demande) }}"
                                                       class="btn btn-info btn-sm" title="Voir détails">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($demande->statut === 'en_attente' &&
                                                        (auth()->id() === $demande->user_id || auth()->user()->hasRole('admin')))
                                                        <form action="{{ route('attestations.annuler', $demande) }}"
                                                              method="POST" class="d-inline cancel-form">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="button" class="btn btn-danger btn-sm cancel-btn" title="Annuler">
                                                                <i class="fas fa-ban"></i>
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
                                                    <i class="fas fa-file-times fa-4x text-muted mb-3"></i>
                                                    <h5>Aucune demande</h5>
                                                    <p class="text-muted">Vous n'avez pas encore de demande d'attestation.</p>
                                                    <a href="{{ route('attestations.create') }}" class="btn btn-primary mt-2">
                                                        <i class="fas fa-plus"></i> Créer une demande
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted small">
                                @if($demandes->total() > 0)
                                    {{ $demandes->firstItem() }} à {{ $demandes->lastItem() }} sur {{ $demandes->total() }} demande(s)
                                @endif
                            </div>
                            {{ $demandes->links('vendor.pagination.stisla') }}
                        </div>

                        {{-- Statistiques --}}
                        <div class="row mt-4">
                            <div class="col-md-4">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-primary"><i class="fas fa-file-alt"></i></div>
                                    <div class="card-wrap">
                                        <div class="card-header"><h4>Total</h4></div>
                                        <div class="card-body">{{ $totalDemandes }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-warning"><i class="fas fa-clock"></i></div>
                                    <div class="card-wrap">
                                        <div class="card-header"><h4>En attente</h4></div>
                                        <div class="card-body">{{ $enAttente }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-success"><i class="fas fa-check-circle"></i></div>
                                    <div class="card-wrap">
                                        <div class="card-header"><h4>Approuvées</h4></div>
                                        <div class="card-body">{{ $approuvees }}</div>
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
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/bundles/select2/dist/css/select2.min.css') }}">
<style>
.empty-state { text-align: center; padding: 30px 20px; }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/bundles/select2/dist/js/select2.full.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function () {
    $('.select2-filter').select2({ allowClear: true, width: '100%' });

    $('.cancel-btn').on('click', function () {
        const form = $(this).closest('form');
        Swal.fire({
            title: 'Annuler cette demande ?',
            text: 'Cette action est irréversible.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Oui, annuler',
            cancelButtonText: 'Non',
        }).then(r => r.isConfirmed && form.submit());
    });
});
</script>
@endpush
