<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $titre }}</title>
    <style>
        /* Même charte que les attestations délivrées (pdfs/attestation_letter) */
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

        /* marge basse réservée au pied de page, répété sur chaque page */
        @page { margin: 35px 50px 85px 50px; }

        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 10.5px;
            line-height: 1.4;
            color: #000;
        }

        /* ── Header : logo ── */
        .header-logo img { max-width: 220px; }

        /* ── Titre : gras, italique, souligné, centré, majuscules ── */
        .titre-doc {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            font-style: italic;
            text-decoration: underline;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 28px;
            margin-bottom: 4px;
        }
        .sous-titre { text-align: center; font-size: 9.5px; color: #555; margin-bottom: 18px; }

        h2 { font-size: 12px; font-weight: bold; margin: 14px 0 6px; }
        table.donnees { width: 100%; border-collapse: collapse; }
        table.donnees thead { display: table-header-group; }   /* en-tête répété sur chaque page */
        table.donnees tr { page-break-inside: avoid; }
        .nouvelle-page { page-break-before: always; }
        table.donnees th { background: #26467F; color: #fff; padding: 5px; font-size: 10px; border: 1px solid #26467F; }
        table.donnees td { border: 1px solid #ccc; padding: 4px 5px; text-align: center; }
        table.donnees td.label { text-align: left; white-space: nowrap; }
        table.donnees tr.total td { font-weight: bold; background: #e9ecef; }
        .diagramme { text-align: center; }

        /* ── Pied de page (identique aux attestations) ── */
        .footer {
            position: fixed;
            bottom: -65px; left: 0; right: 0;
            padding-top: 8px;
            border-top: 1px solid #ccc;
            font-size: 9px;
            line-height: 1.5;
            color: #555;
            text-align: center;
        }
    </style>
</head>
<body>

    {{-- ── Pied de page ── --}}
    <div class="footer">
        N° IFU 3200800611214 · RCCM RB/COT/07B 336 · C/2197 F Immeuble Luca Pacioli, Kouhounou – Cotonou, Bénin<br>
        Tél : +229 01 21 38 04 58 · Mobile : +229 01 90 95 19 59 / 01 95 07 09 48<br>
        Site web : https://www.cofima.cc · Email : cofima@cofimabenin.com / cofima@cofima.cc
    </div>

    {{-- ── Header : logo ── --}}
    <div class="header-logo">
        @if($logo)<img src="{{ $logo }}" alt="Logo COFIMA">@endif
    </div>

    <div class="titre-doc">{{ $titre }}</div>
    <div class="sous-titre">Édité à Cotonou, le {{ now()->isoFormat('D MMMM YYYY [à] HH:mm') }}</div>

    @php
        $fmt = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', ' '), '0'), ',');
        $libelle = fn ($s) => $mode === 'user'
            ? $s->annee
            : $s->user->prenom . ' ' . $s->user->nom . ($annee === 'toutes' ? ' (' . $s->annee . ')' : '');
        // l'image est dessinée à ~1600 px pour la pleine largeur
        $largeurPct = $diagramme ? min(100, round($diagramme['width'] / 1600 * 100)) : 100;
    @endphp

    @if($soldes->isEmpty())
        <p style="text-align:center;color:#888">Aucun solde enregistré.</p>
    @else
        @if($diagramme)
            <h2>Diagramme : jours consommés / restants</h2>
            <div class="diagramme">
                <img src="{{ $diagramme['src'] }}" style="width: {{ $largeurPct }}%">
            </div>
        @endif

        <h2 class="{{ $diagramme ? 'nouvelle-page' : '' }}">Détail</h2>
        <table class="donnees">
            <thead>
                <tr>
                    <th>{{ $mode === 'user' ? 'Année' : 'Collaborateur' }}</th>
                    <th>Acquis</th>
                    <th>Reportés</th>
                    <th>Disponible</th>
                    <th>Consommés</th>
                    <th>Restants</th>
                    <th>Consommation</th>
                </tr>
            </thead>
            <tbody>
                @foreach($soldes as $s)
                    @php $dispo = $s->jours_acquis + ($s->jours_reportes ?? 0); @endphp
                    <tr>
                        <td class="label">{{ $libelle($s) }}</td>
                        <td>{{ $fmt($s->jours_acquis) }}</td>
                        <td>{{ $fmt($s->jours_reportes ?? 0) }}</td>
                        <td>{{ $fmt($dispo) }}</td>
                        <td>{{ $fmt($s->jours_pris) }}</td>
                        <td>{{ $fmt($s->jours_restants) }}</td>
                        <td>{{ $dispo > 0 ? round($s->jours_pris / $dispo * 100, 1) . ' %' : '-' }}</td>
                    </tr>
                @endforeach
                @php $dispoT = $totaux['acquis'] + $totaux['reportes']; @endphp
                <tr class="total">
                    <td class="label">TOTAL</td>
                    <td>{{ $fmt($totaux['acquis']) }}</td>
                    <td>{{ $fmt($totaux['reportes']) }}</td>
                    <td>{{ $fmt($dispoT) }}</td>
                    <td>{{ $fmt($totaux['pris']) }}</td>
                    <td>{{ $fmt($totaux['restants']) }}</td>
                    <td>{{ $dispoT > 0 ? round($totaux['pris'] / $dispoT * 100, 1) . ' %' : '-' }}</td>
                </tr>
            </tbody>
        </table>
    @endif
</body>
</html>
