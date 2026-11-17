<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $titre ?? 'Notification COFIMA' }}</title>
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

        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }

        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 13px;
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
            margin-bottom: 30px;
        }
        .header-logo img { max-width: 140px; }
        .company-info {
            text-align: right;
            font-size: 9px;
            color: #333;
            line-height: 1.6;
        }
        .company-info .name { font-weight: bold; font-size: 11px; }

        .ref-bloc {
            text-align: right;
            font-size: 10px;
            color: #555;
            margin-bottom: 20px;
        }

        .titre-doc {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 25px 0 30px;
        }

        .corps p {
            margin-bottom: 16px;
            text-align: justify;
        }

        .signature {
            margin-top: 45px;
            text-align: left;
        }
        .signature p { line-height: 1.8; }

        .footer {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            padding: 8px 50px;
            border-top: 1px solid #ccc;
            font-size: 8.5px;
            color: #555;
            text-align: center;
            background: #fff;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="header-logo">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" alt="Logo COFIMA">
            @endif
        </div>
        <div class="company-info">
            <div class="name">CABINET COFIMA</div>
            Expert-Comptable Diplômé<br>
            Cotonou, Bénin
        </div>
    </div>

    <div class="ref-bloc">
        Réf. : {{ $reference ?? '' }}<br>
        Le {{ $date ?? now()->isoFormat('D MMMM YYYY') }}
    </div>

    <div class="titre-doc">{{ $titre ?? 'NOTIFICATION' }}</div>

    <div class="corps">
        {!! $corps !!}
    </div>

    <div class="signature">
        <p>
            Pour COFIMA,<br><br><br>
            <strong>Jean-Claude Alain AVANDE</strong><br>
            Expert-Comptable Diplômé<br>
            Associé-Gérant<br>
            Tél. 21 38 04 58 / 90 95 19 59<br>
            Courriel : jcavande@cofimabenin.com
        </p>
    </div>

    <div class="footer">
        N° IFU 3200800611214 · RCCM RB/COT/07B 336 · C/2197 F Immeuble Luca Pacioli, Kouhounou – Cotonou, Bénin<br>
        Tél : +229 01 21 38 04 58 · Mobile : +229 01 90 95 19 59 / 01 95 07 09 48<br>
        Site web : https://www.cofima.cc · Email : cofima@cofimabenin.com
    </div>

</body>
</html>
