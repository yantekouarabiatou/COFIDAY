<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compte mis à jour</title>

    <style>
        body {
            background-color: #f4f4f7;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }
        .email-container { width: 100%; padding: 20px 0; }
        .logo-container { text-align: center; margin-bottom: 20px; }
        .logo { max-width: 140px; height: auto; }
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
        .info-list li { margin-bottom: 8px; font-size: 15px; }
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

    <div class="logo-container">
        <img src="https://cofima.cc/wp-content/uploads/2020/09/logo-cofima-bon.jpg" class="logo">
    </div>

    <div class="header">Mise à jour de votre compte</div>

    <div class="card">
        <h2 style="color:#333;">Bonjour {{ $user->prenom }} {{ $user->nom }},</h2>

        <p style="font-size:15px; color:#555;">
            Vos informations personnelles ont été modifiées par l'administrateur <strong>{{ $modifier }}</strong>.
        </p>

        <p>Voici un résumé de votre profil à jour :</p>

        <ul class="info-list">
            <li><strong>Nom :</strong> {{ $user->nom }}</li>
            <li><strong>Prénom :</strong> {{ $user->prenom }}</li>
            <li><strong>Email :</strong> {{ $user->email }}</li>
            <li><strong>Téléphone :</strong> {{ $user->telephone ?? 'N/A' }}</li>
            <li><strong>Nom d'utilisateur :</strong> {{ $user->username }}</li>
            <li><strong>Poste :</strong> {{ $user->poste?->intitule ?? 'N/A' }}</li>
            <li><strong>Rôle :</strong> {{ $roleName ?? 'N/A' }}</li>
            <li><strong>Compte actif :</strong> {{ $user->is_active ? 'Oui' : 'Non' }}</li>
        </ul>

        <p style="font-size:14px; color:#555;">
            Si vous n'êtes pas à l'origine de cette modification, veuillez contacter immédiatement l'équipe support.
        </p>
    </div>

    <div class="footer">
        © {{ date('Y') }} COFIMA BENIN — Tous droits réservés.
    </div>

</div>
</body>
</html>
