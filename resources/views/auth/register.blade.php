<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inscription - COFIMA</title>

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #1A237E;
            --primary-light: #3949AB;
            --accent-color: #304FFE;
            --bg-gradient-start: #ffffff;
            --bg-gradient-end: #e1dde5;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--bg-gradient-start), var(--bg-gradient-end));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .auth-container {
            width: 100%;
            max-width: 1100px;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,.3);
            display: flex;
            overflow: hidden;
            min-height: 650px;
        }

        /* LEFT */
        .auth-left {
            flex: 1;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            padding: 60px 40px;
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .logo-container img {
            max-width: 200px;
            background: #fff;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,.2);
        }

        .welcome-text h1 {
            font-size: 2.3rem;
            font-weight: 700;
            text-align: center;
        }

        .welcome-text p {
            opacity: .9;
            text-align: center;
        }

        .illustration i {
            font-size: 110px;
            opacity: .3;
            margin-top: 40px;
        }

        /* RIGHT */
        .auth-right {
            flex: 1;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .auth-header h2 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 10px;
        }

        .auth-header p {
            color: #666;
            font-size: .95rem;
        }

        .form-control,
        .form-select {
            padding: 14px 15px;
            border-radius: 10px;
            border: 2px solid #e0e0e0;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 4px rgba(48,79,254,.1);
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: #fff;
            padding: 15px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            width: 100%;
            box-shadow: 0 4px 15px rgba(26,35,126,.3);
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            color: #999;
            font-size: .85rem;
        }

        @media(max-width: 968px){
            .auth-container { flex-direction: column; }
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

<div class="auth-container">

    <!-- LEFT -->
    <div class="auth-left">
        <div class="logo-container">
            <img src="{{ asset('assets/img/logo_cofima_bon.jpg') }}" alt="COFIMA">
        </div>

        <div class="welcome-text">
            <h1>Créer un compte</h1>
            <p>Rejoignez la plateforme sécurisée COFIMA</p>
        </div>

        <div class="illustration">
            <i class="fas fa-user-shield"></i>
        </div>
    </div>

    <!-- RIGHT -->
    <div class="auth-right">

        <div class="auth-header mb-4">
            <h2>Inscription</h2>
            <p>Veuillez remplir les informations ci-dessous</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nom</label>
                    <input type="text" name="nom" value="{{ old('nom') }}"
                        class="form-control @error('nom') is-invalid @enderror" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Prénom</label>
                    <input type="text" name="prenom" value="{{ old('prenom') }}"
                        class="form-control @error('prenom') is-invalid @enderror" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Nom d'utilisateur</label>
                <input type="text" name="username" value="{{ old('username') }}"
                    class="form-control @error('username') is-invalid @enderror" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" name="email" value="{{ old('email') }}"
                    class="form-control @error('email') is-invalid @enderror" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Poste</label>
                <select name="poste_id" class="form-select @error('poste_id') is-invalid @enderror">
                    <option value="">-- Choisir un poste --</option>
                    @foreach(\App\Models\Poste::all() as $poste)
                        <option value="{{ $poste->id }}" {{ old('poste_id') == $poste->id ? 'selected' : '' }}>
                            {{ $poste->libelle ?? $poste->intitule }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Mot de passe</label>
                <input type="password" name="password"
                    class="form-control @error('password') is-invalid @enderror" required>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Confirmer le mot de passe</label>
                <input type="password" name="password_confirmation"
                    class="form-control" required>
            </div>

            <button type="submit" class="btn-primary-custom">
                <i class="fas fa-user-plus me-2"></i>
                Créer mon compte
            </button>
        </form>

        <div class="text-center mt-4">
            <small>
                Déjà un compte ?
                <a href="{{ route('login') }}" class="text-decoration-underline">Se connecter</a>
            </small>
        </div>

        <div class="footer">
            COFIMA © {{ date('Y') }} — Tous droits réservés
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
