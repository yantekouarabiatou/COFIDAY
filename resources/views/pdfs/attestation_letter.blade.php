<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">

    @php
        $sexe       = $employe->sexe ?? 'M';
        $civilite   = $sexe === 'F' ? 'Madame' : 'Monsieur';
        $dateEmbauche = optional($demande->date_embauche)->isoFormat('D MMMM YYYY') ?? '[date d\'embauche]';
        $poste        = $demande->poste ?? 'Collaborateur';
        $titreDoc = 'ATTESTATION DE TRAVAIL';
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

        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }

        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: #000;
            padding: 40px 50px 100px 50px;
            position: relative;
            min-height: 100vh;
        }

        /* ── Header ── */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0;
        }
        .header-logo img { max-width: 220px; }

        /* ── Titre : gras, italique, souligné, centré, +2pt, 5 lignes avant, 2 lignes après ── */
        .titre-doc {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            font-style: italic;
            text-decoration: underline;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 105px;   /* 5 lignes × 21px (14px × 1.5) */
            margin-bottom: 42px; /* 2 lignes × 21px */
        }

        /* ── Corps : taille 15px pour occuper 5 lignes, 1 ligne entre paragraphes ── */
        .content p {
            font-size: 15px;
            margin-bottom: 23px;
            text-align: justify;
        }
        /* Dernier paragraphe sans marge basse (l'espace vient du margin-top de .signature) */
        .content .p-date { margin-bottom: 0; }

        /* ── Signature : 2 lignes entre "Fait à Cotonou" et "Pour COFIMA" ── */
        .signature {
            margin-top: 45px;
            text-align: left;
        }
        .signature p { line-height: 1.8; }
        .signature .sig-nom { line-height: 1.2; display: block; }

        /* ── Pied de page ── */
        .footer {
            position: fixed;
            bottom: 0; left: 0; right: 0;
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

    {{-- ── Header : logo ── --}}
    <div class="header">
        <div class="header-logo">
            <img src="{{ $logoBase64 }}" alt="Logo COFIMA">
        </div>
    </div>

    {{-- ── Titre centré, gras, italique, souligné, taille +2 ── --}}
    <div class="titre-doc">{{ $titreDoc }}</div>

    {{-- ── Corps ── --}}
    <div class="content">

        {{-- Paragraphe principal (5 lignes) --}}
        <p>
            Je soussigné, <strong>AVANDE Jean-Claude Alain</strong>, Expert-Comptable Diplômé inscrit à
            l'Ordre des Experts Comptables et Comptables Agréés du Bénin sous le numéro 006-EC,
            Associé-Gérant du Cabinet COFIMA, atteste que
            <strong>{{ $civilite }} {{ strtoupper($employe->nom) }} {{ $employe->prenom }}</strong>
            {{ strtolower($sexe) === 'f' ? 'est employée' : 'est employé' }} au sein du Cabinet COFIMA depuis le
            <strong>{{ $dateEmbauche }}</strong>
            à ce jour en qualité de <strong>{{ $poste }}{{ $demande->inclure_salaire && $demande->salaire_net ? ', avec une rémunération nette mensuelle de ' . number_format($demande->salaire_net, 0, ',', ' ') . ' FCFA' : '' }}</strong>.
        </p>

        {{-- Paragraphe selon le type d'attestation (1 ligne d'espace avant grâce au margin-bottom du <p> précédent) --}}
        @if($demande->type === 'attestation_banque')
            <p>
                La présente attestation est rédigée à usage bancaire.
                Elle peut être produite auprès des établissements financiers ou des organismes bancaires.
                @if($demande->date_retraite)
                    Sa date de retraite prévue est le <strong>{{ \Carbon\Carbon::parse($demande->date_retraite)->isoFormat('D MMMM YYYY') }}</strong>.
                @endif
            </p>
        @elseif($demande->type === 'attestation_ambassade')
            <p>
                La présente attestation est délivrée à la demande de l'intéressé(e)
                dans le cadre d'une démarche consulaire.
            </p>
        @elseif($demande->type === 'attestation_appel_offre')
            <p>
                La présente attestation est délivrée à la demande de l'intéressé(e)
                dans le cadre d'une manifestation d'intérêt ou d'un appel d'offres,
                en vue de faire valoir ses références professionnelles auprès du commanditaire concerné.
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

        {{-- "Fait à Cotonou" : 1 ligne d'espace fourni par le margin-bottom du <p> précédent --}}
        <p class="p-date">
            Fait à Cotonou, le {{ optional($demande->date_validation)->isoFormat('D MMMM YYYY') ?? now()->isoFormat('D MMMM YYYY') }}.
        </p>

    </div>

    {{-- ── Signature : 2 lignes après "Fait à Cotonou" (margin-top: 45px) ── --}}
    <div class="signature">
        <p>
            Pour COFIMA,<br><br><br>
            <span class="sig-nom">
                <strong>Jean-Claude Alain AVANDE</strong><br>
                Expert-Comptable Diplômé<br>
                Associé-Gérant
            </span>
        </p>
    </div>

    {{-- ── Pied de page ── --}}
    <div class="footer">
        N° IFU 3200800611214 · RCCM RB/COT/07B 336 · C/2197 F Immeuble Luca Pacioli, Kouhounou – Cotonou, Bénin<br>
        Tél : +229 01 21 38 04 58 · Mobile : +229 01 90 95 19 59 / 01 95 07 09 48<br>
        Site web : https://www.cofima.cc · Email : cofima@cofimabenin.com / cofima@cofima.cc
    </div>

</body>
</html>
