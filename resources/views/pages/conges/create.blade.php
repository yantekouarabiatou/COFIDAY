@extends('layaout') <!-- ← corrige en 'layout' si c'est une faute -->

@section('title', 'Nouvelle Demande de Congé')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/bundles/select2/dist/css/select2.min.css') }}">
    <style>
        .solde-badge {
            font-size: 1.3rem;
            padding: 0.8rem 1.5rem;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .solde-detail-popover {
            max-width: 400px;
        }
        .solde-year-card {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            transition: all 0.3s;
        }
        .solde-year-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .form-section {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            padding: 2.5rem;
        }
        .alert-light-custom {
            background: #f8f9fc;
            border-left: 5px solid #17a2b8;
        }
        .btn-primary-custom {
            background: linear-gradient(135deg, #28a745, #218838);
            border: none;
        }
        .btn-primary-custom:hover {
            background: linear-gradient(135deg, #218838, #1e7e34);
        }
    </style>
@endpush

@section('content')
<section class="section col-12">
    <div class="section-header">
        <h1><i class="fas fa-calendar-plus"></i> Nouvelle Demande de Congé</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('conges.index') }}">Congés</a></div>
            <div class="breadcrumb-item active">Nouvelle demande</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row justify-content-center">
            <div class="col-lg-12">

                <!-- Solde résumé (grand badge + popover détail) -->
                @if(isset($totalJoursDisponibles))
                <div class="text-center mb-4">
                    <div class="solde-badge badge bg-success text-white shadow-lg"
                         data-toggle="popover" data-placement="bottom" data-html="true"
                         data-trigger="hover" data-content='
                         <div class="solde-detail-popover p-3">
                             <h6 class="text-center mb-3"><i class="fas fa-layer-group"></i> Soldes par année</h6>
                             <div class="row g-2">
                                 @foreach($soldesParAnnee ?? [] as $s)
                                 <div class="col-6">
                                     <div class="solde-year-card p-2 rounded text-center">
                                         <strong>{{ $s->annee }}</strong><br>
                                         <span class="{{ $s->jours_restants > 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                             {{ $s->jours_restants }} j.
                                         </span>
                                         <small class="d-block text-muted">restants</small>
                                     </div>
                                 </div>
                                 @endforeach
                             </div>
                             <hr class="my-2">
                             <small class="text-muted d-block text-center">
                                 <i class="fas fa-sort-amount-up"></i> Déduction par ordre ancienneté
                             </small>
                         </div>'>
                        <i class="fas fa-coins me-2"></i>
                        {{ $totalJoursDisponibles }} jours disponibles
                    </div>

                    @if($totalJoursDisponibles <= 0)
                    <div class="alert alert-danger mt-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Votre solde total est épuisé. Vous pouvez demander des congés sans solde.
                    </div>
                    @elseif($totalJoursDisponibles < 5)
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Attention : il ne vous reste que <strong>{{ $totalJoursDisponibles }}</strong> jours.
                    </div>
                    @endif
                </div>
                @endif

                <!-- Info calcul -->
                <div class="alert alert-light-custom mb-4">
                    <i class="fas fa-calculator text-dark me-2"></i>
                    <strong style="color: darkblue">Important : seuls les jours ouvrés (lundi–vendredi hors week-ends et jours fériés sont décomptés.)</strong>
                </div>

                <!-- Formulaire -->
                <div class="form-section">
                    <form action="{{ route('conges.store') }}" method="POST" id="demande-form">
                        @csrf

                        <input type="hidden" name="nombre_jours" id="nombre_jours_hidden" value="{{ old('nombre_jours') }}">

                        <div class="row g-4">
                            <!-- Type de congé -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Type de congé <span class="text-danger">*</span></label>
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
                                                @if($type->est_annuel) (Annuel — déduit du solde) @endif
                                                @if($type->est_paye && !$type->est_annuel) (Payé — non déduit) @endif
                                                @if(!$type->est_paye && !$type->est_annuel) (Non payé) @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('type_conge_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <!-- Nombre de jours (affichage) -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Durée (jours ouvrés)</label>
                                    <div class="input-group">
                                        <input type="text" id="nombre_jours_display"
                                               class="form-control text-center fw-bold" value="0" readonly>
                                        <span class="input-group-text">jours ouvrés</span>
                                    </div>
                                    <small class="form-text text-muted">Calculé automatiquement</small>
                                    @error('nombre_jours') <div class="text-danger small">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row g-4">
                            <!-- Date début -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Date de début <span class="text-danger">*</span></label>
                                    <input type="date" name="date_debut" id="date_debut"
                                           class="form-control @error('date_debut') is-invalid @enderror"
                                           value="{{ old('date_debut') }}"
                                           min="{{ date('Y-m-d') }}" required>
                                    @error('date_debut') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <div id="date_debut_info" class="text-info small mt-1"></div>
                                </div>
                            </div>

                            <!-- Date fin -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Date de fin <span class="text-danger">*</span></label>
                                    <input type="date" name="date_fin" id="date_fin"
                                           class="form-control @error('date_fin') is-invalid @enderror"
                                           value="{{ old('date_fin') }}"
                                           min="{{ date('Y-m-d') }}" required>
                                    @error('date_fin') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <div id="date_fin_info" class="text-info small mt-1"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4">
                            <!-- Supérieur -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Supérieur hiérarchique <span class="text-danger">*</span></label>
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
                                    @error('superieur_hierarchique_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <!-- Motif -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Motif <span class="text-danger">*</span></label>
                                    <textarea name="motif" rows="4"
                                              class="form-control @error('motif') is-invalid @enderror"
                                              placeholder="Raison de votre demande..." required>{{ old('motif') }}</textarea>
                                    @error('motif') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Infos période -->
                        <div id="periode-info" class="alert alert-light-custom mt-4" style="display:none;">
                            <i class="fas fa-info-circle me-2" style="color: darkblue"></i>
                            <strong style="color: darkblue">Informations sur la période :</strong>
                            <div id="jours-details" class="mt-2" style="color: darkblue"></div>
                        </div>

                        <!-- Simulation déduction -->
                        <div id="deduction-preview" class="alert alert-warning mt-4" style="display:none;">
                            <i class="fas fa-layer-group me-2"></i>
                            <strong>Simulation de déduction (années les plus anciennes en premier) :</strong>
                            <div id="deduction-details" class="mt-3"></div>
                        </div>

                        <!-- Récapitulatif -->
                        <div class="card border-primary mt-4" id="preview-card" style="display:none;">
                            <div class="card-header text-white" style="background: #053a72d4;">
                                <h5 class="mb-0" style="color: rgb(242, 242, 242)"><i class="fas fa-clipboard-check me-2"></i> Récapitulatif</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <strong style="color: darkblue">Type :</strong> <span id="preview-type" style="color: darkblue"></span>
                                    </div>
                                    <div class="col-md-4">
                                        <strong style="color: darkblue">Période :</strong> <span id="preview-period" style="color: darkblue"></span>
                                    </div>
                                    <div class="col-md-4">
                                        <strong style="color: darkblue">Durée :</strong> <span id="preview-duration" style="color: darkblue"></span>
                                    </div>
                                </div>
                                <hr class="my-3">
                                <strong style="color: darkblue">Impact sur le solde :</strong> <span id="preview-solde" style="color: darkblue"></span>
                            </div>
                        </div>

                        <!-- Info max jours (indicatif) -->
                        <div id="info-max-jours" class="alert alert-warning mt-4 py-2" style="display:none;"></div>

                        <div class="text-end mt-5">
                            <button type="submit" class="btn btn-lg px-5" id="submit-btn" disabled style="background:#00297c;color:#f8f9fc">
                                <i class="fas fa-paper-plane me-2"></i> Soumettre la demande
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('assets/bundles/select2/dist/js/select2.full.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@php
    $soldesParAnneeJson = isset($soldesParAnnee)
        ? $soldesParAnnee->map(fn($s) => ['annee' => $s->annee, 'jours_restants' => (float)$s->jours_restants])->values()->toArray()
        : [];
    $totalJoursDisponiblesVal = isset($totalJoursDisponibles) ? (float)$totalJoursDisponibles : 0;
@endphp

<script>
$(document).ready(function () {
    var soldesParAnnee        = @json($soldesParAnneeJson);
    var totalJoursDisponibles = @json($totalJoursDisponiblesVal);
    var joursFeries           = [];

    // Select2
    $('.select2').select2({ placeholder: 'Sélectionner…', allowClear: true });

    // Jours fériés
    $.get('{{ route("conges.get-feries") }}', function(data) {
        joursFeries = data.jours_feries || [];
    });

    // Helpers dates (même que ton code)
    function isWeekend(dateStr) {
        var d = new Date(dateStr);
        return d.getDay() === 0 || d.getDay() === 6;
    }

    function isJourFerie(dateStr) {
        var d = new Date(dateStr);
        var mm = String(d.getMonth() + 1).padStart(2, '0');
        var jj = String(d.getDate()).padStart(2, '0');
        var fmt = mm + '-' + jj;
        return joursFeries.some(f => f.date === fmt);
    }

    function getNomJourFerie(dateStr) {
        var d = new Date(dateStr);
        var mm = String(d.getMonth() + 1).padStart(2, '0');
        var jj = String(d.getDate()).padStart(2, '0');
        var fmt = mm + '-' + jj;
        var f = joursFeries.find(f => f.date === fmt);
        return f ? f.nom : 'Jour férié';
    }

    function formatDateFr(dateStr) {
        return new Date(dateStr).toLocaleDateString('fr-FR', {
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
        });
    }

    // Simulation FIFO
    function simulerDeductionFIFO(jours) {
        var reste = jours;
        var result = [];
        var soldesTries = [...soldesParAnnee].sort((a,b) => a.annee - b.annee);

        soldesTries.forEach(s => {
            if (reste <= 0) return;
            var pris = Math.min(s.jours_restants, reste);
            reste -= pris;
            result.push({
                annee: s.annee,
                jours_pris: pris,
                jours_restants_apres: s.jours_restants - pris
            });
        });
        return result;
    }

    // Analyse période
    function analyzePeriod() {
        var debut = $('#date_debut').val();
        var fin   = $('#date_fin').val();

        $('#date_debut_info, #date_fin_info, #periode-info, #deduction-preview, #preview-card, #info-max-jours').hide();

        if (!debut || !fin) return;

        if (new Date(fin) < new Date(debut)) {
            $('#date_fin_info').text('La date de fin doit être postérieure à la date de début').show();
            return;
        }

        var start = new Date(debut);
        var end   = new Date(fin);
        var joursOuvrables = 0;
        var nonOuvrables = [];
        var current = new Date(start);

        while (current <= end) {
            var ds = current.toISOString().split('T')[0];
            if (isWeekend(ds)) {
                nonOuvrables.push({date: formatDateFr(ds), raison: 'Week-end'});
            } else if (isJourFerie(ds)) {
                nonOuvrables.push({date: formatDateFr(ds), raison: getNomJourFerie(ds)});
            } else {
                joursOuvrables++;
            }
            current.setDate(current.getDate() + 1);
        }

        if (joursOuvrables <= 0) {
            $('#jours-details').html('Aucun jour ouvrable dans la période.');
            $('#periode-info').show();
            return;
        }

        $('#nombre_jours_display').val(joursOuvrables);
        $('#nombre_jours_hidden').val(joursOuvrables);

        var totalCal = Math.floor((end - start) / 86400000) + 1;
        var msg = `<strong>Période :</strong> ${totalCal} jours calendaires<br>
                   <strong>Jours ouvrés :</strong> ${joursOuvrables}<br>
                   <strong>Exclus :</strong> ${nonOuvrables.length} jours`;

        if (nonOuvrables.length) {
            msg += '<details class="mt-2"><summary>Voir les jours exclus</summary><ul class="mt-1">';
            nonOuvrables.forEach(j => msg += `<li>${j.date} (${j.raison})</li>`);
            msg += '</ul></details>';
        }

        $('#jours-details').html(msg);
        $('#periode-info').show();

        updatePreview();
        checkSolde();
    }

    function updatePreview() {
        var typeText = $('#type_conge_id option:selected').text().trim();
        var debut = $('#date_debut').val();
        var fin = $('#date_fin').val();
        var jours = parseFloat($('#nombre_jours_hidden').val()) || 0;
        var option = $('#type_conge_id option:selected');
        var estAnnuel = option.data('est-annuel');

        if (!typeText || !debut || !fin || !jours) {
            $('#preview-card').hide();
            return;
        }

        $('#preview-type').text(typeText);
        $('#preview-period').text(formatDateFr(debut) + ' au ' + formatDateFr(fin));
        $('#preview-duration').text(jours + ' jour(s) ouvré(s)');

        if (estAnnuel == '1') {
            var nouveauTotal = totalJoursDisponibles - jours;
            var cls = nouveauTotal >= 0 ? 'text-success' : 'text-danger';
            var txt = nouveauTotal >= 0
                ? `Solde global : ${totalJoursDisponibles} → ${nouveauTotal.toFixed(1)} j.`
                : `Dépassement de ${Math.abs(nouveauTotal).toFixed(1)} j.`;

            $('#preview-solde').html(`<span class="${cls}">${txt}</span>`);
        } else {
            $('#preview-solde').html('<span class="text-info">Non déduit du solde annuel</span>');
        }

        $('#preview-card').show();
    }

    function checkSolde() {
        var jours = parseFloat($('#nombre_jours_hidden').val()) || 0;
        var option = $('#type_conge_id option:selected');
        var estAnnuel = option.data('est-annuel');
        var maxJours = option.data('max-jours');

        $('#submit-btn').prop('disabled', true);

        if (jours <= 0) return;

        if (maxJours && jours > maxJours) {
            $('#info-max-jours')
                .html(`<i class="fas fa-info-circle"></i> Ce type de congé a un maximum indicatif de ${maxJours} jours.`)
                .show();
        } else {
            $('#info-max-jours').hide();
        }

        if (estAnnuel == '1' && jours > totalJoursDisponibles) {
            $('#submit-btn').prop('disabled', true);
            Swal.fire({
                icon: 'error',
                title: 'Solde insuffisant',
                html: `Solde disponible : <strong>${totalJoursDisponibles}</strong> j.<br>
                       Demandé : <strong>${jours}</strong> j.<br>
                       <strong class="text-danger">Manquant : ${jours - totalJoursDisponibles} j.</strong>`
            });
        } else {
            $('#submit-btn').prop('disabled', false);
        }
    }

    // Événements
    $('#date_debut, #date_fin').on('change', analyzePeriod);
    $('#type_conge_id').on('change', function() {
        if ($('#date_debut').val() && $('#date_fin').val()) {
            checkSolde();
            updatePreview();
        }
    });

    // Initialisation
    if ($('#date_debut').val() && $('#date_fin').val()) {
        setTimeout(analyzePeriod, 300);
    }

    // Popover soldes
    $('.solde-badge').popover({
        html: true,
        container: 'body',
        sanitize: false
    });
});
</script>
@endpush
