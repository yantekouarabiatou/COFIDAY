@extends('layaout')

@section('title', 'Modifier les Règles de Congés')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-cogs"></i> Modifier les Règles de Congés</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">
                <a href="{{ route('dashboard') }}">Dashboard</a>
            </div>
            <div class="breadcrumb-item">
                <a href="{{ route('admin.regles-conges.index') }}">Règles de Congés</a>
            </div>
            <div class="breadcrumb-item">Modifier</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h4>Paramètres généraux</h4>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form action="{{ route('admin.regles-conges.update', $regles->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <!-- Jours par mois -->
                            <div class="mb-3 row">
                                <label for="jours_par_mois" class="col-sm-4 col-form-label">Jours par mois</label>
                                <div class="col-sm-8">
                                    <input type="number" step="0.01" min="0" name="jours_par_mois" id="jours_par_mois"
                                           class="form-control @error('jours_par_mois') is-invalid @enderror"
                                           value="{{ old('jours_par_mois', $regles->jours_par_mois) }}" required>
                                    @error('jours_par_mois')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Report autorisé -->
                            <div class="mb-3 row">
                                <label class="col-sm-4 col-form-label">Report autorisé</label>
                                <div class="col-sm-8">
                                    <select name="report_autorise" class="form-control select2" required>
                                        <option value="1" {{ $regles->report_autorise ? 'selected' : '' }}>Oui</option>
                                        <option value="0" {{ !$regles->report_autorise ? 'selected' : '' }}>Non</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Limite report -->
                            <div class="mb-3 row">
                                <label for="limite_report" class="col-sm-4 col-form-label">Limite de report (jours)</label>
                                <div class="col-sm-8">
                                    <input type="number" min="0" name="limite_report" id="limite_report"
                                           class="form-control" 
                                           value="{{ old('limite_report', $regles->limite_report) }}">
                                </div>
                            </div>

                            <!-- Validation multiple -->
                            <div class="mb-3 row">
                                <label class="col-sm-4 col-form-label">Validation multiple</label>
                                <div class="col-sm-8">
                                    <select name="validation_multiple" class="form-control select2" required>
                                        <option value="1" {{ $regles->validation_multiple ? 'selected' : '' }}>Oui</option>
                                        <option value="0" {{ !$regles->validation_multiple ? 'selected' : '' }}>Non</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Jours fériés -->
                            <div class="mb-3">
                                <label>Jours fériés</label>
                                <div id="jours-feries-container">
                                    @foreach($regles->jours_feries_array as $i => $jour)
                                    @php
                                        [$mm, $dd] = explode('-', $jour['date']);
                                    @endphp
                                        <div class="border p-2 mb-2 jours-item">
                                            <div class="row g-2 align-items-center">
                                                <div class="col">
                                                    <input type="text" name="jours_feries[{{ $i }}][nom]" 
                                                           class="form-control " placeholder="Nom" 
                                                           value="{{ $jour['nom'] ?? '' }}" required>
                                                </div>
                                                <div class="col input-group mb-2 jours-feries-item">
                                                    <input type="text" name="jours_feries[{{ $i }}][date]"
                                                           class="form-control jour-ferie" value="{{ $dd }}-{{ $mm }}" 
                                                           placeholder="JJ-MM" pattern="^\d{2}-\d{2}$" required>
                                                </div>
                                                <div class="col-auto">
                                                    <button class="btn btn-danger remove-jour" type="button">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" id="add-jour" class="btn btn-secondary btn-sm mt-2">
                                    <i class="fas fa-plus"></i> Ajouter un jour férié
                                </button>
                                <small class="text-muted">
                                    Format attendu : <strong>JJ-MM</strong> (ex : 01-05 pour le 1er mai)
                                </small>
                            </div>

                            <!-- Périodes bloquées -->
                            <div class="mb-3">
                                <label>Périodes bloquées</label>
                                <div id="periodes-bloquees-container">
                                    @foreach($regles->periodes_bloquees_array as $i => $periode)
                                        <div class="border p-2 mb-2 periodes-item">
                                            <div class="row g-2 align-items-center">
                                                <div class="col">
                                                    <input type="text" name="periodes_bloquees[{{ $i }}][nom]" 
                                                           class="form-control" placeholder="Nom" 
                                                           value="{{ $periode['nom'] ?? '' }}" required>
                                                </div>
                                                <div class="col">
                                                    <input type="date" name="periodes_bloquees[{{ $i }}][debut]" 
                                                           class="form-control" placeholder="Début" 
                                                           value="{{ $periode['debut'] ?? '' }}" required>
                                                </div>
                                                <div class="col">
                                                    <input type="date" name="periodes_bloquees[{{ $i }}][fin]" 
                                                           class="form-control" placeholder="Fin" 
                                                           value="{{ $periode['fin'] ?? '' }}" required>
                                                </div>
                                                <div class="col">
                                                    <input type="text" name="periodes_bloquees[{{ $i }}][raison]" 
                                                           class="form-control" placeholder="Raison" 
                                                           value="{{ $periode['raison'] ?? '' }}">
                                                </div>
                                                <div class="col-auto">
                                                    <button class="btn btn-danger remove-periode" type="button">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" id="add-periode" class="btn btn-secondary btn-sm mt-2">
                                    <i class="fas fa-plus"></i> Ajouter une période
                                </button>
                            </div>

                            <!-- Préavis et délai -->
                            <div class="mb-3 row">
                                <label for="preavis_minimum" class="col-sm-4 col-form-label">Préavis minimum (heures)</label>
                                <div class="col-sm-8">
                                    <input type="number" name="preavis_minimum" id="preavis_minimum" 
                                           class="form-control" 
                                           value="{{ old('preavis_minimum', $regles->preavis_minimum) }}" required>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="delai_annulation" class="col-sm-4 col-form-label">Délai annulation (heures)</label>
                                <div class="col-sm-8">
                                    <input type="number" name="delai_annulation" id="delai_annulation" 
                                           class="form-control" 
                                           value="{{ old('delai_annulation', $regles->delai_annulation) }}" required>
                                </div>
                            </div>

                            <!-- Couleur calendrier -->
                            <div class="mb-3 row">
                                <label for="couleur_calendrier" class="col-sm-4 col-form-label">Couleur du calendrier</label>
                                <div class="col-sm-8">
                                    <input type="color" name="couleur_calendrier" id="couleur_calendrier"
                                           class="form-control form-control-color" 
                                           value="{{ old('couleur_calendrier', $regles->couleur_calendrier) }}">
                                </div>
                            </div>

                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save"></i> Enregistrer les modifications
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script src="{{ asset('assets/bundles/select2/dist/js/select2.full.min.js') }}"></script>
<script>
$(document).ready(function() {
    $('.select2').select2({ placeholder: 'Sélectionner...', allowClear: true });

    // Ajouter jour férié
    $('#add-jour').on('click', function() {
        $('#jours-feries-container').append(`
            <div class="input-group mb-2 jours-feries-item">
                <input
                    type="text"
                    name="jours_feries[]"
                    class="form-control jour-ferie"
                    placeholder="JJ-MM"
                    pattern="^\\d{2}-\\d{2}$"
                    required
                >
                <button class="btn btn-danger remove-jour" type="button">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `);
    });

    // Supprimer jour férié
    $(document).on('click', '.remove-jour', function() {
        $(this).closest('.jours-item').remove();
    });

    // Ajouter période bloquée
    let periodeIndex = {{ count($regles->periodes_bloquees_array) }};
    $('#add-periode').on('click', function() {
        $('#periodes-bloquees-container').append(`
            <div class="border p-2 mb-2 periodes-item">
                <div class="row g-2 align-items-center">
                    <div class="col"><input type="text" name="periodes_bloquees[${periodeIndex}][nom]" class="form-control" placeholder="Nom" required></div>
                    <div class="col"><input type="date" name="periodes_bloquees[${periodeIndex}][debut]" class="form-control" required></div>
                    <div class="col"><input type="date" name="periodes_bloquees[${periodeIndex}][fin]" class="form-control" required></div>
                    <div class="col"><input type="text" name="periodes_bloquees[${periodeIndex}][raison]" class="form-control" placeholder="Raison"></div>
                    <div class="col-auto">
                        <button class="btn btn-danger remove-periode" type="button"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            </div>
        `);
        periodeIndex++;
    });

    // Supprimer période bloquée
    $(document).on('click', '.remove-periode', function() {
        $(this).closest('.periodes-item').remove();
    });
});

$('form').on('submit', function () {
    $('.jour-ferie').each(function () {
        let value = $(this).val().trim();

        if (!value) return;

        // JJ-MM → MM-DD
        let parts = value.split('-');
        if (parts.length === 2) {
            let jj = parts[0];
            let mm = parts[1];

            // Sécurité basique
            if (jj.length === 2 && mm.length === 2) {
                $(this).val(mm + '-' + jj);
            }
        }
    });
});

</script>

@endpush
@endsection
