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

                        {{-- ── Bloc solde multi-années ──────────────────────────────────── --}}
                        @if(isset($soldesParAnnee) && $soldesParAnnee->count() > 0)
                        <div class="alert alert-info">
                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                <div>
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Votre solde de congés disponible :</strong>
                                </div>
                                <div>
                                    <span class="badge badge-success badge-lg" style="font-size:1rem; padding:.5rem .9rem;">
                                        <i class="fas fa-coins"></i>
                                        {{ $totalJoursDisponibles }} jours disponibles (toutes années)
                                    </span>
                                </div>
                            </div>

                            {{-- Détail par année --}}
                            <div class="mt-3">
                                <strong><i class="fas fa-layer-group"></i> Détail par année (ordre de déduction) :</strong>
                                <div class="row mt-2">
                                    @foreach($soldesParAnnee as $s)
                                    <div class="col-md-3 col-sm-6 mb-2">
                                        <div class="card mb-0 border
                                            {{ $s->annee == now()->year ? 'border-primary' : 'border-secondary' }}">
                                            <div class="card-body p-2 text-center">
                                                <div class="font-weight-bold
                                                    {{ $s->annee == now()->year ? 'text-primary' : 'text-muted' }}">
                                                    {{ $s->annee }}
                                                    @if($s->annee == now()->year)
                                                        <span class="badge badge-primary" style="font-size:.65rem;">Courante</span>
                                                    @else
                                                        <span class="badge badge-warning" style="font-size:.65rem;">Prioritaire</span>
                                                    @endif
                                                </div>
                                                <div class="text-success font-weight-bold" style="font-size:1.1rem;">
                                                    {{ $s->jours_restants }} j.
                                                </div>
                                                <small class="text-muted">restants</small>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-sort-amount-up"></i>
                                    Les années les plus anciennes sont déduites en priorité.
                                </small>
                            </div>

                            @if($totalJoursDisponibles <= 0)
                            <div class="mt-2 text-danger">
                                <i class="fas fa-exclamation-triangle"></i>
                                Votre solde total est épuisé. Vous pouvez uniquement demander des congés non payés.
                            </div>
                            @elseif($totalJoursDisponibles < 5)
                            <div class="mt-2 text-warning">
                                <i class="fas fa-exclamation-circle"></i>
                                Attention : il ne vous reste que <strong>{{ $totalJoursDisponibles }}</strong> jours au total.
                            </div>
                            @endif
                        </div>
                        @elseif(isset($solde))
                        {{-- Fallback : affichage simple si soldesParAnnee n'est pas transmis --}}
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Solde {{ now()->year }} :</strong>
                            <span class="badge badge-success">{{ $solde->jours_acquis }} acquis</span>
                            <span class="badge badge-warning">{{ $solde->jours_pris }} pris</span>
                            <span class="badge badge-primary">{{ $solde->jours_restants }} restants</span>
                        </div>
                        @endif

                        {{-- ── Info calcul ───────────────────────────────────────────────── --}}
                        <div class="alert alert-light border-left border-info">
                            <i class="fas fa-calculator text-info"></i>
                            <strong>Mode de calcul :</strong>
                            seuls les <strong>jours ouvrés (lun.–ven.)</strong> hors week-ends et jours fériés
                            sont comptabilisés.
                        </div>

                        {{-- ── Formulaire ────────────────────────────────────────────────── --}}
                        <form action="{{ route('conges.store') }}" method="POST" id="demande-form">
                            @csrf
                            <input type="hidden" name="nombre_jours" id="nombre_jours_hidden"
                                   value="{{ old('nombre_jours') }}">

                            <div class="row">
                                {{-- Type de congé --}}
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
                                                        (Annuel — déduit du solde)
                                                    @elseif($type->est_paye)
                                                        (Payé — non déduit)
                                                    @else
                                                        (Non payé)
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('type_conge_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Nombre de jours --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nombre de jours ouvrés</label>
                                        <div class="input-group">
                                            <input type="text" id="nombre_jours_display"
                                                   class="form-control" value="0" readonly>
                                            <div class="input-group-append">
                                                <span class="input-group-text">jours ouvrés</span>
                                            </div>
                                        </div>
                                        <small class="form-text text-muted">Calculé automatiquement</small>
                                        @error('nombre_jours')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                {{-- Date début --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Date de début <span class="text-danger">*</span></label>
                                        <input type="date" name="date_debut" id="date_debut"
                                               class="form-control @error('date_debut') is-invalid @enderror"
                                               value="{{ old('date_debut') }}"
                                               min="{{ date('Y-m-d') }}" required>
                                        @error('date_debut')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div id="date_debut_info" class="text-info small mt-1" style="display:none;"></div>
                                    </div>
                                </div>

                                {{-- Date fin --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Date de fin <span class="text-danger">*</span></label>
                                        <input type="date" name="date_fin" id="date_fin"
                                               class="form-control @error('date_fin') is-invalid @enderror"
                                               value="{{ old('date_fin') }}"
                                               min="{{ date('Y-m-d') }}" required>
                                        @error('date_fin')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div id="date_fin_info" class="text-info small mt-1" style="display:none;"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                {{-- Supérieur --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Supérieur hiérarchique <span class="text-danger">*</span></label>
                                        <select name="superieur_hierarchique_id" id="superieur_hierarchique_id"
                                                class="form-control select2 @error('superieur_hierarchique_id') is-invalid @enderror"
                                                required>
                                            <option value="">Sélectionner un supérieur</option>
                                            @foreach($users as $u)
                                                @if($u->id !== auth()->id())
                                                    <option value="{{ $u->id }}"
                                                        {{ old('superieur_hierarchique_id') == $u->id ? 'selected' : '' }}>
                                                        {{ $u->prenom }} {{ $u->nom }} — {{ $u->email }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                        @error('superieur_hierarchique_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Motif --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Motif <span class="text-danger">*</span></label>
                                        <textarea name="motif"
                                                  class="form-control @error('motif') is-invalid @enderror"
                                                  rows="3" required
                                                  placeholder="Raison de votre demande...">{{ old('motif') }}</textarea>
                                        @error('motif')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Détails période --}}
                            <div id="periode-info" class="alert alert-light mt-3" style="display:none;">
                                <i class="fas fa-info-circle text-primary"></i>
                                <strong>Informations sur la période :</strong>
                                <div id="jours-details" class="mt-1"></div>
                            </div>

                            {{-- Simulation déduction multi-années --}}
                            <div id="deduction-preview" class="alert alert-warning mt-3" style="display:none;">
                                <i class="fas fa-layer-group"></i>
                                <strong>Simulation de déduction sur vos soldes :</strong>
                                <div id="deduction-details" class="mt-2"></div>
                            </div>

                            {{-- Récapitulatif --}}
                            <div class="card border-primary" id="preview-card" style="display:none;">
                                <div class="card-header bg-light">
                                    <h4 class="mb-0"><i class="fas fa-clipboard-check"></i> Récapitulatif</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>Type :</strong> <span id="preview-type"></span>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Période :</strong> <span id="preview-period"></span>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Durée :</strong> <span id="preview-duration"></span>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-12">
                                            <strong>Impact sur le solde :</strong>
                                            <span id="preview-solde"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        {{-- Avertissement indicatif max jours (non bloquant) --}}
                        <div id="info-max-jours" class="alert alert-warning py-2" style="display:none;"></div>

                        <div class="text-right mt-3">
                                <button type="submit" class="btn btn-primary btn-lg" id="submit-btn" disabled>
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
    .border-left { border-left: 4px solid !important; }
    .border-info  { border-color: #17a2b8 !important; }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/bundles/select2/dist/js/select2.full.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@php
    // Sérialisation PHP → JS propre (pas de closures dans @json)
    $soldesParAnneeJson = isset($soldesParAnnee)
        ? $soldesParAnnee->map(fn($s) => ['annee' => $s->annee, 'jours_restants' => (float)$s->jours_restants])->values()->toArray()
        : [];

    $totalJoursDisponiblesVal = isset($totalJoursDisponibles) ? (float)$totalJoursDisponibles : 0;
@endphp

<script>
$(document).ready(function () {

    // ── Données PHP → JS ───────────────────────────────────────────────────
    var soldesParAnnee        = @json($soldesParAnneeJson);     // [{annee, jours_restants}, …]
    var totalJoursDisponibles = @json($totalJoursDisponiblesVal);
    var routeCreate           = @json(route('conges.create'));
    var joursFeries           = [];

    // ── Select2 ────────────────────────────────────────────────────────────
    $('.select2').select2({ placeholder: 'Sélectionner…', allowClear: true });

    // ── Jours fériés ───────────────────────────────────────────────────────
    $.ajax({
        url: '{{ route("conges.get-feries") }}',
        method: 'GET',
        success: function (data) { joursFeries = data.jours_feries || []; },
        error:   function ()     { joursFeries = []; }
    });

    // ── Helpers dates ──────────────────────────────────────────────────────
    function isWeekend(dateStr) {
        var d = new Date(dateStr);
        return d.getDay() === 0 || d.getDay() === 6;
    }

    function isJourFerie(dateStr) {
        var d  = new Date(dateStr);
        var mm = String(d.getMonth() + 1).padStart(2, '0');
        var jj = String(d.getDate()).padStart(2, '0');
        var fmt = mm + '-' + jj;
        return joursFeries.some(function (f) { return f.date === fmt; });
    }

    function getNomJourFerie(dateStr) {
        var d   = new Date(dateStr);
        var mm  = String(d.getMonth() + 1).padStart(2, '0');
        var jj  = String(d.getDate()).padStart(2, '0');
        var fmt = mm + '-' + jj;
        var f   = joursFeries.find(function (f) { return f.date === fmt; });
        return f ? f.nom : 'Jour férié';
    }

    function getJourSemaine(dateStr) {
        var j = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
        return j[new Date(dateStr).getDay()];
    }

    function formatDateFr(dateStr) {
        return new Date(dateStr).toLocaleDateString('fr-FR', {
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
        });
    }

    // ── Simulation de déduction FIFO ───────────────────────────────────────
    /**
     * Simule comment les jours seraient déduits des soldes (du plus ancien au plus récent).
     * Retourne un tableau [{annee, jours_pris, jours_restants_apres}, …]
     */
    function simulerDeductionFIFO(joursADeduire) {
        var reste  = joursADeduire;
        var result = [];

        // Trier du plus ancien au plus récent (comme côté PHP)
        var soldesTries = soldesParAnnee.slice().sort(function (a, b) { return a.annee - b.annee; });

        soldesTries.forEach(function (s) {
            if (reste <= 0) return;
            var pris  = Math.min(s.jours_restants, reste);
            reste    -= pris;
            result.push({
                annee:                s.annee,
                jours_pris:           pris,
                jours_restants_apres: s.jours_restants - pris
            });
        });

        return result;
    }

    // ── Analyse de la période ──────────────────────────────────────────────
    function analyzePeriod() {
        var dateDebut = $('#date_debut').val();
        var dateFin   = $('#date_fin').val();

        // Reset
        $('#date_debut_info, #date_fin_info').hide().empty();
        $('#periode-info').hide();
        $('#jours-details').empty();
        $('#deduction-preview').hide();
        $('#deduction-details').empty();
        $('#nombre_jours_display').val('0');
        $('#nombre_jours_hidden').val('0');
        $('#preview-card').hide();
        $('#submit-btn').prop('disabled', true);

        if (!dateDebut || !dateFin) return false;

        if (new Date(dateFin) < new Date(dateDebut)) {
            $('#date_fin_info').text('La date de fin doit être postérieure à la date de début').show();
            return false;
        }

        // Infos sur les dates de début / fin
        if (isWeekend(dateDebut)) {
            $('#date_debut_info').text(getJourSemaine(dateDebut) + ' (week-end – non comptabilisé)').show();
        } else if (isJourFerie(dateDebut)) {
            $('#date_debut_info').text(getJourSemaine(dateDebut) + ' (' + getNomJourFerie(dateDebut) + ' – non comptabilisé)').show();
        }

        if (isWeekend(dateFin)) {
            $('#date_fin_info').text(getJourSemaine(dateFin) + ' (week-end – non comptabilisé)').show();
        } else if (isJourFerie(dateFin)) {
            $('#date_fin_info').text(getJourSemaine(dateFin) + ' (' + getNomJourFerie(dateFin) + ' – non comptabilisé)').show();
        }

        // Compter les jours ouvrés
        var startDate         = new Date(dateDebut);
        var endDate           = new Date(dateFin);
        var joursOuvrables    = 0;
        var joursNonOuvrables = [];
        var current           = new Date(startDate);

        while (current <= endDate) {
            var ds = current.toISOString().split('T')[0];
            if (isWeekend(ds)) {
                joursNonOuvrables.push({ date: formatDateFr(ds), raison: 'Week-end', type: 'weekend' });
            } else if (isJourFerie(ds)) {
                joursNonOuvrables.push({ date: formatDateFr(ds), raison: getNomJourFerie(ds), type: 'ferie' });
            } else {
                joursOuvrables++;
            }
            current.setDate(current.getDate() + 1);
        }

        if (joursOuvrables <= 0) {
            $('#jours-details').html('La période ne contient aucun jour ouvrable. Veuillez modifier les dates.');
            $('#periode-info').show();
            return false;
        }

        $('#nombre_jours_display').val(joursOuvrables);
        $('#nombre_jours_hidden').val(joursOuvrables);
        $('#submit-btn').prop('disabled', false);

        // Afficher détails
        var total   = Math.floor((endDate - startDate) / 864e5) + 1;
        var message = '<strong>Période totale :</strong> ' + total + ' j. calendaires — '
                    + '<strong>Jours ouvrés :</strong> ' + joursOuvrables + ' — '
                    + '<strong>Exclus :</strong> ' + joursNonOuvrables.length + ' j.';

        if (joursNonOuvrables.length > 0) {
            message += '<details class="mt-1"><summary class="text-primary"><small>Voir les jours exclus</small></summary><ul class="mt-1 mb-0 pl-3">';
            joursNonOuvrables.forEach(function (j) {
                message += '<li><small>' + (j.type === 'weekend' ? '🏖️' : '🎉') + ' ' + j.date + ' (' + j.raison + ')</small></li>';
            });
            message += '</ul></details>';
        }

        $('#jours-details').html(message);
        $('#periode-info').show();

        updatePreview();
        checkSolde();

        return true;
    }

    // ── Simulation déduction et affichage ──────────────────────────────────
    function afficherDeductionSimulee(nombreJours) {
        var selectedOption = $('#type_conge_id option:selected');
        var estAnnuel      = selectedOption.data('est-annuel');

        if (estAnnuel != '1' || nombreJours <= 0 || !soldesParAnnee.length) {
            $('#deduction-preview').hide();
            return;
        }

        var deductions = simulerDeductionFIFO(nombreJours);

        if (!deductions.length) {
            $('#deduction-preview').hide();
            return;
        }

        var html = '<div class="row">';
        deductions.forEach(function (d) {
            html += '<div class="col-md-3 col-sm-6 mb-2">'
                  + '<div class="card mb-0 border-warning">'
                  + '<div class="card-body p-2 text-center">'
                  + '<div class="font-weight-bold">' + d.annee + '</div>'
                  + '<div class="text-danger">−' + d.jours_pris + ' j.</div>'
                  + '<div class="text-success small">Reste : ' + d.jours_restants_apres.toFixed(1) + ' j.</div>'
                  + '</div></div></div>';
        });
        html += '</div>';

        $('#deduction-details').html(html);
        $('#deduction-preview').show();
    }

    // ── Prévisualisation ───────────────────────────────────────────────────
    function updatePreview() {
        var typeText       = $('#type_conge_id option:selected').text().trim();
        var dateDebut      = $('#date_debut').val();
        var dateFin        = $('#date_fin').val();
        var nombreJours    = parseFloat($('#nombre_jours_hidden').val()) || 0;
        var selectedOption = $('#type_conge_id option:selected');
        var estAnnuel      = selectedOption.data('est-annuel');
        var estPaye        = selectedOption.data('est-paye');

        if (!typeText || !dateDebut || !dateFin || !nombreJours) {
            $('#preview-card').hide();
            return;
        }

        $('#preview-type').html(typeText);
        $('#preview-period').html(formatDateFr(dateDebut) + ' au ' + formatDateFr(dateFin));
        $('#preview-duration').html(nombreJours + ' jour(s) ouvré(s)');

        if (estAnnuel == '1') {
            var nouveauTotal   = totalJoursDisponibles - nombreJours;
            var soldeClass     = nouveauTotal >= 0 ? 'text-success' : 'text-danger';
            var soldeText      = nouveauTotal >= 0
                ? 'Solde global : ' + totalJoursDisponibles + ' → ' + nouveauTotal.toFixed(1) + ' j.'
                : 'Dépassement de ' + Math.abs(nouveauTotal).toFixed(1) + ' j. par rapport au solde global';

            $('#preview-solde').html('<span class="' + soldeClass + '">' + soldeText + '</span>');
            afficherDeductionSimulee(nombreJours);
        } else if (estPaye == '1') {
            $('#preview-solde').html('<span class="text-info">Congé payé (non déduit du solde annuel)</span>');
            $('#deduction-preview').hide();
        } else {
            $('#preview-solde').html('<span class="text-secondary">Congé non payé</span>');
            $('#deduction-preview').hide();
        }

        $('#preview-card').show();
    }

    // ── Vérification du solde ──────────────────────────────────────────────
    function checkSolde() {
        var typeCongeId    = $('#type_conge_id').val();
        var nombreJours    = parseFloat($('#nombre_jours_hidden').val()) || 0;
        var selectedOption = $('#type_conge_id option:selected');
        var estAnnuel      = selectedOption.data('est-annuel');
        var maxJours       = selectedOption.data('max-jours');

        if (!typeCongeId || !nombreJours) {
            $('#submit-btn').prop('disabled', true);
            return;
        }

        // Avertissement informatif si dépassement du max du type,
        // mais on ne bloque PAS : c'est le solde global qui fait foi.
        if (maxJours && nombreJours > maxJours) {
            $('#info-max-jours')
                .html('<i class="fas fa-info-circle"></i> Note : ce type de congé a un maximum indicatif de '
                    + maxJours + ' j. Votre demande sera acceptée si votre solde global le couvre.')
                .show();
        } else {
            $('#info-max-jours').hide();
        }

        // Seul vrai blocage : solde global insuffisant (congés annuels uniquement)
        if (estAnnuel == '1') {
            if (nombreJours > totalJoursDisponibles) {
                $('#submit-btn').prop('disabled', true);
                Swal.fire({
                    icon: 'error',
                    title: 'Solde insuffisant',
                    html: '<p><strong>Solde total disponible (toutes années) :</strong> ' + totalJoursDisponibles + ' j.</p>'
                        + '<p><strong>Demandé :</strong> ' + nombreJours + ' j.</p>'
                        + '<p class="text-danger"><strong>Manquant :</strong> ' + (nombreJours - totalJoursDisponibles).toFixed(1) + ' j.</p>'
                });
            } else {
                $('#submit-btn').prop('disabled', false);
            }
        } else {
            $('#submit-btn').prop('disabled', false);
        }
    }

    // ── Soumission ─────────────────────────────────────────────────────────
    $('#demande-form').on('submit', function (e) {
        e.preventDefault();

        var typeCongeId    = $('#type_conge_id').val();
        var dateDebut      = $('#date_debut').val();
        var dateFin        = $('#date_fin').val();
        var nombreJours    = parseFloat($('#nombre_jours_hidden').val()) || 0;
        var selectedOption = $('#type_conge_id option:selected');
        var estAnnuel      = selectedOption.data('est-annuel');
        var maxJours       = selectedOption.data('max-jours');

        // Validations
        if (!typeCongeId || !dateDebut || !dateFin || !nombreJours) {
            Swal.fire({ icon: 'error', title: 'Champs obligatoires', text: 'Veuillez remplir tous les champs.' });
            return false;
        }

        if (new Date(dateFin) < new Date(dateDebut)) {
            Swal.fire({ icon: 'error', title: 'Dates invalides', text: 'La date de fin doit être postérieure à la date de début.' });
            return false;
        }

        if (nombreJours <= 0) {
            Swal.fire({ icon: 'error', title: 'Durée invalide', text: 'La période ne contient aucun jour ouvrable.' });
            return false;
        }

        // nombre_jours_max est indicatif : on ne bloque pas ici.

        if (estAnnuel == '1' && nombreJours > totalJoursDisponibles) {
            Swal.fire({
                icon: 'error',
                title: 'Solde insuffisant',
                html: 'Solde global disponible : <strong>' + totalJoursDisponibles + ' j.</strong><br>'
                    + 'Demandé : <strong>' + nombreJours + ' j.</strong>'
            });
            return false;
        }

        // Construire le résumé de déduction pour la confirmation
        var deductionHtml = '';
        if (estAnnuel == '1') {
            var deductions = simulerDeductionFIFO(nombreJours);
            if (deductions.length) {
                deductionHtml = '<hr><p><strong><i class="fas fa-layer-group"></i> Déduction par année :</strong></p><ul class="text-left">';
                deductions.forEach(function (d) {
                    deductionHtml += '<li>' + d.annee + ' : −' + d.jours_pris + ' j. (reste ' + d.jours_restants_apres.toFixed(1) + ' j.)</li>';
                });
                deductionHtml += '</ul>';
            }
        }

        var total = Math.floor((new Date(dateFin) - new Date(dateDebut)) / 864e5) + 1;
        Swal.fire({
            title: 'Confirmer la demande',
            html: '<div class="text-left">'
                + '<p><strong>Type :</strong> ' + selectedOption.text().trim() + '</p>'
                + '<p><strong>Période :</strong> ' + formatDateFr(dateDebut) + ' au ' + formatDateFr(dateFin) + ' (' + total + ' j. calendaires)</p>'
                + '<p><strong>Jours ouvrés décomptés :</strong> ' + nombreJours + ' j.</p>'
                + deductionHtml
                + '</div>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor:  '#d33',
            confirmButtonText:  'Oui, soumettre',
            cancelButtonText:   'Annuler',
            reverseButtons: true,
            width: 640
        }).then(function (result) {
            if (result.isConfirmed) {
                $('#demande-form').off('submit').submit();
            }
        });

        return false;
    });

    // ── Événements ─────────────────────────────────────────────────────────
    $('#date_debut, #date_fin').on('change', function () { analyzePeriod(); });
    $('#type_conge_id').on('change', function () {
        if ($('#date_debut').val() && $('#date_fin').val()) {
            checkSolde();
            updatePreview();
        }
    });

    // Initialiser si valeurs existantes (retour sur erreur de validation)
    if ($('#date_debut').val() && $('#date_fin').val()) {
        setTimeout(function () { analyzePeriod(); }, 500);
    }
});
</script>
@endpush
