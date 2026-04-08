@extends('layaout')
@section('title', 'Validation des démissions')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-check-double"></i> Validation des démissions</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('demissions.index') }}">Démissions</a></div>
            <div class="breadcrumb-item">Validation</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">

                @if($nbEnAttente > 0)
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>{{ $nbEnAttente }}</strong> lettre(s) de démission en attente de validation.
                        <br><small>À l'approbation, le <strong>certificat de travail sera généré et envoyé automatiquement</strong> à l'employé et à la secrétaire.</small>
                    </div>
                @endif

                <div class="card card-primary">
                    <div class="card-header">
                        <h4>Démissions en attente</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Collaborateur</th>
                                        <th>Départ souhaité</th>
                                        <th>Lettre (extrait)</th>
                                        <th>Soumis le</th>
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
                                                <strong>{{ $demande->date_depart_souhaitee->format('d/m/Y') }}</strong><br>
                                                <small class="text-muted">dans {{ $demande->date_depart_souhaitee->diffForHumans() }}</small>
                                            </td>
                                            <td style="max-width: 280px;">
                                                {{ Str::limit($demande->lettre, 90) }}
                                                <br>
                                                <a href="{{ route('demissions.show', $demande) }}" class="small text-primary">
                                                    Lire tout <i class="fas fa-external-link-alt"></i>
                                                </a>
                                            </td>
                                            <td>
                                                {{ $demande->created_at->format('d/m/Y H:i') }}<br>
                                                <small class="{{ $demande->created_at->diffInDays(now()) > 3 ? 'text-danger' : 'text-muted' }}">
                                                    {{ $demande->created_at->diffForHumans() }}
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    {{-- Approuver --}}
                                                    <button type="button"
                                                            class="btn btn-success btn-sm approve-btn"
                                                            data-id="{{ $demande->id }}"
                                                            data-action="{{ route('demissions.traiter', $demande) }}"
                                                            data-nom="{{ $demande->user->prenom }} {{ $demande->user->nom }}"
                                                            data-depart="{{ $demande->date_depart_souhaitee->format('Y-m-d') }}">
                                                        <i class="fas fa-check"></i> Approuver
                                                    </button>

                                                    {{-- Refuser --}}
                                                    <form action="{{ route('demissions.traiter', $demande) }}"
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
                                                <h5>Aucune démission en attente</h5>
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

{{-- Formulaire caché pour l'approbation (soumis via JS) --}}
<form id="approve-form-hidden" method="POST" style="display:none;">
    @csrf
    <input type="hidden" name="action" value="approuve">
    <input type="hidden" name="date_depart_effective" id="date_depart_effective_input">
    <input type="hidden" name="commentaire" id="commentaire_approbation_input">
</form>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function () {

    // ── Approbation avec date effective ─────────────────────────────────────
    $('.approve-btn').on('click', function () {
        const action   = $(this).data('action');
        const nom      = $(this).data('nom');
        const depart   = $(this).data('depart');

        Swal.fire({
            title: 'Approuver la démission',
            html: `
                <p class="text-left mb-3">Vous allez approuver la démission de <strong>${nom}</strong>.<br>
                Le <strong>certificat de travail sera généré et envoyé automatiquement</strong>.</p>
                <div class="form-group text-left">
                    <label class="font-weight-bold">Date de départ effective <span class="text-danger">*</span></label>
                    <input type="date" id="swal-date" class="swal2-input" value="${depart}" min="${depart}">
                    <small class="text-muted">Date souhaitée par l'employé pré-remplie. Modifiez si nécessaire.</small>
                </div>
                <div class="form-group text-left mt-3">
                    <label>Commentaire (optionnel)</label>
                    <textarea id="swal-commentaire" class="swal2-textarea" placeholder="Ajouter un commentaire…" rows="2"></textarea>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-check"></i> Approuver et générer le certificat',
            cancelButtonText: 'Annuler',
            preConfirm: () => {
                const date = document.getElementById('swal-date').value;
                if (!date) {
                    Swal.showValidationMessage('La date de départ effective est obligatoire');
                    return false;
                }
                return {
                    date: date,
                    commentaire: document.getElementById('swal-commentaire').value,
                };
            },
        }).then(r => {
            if (r.isConfirmed) {
                const form = document.getElementById('approve-form-hidden');
                form.action = action;
                document.getElementById('date_depart_effective_input').value = r.value.date;
                document.getElementById('commentaire_approbation_input').value = r.value.commentaire;
                form.submit();
            }
        });
    });

    // ── Refus ────────────────────────────────────────────────────────────────
    $('.refuse-btn').on('click', function () {
        const form = $(this).closest('form');
        Swal.fire({
            title: 'Refuser la démission ?',
            icon: 'warning',
            input: 'textarea',
            inputLabel: 'Motif du refus (requis)',
            inputPlaceholder: 'Expliquez la raison…',
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
