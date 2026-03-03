<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { background-color: #f4f4f7; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        .email-container { width: 100%; padding: 20px 0; }
        .logo-container { text-align: center; margin-bottom: 20px; }
        .logo { max-width: 140px; height: auto; }
        .header { background: #4a70b7; color: white; padding: 20px; text-align: center; font-size: 22px; font-weight: bold; border-radius: 8px 8px 0 0; }
        .card { background-color: #ffffff; max-width: 600px; margin: auto; border-radius: 8px; padding: 30px; border: 1px solid #e0e0e0; }
        .note { background: #fff4c2; padding: 12px 15px; border-left: 4px solid #e0b200; border-radius: 5px; margin-top: 20px; font-size: 14px; color: #444; }
        .btn { display: inline-block; margin-top: 20px; background: #4a70b7; color: white !important; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: bold; }
        .footer { text-align: center; color: #888; margin-top: 20px; font-size: 12px; }
        .url-fallback { word-break: break-all; font-size: 12px; color: #888; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="email-container">

        <div class="logo-container">
            <img src="https://cofima.cc/wp-content/uploads/2020/09/logo-cofima-bon.jpg" alt="Logo COFIMA" class="logo">
        </div>

        <div class="header">
            Réinitialisation de mot de passe
        </div>

        <div class="card">
            <h2 style="color:#333;">Bonjour,</h2>

            <p style="font-size:15px; color:#555;">
                Vous recevez cet email car nous avons reçu une demande de réinitialisation de mot de passe pour votre compte.
            </p>

            <a href="{{ $actionUrl }}" class="btn">
                Réinitialiser mon mot de passe
            </a>

            <div class="note">
                ⏱️ Ce lien expirera dans <strong>60 minutes</strong>.<br>
                Si vous n'avez pas demandé cette réinitialisation, aucune action n'est requise.
            </div>

            <p style="font-size:13px; color:#888; margin-top:20px;">
                Cordialement,<br>
                <strong>L'équipe COFIMA BENIN</strong>
            </p>

            <p class="url-fallback">
                Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :<br>
                <a href="{{ $actionUrl }}">{{ $actionUrl }}</a>
            </p>
        </div>

        <div class="footer">
            © {{ date('Y') }} COFIMA BENIN — Tous droits réservés.
        </div>
    </div>
</body>
</html>