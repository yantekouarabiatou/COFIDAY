<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @php
        $isPermission = str_contains(strtolower($leave->typeConge->libelle ?? ''), 'permission');
        $motLabel     = $isPermission ? 'permission' : 'congé';
    @endphp

    <title>Demande de {{ $motLabel }}</title>

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

        .header {
            background: #4a70b7;
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

        .content h2 {
            color: #333;
            margin-top: 0;
        }

        .content p {
            font-size: 15px;
            color: #555;
            line-height: 1.6;
        }

        .info-list {
            padding-left: 0;
            margin-top: 15px;
        }

        .info-list li {
            list-style: none;
            background: #f7f8fc;
            padding: 10px 14px;
            margin-bottom: 8px;
            border-radius: 5px;
            font-size: 14px;
            color: #333;
        }

        .info-list strong {
            color: #222;
        }

        .note {
            background: #fff4c2;
            padding: 14px 16px;
            border-left: 4px solid #e0b200;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 14px;
            color: #444;
        }

        .btn-container {
            text-align: center;
            margin-top: 30px;
        }

        /* Bouton principal (bleu) */
        .btn-primary {
            display: inline-block;
            background: #4a70b7;
            color: white !important;
            padding: 12px 28px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
            margin: 0 5px;
        }

        /* Bouton secondaire (gris) visiblement similaire à l'exemple */
        .btn-secondary {
            display: inline-block;
            background: #6c757d;
            color: white !important;
            padding: 12px 28px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
            margin: 0 5px;
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
        <img src="{{ asset('storage/photos/logo-cofima-bon.jpg') }}"
             alt="Logo COFIMA"
             class="logo">
    </div>

    {{-- HEADER --}}
    <div class="header">
        Demande de {{ $motLabel }}
        <div class="sub">{{ $leave->typeConge->libelle ?? '—' }}</div>
    </div>

    {{-- CARD --}}
    <div class="card">
        <div class="content">

            <h2>Bonjour {{ $superieur->nom }} {{ $superieur->prenom ?? 'Madame / Monsieur' }},</h2>

            <p>
                @if($isPermission)
                    Par la présente, je souhaite solliciter une permission selon les modalités suivantes :
                @else
                    Par la présente, je souhaite solliciter un congé selon les modalités suivantes :
                @endif
            </p>

            <ul class="info-list">
                <li>
                    <strong>Type de {{ $motLabel }} :</strong>
                    {{ $leave->typeConge->libelle ?? '—' }}
                </li>
                <li>
                    <strong>Date de début :</strong>
                    {{ $leave->date_debut_formatted ?? $leave->date_debut }}
                </li>
                <li>
                    <strong>Date de fin :</strong>
                    {{ $leave->date_fin_formatted ?? $leave->date_fin }}
                </li>
                <li>
                    <strong>Nombre de jours :</strong>
                    {{ $leave->nombre_jours ?? $leave->days }}
                </li>
            </ul>

            @if(!empty($leave->motif))
                <div class="note">
                    <strong>Motif :</strong><br>
                    {{ $leave->motif }}
                </div>
            @endif

            <p style="margin-top:20px;">
                @if($isPermission)
                    Je veillerai à organiser mon travail en amont afin d'assurer
                    la continuité des activités durant ma permission.
                @else
                    Je veillerai à organiser mon travail en amont afin d'assurer
                    la continuité des activités durant mon absence.
                @endif
            </p>

            {{-- Bouton de pré‑approbation (exactement comme demandé) --}}
            <div class="btn-container">
                <a href="{{ route('conges.pre-approbation.show', $leave->id) }}" class="btn-secondary">
                    Cliquez ici pour Pré‑approuver cette demande
                </a>
            </div>

            <p style="margin-top:25px; font-size:14px;">
                Je reste bien entendu disponible pour toute information complémentaire
                et vous remercie par avance pour l'attention portée à cette demande.
            </p>

            <p style="margin-top:20px;">
                Cordialement,<br>
                <strong>{{ $leave->user->prenom ?? $leave->user->name ?? 'Nom indisponible' }}
                    {{ $leave->user->nom ?? '' }}</strong>
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