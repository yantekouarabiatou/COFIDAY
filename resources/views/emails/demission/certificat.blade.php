<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Certificat de travail</title>
    <style>
        /* Copiez ici tous vos styles (Helvetica, header, footer, etc.) */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 14px;
            line-height: 1.7;
            color: #000;
            padding: 40px 50px 100px 50px;
            position: relative;
            min-height: 100vh;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 35px;
        }
        .header-logo img {
            max-width: 150px;
        }
        .titre-doc {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 30px 0 35px;
        }
        .content p {
            margin-bottom: 18px;
            text-align: justify;
        }
        .signature {
            margin-top: 50px;
            text-align: left;
        }
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
    <div class="header">
        <div class="header-logo">
            <img src="{{ storage_path('app/public/photos/logo-cofima-bon.jpg') }}" alt="Logo COFIMA">
        </div>
    </div>

    <div class="titre-doc">CERTIFICAT DE TRAVAIL</div>


    <div class="content">
        @php
            $employe = $demande->user;
            $sexe = $employe->sexe ?? 'M';
            $civilite = $sexe === 'F' ? 'Madame' : 'Monsieur';
            $dateEmbauche = optional($demande->date_embauche)->isoFormat('D MMMM YYYY') ?? 'date d\'embauche non renseignée';
            $poste = $employe->poste->intitule ?? 'Collaborateur';
            // Gestion sécurisée de la date de départ
            if ($dateDepart instanceof \Carbon\Carbon) {
                $dateDepartFormatted = $dateDepart->isoFormat('D MMMM YYYY');
            } elseif (is_string($dateDepart) && !empty($dateDepart)) {
                $dateDepartFormatted = $dateDepart;
            } else {
                $dateDepartFormatted = $demande->date_depart_confirmee?->isoFormat('D MMMM YYYY') ?? '';
            }
        @endphp
        <p>
            Je soussigné, <strong>AVANDE Jean-Claude</strong>, Expert-Comptable Diplômé inscrit à
            l'Ordre des Experts Comptables et Comptables Agréés du Bénin sous le numéro 006-EC,
            Associé-Gérant du Cabinet COFIMA, atteste que
            <strong>{{ $civilite }} {{ strtoupper($employe->nom) }} {{ $employe->prenom }}</strong>
            {{ strtolower($sexe) === 'f' ? 'est employée' : 'est employé' }}  en qualité de <strong>{{ $poste }}</strong> au sein du Cabinet COFIMA sous un contrat 
            de travail à  durée  indéterminée au sein du cabinet depuis le
            <strong>{{ $dateEmbauche }}</strong>
            jusqu'au <strong>{{ $dateDepartFormatted }}</strong>
            .
        </p>
        <p>
            {{ $civilite }}  <strong>{{ strtoupper($employe->nom) }} {{ $employe->prenom }}</strong> est libre de tout engagement vis-à-vis de COFIMA à compter du
            <strong>{{ \Carbon\Carbon::parse($dateDepart)->addDay()->isoFormat('D MMMM YYYY') }}</strong>.
        </p>
        <p>
            Fait à Cotonou, le <strong>{{ now()->isoFormat('D MMMM YYYY') }}</strong>.
        </p>
    </div>

    <div class="signature">
        <p>
           <strong>Pour COFIMA, </strong> <br><br><br>
            <strong>Jean-Claude AVANDE</strong><br>
            Expert-Comptable Diplômé<br>
            Associé-Gérant<br>
            Tél. 21 38 04 58 / 90 95 19 59<br>
            Courriel : jcavande@cofimabenin.com
        </p>
    </div>

    <div class="footer">
        N° IFU 3200900601214 · RCCM RB/COT/07B/336 · C/2213 F Immeuble Ahouansou Sossou Kouhoumou – Cotonou
        Tél : +229 21 38 04 58 · Mobile : +229 90 95 18 90 / 05 07 09 48 &nbsp;|&nbsp;
        site web : www.cofimabenin.com · cofima@cofimabenin.com
    </div>
</body>
</html>