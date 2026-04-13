<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Création de compte</title>

    <style>
        body {
            background-color: #f4f4f7;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
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
        .header {
            background: #4a70b7;
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            border-radius: 8px 8px 0 0;
        }
        .card {
            background-color: #ffffff;
            max-width: 600px;
            margin: auto;
            border-radius: 8px;
            padding: 30px;
            border: 1px solid #e0e0e0;
        }
        .content {
            padding: 20px;
        }
        .info-list li {
            margin-bottom: 8px;
            font-size: 15px;
        }
        .note {
            background: #fff4c2;
            padding: 12px 15px;
            border-left: 4px solid #e0b200;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 14px;
            color: #444;
        }
        .btn {
            display: inline-block;
            margin-top: 20px;
            background: #4a70b7;
            color: white !important;
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
        }
        .btn-secondary {
            background: #2d8f64;
        }
        .footer {
            text-align: center;
            color: #888;
            margin-top: 20px;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <div class="email-container">

        <!-- LOGO -->
        <div class="logo-container">
            <img src="https://cofima.cc/wp-content/uploads/2020/09/logo-cofima-bon.jpg" alt="Logo COFIMA" class="logo">
        </div>

        <!-- HEADER -->
        <div class="header">
            Votre compte a été créé
        </div>

        <!-- CARD -->
        <div class="card">
            <div class="content">
                <h2 style="color:#333;">Bonjour {{ $user->prenom }} {{ $user->nom }},</h2>

                <p style="font-size:15px; color:#555;">
                    Votre compte a été créé avec succès. Voici les informations associées :
                </p>

                <ul class="info-list" style="padding-left:15px; color:#333;">
                    <li><strong>Nom d'utilisateur :</strong> {{ $user->username }}</li>
                    <li><strong>Email :</strong> {{ $user->email }}</li>
                    <li><strong>Téléphone :</strong> {{ $user->telephone ?? 'N/A' }}</li>
                    <li><strong>Poste :</strong> {{ $user->poste?->intitule ?? 'N/A' }}</li>
                    <li><strong>Rôle :</strong> {{ $roleName ?? 'N/A' }}</li>
                    <li><strong>Créé par :</strong> {{ $user->creator?->nom ?? 'N/A' }} {{ $user->creator?->prenom ?? '' }}</li>
                    <li><strong>Compte actif :</strong> {{ $user->is_active ? 'Oui' : 'Non' }}</li>
                </ul>

                <div class="note">
                    🔐 Pour des raisons de sécurité, nous vous recommandons de définir vous-même un mot de passe avant votre première connexion.  
                    Cliquez simplement sur <strong>"Réinitialiser mon mot de passe"</strong>.
                </div>

                <!-- Bouton Connexion -->
                <a href="{{ url('/login') }}" class="btn">Se connecter</a>

                <!-- Bouton Réinitialiser -->
                <a href="{{ url('/forgot-password') }}" class="btn btn-secondary">Réinitialiser mon mot de passe</a>

            </div>
        </div>

        <!-- FOOTER -->
        <div class="footer">
            © {{ date('Y') }} COFIMA BENIN — Tous droits réservés.
        </div>

    </div>
</body>
</html>
