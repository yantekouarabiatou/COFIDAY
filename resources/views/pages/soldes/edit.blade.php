@extends('layaout')

@section('title', 'Modifier un solde de congés')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
@endpush

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-edit"></i> Modifier le solde</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.soldes.index') }}">Soldes</a></div>
            <div class="breadcrumb-item active">Modifier</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Informations du solde</h4>
                    </div>
                    <form action="{{ route('admin.soldes.update', $solde) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="user_id">Utilisateur <span class="text-danger">*</span></label>
                                        <select name="user_id" id="user_id" class="form-control select2" required>
                                            <option value="">Sélectionnez un utilisateur</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" {{ old('user_id', $solde->user_id) == $user->id ? 'selected' : '' }}>
                                                    {{ $user->prenom }} {{ $user->nom }} ({{ $user->email }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('user_id') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="annee">Année <span class="text-danger">*</span></label>
                                        <select name="annee" id="annee" class="form-control" required>
                                            @foreach($years as $year)
                                                <option value="{{ $year }}" {{ old('annee', $solde->annee) == $year ? 'selected' : '' }}>
                                                    {{ $year }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('annee') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="jours_acquis">Jours acquis <span class="text-danger">*</span></label>
                                        <input type="number" step="0.5" min="0" max="50" name="jours_acquis" id="jours_acquis"
                                               class="form-control" value="{{ old('jours_acquis', $solde->jours_acquis) }}" required>
                                        @error('jours_acquis') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="jours_pris">Jours pris <span class="text-danger">*</span></label>
                                        <input type="number" step="0.5" min="0" name="jours_pris" id="jours_pris"
                                               class="form-control" value="{{ old('jours_pris', $solde->jours_pris) }}" required>
                                        @error('jours_pris') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="jours_reportes">Jours reportés</label>
                                        <input type="number" step="0.5" min="0" name="jours_reportes" id="jours_reportes"
                                               class="form-control" value="{{ old('jours_reportes', $solde->jours_reportes ?? 0) }}">
                                        @error('jours_reportes') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="jours_restants_preview">Jours restants (calculé)</label>
                                        <input type="text" id="jours_restants_preview" class="form-control" readonly disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-info mt-3">
                                <i class="fas fa-info-circle"></i> Le solde restant est automatiquement calculé : (Acquis + Reportés) - Pris.
                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <a href="{{ route('admin.soldes.index') }}" class="btn btn-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary">Mettre à jour</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2').select2({
        theme: 'bootstrap-5',
        placeholder: 'Sélectionnez un utilisateur',
        allowClear: true
    });

    function updateRestants() {
        var acquis = parseFloat($('#jours_acquis').val()) || 0;
        var pris = parseFloat($('#jours_pris').val()) || 0;
        var reportes = parseFloat($('#jours_reportes').val()) || 0;
        var restants = (acquis + reportes) - pris;
        $('#jours_restants_preview').val(restants.toFixed(1));
    }

    $('#jours_acquis, #jours_pris, #jours_reportes').on('input', updateRestants);
    updateRestants();
});
</script>
@endpush
