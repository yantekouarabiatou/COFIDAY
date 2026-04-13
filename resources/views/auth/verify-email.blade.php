<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vérification de l’email - COFIMA</title>

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- === CSS COMMUN AUTH PAGES === -->
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
        .login-right {
            flex: 1;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
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

        .btn-link-custom {
            background: transparent;
            border: none;
            color: #666;
            text-decoration: underline;
            font-size: .9rem;
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

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

<div class="login-container">

    <!-- LEFT -->
    <div class="login-left">
        <div class="logo-container">
            <img src="{{ asset('assets/img/logo_cofima_bon.jpg') }}" alt="COFIMA">
        </div>

        <div class="welcome-text">
            <h1>Vérification requise</h1>
            <p>Un dernier pas avant d’accéder à votre espace</p>
        </div>

        <div class="illustration">
            <i class="fas fa-envelope-circle-check"></i>
        </div>
    </div>

    <!-- RIGHT -->
    <div class="login-right">

        <div class="login-header mb-4">
            <h2>Vérifiez votre email</h2>
            <p>
                Un lien de vérification a été envoyé à votre adresse email.
                <br>Veuillez cliquer sur ce lien pour activer votre compte.
            </p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="alert alert-success mb-4">
                Un nouveau lien de vérification a été envoyé avec succès.
            </div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}" class="mb-3">
            @csrf
            <button type="submit" class="btn-login">
                <i class="fas fa-paper-plane me-2"></i>
                Renvoyer l’email de vérification
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-link-custom">
                Se déconnecter
            </button>
        </form>

        <div class="footer">
            COFIMA © {{ date('Y') }} — Tous droits réservés
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
