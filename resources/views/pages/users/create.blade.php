@extends('layaout')

@section('title', 'Nouvelle Utilisa')

@section('content')
    <section class="section">
        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h4><i class="fas fa-user-plus"></i> Créer un nouveau compte utilisateur</h4>
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

                            <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf

                                <h5 class="mb-3">
                                    <i class="fas fa-user"></i> Informations personnelles
                                </h5>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Nom <span class="text-danger">*</span></label>
                                            <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror"
                                                value="{{ old('nom') }}" placeholder="Ex: Dupont" required autofocus>
                                            @error('nom')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Prénom <span class="text-danger">*</span></label>
                                            <input type="text" name="prenom" class="form-control @error('prenom') is-invalid @enderror"
                                                value="{{ old('prenom') }}" placeholder="Ex: Jean" required>
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
                                                value="{{ old('username') }}" placeholder="Ex: jean.dupont" required>
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
                                                value="{{ old('email') }}" placeholder="exemple@email.com" required>
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
                                                    <option value="{{ $poste->id }}" {{ old('poste_id') == $poste->id ? 'selected' : '' }}>
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
                                                value="{{ old('telephone') }}" placeholder="+229 XX XX XX XX XX">
                                            @error('telephone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Sexe</label>
                                            <select name="sexe" class="form-control @error('sexe') is-invalid @enderror">
                                                <option value="">-- Choisir un sexe --</option>
                                                <option value="M" {{ old('sexe') == 'M' ? 'selected' : '' }}>Masculin</option>
                                                <option value="F" {{ old('sexe') == 'F' ? 'selected' : '' }}>Féminin</option>
                                            </select>
                                            @error('sexe')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                </div>

                                <hr class="my-4">

                                <h5 class="mb-3">
                                    <i class="fas fa-lock"></i> Sécurité
                                </h5>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Mot de passe <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="password" id="password" name="password"
                                                    class="form-control @error('password') is-invalid @enderror"
                                                    placeholder="Minimum 8 caractères" required autocomplete="new-password">

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
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror

                                            <small class="text-muted">
                                                <i class="fas fa-shield-alt"></i> Vous pouvez écrire votre propre mot de passe ou le générer automatiquement.
                                            </small>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Confirmer le mot de passe <span class="text-danger">*</span></label>
                                            <input type="password" id="password_confirmation" name="password_confirmation"
                                                class="form-control" placeholder="Retapez le mot de passe" required
                                                autocomplete="new-password">
                                        </div>
                                    </div>
                                </div>


                                <hr class="my-4">

                                <h5 class="mb-3">
                                    <i class="fas fa-cog"></i> <b>Paramètres supplémentaires</b>
                                </h5>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Rôle</label>
                                            <select name="role_id" class="form-control select2">
                                                <option value="">-- Choisir un rôle --</option>
                                                @foreach($roles as $role)
                                                    <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                                        {{ $role->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('role_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Statut</label>
                                            <select name="is_active" class="form-control select2">
                                                <option value="1" selected>Actif</option>
                                                <option value="0">Inactif</option>
                                            </select>
                                        </div>
                                    </div>

                                    

                                    <div class="row col-12">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Supérieur hiérarchique</label>
                                            <select name="manager_id" class="form-control select2 @error('superieur_id') is-invalid @enderror">
                                                <option value="">-- Aucun supérieur --</option>
                                                @foreach($managers as $manager)
                                                    <option value="{{ $manager->id }}" {{ old('superieur_id') == $manager->id ? 'selected' : '' }}>
                                                        {{ $manager->nom }} {{ $manager->prenom }}
                                                        @if($manager->roles->isNotEmpty())
                                                            — {{ $manager->roles->first()->name }}
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('superieur_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">
                                                <i class="fas fa-sitemap"></i> Responsable direct de cet utilisateur
                                            </small>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Date d'embauche</label>
                                            <input type="date" name="date_embauche" class="form-control @error('date_embauche') is-invalid @enderror"
                                                value="{{ old('date_embauche') }}">
                                            @error('date_embauche')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">
                                                <i class="fas fa-calendar-alt"></i> Facultatif
                                            </small>
                                        </div>
                                    </div>
                                 </div>
                                </div>

                                <div class="form-group">
                                    <label>Photo de profil</label>
                                    <input type="file" name="photo" class="form-control @error('photo') is-invalid @enderror"
                                        accept=".jpg,.jpeg,.png">
                                    @error('photo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">
                                        <i class="fas fa-image"></i> JPG, PNG (max 2 Mo)
                                    </small>
                                </div>

                                <!-- <div class="form-group">
                                    <label>Notes / Observations</label>
                                    <textarea name="notes" class="form-control" rows="3"
                                        placeholder="Informations complémentaires...">{{ old('notes') }}</textarea>
                                </div> -->

                                <hr class="my-4">

                                <div class="text-right">
                                    <a href="{{ route('users.index') }}" class="btn btn-secondary btn-lg px-4 mr-2">
                                        <i class="fas fa-times"></i> Annuler
                                    </a>
                                    @can('créer des utilisateurs')
                                    <button type="submit" class="btn btn-primary btn-lg px-5">
                                        <i class="fas fa-save"></i> Créer l'utilisateur
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
                let password = $('input[name="password"]').val();
                let passwordConfirm = $('input[name="password_confirmation"]').val();

                if (password !== passwordConfirm) {
                    e.preventDefault();
                    alert('Les mots de passe ne correspondent pas !');
                    return false;
                }

                if (password.length < 8) {
                    e.preventDefault();
                    alert('Le mot de passe doit contenir au moins 8 caractères !');
                    return false;
                }
            });

            // Preview de la photo
            $('input[name="photo"]').on('change', function(e) {
                let file = e.target.files[0];
                if (file) {
                    if (file.size > 2048000) {
                        alert('La photo ne doit pas dépasser 2 Mo !');
                        $(this).val('');
                    }
                }
            });
        });
    </script>

    <script>
    document.addEventListener("DOMContentLoaded", function () {

        // Génération aléatoire d'un mot de passe
        function generatePassword(length = 10) {
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
    });
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        @if(session('alert.config'))
            Swal.fire({!! session('alert.config') !!}).then(() => {
                window.location.href = "{{ route('users.index') }}";
            });
        @endif
    });
    </script>


    @endsection
@endsection
