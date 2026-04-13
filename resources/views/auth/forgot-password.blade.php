<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mot de passe oublié - COFIMA</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- === CSS IDENTIQUE AU LOGIN === -->
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

        .login-container {
            width: 100%;
            max-width: 1100px;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,.3);
            display: flex;
            overflow: hidden;
            min-height: 600px;
        }

        /* LEFT */
        .login-left {
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
        }

        .welcome-text p {
            opacity: .9;
            text-align: center;
        }

        /* RIGHT */
        .login-right {
            flex: 1;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-header h2 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 10px;
        }

        .login-header p {
            color: #666;
            font-size: .95rem;
        }

        .form-group label {
            font-weight: 600;
            font-size: .9rem;
        }

        .form-control {
            padding: 14px 15px;
            border-radius: 10px;
            border: 2px solid #e0e0e0;
        }

        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 4px rgba(48,79,254,.1);
        }

        .btn-login {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: #fff;
            padding: 15px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            width: 100%;
            box-shadow: 0 4px 15px rgba(26,35,126,.3);
        }

        .btn-login:hover {
            transform: translateY(-2px);
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            color: #999;
            font-size: .85rem;
        }

        @media(max-width: 968px){
            .login-container { flex-direction: column; }
        }
    </style>

    @vite(['resources/css/app.css', 'resources/css/auth.css', 'resources/js/app.js'])
</head>

<body>

<div class="login-container">

    <!-- LEFT -->
    <div class="login-left auth-left">
        <div class="logo-container">
            <img src="{{ asset('assets/img/logo_cofima_bon.jpg') }}" alt="COFIMA">
        </div>

        <div class="welcome-text">
            <h1>Mot de passe oublié ?</h1>
            <p>Nous vous aiderons à récupérer l’accès à votre compte en toute sécurité</p>
        </div>

        <div class="auth-illustration">
            <i class="fas fa-key"></i>
        </div>
    </div>

    <!-- RIGHT -->
    <div class="login-right">

        <div class="login-header mb-4">
            <h2>Réinitialisation</h2>
            <p>Entrez votre adresse email pour recevoir le lien</p>
        </div>

        <!-- STATUS -->
        <x-auth-session-status class="mb-3 text-success" :status="session('status')" />

        <!-- ERRORS -->
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="form-group mb-4">
                <label for="email">Adresse Email</label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    value="{{ old('email') }}"
                    class="form-control @error('email') is-invalid @enderror"
                    placeholder="Entrez votre adresse email"
                    required
                    autofocus
                >
                @error('email')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-paper-plane me-2"></i>
                Envoyer le lien de réinitialisation
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="{{ route('login') }}" class="text-decoration-underline text-muted">
                ← Retour à la connexion
            </a>
        </div>

        <div class="footer">
            COFIMA © {{ date('Y') }} — Tous droits réservés
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
