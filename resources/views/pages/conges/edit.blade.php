@extends('layaout')

@section('title', 'Modifier la Demande de Congé')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-edit"></i> Modifier la Demande de Congé</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('conges.index') }}">Congés</a></div>
            <div class="breadcrumb-item"><a href="{{ route('conges.show', $demande) }}">Détails</a></div>
            <div class="breadcrumb-item active">Modifier</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Modifier la Demande</h4>
                        <div class="card-header-action">
                            <a href="{{ route('conges.show', $demande) }}" class="btn btn-icon icon-left btn-danger">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Avertissement si demande déjà traitée -->
                        @if($demande->statut !== 'en_attente')
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Cette demande a déjà été traitée ({{ $demande->statut }}) et ne peut plus être modifiée.
                            <a href="{{ route('conges.show', $demande) }}" class="btn btn-sm btn-warning ml-2">
                                <i class="fas fa-eye"></i> Voir les détails
                            </a>
                        </div>
                        @endif

                        <!-- Info solde -->
                        @php
                            $solde = \App\Models\SoldeConge::where('user_id', $demande->user_id)
                                ->where('annee', now()->year)
                                ->first();
                        @endphp

                        @if($solde)
                        <div class="alert alert-info">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Solde de congés annuels :</strong>
                                </div>
                                <div class="text-right">
                                    <span class="badge badge-success">{{ $solde->jours_acquis }} jours acquis</span>
                                    <span class="badge badge-warning">{{ $solde->jours_pris }} jours pris</span>
                                    <span class="badge badge-primary">{{ $solde->jours_restants }} jours restants</span>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Détails actuels -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5>Détails actuels de la demande</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong>Type :</strong><br>
                                        <span class="badge badge-info">{{ $demande->typeConge->libelle }}</span>
                                        @if($demande->typeConge->est_annuel)
                                            <span class="badge badge-success ml-1">Annuel</span>
                                        @elseif($demande->typeConge->est_paye)
                                            <span class="badge badge-primary ml-1">Payé</span>
                                        @else
                                            <span class="badge badge-secondary ml-1">Non payé</span>
                                        @endif
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Statut :</strong><br>
                                        @switch($demande->statut)
                                            @case('en_attente')
                                                <span class="badge badge-warning">En attente</span>
                                                @break
                                            @case('approuve')
                                                <span class="badge badge-success">Approuvé</span>
                                                @break
                                            @case('refuse')
                                                <span class="badge badge-danger">Refusé</span>
                                                @break
                                            @case('annule')
                                                <span class="badge badge-secondary">Annulé</span>
                                                @break
                                        @endswitch
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Période :</strong><br>
                                        {{ \Carbon\Carbon::parse($demande->date_debut)->format('d/m/Y') }}
                                        au {{ \Carbon\Carbon::parse($demande->date_fin)->format('d/m/Y') }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Durée :</strong><br>
                                        {{ $demande->nombre_jours }} jour(s)
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($demande->statut === 'en_attente')
                        <form action="{{ route('conges.update', $demande) }}" method="POST" id="demande-form">
                            @csrf
                            @method('PUT')

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
                                                        {{ old('type_conge_id', $demande->type_conge_id) == $type->id ? 'selected' : '' }}>
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
                                                   value="{{ old('nombre_jours', $demande->nombre_jours) }}"
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
                                               value="{{ old('date_debut', $demande->date_debut->format('Y-m-d')) }}"
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
                                               value="{{ old('date_fin', $demande->date_fin->format('Y-m-d')) }}"
                                               min="{{ date('Y-m-d') }}"
                                               onchange="calculateDays()" required>
                                        @error('date_fin')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Motif</label>
                                <textarea name="motif" class="form-control @error('motif') is-invalid @enderror"
                                        required  rows="3" placeholder="Raison de votre demande de congé...">{{ old('motif', $demande->motif) }}</textarea>
                                <small class="form-text text-muted">
                                    Maximum 1000 caractères
                                </small>
                                @error('motif')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Prévisualisation des changements -->
                            <div class="card preview-card" id="preview-card">
                                <div class="card-header">
                                    <h4>Récapitulatif des modifications</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>Type :</strong><br>
                                            <span id="preview-type">{{ $demande->typeConge->libelle }}</span>
                                            <div id="preview-type-info">
                                                @if($demande->typeConge->est_annuel)
                                                    <span class="badge badge-success">Déduit du solde</span>
                                                @elseif($demande->typeConge->est_paye)
                                                    <span class="badge badge-primary">Non déduit</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Période :</strong><br>
                                            <span id="preview-period">
                                                {{ $demande->date_debut->format('d/m/Y') }} au {{ $demande->date_fin->format('d/m/Y') }}
                                            </span>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Durée :</strong><br>
                                            <span id="preview-duration">{{ $demande->nombre_jours }} jour(s)</span>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <strong>Impact sur le solde :</strong><br>
                                            <span id="preview-solde">
                                                @if($demande->typeConge->est_annuel)
                                                    <span class="text-danger">-{{ $demande->nombre_jours }} jours (déduit)</span>
                                                @else
                                                    <span class="text-info">Aucun impact sur le solde annuel</span>
                                                @endif
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Comparaison des changements -->
                                    <div class="row mt-4 border-top pt-3">
                                        <div class="col-md-12">
                                            <h6>Comparaison :</h6>
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th></th>
                                                        <th>Actuel</th>
                                                        <th>Nouveau</th>
                                                        <th>Changement</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td><strong>Type</strong></td>
                                                        <td>{{ $demande->typeConge->libelle }}</td>
                                                        <td id="compare-type">-</td>
                                                        <td id="compare-type-change">-</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Dates</strong></td>
                                                        <td>{{ $demande->date_debut->format('d/m/Y') }} - {{ $demande->date_fin->format('d/m/Y') }}</td>
                                                        <td id="compare-dates">-</td>
                                                        <td id="compare-dates-change">-</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Durée</strong></td>
                                                        <td>{{ $demande->nombre_jours }} jours</td>
                                                        <td id="compare-duree">-</td>
                                                        <td id="compare-duree-change">-</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Impact solde</strong></td>
                                                        <td>
                                                            @if($demande->typeConge->est_annuel)
                                                                -{{ $demande->nombre_jours }} jours
                                                            @else
                                                                Aucun
                                                            @endif
                                                        </td>
                                                        <td id="compare-solde">-</td>
                                                        <td id="compare-solde-change">-</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-right">
                                <button type="button" class="btn btn-secondary btn-lg" onclick="history.back()">
                                    <i class="fas fa-arrow-left"></i> Retour
                                </button>
                                <button type="submit" class="btn btn-primary btn-lg" id="submit-btn">
                                    <i class="fas fa-save"></i> Enregistrer les modifications
                                </button>
                            </div>
                        </form>
                        @else
                        <div class="text-center py-5">
                            <i class="fas fa-lock fa-3x text-warning mb-3"></i>
                            <h4>Demande non modifiable</h4>
                            <p class="text-muted">Cette demande ne peut plus être modifiée car elle a déjà été traitée.</p>
                            <a href="{{ route('conges.show', $demande) }}" class="btn btn-primary">
                                <i class="fas fa-eye"></i> Voir les détails
                            </a>
                            <a href="{{ route('conges.index') }}" class="btn btn-secondary">
                                <i class="fas fa-list"></i> Retour à la liste
                            </a>
                        </div>
                        @endif
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
    .text-success { color: #28a745 !important; }
    .text-warning { color: #ffc107 !important; }
    .text-danger { color: #dc3545 !important; }
    .text-info { color: #17a2b8 !important; }
    .badge {
        font-size: 0.8em;
        padding: 0.35em 0.65em;
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

    // Initialiser les infos
    updateTypeCongeInfo();
    updatePreview();
});

// Variables pour stocker les valeurs originales
var originalType = "{{ $demande->typeConge->libelle }}";
var originalDateDebut = "{{ $demande->date_debut->format('Y-m-d') }}";
var originalDateFin = "{{ $demande->date_fin->format('Y-m-d') }}";
var originalDuree = {{ $demande->nombre_jours }};
var originalEstAnnuel = {{ $demande->typeConge->est_annuel ? 'true' : 'false' }};
var originalEstPaye = {{ $demande->typeConge->est_paye ? 'true' : 'false' }};
var soldeRestant = {{ $solde ? $solde->jours_restants : 0 }};

// Fonction pour calculer les jours ouvrés
function calculateDays() {
    var dateDebut = $('#date_debut').val();
    var dateFin = $('#date_fin').val();
    var typeCongeId = $('#type_conge_id').val();

    if (!dateDebut || !dateFin || !typeCongeId) {
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
        return;
    }

    // Calculer le nombre de jours (simplifié)
    var start = new Date(dateDebut);
    var end = new Date(dateFin);
    var timeDiff = end.getTime() - start.getTime();
    var daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1;

    // Soustraire les weekends (simplifié)
    var weekends = 0;
    var current = new Date(start);
    while (current <= end) {
        var day = current.getDay();
        if (day === 0 || day === 6) {
            weekends++;
        }
        current.setDate(current.getDate() + 1);
    }

    var workingDays = daysDiff - weekends;

    // Mettre à jour le champ nombre_jours
    $('#nombre_jours').val(workingDays);

    // Mettre à jour les infos
    updateTypeCongeInfo();
    updatePreview();
    verifierSolde();
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

// Fonction pour mettre à jour la prévisualisation
function updatePreview() {
    var typeText = $('#type_conge_id option:selected').text();
    var dateDebut = $('#date_debut').val();
    var dateFin = $('#date_fin').val();
    var nombreJours = $('#nombre_jours').val();
    var estPaye = $('#type_conge_id option:selected').data('est-paye');
    var estAnnuel = $('#type_conge_id option:selected').data('est-annuel');

    if (!typeText || !dateDebut || !dateFin) {
        return;
    }

    // Formater les dates
    var formatDate = function(dateStr) {
        if (!dateStr) return '-';
        var date = new Date(dateStr);
        return date.toLocaleDateString('fr-FR', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    };

    // Mettre à jour les infos de base
    $('#preview-type').html(typeText);

    var typeInfo = '';
    if (estAnnuel == '1') {
        typeInfo = '<span class="badge badge-success">Déduit du solde</span>';
    } else if (estPaye == '1') {
        typeInfo = '<span class="badge badge-primary">Non déduit</span>';
    }
    $('#preview-type-info').html(typeInfo);

    $('#preview-period').html(formatDate(dateDebut) + ' au ' + formatDate(dateFin));
    $('#preview-duration').html(nombreJours + ' jour(s)');

    // Mettre à jour l'impact sur le solde
    if (estAnnuel == '1') {
        $('#preview-solde').html('<span class="text-danger">-' + nombreJours + ' jours (déduit du solde annuel)</span>');
    } else {
        $('#preview-solde').html('<span class="text-info">Aucun impact sur le solde annuel</span>');
    }

    // Mettre à jour la comparaison
    updateComparison(typeText, dateDebut, dateFin, nombreJours, estAnnuel);
}

// Fonction pour mettre à jour la comparaison
function updateComparison(newTypeText, newDateDebut, newDateFin, newDuree, newEstAnnuel) {
    // Comparaison du type
    $('#compare-type').html(newTypeText);
    if (originalType !== newTypeText) {
        $('#compare-type-change').html('<span class="text-warning"><i class="fas fa-exchange-alt"></i> Changé</span>');
    } else {
        $('#compare-type-change').html('<span class="text-success"><i class="fas fa-check"></i> Identique</span>');
    }

    // Comparaison des dates
    var newDatesFormatted = newDateDebut ? (new Date(newDateDebut).toLocaleDateString('fr-FR') + ' - ' + new Date(newDateFin).toLocaleDateString('fr-FR')) : '-';
    $('#compare-dates').html(newDatesFormatted);
    if (originalDateDebut !== newDateDebut || originalDateFin !== newDateFin) {
        $('#compare-dates-change').html('<span class="text-warning"><i class="fas fa-exchange-alt"></i> Changé</span>');
    } else {
        $('#compare-dates-change').html('<span class="text-success"><i class="fas fa-check"></i> Identique</span>');
    }

    // Comparaison de la durée
    $('#compare-duree').html(newDuree + ' jours');
    var dureeChange = newDuree - originalDuree;
    if (dureeChange !== 0) {
        var changeText = dureeChange > 0 ?
            '<span class="text-danger">+' + dureeChange + ' jours</span>' :
            '<span class="text-success">' + dureeChange + ' jours</span>';
        $('#compare-duree-change').html('<span class="text-warning"><i class="fas fa-exchange-alt"></i> ' + changeText + '</span>');
    } else {
        $('#compare-duree-change').html('<span class="text-success"><i class="fas fa-check"></i> Identique</span>');
    }

    // Comparaison de l'impact sur le solde
    var oldSoldeImpact = originalEstAnnuel ? '-' + originalDuree + ' jours' : 'Aucun';
    var newSoldeImpact = newEstAnnuel == '1' ? '-' + newDuree + ' jours' : 'Aucun';
    $('#compare-solde').html(newSoldeImpact);

    if (originalEstAnnuel != (newEstAnnuel == '1')) {
        $('#compare-solde-change').html('<span class="text-warning"><i class="fas fa-exchange-alt"></i> Changé</span>');
    } else {
        $('#compare-solde-change').html('<span class="text-success"><i class="fas fa-check"></i> Identique</span>');
    }
}

// Fonction pour vérifier le solde
function verifierSolde() {
    var typeCongeId = $('#type_conge_id').val();
    var nombreJours = parseFloat($('#nombre_jours').val()) || 0;
    var selectedOption = $('#type_conge_id option:selected');
    var estAnnuel = selectedOption.data('est-annuel');
    var estPaye = selectedOption.data('est-paye');

    // Réinitialiser le bouton
    $('#submit-btn').prop('disabled', false);
    $('#submit-btn').html('<i class="fas fa-save"></i> Enregistrer les modifications');

    // Vérifier uniquement pour les congés annuels
    if (estAnnuel == '1') {
        var changementDuree = nombreJours - originalDuree;

        // Cas 1: Changement vers un congé annuel (était non-annuel avant)
        if (!originalEstAnnuel) {
            // Nouveau congé annuel - vérifier le solde pour toute la durée
            if (nombreJours > soldeRestant) {
                $('#submit-btn').prop('disabled', true);
                $('#submit-btn').html('<i class="fas fa-ban"></i> Solde insuffisant');

                Swal.fire({
                    icon: 'error',
                    title: 'Solde insuffisant',
                    html: '<div class="text-left">' +
                          '<p>Vous changez vers un congé annuel.</p>' +
                          '<p>Durée demandée : <strong>' + nombreJours + ' jours</strong></p>' +
                          '<p>Solde disponible : <strong>' + soldeRestant + ' jours</strong></p>' +
                          '<p class="text-danger">Manquant : <strong>' + (nombreJours - soldeRestant) + ' jours</strong></p>' +
                          '</div>',
                    confirmButtonText: 'Compris'
                });
            }
        }
        // Cas 2: Reste un congé annuel mais augmente la durée
        else if (changementDuree > 0 && changementDuree > soldeRestant) {
            $('#submit-btn').prop('disabled', true);
            $('#submit-btn').html('<i class="fas fa-ban"></i> Solde insuffisant pour l\'augmentation');

            Swal.fire({
                icon: 'error',
                title: 'Solde insuffisant',
                html: '<div class="text-left">' +
                      '<p>Augmentation de la durée : <strong>+' + changementDuree + ' jours</strong></p>' +
                      '<p>Solde disponible : <strong>' + soldeRestant + ' jours</strong></p>' +
                      '<p class="text-danger">Manquant : <strong>' + (changementDuree - soldeRestant) + ' jours</strong></p>' +
                      '</div>',
                confirmButtonText: 'Compris'
            });
        }
        // Cas 3: Réduction de durée ou solde suffisant - OK
        else {
            $('#submit-btn').prop('disabled', false);
            $('#submit-btn').html('<i class="fas fa-save"></i> Enregistrer les modifications');
        }
    } else {
        // Pour les congés non-annuels, toujours activer le bouton
        $('#submit-btn').prop('disabled', false);
        $('#submit-btn').html('<i class="fas fa-save"></i> Enregistrer les modifications');
    }
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

    // Vérifier le solde pour les congés annuels
    if (estAnnuel == '1') {
        var changementDuree = nombreJours - originalDuree;

        // Cas 1: Changement vers annuel (était non-annuel)
        if (!originalEstAnnuel) {
            if (nombreJours > soldeRestant) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Solde insuffisant',
                    text: 'Vous n\'avez pas assez de jours de congés annuels disponibles.',
                });
                return false;
            }
        }
        // Cas 2: Augmentation de durée d'un congé annuel
        else if (changementDuree > 0 && changementDuree > soldeRestant) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Solde insuffisant',
                text: 'Vous n\'avez pas assez de jours de congés annuels disponibles pour augmenter la durée.',
            });
            return false;
        }
    }

    // Demander confirmation
    e.preventDefault();
    Swal.fire({
        title: 'Confirmer la modification',
        html: '<div class="text-left">' +
              '<p>Êtes-vous sûr de vouloir modifier cette demande ?</p>' +
              '<div class="alert alert-warning">' +
              '<i class="fas fa-exclamation-triangle"></i> Cette action sera enregistrée dans l\'historique.' +
              '</div>' +
              '</div>',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Oui, modifier',
        cancelButtonText: 'Annuler'
    }).then((result) => {
        if (result.isConfirmed) {
            // Soumettre le formulaire
            $(this).off('submit').submit();
        }
    });

    return false;
});

// Écouter les changements
$('#type_conge_id, #date_debut, #date_fin').on('change', function() {
    if ($('#date_debut').val() && $('#date_fin').val() && $('#type_conge_id').val()) {
        calculateDays();
    }
});
</script>
@endpush
