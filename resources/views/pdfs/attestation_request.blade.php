<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Demande d'attestation de travail</title>

    @php
        $sexe       = $employe->sexe ?? 'M';
        $civilite   = $sexe === 'F' ? 'Madame' : 'Monsieur';
        $accord     = $sexe === 'F' ? 'e' : '';

        $dateEmbauche = optional($demande->date_embauche)->isoFormat('D MMMM YYYY') ?? '[date d\'embauche]';
        $poste        = $demande->poste ?? 'Collaborateur';

        $contextMotif = match ($demande->type) {
            'attestation_banque'    => 'dans le cadre d\'une démarche bancaire',
            'attestation_ambassade' => 'dans le cadre d\'une démarche consulaire',
            default                 => 'dans le cadre de démarches administratives',
        };

        $usagePhrase = match ($demande->type) {
            'attestation_banque'    => 'comme justificatif auprès d\'un établissement bancaire',
            'attestation_ambassade' => 'comme pièce justificative auprès d\'une ambassade ou d\'un consulat',
            default                 => 'comme preuve de profession',
        };

        $destinatairePhrase = $demande->destinataire
            ? 'à produire auprès de' . e($demande->destinataire) 
            : '';
    @endphp

    <style>
        @font-face {
            font-family: 'Helvetica';
            font-weight: normal;
            src: url("file://{{ str_replace('\\', '/', storage_path('fonts/Helvetica.ttf')) }}") format('truetype');
        }
        @font-face {
            font-family: 'Helvetica';
            font-weight: bold;
            src: url("file://{{ str_replace('\\', '/', storage_path('fonts/Helvetica-Bold.ttf')) }}") format('truetype');
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            height: 100%;
        }

        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.6;
            color: #000;
            padding: 2.5cm 2cm 3cm 2.5cm;
            background: #fff;
        }

        /* ── En-tête côte à côte (classique) ── */
        .header-bloc {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
        }
        .header-logo img {
            max-width: 160px;
            height: auto;
        }
        .sender {
            font-size: 9pt;
            line-height: 1.5;
            text-align: left;
        }
        .sender strong {
            font-size: 10pt;
        }
        .email-blue {
            color: #0066cc;
        }

        .right-bloc {
            text-align: right;
            font-size: 10pt;
            line-height: 1.6;
        }
        .date-line {
            margin-bottom: 1.2rem;
        }

        /* ── Objet ──────────────────────────────────────────────────── */
        .objet {
            margin: 1.5rem 0 1.8rem 0;
            font-size: 10pt;
            font-weight: bold;
        }

        /* ── Corps ──────────────────────────────────────────────────── */
        .content {
            text-align: justify;
            font-size: 10pt;
            line-height: 1.6;
        }
        .content p {
            margin-bottom: 1rem;
        }

        /* ── Signature ──────────────────────────────────────────────── */
        .signature {
            margin-top: 2.5rem;
            text-align: right;
            font-size: 10pt;
            line-height: 1.8;
        }

        /* ── Pied de page (optionnel, désactivé pour rester sur une page) ─ */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 8px 2.5cm;
            border-top: 1px solid #ccc;
            font-size: 8pt;
            color: #777;
            text-align: center;
            background: #fff;
        }
    </style>
</head>
<body>

    {{-- ── Bloc en-tête (horizontal) ─────────────────────────────────────── --}}
    <div class="header-bloc">
            {{-- <div class="header-logo">
                <img src="{{ storage_path('app/public/photos/logo-cofima-bon.jpg') }}" alt="Logo COFIMA" style="max-width: 120px;">
            </div> --}}

        {{-- Expéditeur (gauche) --}}
        <div class="sender">
            <strong>{{ $employe->prenom }} {{ strtoupper($employe->nom) }}</strong><br>
            @if($employe->poste?->intitule)
               <strong> Poste : </strong>{{ $employe->poste->intitule }}
            @endif
            @if($employe->telephone)
                Téléphone : {{ $employe->telephone }}
            @endif
            @if($employe->email)
              <strong>E-mail : <span class="email-blue">{{ $employe->email }}</span></strong>  
            @endif
        </div>

        {{-- Date + Destinataire (droite) --}}
        <div class="right-bloc">
            <div class="date-line">
                Cotonou, le {{ $demande->created_at->isoFormat('D MMMM YYYY') }}
            </div>
            <div>
                À<br>
                <strong>Monsieur l'Associé-Gérant</strong><br>
                du Cabinet COFIMA
            </div>
        </div>

    </div>

    {{-- ── Objet ────────────────────────────────────────────────────────── --}}
    <div class="objet">
        <strong style="text-decoration: underline;">Objet :</strong> Demande d'attestation de travail
    </div>

    {{-- ── Corps de la lettre ───────────────────────────────────────────── --}}
    <div class="content">

        <p>Monsieur l'Associé-Gérant,</p>

        <p>
            Je me permets par la présente de solliciter une
            <strong>{{ $demande->libelleType }}</strong>.
        </p>

        <p>
            En effet, j'exerce au sein du cabinet depuis le
            <strong>{{ $dateEmbauche }}</strong>
            en tant que <strong>{{ $poste }}</strong>,
            et {{ $contextMotif }}, une attestation de travail m'est nécessaire
            {{ $usagePhrase }}{{ $destinatairePhrase ? ', ' . $destinatairePhrase : '' }}.
        </p>

        @if($demande->motif && !in_array($demande->type, ['attestation_banque', 'attestation_ambassade']))
        <p>
            {{ lcfirst($demande->motif) }}
        </p>
        @endif

        <p>
            Je vous saurais gré de bien vouloir m'établir ce document attestant
            de ma présence au sein du cabinet depuis la date susmentionnée.
        </p>

        <p>
            Je vous prie d'agréer, Monsieur l'Associé-Gérant,
            l'expression de mes salutations distinguées.
        </p>

    </div>
     <br><br><br><br>
    {{-- ── Signature ────────────────────────────────────────────────────── --}}
    <div class="signature">
        {{ $employe->prenom }} {{ strtoupper($employe->nom) }}
    </div>

    {{-- Pied de page désactivé pour rester sur une seule page --}}
    {{-- <div class="footer">Document généré automatiquement par COFIMA – Service des ressources humaines</div> --}}

</body>
</html>