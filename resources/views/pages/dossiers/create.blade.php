@extends('layaout')

@section('title', 'Nouveau Dossier')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-plus-circle"></i> Nouveau Dossier</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('dossiers.index') }}">Dossiers</a></div>
            <div class="breadcrumb-item active">Nouveau Dossier</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Créer un Nouveau Dossier</h4>
                        <div class="card-header-action">
                            <a href="{{ route('dossiers.index') }}" class="btn btn-icon icon-left btn-danger">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('dossiers.store') }}" method="POST" enctype="multipart/form-data" id="dossier-form">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Client <span class="text-danger">*</span></label>
                                        <select name="client_id" class="form-control select2 @error('client_id') is-invalid @enderror" required>
                                            <option value="">Sélectionner un client</option>
                                            @foreach($clients as $client)
                                                <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                                    {{ $client->nom }} ({{ $client->statut }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('client_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nom du Dossier <span class="text-danger">*</span></label>
                                        <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror"
                                               value="{{ old('nom') }}" required placeholder="Ex: Audit financier 2024">
                                        @error('nom')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Après le champ client_id -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Collaborateurs</label>
                                        <select name="collaborateurs[]"
                                                class="form-control select2 @error('collaborateurs') is-invalid @enderror"
                                                multiple="multiple">
                                            @foreach($collaborateurs as $collaborateur)
                                                <option value="{{ $collaborateur->id }}"
                                                    {{ in_array($collaborateur->id, old('collaborateurs', [])) ? 'selected' : '' }}>
                                                    {{ $collaborateur->nom }} {{ $collaborateur->prenom }}
                                                    @if($collaborateur->poste)
                                                        ({{ $collaborateur->poste->intitule }})
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">Sélectionnez les collaborateurs qui auront accès à ce dossier</small>
                                        @error('collaborateurs')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Référence</label>
                                        <input type="text" name="reference" class="form-control @error('reference') is-invalid @enderror"
                                               value="{{ old('reference', 'DOS-' . date('Ymd-His')) }}"
                                               placeholder="Générée automatiquement">
                                        @error('reference')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Type de Dossier <span class="text-danger">*</span></label>
                                        <select name="type_dossier" class="form-control select2 @error('type_dossier') is-invalid @enderror" required>
                                            <option value="">Sélectionner un type</option>
                                            <option value="audit" {{ old('type_dossier') == 'audit' ? 'selected' : '' }}>Audit</option>
                                            <option value="conseil" {{ old('type_dossier') == 'conseil' ? 'selected' : '' }}>Conseil</option>
                                            <option value="formation" {{ old('type_dossier') == 'formation' ? 'selected' : '' }}>Formation</option>
                                            <option value="expertise" {{ old('type_dossier') == 'expertise' ? 'selected' : '' }}>Expertise</option>
                                            <option value="autre" {{ old('type_dossier') == 'autre' ? 'selected' : '' }}>Autre</option>
                                        </select>
                                        @error('type_dossier')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Statut <span class="text-danger">*</span></label>
                                        <select name="statut" class="form-control select2 @error('statut') is-invalid @enderror" required>
                                            <option value="">Sélectionner un statut</option>
                                            <option value="ouvert" {{ old('statut') == 'ouvert' ? 'selected' : '' }}>Ouvert</option>
                                            <option value="en_cours" {{ old('statut') == 'en_cours' ? 'selected' : '' }}>En cours</option>
                                            <option value="suspendu" {{ old('statut') == 'suspendu' ? 'selected' : '' }}>Suspendu</option>
                                            <option value="cloture" {{ old('statut') == 'cloture' ? 'selected' : '' }}>Clôturé</option>
                                        </select>
                                        @error('statut')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Date d'ouverture <span class="text-danger">*</span></label>
                                        <input type="date" name="date_ouverture" class="form-control @error('date_ouverture') is-invalid @enderror"
                                               value="{{ old('date_ouverture', date('Y-m-d')) }}" required>
                                        @error('date_ouverture')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Date de clôture prévue</label>
                                        <input type="date" name="date_cloture_prevue" class="form-control @error('date_cloture_prevue') is-invalid @enderror"
                                               value="{{ old('date_cloture_prevue') }}">
                                        @error('date_cloture_prevue')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Heures théoriques (sans weekend)</label>
                                        <div class="input-group">
                                            <input type="number" step="0.5" min="0"
                                                name="heure_theorique_sans_weekend"
                                                id="heure_sans_weekend"
                                                class="form-control"
                                                placeholder="Calcul automatique">
                                            <div class="input-group-append">
                                                <span class="input-group-text">h</span>
                                            </div>
                                        </div>
                                        <small class="text-muted">Basé sur 8h/jour (lundi–vendredi)</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Heures théoriques (avec weekend)</label>
                                        <div class="input-group">
                                            <input type="number" step="0.5" min="0"
                                                name="heure_theorique_avec_weekend"
                                                id="heure_avec_weekend"
                                                class="form-control"
                                                placeholder="Calcul automatique">
                                            <div class="input-group-append">
                                                <span class="input-group-text">h</span>
                                            </div>
                                        </div>
                                        <small class="text-muted">Basé sur 8h/jour (weekend inclus)</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Budget (€)</label>
                                        <div class="input-group">
                                            <input type="number" name="budget" step="0.01" min="0"
                                                   class="form-control @error('budget') is-invalid @enderror"
                                                   value="{{ old('budget') }}" placeholder="0.00">
                                            <div class="input-group-append">
                                                <span class="input-group-text">€</span>
                                            </div>
                                            @error('budget')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Frais de dossier (€)</label>
                                        <div class="input-group">
                                            <input type="number" name="frais_dossier" step="0.01" min="0"
                                                   class="form-control @error('frais_dossier') is-invalid @enderror"
                                                   value="{{ old('frais_dossier') }}" placeholder="0.00">
                                            <div class="input-group-append">
                                                <span class="input-group-text">€</span>
                                            </div>
                                            @error('frais_dossier')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                          rows="4" placeholder="Description détaillée du dossier...">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Document (facultatif)</label>
                                        <input type="file" name="document" class="form-control-file @error('document') is-invalid @enderror"
                                               accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                                        <small class="text-muted">PDF, Word, Excel, Images - Max 5Mo</small>
                                        @error('document')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Notes</label>
                                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
                                          rows="3" placeholder="Notes complémentaires...">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="text-right">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save"></i> Créer le Dossier
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/bundles/select2/dist/css/select2.min.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('assets/bundles/select2/dist/js/select2.full.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Initialiser Select2
    $('.select2').select2({
        placeholder: "Sélectionner...",
        allowClear: true
    });

    // Validation du formulaire
    $('#dossier-form').on('submit', function(e) {
        var nom = $('input[name="nom"]').val().trim();
        var client = $('select[name="client_id"]').val();
        var type = $('select[name="type_dossier"]').val();
        var statut = $('select[name="statut"]').val();
        var dateOuverture = $('input[name="date_ouverture"]').val();

        if (!nom || !client || !type || !statut || !dateOuverture) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Champs obligatoires',
                text: 'Veuillez remplir tous les champs obligatoires (*)',
            });
            return false;
        }

        // Validation des dates
        var dateCloturePrevue = $('input[name="date_cloture_prevue"]').val();
        var dateClotureReelle = $('input[name="date_cloture_reelle"]').val();

        if (dateCloturePrevue && dateCloturePrevue < dateOuverture) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Date invalide',
                text: 'La date de clôture prévue doit être postérieure à la date d\'ouverture',
            });
            return false;
        }

        if (dateClotureReelle && dateClotureReelle < dateOuverture) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Date invalide',
                text: 'La date de clôture réelle doit être postérieure à la date d\'ouverture',
            });
            return false;
        }
    });

    // Génération automatique de référence
    $('input[name="nom"]').on('blur', function() {
        if (!$('input[name="reference"]').val() || $('input[name="reference"]').val().startsWith('DOS-')) {
            var nom = $(this).val().substring(0, 3).toUpperCase();
            var date = new Date().toISOString().slice(2, 10).replace(/-/g, '');
            $('input[name="reference"]').val('DOS-' + nom + '-' + date);
        }
    });

    function calculerHeuresTheoriques() {
    const dateDebut = $('input[name="date_ouverture"]').val();
    const dateFin = $('input[name="date_cloture_prevue"]').val();

    if (!dateDebut || !dateFin) return;

    const start = new Date(dateDebut);
    const end = new Date(dateFin);

    if (end < start) return;

    let totalJours = 0;
    let joursOuvrables = 0;

    let current = new Date(start);

    while (current <= end) {
        totalJours++;

        const day = current.getDay(); // 0 = Dimanche, 6 = Samedi
        if (day !== 0 && day !== 6) {
            joursOuvrables++;
        }

        current.setDate(current.getDate() + 1);
    }

    const heuresAvecWeekend = totalJours * 8;
    const heuresSansWeekend = joursOuvrables * 8;

    // Ne pas écraser si l'utilisateur a déjà modifié
    if (!$('#heure_avec_weekend').is(':focus')) {
        $('#heure_avec_weekend').val(heuresAvecWeekend);
    }

    if (!$('#heure_sans_weekend').is(':focus')) {
        $('#heure_sans_weekend').val(heuresSansWeekend);
    }
}
    $('input[name="date_ouverture"], input[name="date_cloture_prevue"]').on('change', calculerHeuresTheoriques);
});
</script>
@endpush
