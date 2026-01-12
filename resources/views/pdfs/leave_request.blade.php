<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Demande de congé</title>

    <style>
        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 12px;
            line-height: 1.7;
            color: #000;
            margin: 40px 50px;
        }

        .header, .receved {
            text-align: right;
            margin-bottom: 30px;
        }

        .header p, .receved p {
            margin: 0;
        }

        .sender {
            margin-bottom: 25px;
        }

        .object {
            font-weight: bold;
            text-decoration: underline;
            margin: 25px 0;
        }

        .content {
            text-align: justify;
        }

        .signature {
            margin-top: 50px;
        }

        .signature p {
            margin: 0;
        }
    </style>
</head>

<body>

    <!-- EN-TÊTE (Lieu et date) -->
    <div class="header">
        <p>
            {{ $lieu ?? 'Cotonou' }}, le {{ $date ?? date('d/m/Y') }}
        </p>
    </div>

    <!-- EXPÉDITEUR -->
    <div class="sender">
        <p>
            <strong>{{ $leave->user->name }}</strong><br>
            {{ $leave->user->poste ?? '—' }}<br>
            {{ $leave->user->email ?? '' }}
        </p>
    </div>

    <!-- DESTINATAIRE -->
    <div class="receved">
        <p>
            À l’attention de<br>
            <strong>{{ $validator_name ?? 'Monsieur / Madame le Responsable' }}</strong><br>
            {{ $validator_poste ?? '—' }}<br>
            {{ $validator_email ?? '' }}
        </p>
    </div>

    <!-- OBJET -->
    <div class="object">
        Objet : Demande de congé
    </div>

    <!-- CORPS DE LA LETTRE -->
    <div class="content">
        <p>
            Monsieur / Madame,
        </p>

        <p>
            J’ai l’honneur de solliciter, par la présente, l’autorisation de bénéficier
            d’un congé du <strong>{{ $leave->date_debut_formatted }}</strong> au
            <strong>{{ $leave->date_fin_formatted }}</strong>, soit
            <strong>{{ $leave->days }}</strong> jour(s).
        </p>

        @if(!empty($leave->reason))
            <p>
                Cette demande est formulée pour {{ $leave->reason }}.
            </p>
        @endif

        <p>
            Je m’engage à prendre toutes les dispositions utiles afin d’assurer
            la continuité du service durant mon absence.
        </p>

        <p>
            Dans l’attente d’une suite favorable, je vous prie d’agréer,
            Monsieur / Madame, l’expression de ma considération distinguée.
        </p>
    </div>

    <!-- SIGNATURE -->
    <div class="signature">
        <p>
            {{ $leave->user->name }}<br>
            {{ $leave->user->poste ?? '' }}
        </p>
    </div>

</body>
</html>
