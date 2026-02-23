<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dossier assigné</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background: #244584; padding: 30px; text-align: center; }
        .header h1 { color: white; margin: 0; font-size: 22px; }
        .header p { color: #a8c4e8; margin: 6px 0 0; font-size: 13px; }
        .body { padding: 30px; color: #2d3748; }
        .body p { line-height: 1.7; margin-bottom: 14px; }
        .info-box { background: #f0f5ff; border-left: 4px solid #244584; border-radius: 4px; padding: 16px 20px; margin: 20px 0; }
        .info-box table { width: 100%; border-collapse: collapse; }
        .info-box td { padding: 6px 0; font-size: 14px; }
        .info-box td:first-child { color: #6b7280; width: 40%; }
        .info-box td:last-child { font-weight: bold; color: #1a202c; }
        .btn { display: inline-block; background: #244584; color: white !important; padding: 12px 28px; border-radius: 6px; text-decoration: none; font-weight: bold; margin-top: 10px; }
        .footer { background: #f8f9fa; padding: 20px 30px; text-align: center; font-size: 11px; color: #9ca3af; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Nouvelle assignation</h1>
        <p>Vous avez été ajouté à un dossier</p>
    </div>

    <div class="body">
        <p>Bonjour <strong>{{ $collaborateur->prenom }} {{ $collaborateur->nom }}</strong>,</p>

        <p>Vous avez été assigné comme collaborateur sur le dossier suivant :</p>

        <div class="info-box">
            <table>
                <tr>
                    <td>Dossier</td>
                    <td>{{ $dossier->nom }}</td>
                </tr>
                <tr>
                    <td>Référence</td>
                    <td>{{ $dossier->reference ?? '—' }}</td>
                </tr>
                <tr>
                    <td>Client</td>
                    <td>{{ $dossier->client->nom }}</td>
                </tr>
                <tr>
                    <td>Type</td>
                    <td>{{ ucfirst($dossier->type_dossier) }}</td>
                </tr>
                <tr>
                    <td>Date d'ouverture</td>
                    <td>{{ \Carbon\Carbon::parse($dossier->date_ouverture)->format('d/m/Y') }}</td>
                </tr>
                @if($dossier->date_cloture_prevue)
                <tr>
                    <td>Clôture prévue</td>
                    <td>{{ \Carbon\Carbon::parse($dossier->date_cloture_prevue)->format('d/m/Y') }}</td>
                </tr>
                @endif
                @if($dossier->description)
                <tr>
                    <td>Description</td>
                    <td>{{ $dossier->description }}</td>
                </tr>
                @endif
            </table>
        </div>

        <p>Vous pouvez accéder au dossier en cliquant sur le bouton ci-dessous :</p>

        <a href="{{ route('dossiers.show', $dossier) }}" class="btn">
            Voir le dossier
        </a>

        <p style="margin-top: 24px; font-size: 13px; color: #6b7280;">
            Si vous pensez que cette assignation est une erreur, contactez votre responsable.
        </p>
    </div>

    <div class="footer">
        Ce mail a été généré automatiquement — Merci de ne pas y répondre.<br>
        {{ config('app.name') }}
    </div>
</div>
</body>
</html>