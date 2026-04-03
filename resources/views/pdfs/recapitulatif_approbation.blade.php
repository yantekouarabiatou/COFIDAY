<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">

    @php
        $isPermission = str_contains(strtolower($demande->typeConge->libelle ?? ''), 'permission');
        $motLabel     = $isPermission ? 'permission' : 'congé';
        $motLabelCap  = $isPermission ? 'Permission' : 'Congé';

        // Ajout des variables de genre
        $sexe      = $demande->user->sexe;
        $civilite  = $sexe === 'M' ? 'Monsieur' : ($sexe === 'F' ? 'Madame' : '');
        $employe   = $sexe === 'M' ? 'employé' : ($sexe === 'F' ? 'employée' : 'employé(e)');
        $pronom    = $sexe === 'M' ? 'Il' : ($sexe === 'F' ? 'Elle' : 'Il/Elle');
    @endphp

    <title>Note de service N°{{ $numeroNote }}</title>

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

        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 12px;
            line-height: 1.7;
            color: #000;
            margin: 40px 50px;
        }

        .entete            { display: table; width: 100%; margin-bottom: 10px; }
        .entete-logo       { display: table-cell; width: 150px; vertical-align: middle; }
        .entete-logo img   { max-width: 130px; }
        .entete-texte      { display: table-cell; vertical-align: middle; text-align: right; font-size: 9px; color: #555; }
        .entete-texte .societe { font-size: 12px; font-weight: bold; color: #000; }

        hr.sep { border: none; border-top: 2px solid #000; margin: 8px 0 20px; }

        .numero-note {
            font-size: 14px; font-weight: bold; text-align: center;
            text-decoration: underline; text-transform: uppercase;
            letter-spacing: 0.4px; margin-bottom: 28px;
        }

        .corps p { margin-bottom: 14px; text-align: justify; }

        .table-conge { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .table-conge th {
            background-color: #244584; color: #fff;
            padding: 8px 10px; font-size: 11px;
            text-align: center; border: 1px solid #000;
        }
        .table-conge td {
            padding: 10px 12px; font-size: 11px;
            border: 1px solid #000; vertical-align: top;
        }
        .table-conge td.centre { text-align: center; vertical-align: middle; }

        .table-soldes { width: 100%; border-collapse: collapse; margin: 8px 0 6px; }
        .table-soldes th {
            background-color: #333; color: #fff;
            padding: 7px 10px; font-size: 10px;
            text-align: center; border: 1px solid #000;
        }
        .table-soldes td {
            padding: 6px 10px; font-size: 11px;
            text-align: center; border: 1px solid #ccc;
        }
        .table-soldes tr.annee-prelevee td { font-weight: bold; background-color: #f0f4ff; }

        .sig-haut      { width: 50%; vertical-align: top; font-size: 12px; margin-bottom: 50px; }
        .sig-bas      { width: 50%; vertical-align: top; text-align: left; font-size: 12px; }

        .footer {
            margin-top: 40px; border-top: 1px solid #aaa;
            padding-top: 8px; font-size: 8.5px; color: #666; text-align: center;
        }
    </style>
</head>
<body>

    {{-- EN-TÊTE --}}
    <div class="entete">
        <div class="entete-logo">
            <div class="logo-container">
                <img src="file://{{ public_path('storage/photos/logo-cofima-bon.jpg') }}" class="logo">
            </div>
        </div>
        <div class="entete-texte">
            <span class="societe">COFIMA BENIN</span><br>
            Compagnie Fiduciaire de Management et d'Audit<br>
            Cotonou — Bénin
        </div>
    </div>

    <hr class="sep">

    {{-- NUMÉRO DE NOTE --}}
    <div class="numero-note">
        Note de service N°{{ $numeroNote }}
    </div>

    {{-- CORPS --}}
    <div class="corps">
        <p>
            {{ $civilite }}
            <strong>{{ $demande->user->nom }} {{ $demande->user->prenom }}</strong>,
            {{ $employe }} à COFIMA, bénéficiera de
            @if($isPermission) sa permission @else ses congés @endif
            administratifs pour la période allant du
            <strong>{{ $demande->date_debut_formatted ?? $demande->date_debut }}</strong>
            au
            <strong>{{ $demande->date_fin_formatted ?? $demande->date_fin }}</strong>
            soit <strong>{{ $demande->nombre_jours }}</strong> jour(s) ouvrables.
        </p>

        <p>
            {{ $pronom }} reprend normalement service le
            <strong>{{ $dateRepriseFormatee }}</strong>.
        </p>
    </div>

    {{-- TABLE TITRE DE CONGÉ --}}
    <table class="table-conge">
        <thead>
            <tr>
                <th>Nom et prénom</th>
                <th>Début {{ $motLabel }}</th>
                <th>Fin {{ $motLabel }}</th>
                <th>Détail</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $demande->user->nom }} {{ $demande->user->prenom }}</td>
                <td class="centre">{{ $demande->date_debut_formatted ?? $demande->date_debut }}</td>
                <td class="centre">{{ $demande->date_fin_formatted ?? $demande->date_fin }}</td>
                <td>
                    @if($demande->typeConge && $demande->typeConge->est_annuel)
                        {{ $motLabelCap }}s administratifs prélevés sur les années suivantes :<br>
                        @foreach($anneesPrelevees as $annee)
                            Reste dû {{ $annee }} :
                            <strong>{{ $soldesParAnnee[$annee] ?? '—' }} jour(s)</strong><br>
                        @endforeach
                    @else
                        {{ $demande->typeConge->libelle ?? '—' }}<br>
                        <strong>{{ $demande->nombre_jours }} jour(s)</strong>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    {{-- RÉCAPITULATIF SOLDES --}}
    @if($demande->typeConge && $demande->typeConge->est_annuel && $soldes->isNotEmpty())

        <p style="font-size:11px; margin: 16px 0 4px;">
            <strong>Récapitulatif du solde de congés :</strong>
        </p>

        <table class="table-soldes">
            <thead>
                <tr>
                    <th>Année</th>
                    <th>Jours acquis</th>
                    <th>Jours pris</th>
                    <th>Jours restants</th>
                </tr>
            </thead>
            <tbody>
                @foreach($soldes as $solde)
                <tr class="{{ in_array($solde->annee, $anneesPrelevees) ? 'annee-prelevee' : '' }}">
                    <td>
                        {{ $solde->annee }}
                    </td>
                    <td>{{ $solde->jours_acquis }}</td>
                    <td>{{ $solde->jours_pris }}</td>
                    <td style="color: {{ $solde->jours_restants > 0 ? '#166534' : '#dc2626' }}; font-weight:bold;">
                        {{ $solde->jours_restants }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

    @endif

    {{-- SIGNATURE --}}
    <div class="signature-wrap">
        <div class="sig-haut">
            Fait à Cotonou, le {{ now()->format('d/m/Y') }}
        </div>
        
        <div class="sig-bas">
            Pour COFIMA,<br><br>
            {{-- CACHET APPROUVÉ --}}
            <div style="
                display: inline-block;
                border: 3px dashed #16a34a;
                border-radius: 6px;
                padding: 10px 28px;
                margin-top: 24px;
                text-align: center;
                transform: rotate(-8deg);
                transform-origin: left center;
            ">
                <span style="
                    display: block;
                    font-size: 22px;
                    font-weight: bold;
                    color: #16a34a;
                    letter-spacing: 4px;
                    text-transform: uppercase;
                    line-height: 1.2;
                ">APPROUVÉ</span>
            </div><br>
            <br><strong>Jean Claude AVANDE</strong><br>
            Expert-Comptable Diplômé<br>
            Associé-Gérant
        </div>
    </div>

    {{-- FOOTER --}}
    <div class="footer">
        N° IFU 3200900601214 · RCCM RB/COT/07B/336 · C/2213 F Immeuble Athoueinsou Sessou Koutoumou – Cotonou<br>
        Tél : +229 21 38 04 58 · Mobile : +229 90 95 18 90 / 05 07 09 48<br>
        Site web : www.cofimabenin.com · Email : cofima@cofimabenin.com
    </div>

</body>
</html>