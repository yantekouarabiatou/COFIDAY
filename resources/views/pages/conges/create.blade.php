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

                        <!-- Information sur le calcul -->
                        <div class="alert alert-info">
                            <i class="fas fa-calculator"></i>
                            <strong>Mode de calcul :</strong>
                            <ul class="mb-0 mt-1">
                                <li>Seuls les <strong>jours ouvrés (lundi à vendredi)</strong> sont comptabilisés</li>
                                <li>Les <strong>week-ends (samedi et dimanche)</strong> sont exclus du calcul</li>
                                <li>Les <strong>jours fériés</strong> sont exclus du calcul</li>
                                <li>Le nombre de jours affiché correspond aux <strong>jours ouvrés uniquement</strong></li>
                            </ul>
                        </div>

                        <form action="{{ route('conges.store') }}" method="POST" id="demande-form">
                            @csrf

                            <!-- Champ hidden pour envoyer le nombre de jours calculé -->
                            <input type="hidden" name="nombre_jours" id="nombre_jours_hidden" value="{{ old('nombre_jours') }}">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Type de congé <span class="text-danger">*</span></label>
                                        <select name="type_conge_id" id="type_conge_id"
                                                class="form-control select2 @error('type_conge_id') is-invalid @enderror"
                                                required>
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
                                            <input type="number" id="nombre_jours_display"
                                                   class="form-control-plaintext"
                                                   value="{{ old('nombre_jours', '0') }}"
                                                   readonly>
                                            <div class="input-group-append">
                                                <span class="input-group-text">jours ouvrés</span>
                                            </div>
                                        </div>
                                        <small class="form-text text-muted">
                                            Calculé automatiquement (jours ouvrés uniquement)
                                        </small>
                                        @error('nombre_jours')
                                            <div class="text-danger small">{{ $message }}</div>
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
                                               required>
                                        @error('date_debut')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div id="date_debut_info" class="text-info small mt-1" style="display: none;"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Date de fin <span class="text-danger">*</span></label>
                                        <input type="date" name="date_fin" id="date_fin"
                                               class="form-control @error('date_fin') is-invalid @enderror"
                                               value="{{ old('date_fin') }}"
                                               min="{{ date('Y-m-d') }}"
                                               required>
                                        @error('date_fin')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div id="date_fin_info" class="text-info small mt-1" style="display: none;"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="superieur_hierarchique_id">
                                            Supérieur hiérarchique <span class="text-danger">*</span>
                                        </label>
                                        <select
                                            name="superieur_hierarchique_id"
                                            id="superieur_hierarchique_id"
                                            class="form-control select2 @error('superieur_hierarchique_id') is-invalid @enderror"
                                            required
                                        >
                                            <option value="">Sélectionner un supérieur</option>
                                            @foreach($users as $u)
                                                @if($u->id !== auth()->id())
                                                    <option
                                                        value="{{ $u->id }}"
                                                        {{ old('superieur_hierarchique_id') == $u->id ? 'selected' : '' }}
                                                    >
                                                        {{ $u->prenom }} {{ $u->nom }} — {{ $u->email }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">
                                            Vous pouvez rechercher par nom, prénom ou email
                                        </small>
                                        @error('superieur_hierarchique_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Motif</label>
                                        <textarea name="motif" class="form-control @error('motif') is-invalid @enderror"
                                                required rows="3" placeholder="Raison de votre demande de congé...">{{ old('motif') }}</textarea>
                                        <small class="form-text text-muted">
                                            Maximum 1000 caractères
                                        </small>
                                        @error('motif')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Informations sur la période -->
                            <div id="periode-info" class="alert alert-light mt-3" style="display: none;">
                                <i class="fas fa-info-circle text-primary"></i>
                                <strong>Informations sur la période :</strong>
                                <div id="jours-details" class="mt-1"></div>
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
    #jours-details ul {
        margin-bottom: 0;
    }
    .form-control-plaintext {
        background-color: #f8f9fa;
        padding: 0.375rem 0.75rem;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        font-weight: 500;
        color: #495057;
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

    // Charger les jours fériés
    var joursFeries = [];
    loadJoursFeries();
});

// Charger les jours fériés depuis le serveur
function loadJoursFeries() {
    $.ajax({
        url: '{{ route("conges.get-feries") }}',
        method: 'GET',
        success: function(data) {
            joursFeries = data.jours_feries || [];
        },
        error: function() {
            joursFeries = [];
        }
    });
}

// Vérifier si un jour est un week-end
function isWeekend(dateStr) {
    var date = new Date(dateStr);
    var day = date.getDay(); // 0 = dimanche, 6 = samedi
    return day === 0 || day === 6;
}

// Vérifier si un jour est férié
function isJourFerie(dateStr) {
    if (!joursFeries || !Array.isArray(joursFeries)) return false;
    
    var date = new Date(dateStr);
    var mois = (date.getMonth() + 1).toString().padStart(2, '0');
    var jour = date.getDate().toString().padStart(2, '0');
    var dateFormatted = mois + '-' + jour;
    
    return joursFeries.some(function(ferie) {
        return ferie.date === dateFormatted;
    });
}

// Obtenir le nom d'un jour férié
function getNomJourFerie(dateStr) {
    if (!joursFeries || !Array.isArray(joursFeries)) return 'Jour férié';
    
    var date = new Date(dateStr);
    var mois = (date.getMonth() + 1).toString().padStart(2, '0');
    var jour = date.getDate().toString().padStart(2, '0');
    var dateFormatted = mois + '-' + jour;
    
    var ferie = joursFeries.find(function(f) {
        return f.date === dateFormatted;
    });
    
    return ferie ? ferie.nom : 'Jour férié';
}

// Obtenir le nom du jour de la semaine
function getJourSemaine(dateStr) {
    var jours = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
    var date = new Date(dateStr);
    return jours[date.getDay()];
}

// Formater une date en français
function formatDateFr(dateStr) {
    var date = new Date(dateStr);
    return date.toLocaleDateString('fr-FR', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// Analyser la période sélectionnée
function analyzePeriod() {
    var dateDebut = $('#date_debut').val();
    var dateFin = $('#date_fin').val();
    
    // Masquer les infos précédentes
    $('#date_debut_info').hide().empty();
    $('#date_fin_info').hide().empty();
    $('#periode-info').hide();
    $('#jours-details').empty();
    $('#nombre_jours_display').val('0');
    $('#nombre_jours_hidden').val('0');
    $('#preview-card').hide();
    $('#submit-btn').prop('disabled', true);
    
    if (!dateDebut || !dateFin) {
        return false;
    }
    
    // Vérifier que la date de fin est après la date de début
    if (new Date(dateFin) < new Date(dateDebut)) {
        $('#date_fin_info').text('La date de fin doit être postérieure à la date de début').show();
        return false;
    }
    
    // Afficher des infos sur les dates sélectionnées
    var debutJour = getJourSemaine(dateDebut);
    var finJour = getJourSemaine(dateFin);
    
    if (isWeekend(dateDebut)) {
        $('#date_debut_info').text(debutJour + ' (week-end - non comptabilisé)').show();
    } else if (isJourFerie(dateDebut)) {
        var nomFerie = getNomJourFerie(dateDebut);
        $('#date_debut_info').text(debutJour + ' (' + nomFerie + ' - non comptabilisé)').show();
    }
    
    if (isWeekend(dateFin)) {
        $('#date_fin_info').text(finJour + ' (week-end - non comptabilisé)').show();
    } else if (isJourFerie(dateFin)) {
        var nomFerie = getNomJourFerie(dateFin);
        $('#date_fin_info').text(finJour + ' (' + nomFerie + ' - non comptabilisé)').show();
    }
    
    // Analyser tous les jours de la période
    var startDate = new Date(dateDebut);
    var endDate = new Date(dateFin);
    var joursOuvrables = 0;
    var joursNonOuvrables = [];
    var currentDate = new Date(startDate);
    
    while (currentDate <= endDate) {
        var dateStr = currentDate.toISOString().split('T')[0];
        var jourSemaine = getJourSemaine(dateStr);
        
        if (isWeekend(dateStr)) {
            joursNonOuvrables.push({
                date: formatDateFr(dateStr),
                raison: 'Week-end (' + jourSemaine + ')',
                type: 'weekend'
            });
        } else if (isJourFerie(dateStr)) {
            var nomFerie = getNomJourFerie(dateStr);
            joursNonOuvrables.push({
                date: formatDateFr(dateStr),
                raison: nomFerie,
                type: 'ferie'
            });
        } else {
            joursOuvrables++;
        }
        
        currentDate.setDate(currentDate.getDate() + 1);
    }
    
    // Mettre à jour le nombre de jours ouvrés
    if (joursOuvrables > 0) {
        $('#nombre_jours_display').val(joursOuvrables);
        $('#nombre_jours_hidden').val(joursOuvrables);
        $('#submit-btn').prop('disabled', false);
        
        // Afficher les détails de la période
        var totalJours = Math.floor((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
        var message = '<div class="mb-2">';
        message += '<strong>Période totale :</strong> ' + totalJours + ' jour(s) calendaire(s)<br>';
        message += '<strong>Jours ouvrés :</strong> ' + joursOuvrables + ' jour(s)<br>';
        message += '<strong>Jours exclus :</strong> ' + joursNonOuvrables.length + ' jour(s)</div>';
        
        if (joursNonOuvrables.length > 0) {
            message += '<details>';
            message += '<summary class="text-primary"><small>Voir les jours exclus du calcul</small></summary>';
            message += '<ul class="mt-2 mb-0 pl-3">';
            joursNonOuvrables.forEach(function(jour) {
                var icon = jour.type === 'weekend' ? '🏖️' : '🎉';
                message += '<li><small>' + icon + ' ' + jour.date + ' (' + jour.raison + ')</small></li>';
            });
            message += '</ul>';
            message += '</details>';
        }
        
        $('#jours-details').html(message);
        $('#periode-info').show();
        
        // Mettre à jour la prévisualisation
        updatePreview();
        checkSolde();
        
        return true;
    } else {
        $('#jours-details').html('La période sélectionnée ne contient aucun jour ouvrable. Veuillez modifier les dates.');
        $('#periode-info').show();
        return false;
    }
}

// Mettre à jour la prévisualisation
function updatePreview() {
    var typeText = $('#type_conge_id option:selected').text();
    var dateDebut = $('#date_debut').val();
    var dateFin = $('#date_fin').val();
    var nombreJours = $('#nombre_jours_hidden').val();
    var selectedOption = $('#type_conge_id option:selected');
    var estPaye = selectedOption.data('est-paye');
    var estAnnuel = selectedOption.data('est-annuel');
    var soldeRestant = {{ isset($solde) ? $solde->jours_restants : 0 }};

    if (!typeText || !dateDebut || !dateFin || !nombreJours) {
        $('#preview-card').hide();
        return;
    }

    $('#preview-type').html(typeText);
    $('#preview-period').html(formatDateFr(dateDebut) + ' au ' + formatDateFr(dateFin));
    $('#preview-duration').html(nombreJours + ' jour(s) ouvrable(s)');

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

// Vérifier le solde
function checkSolde() {
    var typeCongeId = $('#type_conge_id').val();
    var nombreJours = parseFloat($('#nombre_jours_hidden').val()) || 0;

    if (!typeCongeId || !nombreJours) {
        $('#submit-btn').prop('disabled', true);
        return;
    }

    var selectedOption = $('#type_conge_id option:selected');
    var estAnnuel = selectedOption.data('est-annuel');
    var soldeRestant = {{ isset($solde) ? $solde->jours_restants : 0 }};
    var maxJours = selectedOption.data('max-jours');

    // Vérifier le maximum de jours
    if (maxJours && nombreJours > maxJours) {
        $('#submit-btn').prop('disabled', true);
        Swal.fire({
            icon: 'warning',
            title: 'Dépassement',
            text: 'Le nombre de jours demandé (' + nombreJours + ') dépasse le maximum autorisé (' + maxJours + ' jours) pour ce type de congé.',
        });
        return;
    }

    // Vérifier uniquement pour les congés annuels
    if (estAnnuel == '1') {
        if (nombreJours > soldeRestant) {
            $('#submit-btn').prop('disabled', true);
            
            var typeText = selectedOption.text().replace(/<[^>]*>/g, '');
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
                      '<li>Choisir un autre type de congé</li>' +
                      '</ul>' +
                      '</div>',
                confirmButtonText: 'Compris'
            });
        } else {
            $('#submit-btn').prop('disabled', false);
        }
    } else {
        // Pour tous les autres types, activer le bouton si tout est valide
        $('#submit-btn').prop('disabled', false);
    }
}

// Validation du formulaire
$('#demande-form').on('submit', function(e) {
    e.preventDefault();
    
    var typeCongeId = $('#type_conge_id').val();
    var dateDebut = $('#date_debut').val();
    var dateFin = $('#date_fin').val();
    var nombreJours = parseFloat($('#nombre_jours_hidden').val()) || 0;
    var selectedOption = $('#type_conge_id option:selected');
    var estAnnuel = selectedOption.data('est-annuel');
    var maxJours = selectedOption.data('max-jours');
    var soldeRestant = {{ isset($solde) ? $solde->jours_restants : 0 }};
    
    // Validation basique
    if (!typeCongeId || !dateDebut || !dateFin || !nombreJours) {
        Swal.fire({
            icon: 'error',
            title: 'Champs obligatoires',
            text: 'Veuillez remplir tous les champs obligatoires',
        });
        return false;
    }
    
    // Vérifier la date de fin
    if (new Date(dateFin) < new Date(dateDebut)) {
        Swal.fire({
            icon: 'error',
            title: 'Dates invalides',
            text: 'La date de fin doit être postérieure à la date de début',
        });
        return false;
    }
    
    // Vérifier si au moins 1 jour ouvrable
    if (nombreJours <= 0) {
        Swal.fire({
            icon: 'error',
            title: 'Durée invalide',
            text: 'La période sélectionnée ne contient aucun jour ouvrable.',
        });
        return false;
    }
    
    // Vérifier le maximum de jours
    if (maxJours && nombreJours > maxJours) {
        Swal.fire({
            icon: 'error',
            title: 'Dépassement',
            text: 'Le nombre de jours demandé dépasse le maximum autorisé pour ce type de congé.',
        });
        return false;
    }
    
    // Vérifier le solde uniquement pour les congés annuels
    if (estAnnuel == '1' && nombreJours > soldeRestant) {
        Swal.fire({
            icon: 'error',
            title: 'Solde insuffisant',
            html: 'Vous n\'avez pas assez de jours de congés annuels disponibles.<br>' +
                  'Solde restant : <strong>' + soldeRestant + ' jours</strong><br>' +
                  'Demandé : <strong>' + nombreJours + ' jours</strong>',
        });
        return false;
    }
    
    // Demander confirmation avec détails du calcul
    var totalJours = Math.floor((new Date(dateFin) - new Date(dateDebut)) / (1000 * 60 * 60 * 24)) + 1;
    var confirmationMessage = '<div class="text-left">';
    confirmationMessage += '<p><strong>Type :</strong> ' + $('#type_conge_id option:selected').text() + '</p>';
    confirmationMessage += '<p><strong>Période calendaire :</strong> ' + formatDateFr(dateDebut) + ' au ' + formatDateFr(dateFin) + ' (' + totalJours + ' jours)</p>';
    confirmationMessage += '<p><strong>Jours ouvrés pris :</strong> ' + nombreJours + ' jour(s)</p>';
    
    if (estAnnuel == '1') {
        var nouveauSolde = soldeRestant - nombreJours;
        confirmationMessage += '<p><strong>Impact sur solde annuel :</strong> ' + soldeRestant + ' → ' + nouveauSolde + ' jours</p>';
    }
    
    confirmationMessage += '<hr><p class="text-muted"><small><i class="fas fa-info-circle"></i> Les week-ends et jours fériés ne sont pas comptabilisés dans le calcul.</small></p>';
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
        reverseButtons: true,
        width: 600
    }).then((result) => {
        if (result.isConfirmed) {
            // Soumettre le formulaire
            $(this).off('submit').submit();
        }
    });

    return false;
});

// Événements de changement
$('#date_debut, #date_fin').on('change', function() {
    analyzePeriod();
});

$('#type_conge_id').on('change', function() {
    if ($('#date_debut').val() && $('#date_fin').val()) {
        checkSolde();
        updatePreview();
    }
});

// Initialiser l'affichage si des valeurs existent déjà (en cas d'erreur de validation)
$(document).ready(function() {
    if ($('#date_debut').val() && $('#date_fin').val()) {
        setTimeout(function() {
            analyzePeriod();
        }, 500);
    }
});
</script>
@endpush