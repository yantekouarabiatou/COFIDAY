<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">

    @php
        $sexe      = $demande->user->sexe;
        $civilite  = $sexe === 'M' ? 'Monsieur' : ($sexe === 'F' ? 'Madame' : '');
        $employe   = $sexe === 'M' ? 'employé' : ($sexe === 'F' ? 'employée' : 'employé(e)');

        $intitulePoste = $demande->user->poste->intitule ?? 'Auditeur';
        $voyelles      = ['a','e','i','o','u','h','â','à','é','è','ê','ë','î','ï','ô','ù','û','ü'];
        $premiereLettre = mb_strtolower(mb_substr($intitulePoste, 0, 1));
        $dePoste = in_array($premiereLettre, $voyelles) ? "d'" : "de ";
    @endphp

    <title>Attestation de congé</title>

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
            margin-top: 105px;   /* 5 lignes × 21px */
            margin-bottom: 42px; /* 2 lignes × 21px */
        }

        /* ── Corps : taille 15px, 1 ligne entre paragraphes ── */
        .content p {
            font-size: 15px;
            margin-bottom: 23px;
            text-align: justify;
        }
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
    <div class="titre-doc">ATTESTATION DE CONGÉ</div>

    {{-- ── Corps ── --}}
    <div class="content">

        {{-- Paragraphe principal --}}
        <p>
            Je soussigné, <strong>AVANDE Jean-Claude Alain</strong>, Expert-Comptable Diplômé inscrit à
            l'Ordre des Experts Comptables et Comptables Agréés du Bénin sous le numéro 006-EC,
            Associé-Gérant du Cabinet <strong>COFIMA</strong>,
            certifie que {{ $civilite }}
            <strong>{{ strtoupper($demande->user->nom) }} {{ $demande->user->prenom }}</strong>,
            {{ $employe }} au poste {{ $dePoste }}<strong>{{ $intitulePoste }}</strong>,
            sera en congé administratif du
            <strong>{{ $demande->date_debut_formatted ?? \Carbon\Carbon::parse($demande->date_debut)->isoFormat('D MMMM YYYY') }}</strong>
            au
            <strong>{{ $demande->date_fin_formatted ?? \Carbon\Carbon::parse($demande->date_fin)->isoFormat('D MMMM YYYY') }}</strong>.
        </p>

        @if(!empty($demande->motif))
        <p>
            Cette période de congé lui a été accordée conformément aux dispositions internes
            en vigueur, afin de lui permettre de participer aux
            <strong>{{ $demande->motif }}</strong>
            durant ladite période.
        </p>
        @endif

        <p>
            La présente attestation lui est délivrée pour servir et valoir ce que de droit.
        </p>

        <p class="p-date">
            Fait à Cotonou, le {{ now()->isoFormat('D MMMM YYYY') }}.
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
