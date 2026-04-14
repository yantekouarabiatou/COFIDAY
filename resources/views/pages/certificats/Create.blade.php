@extends('layaout')
@section('title', 'Soumettre ma démission')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-sign-out-alt"></i> Lettre de démission</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('demissions.index') }}">Mes démissions</a></div>
            <div class="breadcrumb-item">Nouvelle</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row justify-content-center">
            <div class="col-lg-12 col-md-12 col-sm-12">

                @if($demissionActive)
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Vous avez déjà une demande de démission <strong>en cours de traitement</strong>.
                        Veuillez attendre la décision du Directeur Général avant d'en soumettre une nouvelle.
                        <a href="{{ route('demissions.index') }}" class="btn btn-sm btn-warning ml-2">Voir mes demandes</a>
                    </div>
                @else

                {{-- Avertissement important --}}
                <div class="alert alert-danger mb-4">
                    <h6 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Attention — Acte définitif</h6>
                    <p class="mb-1">Soumettre cette lettre déclenche la procédure officielle de démission.</p>
                    <p class="mb-0">Une fois acceptée par le Directeur Général, votre <strong>certificat de travail</strong>
                        sera généré automatiquement et envoyé par mail.</p>
                </div>

                <div class="card card-primary">
                    <div class="card-header">
                        <h4><i class="fas fa-envelope-open-text"></i> Votre lettre de démission</h4>
                    </div>
                    <div class="card-body">

                        {{-- Infos employé --}}
                        <div class="info-employe-grid mb-4">
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-user"></i> Nom complet</span>
                                <span class="info-value">{{ $user->prenom }} {{ $user->nom }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-briefcase"></i> Poste</span>
                                <span class="info-value">{{ $user->poste->intitule ?? 'Non défini' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-envelope"></i> Email</span>
                                <span class="info-value">{{ $user->email }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-calendar-alt"></i> Date</span>
                                <span class="info-value">{{ now()->isoFormat('D MMMM YYYY') }}</span>
                            </div>
                        </div>

                        <form action="{{ route('demissions.store') }}" method="POST" id="demission-form">
                            @csrf

                            {{-- En-tête de section --}}
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Informations sur la démission</h5>
                                </div>
                                <div class="col-md-4 text-md-right">
                                    <span class="badge badge-info p-2">
                                        <i class="fas fa-info"></i> Veuillez remplir tous les champs obligatoires
                                    </span>
                                </div>
                            </div>

                            {{-- Ligne des deux dates --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">
                                            <i class="fas fa-calendar-day mr-1 text-danger"></i>
                                            Date de départ souhaitée <span class="text-danger">*</span>
                                        </label>
                                        <input type="date"
                                            name="date_depart_souhaitee"
                                            class="form-control @error('date_depart_souhaitee') is-invalid @enderror"
                                            value="{{ old('date_depart_souhaitee') }}"
                                            min="{{ now()->addDay()->format('Y-m-d') }}">
                                        @error('date_depart_souhaitee')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">
                                            Respectez le délai de préavis prévu dans votre contrat.
                                        </small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">
                                            <i class="fas fa-calendar-alt mr-1 text-primary"></i>
                                            Date d'embauche <span class="text-danger">*</span>
                                        </label>
                                        <input type="date"
                                            name="date_embauche"
                                            class="form-control @error('date_embauche') is-invalid @enderror"
                                            value="{{ old('date_embauche') }}"
                                            max="{{ now()->format('Y-m-d') }}">
                                        @error('date_embauche')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">
                                            Indiquez la date de votre embauche dans le cabinet.
                                        </small>
                                    </div>
                                </div>
                            </div>

                            {{-- Corps de la lettre --}}
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    <i class="fas fa-pen mr-1 text-primary"></i>
                                    Corps de la lettre <span class="text-danger">*</span>
                                </label>
                                <div class="motif-editor">
                                    <div class="motif-toolbar">
                                        <button type="button" class="toolbar-btn" onclick="insertTemplate()">
                                            <i class="fas fa-magic"></i> Modèle
                                        </button>
                                        <span id="char-counter" class="toolbar-counter">0 / 5000</span>
                                    </div>
                                    <textarea name="lettre"
                                              id="lettre-textarea"
                                              class="motif-textarea @error('lettre') is-invalid @enderror"
                                              maxlength="5000"
                                              placeholder="Rédigez ici votre lettre de démission…">{{ old('lettre') }}</textarea>
                                    @error('lettre')
                                        <div class="invalid-feedback d-block px-3 pb-2">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Confirmation --}}
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input"
                                           id="confirm-check" required>
                                    <label class="custom-control-label font-weight-bold text-danger" for="confirm-check">
                                        Je confirme vouloir soumettre ma démission et comprends que cet acte est définitif.
                                    </label>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('demissions.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left"></i> Annuler
                                </a>
                                <button type="submit" class="btn btn-danger btn-lg" id="submit-btn" disabled>
                                    <i class="fas fa-paper-plane"></i> Soumettre ma démission
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                @endif
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.info-employe-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; background:#f8f9fc; border:1px solid #e3e6f0; border-radius:8px; padding:16px; }
.info-item { display:flex; flex-direction:column; }
.info-label { font-size:.75rem; color:#858796; font-weight:600; margin-bottom:2px; }
.info-value { font-size:.9rem; color:#2e2e2e; font-weight:500; }
.motif-editor { border:1px solid #ced4da; border-radius:8px; overflow:hidden; }
.motif-editor:focus-within { border-color:#e74a3b; box-shadow:0 0 0 .2rem rgba(231,74,59,.12); }
.motif-toolbar { background:#f8f9fc; border-bottom:1px solid #e3e6f0; padding:8px 12px; display:flex; align-items:center; gap:8px; }
.toolbar-btn { border:1px solid #d1d3e2; background:#fff; border-radius:4px; padding:4px 12px; font-size:.8rem; cursor:pointer; }
.toolbar-btn:hover { background:#e8eaf5; }
.toolbar-counter { margin-left:auto; font-size:.78rem; color:#858796; font-weight:600; }
.motif-textarea { width:100%; min-height:260px; border:none !important; outline:none !important; box-shadow:none !important; padding:14px 16px; font-size:.9rem; line-height:1.75; resize:vertical; background:#fff; font-family:inherit; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
@php
    $modeleDemission = "Je soussigné(e) {$user->prenom} {$user->nom}, "
        . ($user->poste->intitule ?? 'poste non défini')
        . " au sein du Cabinet COFIMA, ai l'honneur de vous informer par la présente de ma décision de démissionner de mes fonctions.\n\n"
        . "Conformément aux dispositions de mon contrat de travail, je m'engage à effectuer mon préavis dont le terme est fixé au [date de départ souhaitée].\n\n"
        . "Je tiens à vous assurer que je ferai tout mon possible pour assurer une transition en bonne et due forme, notamment en transmettant les dossiers en cours à la personne qui me sera désignée.\n\n"
        . "Je vous remercie pour l'opportunité professionnelle que vous m'avez accordée et pour la confiance dont vous m'avez témoigné tout au long de ma collaboration au sein du Cabinet.\n\n"
        . "Je vous prie d'agréer, Monsieur l'Associé-Gérant, l'expression de mes respectueuses salutations.";
@endphp
const TEMPLATE_DEMISSION = @json($modeleDemission);

function insertTemplate() {
    document.getElementById('lettre-textarea').value = TEMPLATE_DEMISSION;
    updateCounter();
}

function updateCounter() {
    const len = document.getElementById('lettre-textarea').value.length;
    const el  = document.getElementById('char-counter');
    el.textContent = len + ' / 5000';
}

document.getElementById('lettre-textarea').addEventListener('input', updateCounter);

document.getElementById('confirm-check').addEventListener('change', function() {
    document.getElementById('submit-btn').disabled = !this.checked;
});

// Confirmation finale avant soumission
document.getElementById('demission-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    Swal.fire({
        title: 'Confirmer la démission',
        html: 'Vous êtes sur le point de soumettre officiellement votre lettre de démission.<br><br><strong>Cette action est irréversible une fois acceptée par le DG.</strong>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Oui, soumettre',
        cancelButtonText: 'Annuler'
    }).then(r => { if (r.isConfirmed) form.submit(); });
});

document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('lettre-textarea');
    if (!textarea.value.trim()) {
        insertTemplate();
    }
    updateCounter();
});
</script>
@endpush
