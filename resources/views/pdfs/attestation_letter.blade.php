<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">

    @php
        $sexe       = $employe->sexe ?? 'M';
        $civilite   = $sexe === 'F' ? 'Madame' : 'Monsieur';
        $dateEmbauche = optional($demande->date_embauche)->isoFormat('D MMMM YYYY') ?? '[date d\'embauche]';
        $poste        = $demande->poste ?? 'Collaborateur';
        $titreDoc   = match ($demande->type) {
            'attestation_simple' => 'Attestation de travail',
            'attestation_banque' => 'Attestation de travail',
            'attestation_ambassade' => 'Attestation de travail',
            default => 'Attestation de travail',
        };
    @endphp

    <title>{{ $titreDoc }}</title>

    <style>
        @font-face {
            font-family: 'Helvetica';
            font-style: normal;
            font-weight: normal;
            src: url("file://{{ str_replace('\\', '/', storage_path('fonts/Helvetica.ttf')) }}") format('truetype');
        }
        @font-face {
            font-family: 'Helvetica';
            font-style: normal;
            font-weight: bold;
            src: url("file://{{ str_replace('\\', '/', storage_path('fonts/Helvetica-Bold.ttf')) }}") format('truetype');
        }
        @font-face {
            font-family: 'Helvetica';
            font-style: italic;
            font-weight: normal;
            src: url("file://{{ str_replace('\\', '/', storage_path('fonts/Helvetica-Oblique.ttf')) }}") format('truetype');
        }
        @font-face {
            font-family: 'Helvetica';
            font-style: italic;
            font-weight: bold;
            src: url("file://{{ str_replace('\\', '/', storage_path('fonts/Helvetica-BoldOblique.ttf')) }}") format('truetype');
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            height: 100%;
        }

        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 14px;
            line-height: 1.7;
            color: #000;
            /* Marges de page via padding */
            padding: 40px 50px 100px 50px; /* bas = espace pour le footer fixe */
            position: relative;
            min-height: 100vh;
        }

        /* ── Header ────────────────────────────────────────────────── */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 35px;
        }
        .header-logo img {
            max-width: 150px;
        }
        .company-info {
            text-align: right;
            font-size: 10px;
            color: #333;
            line-height: 1.6;
        }
        .company-info .name {
            font-weight: bold;
            font-size: 12px;
            color: #000;
        }

        /* ── Titre principal ────────────────────────────────────────── */
        .titre-doc {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 30px 0 35px;
        }

        /* ── Corps ──────────────────────────────────────────────────── */
        .content p {
            margin-bottom: 18px;
            text-align: justify;
        }

        /* ── Signature (gauche) ─────────────────────────────────────── */
        .signature {
            margin-top: 50px;
            text-align: left;
        }
        .signature p {
            line-height: 1.8;
        }

        /* ── Pied de page fixe en bas de feuille ───────────────────── */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 10px 50px;
            border-top: 1px solid #ccc;
            font-size: 9px;
            color: #555;
            text-align: center;
            background: #fff;
        }
    </style>
</head>
<body>

    {{-- ── Header : logo + infos cabinet ──────────────────────────────────── --}}
    <div class="header">
        <div class="header-logo">
            <img src="{{ storage_path('app/public/photos/logo-cofima-bon.jpg') }}" alt="Logo COFIMA">
        </div>
    </div>

    {{-- ── Titre centré, majuscules, gras, souligné ────────────────────────── --}}
    <div class="titre-doc">{{ strtoupper($titreDoc) }}</div>

    {{-- ── Corps du document ────────────────────────────────────────────────── --}}
    <div class="content">
        <p>
            Je soussigné, <strong>AVANDE Jean-Claude</strong>, Expert-Comptable Diplômé inscrit à
            l'Ordre des Experts Comptables et Comptables Agréés du Bénin sous le numéro 006-EC,
            Associé-Gérant du Cabinet COFIMA, atteste que
            <strong>{{ $civilite }} {{ strtoupper($employe->nom) }} {{ $employe->prenom }}</strong>
            {{ strtolower($sexe) === 'f' ? 'est employée' : 'est employé' }} au sein du Cabinet COFIMA depuis le
            <strong>{{ $dateEmbauche }}</strong>
            à ce jour en qualité de <strong>{{ $poste }}{{ $demande->inclure_salaire && $demande->salaire_net ? ', avec une rémunération nette mensuelle de' . number_format($demande->salaire_net, 0, ',', ' ') . ' FCFA' : '' }}.
         </strong></p>

        @if($demande->type === 'attestation_banque')
            <p>
                La présente attestation est rédigée à usage bancaire.
                Elle peut être produite auprès des établissements financiers ou des organismes bancaires.
            </p>
        @elseif($demande->type === 'attestation_ambassade')
            <p>
                La présente attestation est délivrée à la demande de l'intéressé(e)
                dans le cadre d'une démarche consulaire.
            </p>
        @else
            <p>
                En foi de quoi, la présente attestation est établie pour servir et valoir ce que de droit.
            </p>
        @endif

        @if($demande->destinataire)
            <p>
                Elle est établie à la demande de l'intéressé(e) pour être produite auprès de
                <strong>{{ $demande->destinataire }}</strong>.
            </p>
        @endif

        <p>
            Fait à Cotonou, le <strong>{{ optional($demande->date_validation)->isoFormat('D MMMM YYYY') ?? now()->isoFormat('D MMMM YYYY') }}</strong>.
        </p>
    </div>

    {{-- ── Signature à gauche ───────────────────────────────────────────────── --}}
    <div class="signature">
        <p>
            Pour COFIMA,<br><br><br>    
            <strong>Jean-Claude AVANDE</strong><br>
            Expert-Comptable Diplômé<br>
            Associé-Gérant<br>
            Tél. 21 38 04 58 / 90 95 19 59<br>
            Courriel : jcavande@cofimabenin.com
        </p>
    </div>

    {{-- ── Pied de page fixe ───────────────────────────────────────────────── --}}
    <div class="footer">
        N° IFU 3200900601214 · RCCM RB/COT/07B/336 · C/2213 F Immeuble Ahouansou Sossou Kouhoumou – Cotonou
        Tél : +229 21 38 04 58 · Mobile : +229 90 95 18 90 / 05 07 09 48 &nbsp;|&nbsp;
        site web : www.cofimabenin.com · cofima@cofimabenin.com
    </div>

</body>
</html>