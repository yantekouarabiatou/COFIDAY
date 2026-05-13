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
            padding-bottom: 60px;
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
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            border-top: 1px solid #ccc;
            padding: 8px 50px;
            font-size: 9px;
            color: #555;
            text-align: center;
            background: #fff;
        }
    </style>
</head>

<body>

    {{-- EN-TÊTE --}}
    <div class="entete">
        <div class="logo">
            <img src="{{ $logoBase64 }}">
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
            N° IFU 3200800611214 · RCCM RB/COT/07B 738 · C/2197 Immeuble Luca Pacioli, Kouhounou – Cotonou,Bénin<br>
            Tél : +229 01 21 38 04 58 · Mobile : +229 01 90 95 19 59 / 01 95 07 09 48<br>
            Site web : https:www.cofima.cc · Email : cofima@cofima.cc / cofima@cofimabenin.com 
     </div>
        
    </div>

</body>
</html>