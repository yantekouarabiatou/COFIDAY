@extends('layaout')

@section('title', 'Modifier Client')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-edit"></i> Modifier Client</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('clients.index') }}">Clients</a></div>
            <div class="breadcrumb-item"><a href="{{ route('clients.show', $client) }}">{{ $client->nom }}</a></div>
            <div class="breadcrumb-item active">Modifier</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Modification de {{ $client->nom }}</h4>
                        <div class="card-header-action">
                            <a href="{{ route('clients.show', $client) }}" class="btn btn-icon icon-left btn-info">
                                <i class="fas fa-eye"></i> Voir
                            </a>
                            <a href="{{ route('clients.index') }}" class="btn btn-icon icon-left btn-danger">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('clients.update', $client) }}" method="POST" enctype="multipart/form-data" id="client-form">
                            @csrf
                            @method('PUT')

                            <!-- Aperçu du logo actuel -->
                            <div class="text-center mb-4">
                                <div class="form-group">
                                    <label>Logo actuel</label><br>
                                    @if($client->logo)
                                        <div class="mr-3">
                                            <img src="{{ asset('storage/' . $client->logo) }}"
                                                    alt="Logo {{ $client->nom }}"
                                                    class="img-fluid rounded-circle border" width="120" height="120"
                                                    style="object-fit: cover; border: 3px solid #f0f0f0;">
                                        </div>
                                    @endif
                                    @if($client->logo)
                                        <div class="mt-2">
                                            <a href="{{ route('clients.logo.download', $client) }}"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-download"></i> Télécharger
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                    onclick="confirmLogoDelete()">
                                                <i class="fas fa-trash"></i> Supprimer
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nom du Client <span class="text-danger">*</span></label>
                                        <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror"
                                               value="{{ old('nom', $client->nom) }}" required>
                                        @error('nom')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Statut <span class="text-danger">*</span></label>
                                        <select name="statut" class="form-control select2 @error('statut') is-invalid @enderror" required>
                                            <option value="prospect" {{ old('statut', $client->statut) == 'prospect' ? 'selected' : '' }}>Prospect</option>
                                            <option value="actif" {{ old('statut', $client->statut) == 'actif' ? 'selected' : '' }}>Actif</option>
                                            <option value="inactif" {{ old('statut', $client->statut) == 'inactif' ? 'selected' : '' }}>Inactif</option>
                                        </select>
                                        @error('statut')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Les mêmes champs que create.blade.php mais avec les valeurs actuelles -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                               value="{{ old('email', $client->email) }}">
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Téléphone</label>
                                        <input type="text" name="telephone" class="form-control @error('telephone') is-invalid @enderror"
                                               value="{{ old('telephone', $client->telephone) }}">
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
                                               value="{{ old('siege_social', $client->siege_social) }}">
                                        @error('siege_social')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Contact Principal</label>
                                        <input type="text" name="contact_principal" class="form-control @error('contact_principal') is-invalid @enderror"
                                               value="{{ old('contact_principal', $client->contact_principal) }}">
                                        @error('contact_principal')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Adresse</label>
                                <textarea name="adresse" class="form-control @error('adresse') is-invalid @enderror"
                                          rows="3">{{ old('adresse', $client->adresse) }}</textarea>
                                @error('adresse')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Secteur d'Activité</label>
                                        <input type="text" name="secteur_activite" class="form-control @error('secteur_activite') is-invalid @enderror"
                                               value="{{ old('secteur_activite', $client->secteur_activite) }}">
                                        @error('secteur_activite')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Numéro IFU</label>
                                        <input type="text" name="numero_siret" class="form-control @error('numero_siret') is-invalid @enderror"
                                               value="{{ old('numero_siret', $client->numero_siret) }}">
                                        @error('numero_siret')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <!-- <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Code NAF</label>
                                        <input type="text" name="code_naf" class="form-control @error('code_naf') is-invalid @enderror"
                                               value="{{ old('code_naf', $client->code_naf) }}">
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
                                               value="{{ old('site_web', $client->site_web) }}">
                                        @error('site_web')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nouveau Logo (facultatif)</label>
                                        <input type="file" name="logo" class="form-control-file @error('logo') is-invalid @enderror"
                                               accept="image/*">
                                        <small class="text-muted">Laisser vide pour conserver l'actuel</small>
                                        @error('logo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Notes</label>
                                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
                                          rows="4">{{ old('notes', $client->notes) }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="text-right">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save"></i> Mettre à jour
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
});

// Confirmation suppression logo
function confirmLogoDelete() {
    Swal.fire({
        title: 'Supprimer le logo ?',
        text: "Le logo actuel sera supprimé définitivement !",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Oui, supprimer !',
        cancelButtonText: 'Annuler'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "{{ route('clients.logo.delete', $client) }}";
        }
    });
}
</script>
@endpush
