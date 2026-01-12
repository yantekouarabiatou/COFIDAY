<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande de congé</title>

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

        .btn {
            display: inline-block;
            background: #4a70b7;
            color: white !important;
            padding: 12px 28px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
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
        <img src="https://cofima.cc/wp-content/uploads/2020/09/logo-cofima-bon.jpg"
             alt="Logo COFIMA"
             class="logo">
    </div>

    <!-- HEADER -->
    <div class="header">
        Demande de congé
    </div>

    <!-- CARD -->
    <div class="card">
        <div class="content">

            <h2>Bonjour 'Madame / Monsieur',</h2>

            <p>
                Par la présente, je souhaite solliciter un congé selon les modalités suivantes :
            </p>

            <ul class="info-list">
                <li>
                    <strong>Type de congé :</strong>
                    {{ $leave->type->name }}
                </li>
                <li>
                    <strong>Date de début :</strong>
                    {{ $leave->date_debut }}
                </li>
                <li>
                    <strong>Date de fin :</strong>
                    {{ $leave->date_fin }}
                </li>
                <li>
                    <strong>Nombre de jours :</strong>
                    {{ $leave->days }}
                </li>
            </ul>

            @if(!empty($leave->reason))
                <div class="note">
                    <strong>Motif :</strong><br>
                    {{ $leave->reason }}
                </div>
            @endif

            <p style="margin-top:20px;">
                Je veillerai à organiser mon travail en amont afin d’assurer
                la continuité des activités durant mon absence.
            </p>

            <div class="btn-container">
                <a href="{{ $approval_link ?? '#' }}" class="btn">
                    Examiner la demande
                </a>
            </div>

            <p style="margin-top:25px; font-size:14px;">
                Je reste bien entendu disponible pour toute information complémentaire
                et vous remercie par avance pour l’attention portée à cette demande.
            </p>

            <p style="margin-top:20px;">
                Cordialement,<br>
                <strong>{{ $leave->user->name }}</strong>
            </p>

        </div>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        © {{ date('Y') }} COFIMA BENIN — Tous droits réservés.
    </div>

</div>
</body>
</html>
