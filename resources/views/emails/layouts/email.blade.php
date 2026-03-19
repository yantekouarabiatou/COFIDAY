<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'COFIMA BENIN')</title>
    <style>
        body {
            background-color: #f4f4f7;
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
        }
        .email-container {
            width: 100%;
            padding: 20px 0;
        }
        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo {
            max-width: 140px;
            height: auto;
        }
        .card {
            background-color: #ffffff;
            max-width: 600px;
            margin: auto;
            border-radius: 8px;
            padding: 30px;
            border: 1px solid #e0e0e0;
        }
        .footer {
            text-align: center;
            color: #888;
            margin-top: 20px;
            font-size: 12px;
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="email-container">

        {{-- Logo --}}
        <div class="logo-container">
            <img src="https://cofima.cc/wp-content/uploads/2020/09/logo-cofima-bon.jpg" alt="Logo COFIMA" class="logo">
        </div>

        {{-- Contenu principal --}}
        @yield('content')

        {{-- Footer --}}
        <div class="footer">
            © {{ date('Y') }} COFIMA BENIN — Tous droits réservés.
        </div>

    </div>
</body>
</html>
