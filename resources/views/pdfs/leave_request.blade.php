<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Demande de congé</title>

    <style>
        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #000;
            margin: 40px 50px;
        }

        .header, .recipient {
            text-align: right;
            margin-bottom: 30px;
        }

        .header p, .recipient p {
            margin: 0;
        }

        .sender {
            margin-bottom: 25px;
        }

        .sender p {
            margin: 0;
        }

        .object {
            font-weight: bold;
            text-decoration: underline;
            margin: 25px 0;
            font-size: 14px;
        }

        .content {
            text-align: justify;
        }

        .content p {
            margin-bottom: 15px;
        }

        .signature {
            margin-top: 50px;
        }

        .signature p {
            margin: 0;
        }

        .highlight {
            font-weight: bold;
        }
    </style>
</head>

<body>

    <!-- EN-TÊTE (Lieu et date) -->
    <div class="header">
        <p>{{ $lieu ?? 'Cotonou' }}, le {{ $date ?? date('d/m/Y') }}</p>
    </div>

    <!-- EXPÉDITEUR -->
    <div class="sender">
        <p>
            <strong>{{ $leave->user->nom }} {{ $leave->user->prenom }}</strong><br>
            {{ $leave->user->poste->intitule ?? '—' }}<br>
            {{ $leave->user->email ?? '' }}
        </p>
    </div>

    <!-- DESTINATAIRE -->
    <div class="recipient">
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
            d’un congé de type <span class="highlight">{{ $leave->typeConge->libelle ?? '—' }}</span>
            du <span class="highlight">{{ $leave->date_debut_formatted}}</span> au
            <span class="highlight">{{ $leave->date_fin_formatted}}</span>,
            soit <span class="highlight">{{ $leave->nombre_jours }}</span> jour(s).
        </p>

        @if(!empty($leave->motif ?? $leave->reason))
            <p>
                Cette demande est formulée pour : {{ $leave->motif ?? $leave->reason }}.
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
            {{ $leave->user->prenom }} {{ $leave->user->nom }}<br>
            {{ $leave->user->poste->intitule ?? '' }}
        </p>
    </div>

</body>
</html>
