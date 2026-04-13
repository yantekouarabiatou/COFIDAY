@extends('layaout')
@section('title', 'Validation des attestations')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-check-double"></i> Validation des attestations</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('attestations.index') }}">Attestations</a></div>
            <div class="breadcrumb-item">Validation</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">

                @if($nbEnAttente > 0)
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>{{ $nbEnAttente }}</strong> demande(s) en attente de votre validation.
                    </div>
                @endif

                <div class="card card-primary">
                    <div class="card-header">
                        <h4>Demandes en attente</h4>
                        <div class="card-header-action">
                            <a href="{{ route('attestations.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-list"></i> Toutes les demandes
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Collaborateur</th>
                                        <th>Type</th>
                                        <th>Motif</th>
                                        <th>Date demande</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($demandes as $demande)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <strong>{{ $demande->user->prenom }} {{ $demande->user->nom }}</strong><br>
                                                <small class="text-muted">{{ $demande->user->poste->libelle ?? '' }}</small><br>
                                                <small class="text-muted">{{ $demande->user->email }}</small>
                                            </td>
                                            <td>
                                                @switch($demande->type)
                                                    @case('attestation_simple')
                                                        <span class="badge badge-primary"><i class="fas fa-file-alt"></i> Simple</span>@break
                                                    @case('attestation_banque')
                                                        <span class="badge badge-success"><i class="fas fa-university"></i> Banque</span>
                                                        @if($demande->destinataire)
                                                            <br><small class="text-muted">{{ $demande->destinataire }}</small>
                                                        @endif
                                                        @if($demande->inclure_salaire)
                                                            <br><small class="text-success"><i class="fas fa-money-bill-wave"></i> Avec salaire</small>
                                                        @endif
                                                        @break
                                                    @case('attestation_ambassade')
                                                        <span class="badge badge-info"><i class="fas fa-passport"></i> Ambassade</span>
                                                        @if($demande->destinataire)
                                                            <br><small class="text-muted">{{ $demande->destinataire }}</small>
                                                        @endif
                                                        @break
                                                    @case('attestation_autre')
                                                        <span class="badge badge-secondary"><i class="fas fa-ellipsis-h"></i> Spécifique</span>
                                                        <br><small class="text-warning">RH rédigera manuellement</small>@break
                                                @endswitch
                                            </td>
                                            <td style="max-width: 280px;">
                                                <span data-toggle="tooltip" title="{{ $demande->motif }}">
                                                    {{ Str::limit($demande->motif, 80) }}
                                                </span>
                                                <br>
                                                <a href="{{ route('attestations.show', $demande) }}" class="small text-primary">
                                                    Lire tout <i class="fas fa-external-link-alt"></i>
                                                </a>
                                            </td>
                                            <td>
                                                {{ $demande->created_at->format('d/m/Y H:i') }}<br>
                                                <small class="{{ $demande->created_at->diffInDays(now()) > 2 ? 'text-danger' : 'text-muted' }}">
                                                    {{ $demande->created_at->diffForHumans() }}
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    {{-- Approuver --}}
                                                    <form action="{{ route('attestations.traiter', $demande) }}"
                                                          method="POST" class="d-inline approve-form">
                                                        @csrf
                                                        <input type="hidden" name="action" value="approuve">
                                                        <button type="button" class="btn btn-success btn-sm approve-btn">
                                                            <i class="fas fa-check"></i> Approuver
                                                        </button>
                                                    </form>

                                                    {{-- Refuser --}}
                                                    <form action="{{ route('attestations.traiter', $demande) }}"
                                                          method="POST" class="d-inline refuse-form">
                                                        @csrf
                                                        <input type="hidden" name="action" value="refuse">
                                                        <button type="button" class="btn btn-danger btn-sm refuse-btn">
                                                            <i class="fas fa-times"></i> Refuser
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <i class="fas fa-check-circle fa-4x text-success mb-3 d-block"></i>
                                                <h5>Aucune demande en attente</h5>
                                                <p class="text-muted">Tout est à jour.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        {{ $demandes->links('vendor.pagination.stisla') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function () {
    $('[data-toggle="tooltip"]').tooltip();

    // Approbation
    $('.approve-btn').on('click', function () {
        const form = $(this).closest('form');
        Swal.fire({
            title: 'Approuver cette demande ?',
            text: 'Un mail avec le document sera envoyé à l\'employé et à la secrétaire.',
            icon: 'question',
            input: 'textarea',
            inputLabel: 'Commentaire (optionnel)',
            inputPlaceholder: 'Ajouter un commentaire…',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-check"></i> Approuver',
            cancelButtonText: 'Annuler',
        }).then(r => {
            if (r.isConfirmed) {
                if (r.value) {
                    $('<input>').attr({ type: 'hidden', name: 'commentaire', value: r.value }).appendTo(form);
                }
                form.submit();
            }
        });
    });

    // Refus
    $('.refuse-btn').on('click', function () {
        const form = $(this).closest('form');
        Swal.fire({
            title: 'Refuser cette demande ?',
            icon: 'warning',
            input: 'textarea',
            inputLabel: 'Motif du refus (requis)',
            inputPlaceholder: 'Expliquez la raison du refus…',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-times"></i> Refuser',
            cancelButtonText: 'Annuler',
            preConfirm: v => {
                if (!v) Swal.showValidationMessage('Le motif est obligatoire');
                return v;
            },
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
