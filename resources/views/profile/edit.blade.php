@extends('layaout')

@section('title', 'Modifier mon Profil')

@section('content')
    <section class="section">
        <div class="section-body">
            <!-- Breadcrumb -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body py-3">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0 bg-transparent">
                                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('user-profile.show', $user->id) }}">Mon Profil</a></li>
                                    <li class="breadcrumb-item active">Modifier</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Colonne gauche: Photo et infos -->
                <div class="col-md-4">
                    <!-- Carte Profil -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h4><i class="fas fa-user-circle"></i> Photo de Profil</h4>
                        </div>
                        <div class="card-body text-center">
                            <div class="mb-3">
                                @if($user->photo)
                                    <img src="{{ asset('storage/' . $user->photo) }}"
                                         alt="Photo profil"
                                         class="img-fluid rounded-circle"
                                         style="width: 180px; height: 180px; object-fit: cover;">
                                @else
                                    <div class="avatar-placeholder rounded-circle d-inline-flex align-items-center justify-content-center"
                                         style="width: 180px; height: 180px; background: #e9ecef; font-size: 60px;">
                                        <i class="fas fa-user text-muted"></i>
                                    </div>
                                @endif
                            </div>
                            <h5 class="mb-1">{{ $user->prenom }} {{ $user->nom }}</h5>
                            <p class="text-muted mb-0">{{ $user->poste->intitule ?? 'Non spécifié' }}</p>
                            <div class="mt-3">
                                <span class="badge badge-{{ $user->is_active ? 'success' : 'danger' }}">
                                    {{ $user->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Informations de connexion -->
                    <div class="card card-primary mt-4">
                        <div class="card-header">
                            <h4><i class="fas fa-lock"></i> Sécurité</h4>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" class="form-control" value="{{ $user->email }}" readonly>
                            </div>
                            <div class="form-group">
                                <label>Nom d'utilisateur</label>
                                <input type="text" class="form-control" value="{{ $user->username }}" readonly>
                            </div>
                            <button type="button" class="btn btn-warning btn-block" data-toggle="modal" data-target="#changePasswordModal">
                                <i class="fas fa-key"></i> Changer le mot de passe
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Colonne droite: Formulaire et statistiques -->
                <div class="col-md-8">
                    <!-- Formulaire de modification -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h4><i class="fas fa-edit"></i> Modifier le Profil</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('user-profile.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Nom <span class="text-danger">*</span></label>
                                            <input type="text" name="nom"
                                                   class="form-control @error('nom') is-invalid @enderror"
                                                   value="{{ old('nom', $user->nom) }}"
                                                   placeholder="Votre nom" required>
                                            @error('nom')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Prénom <span class="text-danger">*</span></label>
                                            <input type="text" name="prenom"
                                                   class="form-control @error('prenom') is-invalid @enderror"
                                                   value="{{ old('prenom', $user->prenom) }}"
                                                   placeholder="Votre prénom" required>
                                            @error('prenom')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Téléphone</label>
                                            <input type="text" name="telephone"
                                                   class="form-control @error('telephone') is-invalid @enderror"
                                                   value="{{ old('telephone', $user->telephone) }}"
                                                   placeholder="Ex: +225 01 23 45 67 89">
                                            @error('telephone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Poste</label>
                                            <select name="poste_id" class="form-control select2 @error('poste_id') is-invalid @enderror">
                                                <option value="">Sélectionner un poste...</option>
                                                @foreach($postes as $poste)
                                                    <option value="{{ $poste->id }}"
                                                            {{ $user->poste_id == $poste->id ? 'selected' : '' }}>
                                                        {{ $poste->intitule }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('poste_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Photo de profil</label>
                                    <div class="custom-file">
                                        <input type="file" name="photo"
                                               class="custom-file-input @error('photo') is-invalid @enderror"
                                               id="photoUpload"
                                               accept="image/jpeg,image/png,image/jpg">
                                        <label class="custom-file-label" for="photoUpload">
                                            {{ $user->photo ? basename($user->photo) : 'Choisir une image...' }}
                                        </label>
                                        @error('photo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="text-muted">Formats acceptés: JPG, PNG. Taille max: 2MB</small>
                                </div>

                                @if(auth()->user()->hasRole('admin|super-admin'))
                                    <div class="form-group">
                                        <label>Rôle</label>
                                        <select name="role_name" class="form-control @error('role_name') is-invalid @enderror">
                                            <option value="">Sélectionner un rôle...</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->name }}"
                                                        {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                                                    {{ ucwords(str_replace('-', ' ', $role->name)) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('role_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @else
                                    <div class="form-group">
                                        <label>Rôle</label>
                                        <input type="text" class="form-control"
                                               value="{{ $user->roles->first()?->name ? ucwords(str_replace('-', ' ', $user->roles->first()->name)) : 'Non défini' }}"
                                               readonly>
                                    </div>
                                @endif

                                <div class="form-group">
                                    <label>Créé par</label>
                                    <input type="text" class="form-control"
                                           value="{{ $user->creator->fullName ?? 'Système' }}" readonly>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Date de création</label>
                                            <input type="text" class="form-control"
                                                   value="{{ $user->created_at->format('d/m/Y H:i') }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Dernière modification</label>
                                            <input type="text" class="form-control"
                                                   value="{{ $user->updated_at->format('d/m/Y H:i') }}" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-right mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg px-5">
                                        <i class="fas fa-save"></i> Mettre à jour
                                    </button>
                                    <a href="{{ route('user-profile.show', $user->id) }}" class="btn btn-outline-secondary btn-lg ml-2">
                                        <i class="fas fa-times"></i> Annuler
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Statistiques de temps -->
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <div class="card card-statistic-1">
                                <div class="card-icon bg-primary">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="card-wrap">
                                    <div class="card-header">
                                        <h4>Heures ce mois</h4>
                                    </div>
                                    <div class="card-body">
                                        {{ $statistiques['heures_mois_en_cours'] ?? 0 }}h
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card card-statistic-1">
                                <div class="card-icon bg-success">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <div class="card-wrap">
                                    <div class="card-header">
                                        <h4>Journées validées</h4>
                                    </div>
                                    <div class="card-body">
                                        {{ $statistiques['journees_validees'] ?? 0 }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card card-statistic-1">
                                <div class="card-icon bg-warning">
                                    <i class="fas fa-umbrella-beach"></i>
                                </div>
                                <div class="card-wrap">
                                    <div class="card-header">
                                        <h4>Congés pris</h4>
                                    </div>
                                    <div class="card-body">
                                        {{ $statistiques['conges_pris'] ?? 0 }} jours
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal changement de mot de passe -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-key mr-2"></i> Changer le mot de passe</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('user-profile.change-password') }}" method="POST" id="changePasswordForm">
                    @csrf
                    <div class="modal-body">
                        <!-- Messages d'erreur généraux -->
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Mot de passe actuel <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" name="current_password" id="current_password"
                                            class="form-control @error('current_password') is-invalid @enderror"
                                            required
                                            placeholder="Votre mot de passe actuel">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary toggle-password"
                                                    type="button" data-target="current_password">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                        @error('current_password')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nouveau mot de passe <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" name="new_password" id="new_password"
                                            class="form-control @error('new_password') is-invalid @enderror"
                                            required
                                            placeholder="Nouveau mot de passe sécurisé"
                                            onkeyup="checkPasswordStrength()">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary toggle-password"
                                                    type="button" data-target="new_password">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Force du mot de passe -->
                                    <div class="mt-2">
                                        <div class="progress" style="height: 6px;">
                                            <div id="passwordStrengthBar" class="progress-bar"
                                                role="progressbar" style="width: 0%"></div>
                                        </div>
                                        <small id="passwordStrengthText" class="text-muted">Force du mot de passe</small>
                                    </div>

                                    <!-- Règles de validation -->
                                    <div class="password-rules mt-3">
                                        <small class="text-muted d-block mb-2">Le mot de passe doit contenir :</small>
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="form-check mb-1">
                                                    <input type="checkbox" class="form-check-input" id="ruleLength" disabled>
                                                    <label class="form-check-label small" for="ruleLength">
                                                        <i class="fas fa-check-circle text-success d-none"></i>
                                                        <i class="fas fa-times-circle text-danger"></i>
                                                        Minimum 8 caractères
                                                    </label>
                                                </div>
                                                <div class="form-check mb-1">
                                                    <input type="checkbox" class="form-check-input" id="ruleUppercase" disabled>
                                                    <label class="form-check-label small" for="ruleUppercase">
                                                        <i class="fas fa-check-circle text-success d-none"></i>
                                                        <i class="fas fa-times-circle text-danger"></i>
                                                        Majuscule
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-check mb-1">
                                                    <input type="checkbox" class="form-check-input" id="ruleLowercase" disabled>
                                                    <label class="form-check-label small" for="ruleLowercase">
                                                        <i class="fas fa-check-circle text-success d-none"></i>
                                                        <i class="fas fa-times-circle text-danger"></i>
                                                        Minuscule
                                                    </label>
                                                </div>
                                                <div class="form-check mb-1">
                                                    <input type="checkbox" class="form-check-input" id="ruleNumber" disabled>
                                                    <label class="form-check-label small" for="ruleNumber">
                                                        <i class="fas fa-check-circle text-success d-none"></i>
                                                        <i class="fas fa-times-circle text-danger"></i>
                                                        Chiffre
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="ruleSpecial" disabled>
                                                    <label class="form-check-label small" for="ruleSpecial">
                                                        <i class="fas fa-check-circle text-success d-none"></i>
                                                        <i class="fas fa-times-circle text-danger"></i>
                                                        Caractère spécial
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @error('new_password')
                                        <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Confirmer le mot de passe <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" name="new_password_confirmation" id="new_password_confirmation"
                                            class="form-control"
                                            required
                                            placeholder="Confirmez le nouveau mot de passe">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary toggle-password"
                                                    type="button" data-target="new_password_confirmation">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <small id="passwordMatchText" class="text-danger d-none">
                                            <i class="fas fa-times-circle"></i> Les mots de passe ne correspondent pas
                                        </small>
                                        <small id="passwordMatchSuccess" class="text-success d-none">
                                            <i class="fas fa-check-circle"></i> Les mots de passe correspondent
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Générer un mot de passe sécurisé</label>
                                    <div class="input-group">
                                        <input type="text" id="generatedPassword"
                                            class="form-control"
                                            placeholder="Mot de passe généré"
                                            readonly>
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-primary" type="button" id="generatePassword">
                                                <i class="fas fa-random"></i> Générer
                                            </button>
                                            <button class="btn btn-outline-success" type="button" id="copyPassword">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <small class="text-muted d-block mt-2">
                                        <i class="fas fa-info-circle"></i>
                                        Le mot de passe généré est automatiquement copié dans les champs
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i> Annuler
                        </button>
                        <button type="submit" class="btn btn-primary" id="submitPasswordBtn" disabled>
                            <i class="fas fa-save mr-1"></i> Mettre à jour
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
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

        .card-statistic-1 {
            margin-bottom: 0;
            transition: transform 0.3s;
        }

        .card-statistic-1:hover {
            transform: translateY(-5px);
        }

        .avatar-placeholder {
            border: 2px solid #dee2e6;
        }

        .custom-file-label::after {
            content: "Parcourir";
        }

        .password-rules .form-check-label i {
            font-size: 12px;
            margin-right: 5px;
        }

        .progress-bar {
            transition: width 0.5s ease;
        }

        #passwordMatchText, #passwordMatchSuccess {
            font-size: 12px;
        }
    </style>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialisation Select2
        $('.select2').select2({
            placeholder: "Sélectionner...",
            allowClear: true,
            width: '100%'
        });

        // Affichage du nom du fichier photo
        $('#photoUpload').on('change', function() {
            var fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName || 'Choisir une image...');
        });

        // Validation du formulaire principal
        $('form').on('submit', function(e) {
            if (!$(this).attr('id') || $(this).attr('id') !== 'changePasswordForm') {
                $('button[type="submit"]').prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin"></i> Mise à jour...');
            }
        });

        // ============ GESTION DU MODAL DE MOT DE PASSE ============

        // Ouvrir le modal si il y a des erreurs de mot de passe
        @if($errors->has('current_password') || $errors->has('new_password'))
            $('#changePasswordModal').modal('show');
        @endif

        // Toggle afficher/masquer mot de passe dans le modal
        $('.toggle-password').click(function() {
            const targetId = $(this).data('target');
            const input = $('#' + targetId);
            const icon = $(this).find('i');

            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                input.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

        // Vérification de la correspondance des mots de passe
        $('#new_password, #new_password_confirmation').on('keyup', function() {
            checkPasswordMatch();
        });

        // Générer un mot de passe sécurisé
        $('#generatePassword').click(function() {
            const password = generateSecurePassword();
            $('#generatedPassword').val(password);
            $('#new_password').val(password);
            $('#new_password_confirmation').val(password);

            // Déclencher les vérifications
            $('#new_password').trigger('keyup');
            $('#new_password_confirmation').trigger('keyup');

            // Afficher notification
            showToast('Mot de passe généré et copié!', 'success');
        });

        // Copier le mot de passe généré
        $('#copyPassword').click(function() {
            const generatedPassword = $('#generatedPassword').val();
            if (generatedPassword) {
                navigator.clipboard.writeText(generatedPassword).then(function() {
                    showToast('Mot de passe copié dans le presse-papier!', 'success');
                }).catch(function(err) {
                    console.error('Erreur lors de la copie:', err);
                    // Fallback pour les anciens navigateurs
                    const tempInput = document.createElement('input');
                    tempInput.value = generatedPassword;
                    document.body.appendChild(tempInput);
                    tempInput.select();
                    document.execCommand('copy');
                    document.body.removeChild(tempInput);
                    showToast('Mot de passe copié!', 'success');
                });
            }
        });

        // Validation du formulaire de mot de passe
        $('#current_password, #new_password, #new_password_confirmation').on('keyup', function() {
            checkPasswordFormValidity();
        });

        // Vérifier la force du mot de passe
        $('#new_password').on('keyup', function() {
            checkPasswordStrength($(this).val(), true);
        });

        // Validation finale avant soumission
        $('#changePasswordForm').on('submit', function(e) {
            return validatePasswordForm(e);
        });

        // Réinitialiser le modal quand il se ferme
        $('#changePasswordModal').on('hidden.bs.modal', function() {
            resetPasswordModal();
        });
    });

    // ============ FONCTIONS UTILITAIRES ============

    // Vérifier la correspondance des mots de passe
    function checkPasswordMatch() {
        const password = $('#new_password').val();
        const confirmPassword = $('#new_password_confirmation').val();

        if (password && confirmPassword) {
            if (password === confirmPassword) {
                $('#passwordMatchText').addClass('d-none');
                $('#passwordMatchSuccess').removeClass('d-none');
            } else {
                $('#passwordMatchText').removeClass('d-none');
                $('#passwordMatchSuccess').addClass('d-none');
            }
        }
    }

    // Générer un mot de passe sécurisé
    function generateSecurePassword() {
        const length = 16;
        const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+~`|}{[]:;?><,./-=";
        let password = "";

        // Assurer au moins un caractère de chaque type
        password += "ABCDEFGHIJKLMNOPQRSTUVWXYZ".charAt(Math.floor(Math.random() * 26));
        password += "abcdefghijklmnopqrstuvwxyz".charAt(Math.floor(Math.random() * 26));
        password += "0123456789".charAt(Math.floor(Math.random() * 10));
        password += "!@#$%^&*()_+~`|}{[]:;?><,./-=".charAt(Math.floor(Math.random() * 28));

        // Remplir le reste
        for (let i = 4; i < length; i++) {
            password += charset.charAt(Math.floor(Math.random() * charset.length));
        }

        // Mélanger le mot de passe
        return password.split('').sort(() => Math.random() - 0.5).join('');
    }

    // Vérifier la validité du formulaire de mot de passe
    function checkPasswordFormValidity() {
        const currentPassword = $('#current_password').val();
        const newPassword = $('#new_password').val();
        const confirmPassword = $('#new_password_confirmation').val();
        const passwordValid = checkPasswordStrength(newPassword, true);

        const formValid = currentPassword && newPassword && confirmPassword &&
                         (newPassword === confirmPassword) && passwordValid;

        $('#submitPasswordBtn').prop('disabled', !formValid);
        return formValid;
    }

    // Vérifier la force du mot de passe
    function checkPasswordStrength(password, updateRules = false) {
        if (!password) {
            $('#passwordStrengthBar').css('width', '0%').removeClass().addClass('progress-bar');
            $('#passwordStrengthText').text('Force du mot de passe').removeClass().addClass('text-muted');
            if (updateRules) resetPasswordRules();
            return false;
        }

        let strength = 0;
        const rules = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[^A-Za-z0-9]/.test(password)
        };

        if (updateRules) {
            Object.keys(rules).forEach(rule => {
                const ruleName = rule.charAt(0).toUpperCase() + rule.slice(1);
                const checkIcon = $(`#rule${ruleName}`).siblings('label').find('.fa-check-circle');
                const timesIcon = $(`#rule${ruleName}`).siblings('label').find('.fa-times-circle');
                const checkbox = $(`#rule${ruleName}`);

                if (rules[rule]) {
                    checkIcon.removeClass('d-none');
                    timesIcon.addClass('d-none');
                    checkbox.prop('checked', true);
                } else {
                    checkIcon.addClass('d-none');
                    timesIcon.removeClass('d-none');
                    checkbox.prop('checked', false);
                }
            });
        }

        // Calcul du score
        Object.values(rules).forEach(isValid => {
            if (isValid) strength += 20;
        });

        // Mettre à jour la barre de progression
        const progressBar = $('#passwordStrengthBar');
        const strengthText = $('#passwordStrengthText');

        progressBar.css('width', strength + '%');

        if (strength <= 40) {
            progressBar.removeClass().addClass('progress-bar bg-danger');
            strengthText.removeClass().addClass('text-danger').text('Faible');
        } else if (strength <= 60) {
            progressBar.removeClass().addClass('progress-bar bg-warning');
            strengthText.removeClass().addClass('text-warning').text('Moyen');
        } else if (strength <= 80) {
            progressBar.removeClass().addClass('progress-bar bg-info');
            strengthText.removeClass().addClass('text-info').text('Bon');
        } else {
            progressBar.removeClass().addClass('progress-bar bg-success');
            strengthText.removeClass().addClass('text-success').text('Excellent');
        }

        return strength >= 60;
    }

    // Réinitialiser les règles de mot de passe
    function resetPasswordRules() {
        $('.password-rules .fa-check-circle').addClass('d-none');
        $('.password-rules .fa-times-circle').removeClass('d-none');
        $('.password-rules input[type="checkbox"]').prop('checked', false);
    }

    // Réinitialiser le modal de mot de passe
    function resetPasswordModal() {
        $('#current_password').val('');
        $('#new_password').val('');
        $('#new_password_confirmation').val('');
        $('#generatedPassword').val('');

        resetPasswordRules();
        checkPasswordStrength('', true);

        $('#passwordMatchText').addClass('d-none');
        $('#passwordMatchSuccess').addClass('d-none');

        $('#passwordStrengthBar').css('width', '0%').removeClass().addClass('progress-bar');
        $('#passwordStrengthText').text('Force du mot de passe').removeClass().addClass('text-muted');

        $('#submitPasswordBtn').prop('disabled', true);

        // Réinitialiser les icônes d'œil
        $('.toggle-password i').removeClass('fa-eye-slash').addClass('fa-eye');
        $('.toggle-password').each(function() {
            const targetId = $(this).data('target');
            $('#' + targetId).attr('type', 'password');
        });
    }

    // Validation finale du formulaire de mot de passe
    function validatePasswordForm(e) {
        const password = $('#new_password').val();
        const confirmPassword = $('#new_password_confirmation').val();

        if (password !== confirmPassword) {
            e.preventDefault();
            showToast('Les mots de passe ne correspondent pas!', 'error');
            return false;
        }

        if (!checkPasswordStrength(password)) {
            e.preventDefault();
            showToast('Le mot de passe n\'est pas assez sécurisé! Il doit être au moins "Moyen".', 'error');
            return false;
        }

        // Désactiver le bouton pour éviter les doubles clics
        $('#submitPasswordBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Mise à jour...');
        return true;
    }

    // Afficher une notification toast
    function showToast(message, type = 'info') {
        if (typeof Toastify !== 'undefined') {
            Toastify({
                text: message,
                duration: 3000,
                gravity: "top",
                position: "right",
                backgroundColor: type === 'success' ? "#28a745" :
                              type === 'error' ? "#dc3545" :
                              type === 'warning' ? "#ffc107" : "#17a2b8",
            }).showToast();
        } else {
            alert(message);
        }
    }

    // Initialiser la force du mot de passe au chargement si le modal est ouvert
    @if($errors->has('new_password'))
        $(window).on('load', function() {
            const password = $('#new_password').val();
            if (password) {
                checkPasswordStrength(password, true);
                checkPasswordFormValidity();
            }
        });
    @endif
</script>
@endsection
