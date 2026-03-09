@extends('layaout')

@section('title', 'Modifier Paramètres')

@section('content')
    <section class="section">
        <div class="section-body">
            <div class="row">
                <div class="col-12 col-md-12 offset-md-1 col-lg-12">
                    <div class="card card-primary shadow-lg">
                        <div class="card-header">
                            <h4><i class="fas fa-edit mr-1"></i> Modification des Paramètres de l'Entreprise</h4>
                            <div class="card-header-action">
                                <a href="{{ route('settings.show') }}" class="btn btn-danger btn-icon icon-left">
                                    <i class="fas fa-times"></i> Annuler
                                </a>
                            </div>
                        </div>

                        <div class="card-body">
                            <form action="{{ route('settings.update', $setting->id) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                {{-- Informations de l'Entreprise --}}
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-info-circle mr-1"></i> Informations de l'Entreprise
                                </h6>
                                <hr class="mt-0">

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="company_name">Nom de l'Entreprise <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" id="company_name" name="company_name"
                                                class="form-control @error('company_name') is-invalid @enderror"
                                                value="{{ old('company_name', $setting->company_name) }}" required>
                                            @error('company_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="slogan">Slogan/Devise</label>
                                            <input type="text" id="slogan" name="slogan"
                                                class="form-control @error('slogan') is-invalid @enderror"
                                                value="{{ old('slogan', $setting->slogan) }}"
                                                placeholder="Ex: Compagnie de Fiduciaire...">
                                            @error('slogan')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- Contact --}}
                                <h6 class="text-primary mt-4 mb-3">
                                    <i class="fas fa-phone-alt mr-1"></i> Contact
                                </h6>
                                <hr class="mt-0">

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email">Email <span class="text-danger">*</span></label>
                                            <input type="email" id="email" name="email"
                                                class="form-control @error('email') is-invalid @enderror"
                                                value="{{ old('email', $setting->email) }}" required>
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="telephone">Téléphone</label>
                                            <input type="text" id="telephone" name="telephone"
                                                class="form-control @error('telephone') is-invalid @enderror"
                                                value="{{ old('telephone', $setting->telephone) }}">
                                            @error('telephone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="site_web">Site Web</label>
                                    <input type="url" id="site_web" name="site_web"
                                        class="form-control @error('site_web') is-invalid @enderror"
                                        value="{{ old('site_web', $setting->site_web) }}"
                                        placeholder="https://www.entreprise.com">
                                    @error('site_web')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Localisation --}}
                                <h6 class="text-primary mt-4 mb-3">
                                    <i class="fas fa-map-marker-alt mr-1"></i> Localisation
                                </h6>
                                <hr class="mt-0">

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="pays">Pays</label>
                                            <input type="text" id="pays" name="pays"
                                                class="form-control @error('pays') is-invalid @enderror"
                                                value="{{ old('pays', $setting->pays) }}">
                                            @error('pays')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="ville">Ville</label>
                                            <input type="text" id="ville" name="ville"
                                                class="form-control @error('ville') is-invalid @enderror"
                                                value="{{ old('ville', $setting->ville) }}">
                                            @error('ville')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="adresse">Adresse</label>
                                            <input type="text" id="adresse" name="adresse"
                                                class="form-control @error('adresse') is-invalid @enderror"
                                                value="{{ old('adresse', $setting->adresse) }}"
                                                placeholder="Adresse complète">
                                            @error('adresse')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- Logo & Guide --}}
                                <h6 class="text-primary mt-4 mb-3">
                                    <i class="fas fa-paperclip mr-1"></i> Logo et Documentation
                                </h6>
                                <hr class="mt-0">

                                <div class="row">

                                    {{-- Champ Logo --}}
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="logo">Logo de l'Entreprise</label>

                                            {{-- Aperçu logo actuel --}}
                                            @if($setting->logo)
                                                <div class="mb-2">
                                                    <img src="{{ asset('storage/' . $setting->logo) }}" alt="Logo actuel"
                                                        style="max-height: 80px; border: 1px solid #dee2e6; padding: 4px; border-radius: 4px;">
                                                </div>
                                            @endif

                                            <div class="custom-file">
                                                <input type="file" id="logo" name="logo"
                                                    class="custom-file-input @error('logo') is-invalid @enderror"
                                                    accept="image/*">
                                                <label class="custom-file-label" for="logo" id="logo-label">
                                                    {{ $setting->logo ? basename($setting->logo) : 'Choisir un logo...' }}
                                                </label>
                                                @error('logo')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <small class="text-muted mt-1 d-block">
                                                @if($setting->logo)
                                                    <i class="fas fa-check-circle text-success"></i> Logo actuel présent.
                                                @else
                                                    Aucun logo actuellement.
                                                @endif
                                                Fichier image uniquement (max 2 Mo).
                                            </small>
                                        </div>
                                    </div>

                                    {{-- Champ Guide --}}
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="guide">Guide d'utilisation</label>

                                            @if($setting->guide)
                                                <div class="mb-2">
                                                    <a href="{{ route('settings.guide.view') }}" target="_blank"
                                                        class="btn btn-outline-info btn-sm">
                                                        <i class="fas fa-eye mr-1"></i> Voir le guide actuel
                                                    </a>
                                                    <a href="{{ route('settings.guide.download') }}"
                                                        class="btn btn-outline-primary btn-sm ml-1">
                                                        <i class="fas fa-download mr-1"></i> Télécharger
                                                    </a>
                                                </div>
                                            @endif

                                            <div class="custom-file">
                                                <input type="file" id="guide" name="guide"
                                                    class="custom-file-input @error('guide') is-invalid @enderror"
                                                    accept=".pdf,.doc,.docx">
                                                <label class="custom-file-label" for="guide" id="guide-label">
                                                    {{ $setting->guide ? basename($setting->guide) : 'Choisir un fichier...' }}
                                                </label>
                                                @error('guide')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <small class="text-muted mt-1 d-block">
                                                @if($setting->guide)
                                                    <i class="fas fa-check-circle text-success"></i> Guide actuel présent.
                                                @else
                                                    Aucun guide actuellement.
                                                @endif
                                                Fichiers acceptés : PDF, DOC, DOCX (max 10 Mo).
                                            </small>
                                        </div>
                                    </div>

                                </div>

                                <div class="text-right mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg px-5">
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
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {

            // ✅ Chaque input gère son propre label — ciblage par ID précis
            $('#logo').on('change', function () {
                let fileName = $(this).val().split('\\').pop();
                $('#logo-label').html(fileName || 'Choisir un logo...');
            });

            $('#guide').on('change', function () {
                let fileName = $(this).val().split('\\').pop();
                $('#guide-label').html(fileName || 'Choisir un fichier...');
            });

        });
    </script>
@endpush

