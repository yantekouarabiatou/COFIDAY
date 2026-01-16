<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <style>
        :root {
            --primary-color: #1A237E; /*244584*/
            --primary-dark: #0D1642;
            --primary-light: #3949AB;
            --accent-color: #304FFE;
            --bg-gradient-start: #ffffffff;
            --bg-gradient-end: #e1dde5ff;
        }
        body {
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: "Segoe UI", system-ui, sans-serif;
        }

        .error-card {
            background: #fff;
            max-width: 500px;
            width: 100%;
            padding: 40px 30px;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.12);
            text-align: center;
            animation: fadeIn 0.6s ease-in-out;
        }

        .error-code {
            font-size: 6rem;
            font-weight: 800;
            color: #1A237E;
            margin-bottom: 10px;
        }

        .error-message {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
        }

        .error-description {
            color: #6c757d;
            font-size: 1rem;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .error-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 22px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-secondary {
            background: #6c757d;
            color: #fff;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .btn-primary {
            background: #3949AB;
            color: #fff;
        }

        .btn-primary:hover {
            background: #1A237E;
            transform: translateY(-2px);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>

<div class="error-card">
    <div class="error-code">@yield('code')</div>

    <div class="error-message">
        @yield('message')
    </div>

    <div class="error-description">
        @yield('description')
    </div>

    <div class="error-actions">
        <a href="{{ url()->previous() }}" class="btn btn-secondary">
            ⬅ Retour
        </a>

        <a href="{{ route('dashboard') }}" class="btn btn-primary">
            Dashboard
        </a>
    </div>
</div>

</body>
</html>
