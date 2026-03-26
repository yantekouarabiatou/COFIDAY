<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">

    @php
        $isPermission = str_contains(strtolower($leave->typeConge->libelle ?? ''), 'permission');
        $motLabel     = $isPermission ? 'permission' : 'congé';

        // Genre de l'expéditeur
        $sexe      = $leave->user->sexe;
        $civiliteExp = $sexe === 'F' ? 'Madame' : 'Monsieur';

        // Genre du supérieur (destinataire)
        $sexeSup       = $superieur->sexe ?? 'M';
        $civiliteSup   = $sexeSup === 'F' ? 'Madame' : 'Monsieur';

        // Article devant le poste du supérieur (le / la / l')
        $intitulePoste = $superieur->poste->intitule ?? '';
        $voyelles      = ['a', 'e', 'i', 'o', 'u', 'h', 'â', 'è', 'é', 'ê', 'î', 'ô', 'û'];
        $premiereLettre = mb_strtolower(mb_substr($intitulePoste, 0, 1));
        $articlePoste  = in_array($premiereLettre, $voyelles) ? "l'" : ($sexeSup === 'F' ? 'la ' : 'le ');
    @endphp

    <title>Demande de {{ $motLabel }}</title>

    <style>
        @font-face {
            font-family: 'Helvetica';
            font-weight: normal;
            src: url("{{ storage_path('fonts/Helvetica.ttf') }}") format('truetype');
        }
        @font-face {
            font-family: 'Helvetica';
            font-weight: bold;
            src: url("{{ storage_path('fonts/Helvetica-Bold.ttf') }}") format('truetype');
        }
        @font-face {
            font-family: 'Helvetica';
            font-style: italic;
            src: url("{{ storage_path('fonts/Helvetica-Oblique.ttf') }}") format('truetype');
        }
        @font-face {
            font-family: 'Helvetica';
            font-style: italic;
            font-weight: bold;
            src: url("{{ storage_path('fonts/Helvetica-BoldOblique.ttf') }}") format('truetype');
        }

        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #000;
            margin: 40px 50px;
        }
        .logo-container { text-align: center; margin-bottom: 20px; }
        .logo { max-width: 140px; height: auto; }
        .header { text-align: right; margin-bottom: 30px; }
        .header p { margin: 0; }
        .sender { margin-bottom: 25px; }
        .sender p { margin: 0; }
        .recipient {
            width: 50%;
            margin-left: 50%;
            text-align: center;
            margin-bottom: 30px;
        }
        .recipient p { margin: 0; }
        .object {
            font-weight: bold;
            text-decoration: underline;
            margin: 25px 0;
            font-size: 14px;
        }
        .content { text-align: justify; }
        .content p { margin-bottom: 15px; }
        .signature {
            margin-top: 50px;
            float: right;
            text-align: center;
        }
        .signature p { margin: 0; }
        .highlight { font-weight: bold; }
    </style>
</head>

<body>

    {{-- LOGO --}}
    @php
        $logoPath   = public_path('storage/photos/logo-cofima-bon.jpg');
        $logoBase64 = file_exists($logoPath)
            ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath))
            : null;
    @endphp
    @if($logoBase64)
        <div class="logo-container">
            <img src="{{ $logoBase64 }}" alt="Logo COFIMA" class="logo">
        </div>
    @endif

    {{-- EN-TÊTE --}}
    <div class="header">
        <p>{{ $lieu ?? 'Cotonou' }}, le {{ $date ?? date('d/m/Y') }}</p>
    </div>

    {{-- EXPÉDITEUR --}}
    <div class="sender">
        <p>
            <strong>{{ $leave->user->prenom }} {{ $leave->user->nom }}</strong><br>
            {{ $leave->user->poste->intitule ?? '—' }}<br>
            @if($leave->user->email)
                {{ $leave->user->email }}<br>
            @endif
            @if($leave->user->telephone)
                {{ $leave->user->telephone }}
            @endif
        </p>
    </div>

    {{-- DESTINATAIRE --}}
    <div class="recipient">
        <p>
            À<br>
            {{ $civiliteSup }}
            <strong>{{ $superieur->prenom ?? '' }} {{ $superieur->nom }}</strong>,<br>
            {{ $intitulePoste }} de COFIMA
        </p>
    </div>

    {{-- OBJET --}}
    <div class="object">
        Objet : Demande de {{ $motLabel }}
    </div>

    {{-- CORPS --}}
    <div class="content">

        <p>{{ $civiliteSup }} {{ $articlePoste }}{{ $intitulePoste }},</p>

        @if($isPermission)
            <p>
                J'ai l'honneur de solliciter, par la présente, votre autorisation
                pour bénéficier d'une <span class="highlight">permission</span>
                de type <span class="highlight">{{ $leave->typeConge->libelle ?? '—' }}</span>,
                du <span class="highlight">{{ $leave->date_debut_formatted }}</span>
                au <span class="highlight">{{ $leave->date_fin_formatted }}</span>,
                soit <span class="highlight">{{ $leave->nombre_jours }}</span> jour(s) ouvrable(s).
            </p>

            @if(!empty($leave->motif ?? $leave->reason))
                <p>
                    Cette permission est sollicitée pour le motif suivant :
                    <span class="highlight">{{ $leave->motif ?? $leave->reason }}</span>.
                </p>
            @endif

            <p>
                Je m'engage à assurer, dans la mesure du possible, la continuité
                des tâches qui me sont confiées avant mon absence.
            </p>

            <p>
                Dans l'attente d'une décision favorable, je vous prie d'agréer,
                {{ $civiliteSup }} {{ $articlePoste }}{{ $intitulePoste }},
                l'expression de ma considération distinguée.
            </p>

        @else
            <p>
                J'ai l'honneur de solliciter, par la présente, l'autorisation
                de bénéficier d'un <span class="highlight">congé</span>
                de type <span class="highlight">{{ $leave->typeConge->libelle ?? '—' }}</span>,
                du <span class="highlight">{{ $leave->date_debut_formatted }}</span>
                au <span class="highlight">{{ $leave->date_fin_formatted }}</span>,
                soit <span class="highlight">{{ $leave->nombre_jours }}</span> jour(s) ouvrable(s).
            </p>

            @if(!empty($leave->motif ?? $leave->reason))
                <p>
                    Cette demande est formulée pour :
                    <span class="highlight">{{ $leave->motif ?? $leave->reason }}</span>.
                </p>
            @endif

            <p>
                Je m'engage à prendre toutes les dispositions utiles afin d'assurer
                la continuité du service durant mon absence.
            </p>

            <p>
                Dans l'attente d'une suite favorable, je vous prie d'agréer,
                {{ $civiliteSup }} {{ $articlePoste }}{{ $intitulePoste }},
                l'expression de ma considération distinguée.
            </p>
        @endif

    </div>

    {{-- SIGNATURE --}}
    <div class="signature">
        <p>
            {{ $leave->user->prenom }} {{ $leave->user->nom }}<br>
            {{ $leave->user->poste->intitule ?? '' }}
        </p>
    </div>

</body>
</html>