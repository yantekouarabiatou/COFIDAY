<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @php
        $isPermission = str_contains(strtolower($demande->typeConge->libelle ?? ''), 'permission');
        $motLabel     = $isPermission ? 'permission' : 'congé';
        $motLabelCap  = $isPermission ? 'Permission' : 'Congé';
        $themeColor   = '#2d8f64';
        $themeBg      = '#f3fbf7';
        $themeBorder  = '#2d8f64';
        $themeNote    = '#eeeafc';
        $icon         = '✅';
    @endphp

    <title>Demande de {{ $motLabel }} approuvée</title>

    <style>
        body {
            background-color: #f4f4f7;
            margin: 0; padding: 0;
            font-family: Arial, Helvetica, sans-serif;
        }
        .email-container { width: 100%; padding: 20px 0; }
        .logo-container  { text-align: center; margin-bottom: 20px; }
        .logo            { max-width: 140px; height: auto; }

        .header {
            background: {{ $themeColor }};
            color: white;
            padding: 22px;
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            border-radius: 8px 8px 0 0;
        }
        .header .sub {
            font-size: 14px;
            font-weight: normal;
            opacity: 0.85;
            margin-top: 4px;
        }

        .card {
            background-color: #ffffff;
            max-width: 600px;
            margin: auto;
            border-radius: 0 0 8px 8px;
            padding: 30px;
            border: 1px solid #e0e0e0;
        }

        .content h2 { color: #333; margin-top: 0; }
        .content p  { font-size: 15px; color: #555; line-height: 1.6; }

        .info-list   { padding-left: 0; margin-top: 15px; }
        .info-list li {
            list-style: none;
            background: {{ $themeBg }};
            padding: 10px 14px;
            margin-bottom: 8px;
            border-radius: 5px;
            font-size: 14px;
            color: #333;
        }

        .note {
            background: {{ $themeNote }};
            padding: 14px 16px;
            border-left: 4px solid {{ $themeBorder }};
            border-radius: 5px;
            margin-top: 20px;
            font-size: 14px;
            color: #444;
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

    {{-- LOGO --}}
    <div class="logo-container">
        <img src="https://cofima.cc/wp-content/uploads/2020/09/logo-cofima-bon.jpg"
             alt="Logo COFIMA" class="logo">
    </div>

    {{-- HEADER --}}
    <div class="header">
        Demande de {{ $motLabel }} approuvée
        <div class="sub">{{ $demande->typeConge->libelle ?? '—' }}</div>
    </div>

    {{-- CARD --}}
    <div class="card">
        <div class="content">

            <h2>Bonjour {{ $demande->user->prenom ?? $demande->user->name }},</h2>

            <p>
                Nous avons le plaisir de vous informer que votre demande de
                <strong>{{ $motLabel }}</strong> a été <strong>approuvée</strong>.
            </p>

            <ul class="info-list">
                <li>
                    <strong>Type de {{ $motLabel }} :</strong>
                    {{ $demande->typeConge->libelle ?? '—' }}
                </li>
                <li>
                    <strong>Date de début :</strong>
                    {{ $demande->date_debut_formatted ?? $demande->date_debut }}
                </li>
                <li>
                    <strong>Date de fin :</strong>
                    {{ $demande->date_fin_formatted ?? $demande->date_fin }}
                </li>
                <li>
                    <strong>Nombre de jours :</strong>
                    {{ $demande->nombre_jours }}
                </li>
            </ul>

            <div class="note">
                {{ $icon }}
                @if($isPermission)
                    Votre permission est désormais enregistrée dans le système.
                    Nous vous souhaitons une excellente permission.
                @else
                    Votre absence est désormais enregistrée dans le système.
                    Nous vous souhaitons un excellent congé.
                @endif
            </div>

            <p style="margin-top:20px;">
                @if($isPermission)
                    Nous vous remercions d'avoir informé le service RH
                    et d'avoir pris les dispositions nécessaires avant votre absence.
                @else
                    Nous vous remercions d'avoir pris les dispositions nécessaires
                    afin d'assurer la continuité du service.
                @endif
            </p>

            <p style="margin-top:20px;">
                Cordialement,<br>
                <strong>Le service RH</strong>
            </p>

        </div>
    </div>

    {{-- FOOTER --}}
    <div class="footer">
        © {{ date('Y') }} COFIMA BENIN — Tous droits réservés.
    </div>

</div>
</body>
</html>