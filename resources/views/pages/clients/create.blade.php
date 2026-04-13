@extends('layaout')

@section('title', 'Nouveau Client')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-user-plus"></i> Nouveau Client</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('clients.index') }}">Clients</a></div>
            <div class="breadcrumb-item active">Nouveau Client</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Informations du Client</h4>
                        <div class="card-header-action">
                            <a href="{{ route('clients.index') }}" class="btn btn-icon icon-left btn-danger">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('clients.store') }}" method="POST" enctype="multipart/form-data" id="client-form">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nom du Client <span class="text-danger">*</span></label>
                                        <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror"
                                               value="{{ old('nom') }}" required placeholder="Ex: Société ABC">
                                        @error('nom')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Statut <span class="text-danger">*</span></label>
                                        <select name="statut" class="form-control select2 @error('statut') is-invalid @enderror" required>
                                            <option value="">Sélectionner un statut</option>
                                            <option value="prospect" {{ old('statut') == 'prospect' ? 'selected' : '' }}>Prospect</option>
                                            <option value="actif" {{ old('statut') == 'actif' ? 'selected' : '' }}>Actif</option>
                                            <option value="inactif" {{ old('statut') == 'inactif' ? 'selected' : '' }}>Inactif</option>
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
                                        <label>Email</label>
                                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                               value="{{ old('email') }}" placeholder="exemple@entreprise.com">
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Téléphone</label>
                                        <input type="text" name="telephone" class="form-control @error('telephone') is-invalid @enderror"
                                               value="{{ old('telephone') }}" placeholder="+229 01 00 00 00 00">
                                        @error('telephone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Siège Social</label>
                                        <input type="text" name="siege_social" class="form-control @error('siege_social') is-invalid @enderror"
                                               value="{{ old('siege_social') }}" placeholder="Ville, Pays">
                                        @error('siege_social')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Contact Principal</label>
                                        <input type="text" name="contact_principal" class="form-control @error('contact_principal') is-invalid @enderror"
                                               value="{{ old('contact_principal') }}" placeholder="Nom du contact">
                                        @error('contact_principal')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Adresse</label>
                                <textarea name="adresse" class="form-control @error('adresse') is-invalid @enderror"
                                          rows="3" placeholder="Adresse complète">{{ old('adresse') }}</textarea>
                                @error('adresse')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Secteur d'Activité</label>
                                        <input type="text" name="secteur_activite" class="form-control @error('secteur_activite') is-invalid @enderror"
                                               value="{{ old('secteur_activite') }}" placeholder="Ex: Informatique">
                                        @error('secteur_activite')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Numéro IFU</label>
                                        <input type="text" name="numero_siret" class="form-control @error('numero_siret') is-invalid @enderror"
                                               value="{{ old('numero_siret') }}" placeholder="14 chiffres">
                                        @error('numero_siret')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <!-- <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Code NAF</label>
                                        <input type="text" name="code_naf" class="form-control @error('code_naf') is-invalid @enderror"
                                               value="{{ old('code_naf') }}" placeholder="Ex: 6201Z">
                                        @error('code_naf')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div> -->
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Site Web</label>
                                        <input type="url" name="site_web" class="form-control @error('site_web') is-invalid @enderror"
                                               value="{{ old('site_web') }}" placeholder="https://www.exemple.com">
                                        @error('site_web')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Logo (facultatif)</label>
                                        <input type="file" name="logo" class="form-control-file @error('logo') is-invalid @enderror"
                                               accept="image/*">
                                        <small class="text-muted">JPG, PNG, GIF, SVG - Max 2Mo</small>
                                        @error('logo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Notes</label>
                                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
                                          rows="4" placeholder="Informations complémentaires...">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="text-right">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save"></i> Enregistrer le Client
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

    // Validation du formulaire
    $('#client-form').on('submit', function(e) {
        var nom = $('input[name="nom"]').val().trim();
        var statut = $('select[name="statut"]').val();

        if (!nom || !statut) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Champs obligatoires',
                text: 'Veuillez remplir tous les champs obligatoires (*)',
            });
            return false;
        }
    });

    // Format SIRET
    $('input[name="numero_siret"]').on('input', function() {
        var value = $(this).val().replace(/\D/g, '');
        if (value.length > 14) {
            value = value.substr(0, 14);
        }
        $(this).val(value);
    });

    // Format téléphone
    $('input[name="telephone"]').on('input', function() {
        var value = $(this).val().replace(/\D/g, '');
        if (value.length > 10) {
            value = value.substr(0, 10);
        }
        if (value.length > 0) {
            value = value.replace(/(\d{2})(?=\d)/g, '$1 ');
        }
        $(this).val(value);
    });
});
</script>
@endpush
