<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">

    @php
        $sexe      = $demande->user->sexe;
        $civilite  = $sexe === 'M' ? 'Monsieur' : ($sexe === 'F' ? 'Madame' : '');
        $employe   = $sexe === 'M' ? 'employé' : ($sexe === 'F' ? 'employée' : 'employé(e)');

        // ── Gestion du "de / d'" pour le poste ─────────────────────
        $intitulePoste = $demande->user->poste->intitule ?? 'Auditeur';
        $voyelles      = ['a','e','i','o','u','h','â','à','é','è','ê','ë','î','ï','ô','ù','û','ü'];

        $premiereLettre = mb_strtolower(mb_substr($intitulePoste, 0, 1));

        $dePoste = in_array($premiereLettre, $voyelles) ? "d’" : "de ";
    @endphp

    <title>Attestation de congé</title>

    <style>
        @font-face {
            font-family: 'Helvetica';
            src: url("file://{{ str_replace('\\', '/', storage_path('fonts/Helvetica.ttf')) }}") format('truetype');
        }

        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 12px;
            line-height: 1.8;
            margin: 40px 50px;
            color: #000;
        }

        .entete {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .logo {
            display: table-cell;
            width: 150px;
        }

        .logo img {
            max-width: 150px;
        }

        .titre {
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            margin: 30px 0;
            text-transform: uppercase;
            text-decoration: underline;
        }

        .contenu {
            text-align: justify;
        }

        .contenu p {
            margin-bottom: 15px;
        }

        .signature {
            margin-top: 60px;
        }

        .footer {
            margin-top: 50px;
            font-size: 10px;
        }
    </style>
</head>

<body>

    {{-- EN-TÊTE --}}
    <div class="entete">
        <div class="logo">
            <img src="file://{{ public_path('storage/photos/logo-cofima-bon.jpg') }}">
        </div>
    </div>

    {{-- TITRE --}}
    <div class="titre">
        ATTESTATION DE CONGÉ
    </div>

    {{-- CONTENU --}}
    <div class="contenu">
        <p>
            Je soussigné, <strong>AVANDE Jean Claude</strong>,
            Expert-Comptable Diplômé inscrit à l’Ordre des Experts Comptables
            et Comptables Agréés du Bénin, Directeur du Cabinet
            <strong>COFIMA</strong>,
            certifie que {{ $civilite }}
            <strong>{{ $demande->user->nom }} {{ $demande->user->prenom }}</strong>,
            {{ $employe }} au poste {{ $dePoste }} <strong>{{ $intitulePoste }}</strong>,
            sera en congé administratif du
            <strong>{{ $demande->date_debut_formatted ?? $demande->date_debut }}</strong>
            au
            <strong>{{ $demande->date_fin_formatted ?? $demande->date_fin }}</strong>.
        </p>

        <p>
            Cette période de congé lui a été accordée conformément aux dispositions internes
            en vigueur, afin de lui permettre de participer aux
            <strong>{{ $demande->motif ?? 'activités prévues' }}</strong>
            durant ladite période.
        </p>

        <p>
            La présente attestation lui est délivrée pour servir et valoir ce que de droit.
        </p>
    </div>

    {{-- SIGNATURE --}}
    <div class="signature">
        Fait à Cotonou, le {{ now()->format('d/m/Y') }}<br><br>

        Pour {{ $cabinet ?? 'COFIMA' }},<br><br><br>

        <strong>Jean Claude AVANDE</strong><br>
        Expert-Comptable Diplômé<br>
        Associé-Gérant
    </div>

        {{-- FOOTER --}}
    <div class="footer">
        <div class="gauche-footer">
            C/2213 F Immeuble Athouansou Sossou Kouhou – Cotonou<br>
            Tél : +229 21 38 04 58 · Mobile : +229 90 95 18 90 / 05 07 09 48<br>
            Site web : www.cofimabenin.com · Email : cofima@cofimabenin.com
        </div>
        <div class="droite-footer">
            <div class="logo2">
                <img src="file://{{ public_path('storage/photos/logo-cofima-bon.jpg') }}">
            </div>
        </div>
    </div>

</body>
</html>