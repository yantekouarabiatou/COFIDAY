@extends('layaout')

@section('title', 'Modifier Utilisateur')

@section('content')
    <section class="section">
        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h4><i class="fas fa-user-edit"></i> Modifier l'utilisateur</h4>
                            <div class="card-header-action">
                                <a href="{{ route('users.index') }}" class="btn btn-danger">
                                    <i class="fas fa-times"></i> Annuler
                                </a>
                            </div>
                        </div>

                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <strong><i class="fas fa-exclamation-triangle"></i> Erreurs détectées :</strong>
                                    <ul class="mb-0 mt-2">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            <form action="{{ route('users.update', $user) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-user"></i> Informations personnelles
                                </h5>

                                <!-- Photo actuelle -->
                                @if($user->photo)
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <div class="text-center">
                                            <label class="d-block">Photo actuelle</label>
                                            <img src="{{ Storage::url($user->photo) }}" alt="{{ $user->nom }}" 
                                                class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Nom <span class="text-danger">*</span></label>
                                            <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror" 
                                                value="{{ old('nom', $user->nom) }}" placeholder="Ex: Dupont" required autofocus>
                                            @error('nom')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Prénom <span class="text-danger">*</span></label>
                                            <input type="text" name="prenom" class="form-control @error('prenom') is-invalid @enderror" 
                                                value="{{ old('prenom', $user->prenom) }}" placeholder="Ex: Jean" required>
                                            @error('prenom')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Nom d'utilisateur <span class="text-danger">*</span></label>
                                            <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" 
                                                value="{{ old('username', $user->username) }}" placeholder="Ex: jean.dupont" required>
                                            @error('username')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle"></i> Utilisé pour la connexion
                                            </small>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Email <span class="text-danger">*</span></label>
                                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                                value="{{ old('email', $user->email) }}" placeholder="exemple@email.com" required>
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Poste <span class="text-danger">*</span></label>
                                            <select name="poste_id" class="form-control select2 @error('poste_id') is-invalid @enderror" required>
                                                <option value="">-- Choisir un poste --</option>
                                                @foreach($postes as $poste)
                                                    <option value="{{ $poste->id }}" 
                                                        {{ old('poste_id', $user->poste_id) == $poste->id ? 'selected' : '' }}>
                                                        {{ $poste->libelle ?? $poste->intitule }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('poste_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Téléphone</label>
                                            <input type="tel" name="telephone" class="form-control @error('telephone') is-invalid @enderror" 
                                                value="{{ old('telephone', $user->telephone) }}" placeholder="+229 XX XX XX XX XX">
                                            @error('telephone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Sexe</label>
                                            <select name="sexe" class="form-control select2 @error('sexe') is-invalid @enderror">
                                                <option value="">-- Choisir le sexe --</option>
                                                <option value="M" {{ old('sexe', $user->sexe) == 'M' ? 'selected' : '' }}>Masculin</option>
                                                <option value="F" {{ old('sexe', $user->sexe) == 'F' ? 'selected' : '' }}>Féminin</option>
                                            </select>
                                            @error('sexe')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-4">

                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-lock"></i> Modifier le mot de passe (optionnel)
                                </h5>

                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Laissez vide si vous ne souhaitez pas changer le mot de passe
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Nouveau mot de passe</label>
                                            <div class="input-group">
                                                <input type="password" id="password" name="password"
                                                    class="form-control @error('password') is-invalid @enderror"
                                                    placeholder="Minimum 8 caractères" autocomplete="new-password">

                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-secondary" id="generate-password">
                                                        Générer
                                                    </button>
                                                </div>
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-info" id="toggle-password">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            @error('password')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror

                                            <small class="text-muted">
                                                <i class="fas fa-shield-alt"></i> Vous pouvez écrire votre propre mot de passe ou le générer automatiquement.
                                            </small>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Confirmer le nouveau mot de passe</label>
                                            <input type="password" id="password_confirmation" name="password_confirmation"
                                                class="form-control" placeholder="Retapez le mot de passe"
                                                autocomplete="new-password">
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-4">

                                <h5 class="mb-3">
                                    <i class="fas fa-cog"></i> Paramètres supplémentaires
                                </h5>

                                <div class="row">
                                    <!-- Rôle -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Rôle</label>
                                            <select name="role_id" class="form-control select2 @error('role_id') is-invalid @enderror">
                                                <option value="">-- Choisir un rôle --</option>
                                                @foreach($roles as $role)
                                                    <option value="{{ $role->id }}" 
                                                        {{ $roleActuel?->id == $role->id ? 'selected' : '' }}>
                                                        {{ $role->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('role_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Statut -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Statut</label>
                                            <select name="is_active" class="form-control select2 @error('is_active') is-invalid @enderror">
                                                <option value="1" {{ old('is_active', $user->is_active) == 1 ? 'selected' : '' }}>Actif</option>
                                                <option value="0" {{ old('is_active', $user->is_active) == 0 ? 'selected' : '' }}>Inactif</option>
                                            </select>
                                            @error('is_active')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>


                                <div class="form-group">
                                    <label>Photo de profil</label>
                                    <input type="file" name="photo" class="form-control @error('photo') is-invalid @enderror" 
                                        accept=".jpg,.jpeg,.png" id="photo-input">
                                    @error('photo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">
                                        <i class="fas fa-image"></i> JPG, PNG (max 2 Mo) - Laissez vide pour conserver l'actuelle
                                    </small>
                                </div>

                                <!-- Preview de la nouvelle photo -->
                                <div id="photo-preview" class="mb-3" style="display: none;">
                                    <label class="d-block">Aperçu de la nouvelle photo</label>
                                    <img id="preview-image" src="" alt="Preview" 
                                        class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                                </div>

                                <hr class="my-4">

                                <div class="text-right">
                                    <a href="{{ route('users.index') }}" class="btn btn-secondary btn-lg px-4 mr-2">
                                        <i class="fas fa-times"></i> Annuler
                                    </a>
                                    @can('modifier des utilisateurs')
                                    <button type="submit" class="btn btn-primary btn-lg px-5">
                                        <i class="fas fa-save"></i> Mettre à jour
                                    </button>
                                    @endcan     
                            </div>                                                                            
                        </form>
                    </div>  
                </div>  
            </div>  
        </div>  
    </div> 
</section>
@endsection

@section('styles')
    <style>
        .select2-container--default .select2-selection--single {
            height: 38px !important;
            padding: 6px 12px;
            border: 1px solid #ced4da;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 26px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }

        .form-group label {
            font-weight: 600;
            color: #34395e;
        }

        .text-danger {
            font-weight: bold;
        }

        hr {
            border-top: 2px solid #e8e8e8;
        }
    </style>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Initialisation Select2
            $('.select2').select2({
                placeholder: "Choisir une option",
                allowClear: true,
                width: '100%'
            });

            // Validation du formulaire
            $('form').on('submit', function(e) {
                let password = $('#password').val();
                let passwordConfirm = $('#password_confirmation').val();

                // Validation seulement si un mot de passe est saisi
                if (password || passwordConfirm) {
                    if (password !== passwordConfirm) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur',
                            text: 'Les mots de passe ne correspondent pas !',
                        });
                        return false;
                    }

                    if (password.length < 8) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur',
                            text: 'Le mot de passe doit contenir au moins 8 caractères !',
                        });
                        return false;
                    }
                }
            });

            // Preview de la photo
            $('#photo-input').on('change', function(e) {
                let file = e.target.files[0];
                if (file) {
                    // Vérifier la taille
                    if (file.size > 2048000) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Fichier trop volumineux',
                            text: 'La photo ne doit pas dépasser 2 Mo !',
                        });
                        $(this).val('');
                        $('#photo-preview').hide();
                        return;
                    }

                    // Afficher l'aperçu
                    let reader = new FileReader();
                    reader.onload = function(e) {
                        $('#preview-image').attr('src', e.target.result);
                        $('#photo-preview').show();
                    };
                    reader.readAsDataURL(file);
                } else {
                    $('#photo-preview').hide();
                }
            });
        });

        // Génération aléatoire d'un mot de passe
        function generatePassword(length = 12) {
            const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789@$!%*?&";
            let password = "";
            for (let i = 0; i < length; i++) {
                password += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            return password;
        }

        // Bouton Générer
        document.getElementById("generate-password").addEventListener("click", function () {
            let pwd = generatePassword(12);
            document.getElementById("password").value = pwd;
            document.getElementById("password_confirmation").value = pwd;
            
            Swal.fire({
                icon: 'success',
                title: 'Mot de passe généré',
                html: `<p>Nouveau mot de passe : <strong>${pwd}</strong></p><p class="text-danger">Copiez-le avant de soumettre le formulaire!</p>`,
                confirmButtonText: 'OK'
            });
        });

        // Bouton Voir / Masquer
        document.getElementById("toggle-password").addEventListener("click", function () {
            let input = document.getElementById("password");
            if (input.type === "password") {
                input.type = "text";
                this.innerHTML = '<i class="fas fa-eye-slash"></i>';
            } else {
                input.type = "password";
                this.innerHTML = '<i class="fas fa-eye"></i>';
            }
        });

        // Alert config si présent
        document.addEventListener('DOMContentLoaded', function () {
            @if(session('alert.config'))
                Swal.fire({!! session('alert.config') !!}).then(() => {
                    window.location.href = "{{ route('users.index') }}";
                });
            @endif
        });
    </script>
@endsection