@extends('layaout')

@section('title', 'Nouvelle Demande de Congé')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-calendar-plus"></i> Nouvelle Demande de Congé</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('conges.index') }}">Congés</a></div>
            <div class="breadcrumb-item active">Nouvelle Demande</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Informations de la Demande</h4>
                        <div class="card-header-action">
                            <a href="{{ route('conges.index') }}" class="btn btn-icon icon-left btn-danger">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Avertissement solde insuffisant -->
                        @if(isset($solde) && $solde->jours_restants <= 0)
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Votre solde de congés payés est épuisé ({{ $solde->jours_restants }} jours).
                            Seuls les congés sans solde sont disponibles.
                        </div>
                        @endif

                        <!-- Info solde -->
                        @if(isset($solde))
                        <div class="alert alert-info">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Votre solde de congés payés :</strong>
                                </div>
                                <div class="text-right">
                                    <span class="badge badge-success">{{ $solde->jours_acquis }} jours acquis</span>
                                    <span class="badge badge-warning">{{ $solde->jours_pris }} jours pris</span>
                                    <span class="badge badge-primary">{{ $solde->jours_restants }} jours restants</span>
                                </div>
                            </div>

                            @if($solde->jours_restants <= 0)
                            <div class="mt-2">
                                <i class="fas fa-exclamation-triangle text-warning"></i>
                                Votre solde de congés payés est épuisé. Vous pouvez seulement demander des congés non payés.
                            </div>
                            @elseif($solde->jours_restants < 5)
                            <div class="mt-2">
                                <i class="fas fa-info-circle text-info"></i>
                                Il vous reste seulement {{ $solde->jours_restants }} jours de congés payés.
                            </div>
                            @endif
                        </div>
                        @endif

                        <form action="{{ route('conges.store') }}" method="POST" id="demande-form">
                            @csrf

                            <!-- @role('admin')
                            <div class="form-group">
                                <label>Utilisateur <span class="text-danger">*</span></label>
                                <select name="user_id" class="form-control select2 @error('user_id') is-invalid @enderror" required>
                                    <option value="">Sélectionner un utilisateur</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->prenom }} {{ $user->nom }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            @endrole -->

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Type de congé <span class="text-danger">*</span></label>
                                        <select name="type_conge_id" id="type_conge_id"
                                    class="form-control select2 @error('type_conge_id') is-invalid @enderror"
                                    required
                                    onchange="updateTypeCongeInfo()">
                                <option value="">Sélectionner un type</option>
                                @foreach($typesConges as $type)
                                    <option value="{{ $type->id }}"
                                            data-est-paye="{{ $type->est_paye ? '1' : '0' }}"
                                            data-est-annuel="{{ $type->est_annuel ? '1' : '0' }}"
                                            data-max-jours="{{ $type->nombre_jours_max }}"
                                            {{ old('type_conge_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->libelle }}
                                        @if($type->est_annuel)
                                            <span class="text-success"> (Annuel - déduit du solde)</span>
                                        @elseif($type->est_paye)
                                            <span class="text-primary"> (Payé - non déduit)</span>
                                        @else
                                            <span class="text-warning"> (Non payé)</span>
                                        @endif
                                    </option>
                                @endforeach
</select>
                                        <small class="form-text text-muted" id="type-conge-info">
                                            Sélectionnez un type pour voir les détails
                                        </small>
                                        @error('type_conge_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nombre de jours <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" name="nombre_jours" id="nombre_jours"
                                                   class="form-control @error('nombre_jours') is-invalid @enderror"
                                                   value="{{ old('nombre_jours') }}"
                                                   step="0.5" min="0.5" max="90" readonly>
                                            <div class="input-group-append">
                                                <span class="input-group-text">jours</span>
                                            </div>
                                        </div>
                                        <small class="form-text text-muted">
                                            Calculé automatiquement en fonction des dates
                                        </small>
                                        @error('nombre_jours')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Date de début <span class="text-danger">*</span></label>
                                        <input type="date" name="date_debut" id="date_debut"
                                               class="form-control @error('date_debut') is-invalid @enderror"
                                               value="{{ old('date_debut') }}"
                                               min="{{ date('Y-m-d') }}"
                                               onchange="calculateDays()" required>
                                        @error('date_debut')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Date de fin <span class="text-danger">*</span></label>
                                        <input type="date" name="date_fin" id="date_fin"
                                               class="form-control @error('date_fin') is-invalid @enderror"
                                               value="{{ old('date_fin') }}"
                                               min="{{ date('Y-m-d') }}"
                                               onchange="calculateDays()" required>
                                        @error('date_fin')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Motif (optionnel)</label>
                                <textarea name="motif" class="form-control @error('motif') is-invalid @enderror"
                                        required  rows="3" placeholder="Raison de votre demande de congé...">{{ old('motif') }}</textarea>
                                <small class="form-text text-muted">
                                    Maximum 1000 caractères
                                </small>
                                @error('motif')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Prévisualisation -->
                            <div class="card preview-card" style="display: none;" id="preview-card">
                                <div class="card-header">
                                    <h4>Récapitulatif</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>Type :</strong>
                                            <span id="preview-type"></span>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Période :</strong>
                                            <span id="preview-period"></span>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Durée :</strong>
                                            <span id="preview-duration"></span>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <strong>Solde après congé :</strong>
                                            <span id="preview-solde"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-right">
                                <button type="submit" class="btn btn-primary btn-lg" id="submit-btn">
                                    <i class="fas fa-paper-plane"></i> Soumettre la Demande
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
<style>
    .select2-container--default .select2-selection--single {
        height: 42px !important;
        border: 1px solid #e3e6f0;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 40px;
    }
    .preview-card {
        border-left: 4px solid #3B82F6;
        margin-bottom: 20px;
    }
    .text-success {
        color: #28a745 !important;
    }
    .text-warning {
        color: #ffc107 !important;
    }
</style>
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

    // Définir la date minimale à aujourd'hui
    var today = new Date().toISOString().split('T')[0];
    $('#date_debut').attr('min', today);
    $('#date_fin').attr('min', today);
});

// Fonction pour calculer les jours ouvrés
function calculateDays() {
    var dateDebut = $('#date_debut').val();
    var dateFin = $('#date_fin').val();
    var typeCongeId = $('#type_conge_id').val();

    if (!dateDebut || !dateFin) {
        $('#preview-card').hide();
        return;
    }

    // Vérifier que la date de fin est après la date de début
    if (new Date(dateFin) < new Date(dateDebut)) {
        Swal.fire({
            icon: 'error',
            title: 'Dates invalides',
            text: 'La date de fin doit être postérieure à la date de début',
        });
        $('#date_fin').val('');
        $('#nombre_jours').val('');
        return;
    }

    // Calculer le nombre de jours (simplifié - en production, calculer les jours ouvrés)
    var start = new Date(dateDebut);
    var end = new Date(dateFin);
    var timeDiff = end.getTime() - start.getTime();
    var daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1;

    // Soustraire les weekends (simplifié)
    var weekends = 0;
    var current = new Date(start);
    while (current <= end) {
        var day = current.getDay();
        if (day === 0 || day === 6) { // Dimanche = 0, Samedi = 6
            weekends++;
        }
        current.setDate(current.getDate() + 1);
    }

    var workingDays = daysDiff - weekends;

    // Mettre à jour le champ nombre_jours
    $('#nombre_jours').val(workingDays);

    // Mettre à jour les infos du type de congé
    updateTypeCongeInfo();

    // Vérifier le solde
    verifierSolde();

    // Afficher la prévisualisation
    updatePreview();
}

// Fonction pour mettre à jour les infos du type de congé
function updateTypeCongeInfo() {
    var selectedOption = $('#type_conge_id option:selected');
    var typeCongeId = selectedOption.val();
    var estPaye = selectedOption.data('est-paye');
    var estAnnuel = selectedOption.data('est-annuel');
    var maxJours = selectedOption.data('max-jours');
    var nombreJours = parseFloat($('#nombre_jours').val()) || 0;

    if (!typeCongeId) {
        $('#type-conge-info').text('Sélectionnez un type pour voir les détails');
        return;
    }

    var infoText = '';
    if (maxJours) {
        infoText += 'Maximum ' + maxJours + ' jours. ';
    }

    // Afficher le type de congé
    if (estAnnuel == '1') {
        infoText += 'Congé annuel (déduit du solde).';
    } else if (estPaye == '1') {
        infoText += 'Congé payé (non déduit du solde).';
    } else {
        infoText += 'Congé non payé.';
    }

    $('#type-conge-info').html(infoText);

    // Vérifier si le nombre de jours dépasse le maximum
    if (maxJours && nombreJours > maxJours) {
        Swal.fire({
            icon: 'warning',
            title: 'Dépassement',
            text: 'Le nombre de jours demandé (' + nombreJours + ') dépasse le maximum autorisé (' + maxJours + ' jours) pour ce type de congé.',
        });
    }
}

// Fonction pour vérifier le solde
function verifierSolde() {
    var typeCongeId = $('#type_conge_id').val();
    var nombreJours = parseFloat($('#nombre_jours').val()) || 0;

    if (!typeCongeId || !nombreJours) {
        $('#submit-btn').prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Soumettre la Demande');
        return;
    }

    var selectedOption = $('#type_conge_id option:selected');
    var estAnnuel = selectedOption.data('est-annuel');
    var soldeRestant = {{ isset($solde) ? $solde->jours_restants : 0 }};
    var typeText = selectedOption.text().replace(/<[^>]*>/g, ''); // Retirer le HTML

    // Vérifier uniquement pour les congés annuels
    if (estAnnuel == '1') {
        if (nombreJours > soldeRestant) {
            $('#submit-btn').prop('disabled', true);
            $('#submit-btn').html('<i class="fas fa-ban"></i> Solde insuffisant');

            Swal.fire({
                icon: 'error',
                title: 'Solde insuffisant',
                html: '<div class="text-left">' +
                      '<p><strong>Type :</strong> ' + typeText + ' (Congé annuel)</p>' +
                      '<p><strong>Solde disponible :</strong> ' + soldeRestant + ' jours</p>' +
                      '<p><strong>Demandé :</strong> ' + nombreJours + ' jours</p>' +
                      '<p class="text-danger"><strong>Manquant :</strong> ' + (nombreJours - soldeRestant) + ' jours</p>' +
                      '<hr>' +
                      '<p class="mb-2"><strong>Options :</strong></p>' +
                      '<ul class="text-left">' +
                      '<li>Réduire la durée du congé</li>' +
                      '<li>Choisir un autre type de congé (ex: maladie, maternité)</li>' +
                      '</ul>' +
                      '</div>',
                confirmButtonText: 'Compris'
            });
        } else {
            $('#submit-btn').prop('disabled', false);
            $('#submit-btn').html('<i class="fas fa-paper-plane"></i> Soumettre la Demande');
        }
    } else {
        // Pour tous les autres types (payés non annuels ou non payés), activer le bouton
        $('#submit-btn').prop('disabled', false);
        $('#submit-btn').html('<i class="fas fa-paper-plane"></i> Soumettre la Demande');
    }
}

// Fonction pour mettre à jour la prévisualisation
function updatePreview() {
    var typeText = $('#type_conge_id option:selected').text();
    var dateDebut = $('#date_debut').val();
    var dateFin = $('#date_fin').val();
    var nombreJours = $('#nombre_jours').val();
    var selectedOption = $('#type_conge_id option:selected');
    var estPaye = selectedOption.data('est-paye');
    var estAnnuel = selectedOption.data('est-annuel');
    var soldeRestant = {{ isset($solde) ? $solde->jours_restants : 0 }};

    if (!typeText || !dateDebut || !dateFin || !nombreJours) {
        $('#preview-card').hide();
        return;
    }

    // Formater les dates
    var formatDate = function(dateStr) {
        var date = new Date(dateStr);
        return date.toLocaleDateString('fr-FR', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    };

    $('#preview-type').html(typeText);
    $('#preview-period').html(formatDate(dateDebut) + ' au ' + formatDate(dateFin));
    $('#preview-duration').html(nombreJours + ' jour(s)');

    // Afficher l'impact sur le solde selon le type
    if (estAnnuel == '1') {
        var nouveauSolde = soldeRestant - parseFloat(nombreJours);
        var soldeClass = nouveauSolde >= 0 ? 'text-success' : 'text-danger';
        var soldeText = nouveauSolde >= 0 ?
            nouveauSolde.toFixed(1) + ' jours restants' :
            'Dépassement de ' + Math.abs(nouveauSolde).toFixed(1) + ' jours';

        $('#preview-solde').html('<span class="' + soldeClass + '">' + soldeText + '</span>');
    } else if (estPaye == '1') {
        $('#preview-solde').html('<span class="text-info">Congé payé (non déduit du solde annuel)</span>');
    } else {
        $('#preview-solde').html('<span class="text-secondary">Congé non payé</span>');
    }

    $('#preview-card').show();
}

// Validation du formulaire
$('#demande-form').on('submit', function(e) {
    var typeCongeId = $('#type_conge_id').val();
    var dateDebut = $('#date_debut').val();
    var dateFin = $('#date_fin').val();
    var nombreJours = parseFloat($('#nombre_jours').val()) || 0;
    var selectedOption = $('#type_conge_id option:selected');
    var estAnnuel = selectedOption.data('est-annuel');
    var maxJours = selectedOption.data('max-jours');
    var soldeRestant = {{ isset($solde) ? $solde->jours_restants : 0 }};

    // Validation basique
    if (!typeCongeId || !dateDebut || !dateFin) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Champs obligatoires',
            text: 'Veuillez remplir tous les champs obligatoires (*)',
        });
        return false;
    }

    // Vérifier la date de fin
    if (new Date(dateFin) < new Date(dateDebut)) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Dates invalides',
            text: 'La date de fin doit être postérieure à la date de début',
        });
        return false;
    }

    // Vérifier si un nombre de jours est calculé
    if (!nombreJours || nombreJours <= 0) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Durée invalide',
            text: 'Veuillez vérifier vos dates pour calculer une durée valide',
        });
        return false;
    }

    // Vérifier le maximum de jours
    if (maxJours && nombreJours > maxJours) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Dépassement',
            text: 'Le nombre de jours demandé dépasse le maximum autorisé pour ce type de congé.',
        });
        return false;
    }

    // Vérifier le solde uniquement pour les congés annuels
    if (estAnnuel == '1' && nombreJours > soldeRestant) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Solde insuffisant',
            html: 'Vous n\'avez pas assez de jours de congés annuels disponibles.<br>' +
                  'Solde restant : <strong>' + soldeRestant + ' jours</strong><br>' +
                  'Demandé : <strong>' + nombreJours + ' jours</strong>',
        });
        return false;
    }

    // Demander confirmation
    e.preventDefault();

    var confirmationMessage = '<div class="text-left">' +
        '<p><strong>Type :</strong> ' + $('#type_conge_id option:selected').text() + '</p>' +
        '<p><strong>Période :</strong> ' + dateDebut + ' au ' + dateFin + '</p>' +
        '<p><strong>Durée :</strong> ' + nombreJours + ' jour(s)</p>';

    if (estAnnuel == '1') {
        var nouveauSolde = soldeRestant - nombreJours;
        confirmationMessage += '<p><strong>Nouveau solde :</strong> ' + nouveauSolde + ' jours</p>';
    }

    confirmationMessage += '</div>';

    Swal.fire({
        title: 'Confirmer la demande',
        html: confirmationMessage,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Oui, soumettre',
        cancelButtonText: 'Annuler',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Soumettre le formulaire
            $(this).off('submit').submit();
        }
    });

    return false;
});

// Événements de changement
$('#type_conge_id, #date_debut, #date_fin').on('change', function() {
    if ($('#date_debut').val() && $('#date_fin').val()) {
        calculateDays();
    } else {
        $('#nombre_jours').val('');
        $('#preview-card').hide();
        $('#submit-btn').prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Soumettre la Demande');
    }
});

// Réinitialiser le formulaire si besoin
$('.btn-danger').on('click', function(e) {
    if ($('#date_debut').val() || $('#date_fin').val() || $('#type_conge_id').val()) {
        e.preventDefault();
        Swal.fire({
            title: 'Annuler la demande ?',
            text: 'Les informations saisies seront perdues.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Oui, annuler',
            cancelButtonText: 'Continuer'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "{{ route('conges.index') }}";
            }
        });
    }
});

// Initialiser l'affichage si des valeurs existent déjà (en cas d'erreur de validation)
$(document).ready(function() {
    if ($('#date_debut').val() && $('#date_fin').val()) {
        calculateDays();
    }
});
</script>
@endpush
