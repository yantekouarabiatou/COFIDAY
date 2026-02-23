<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Clients</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1a202c;
            background: #fff;
        }

        .page {
            padding: 28px 30px;
        }

        /* ── HEADER ── */
        .header-table {
            width: 100%;
            margin-bottom: 20px;
            padding-bottom: 14px;
            border-bottom: 3px solid #244584;
        }
        .header-table td {
            vertical-align: middle;
            padding: 0;
        }
        .header-logo-cell {
            width: 80px;
        }
        .header-logo-cell img {
            height: 55px;
            width: auto;
        }
        .header-title-cell {
            padding-left: 14px !important;
        }
        .header-title-cell h1 {
            font-size: 20px;
            font-weight: bold;
            color: #244584;
        }
        .header-title-cell .subtitle {
            font-size: 14px;
            color: #718096;
            margin-top: 3px;
        }
        .header-meta-cell {
            text-align: right;
            white-space: nowrap;
        }
        .header-meta-cell .meta-line {
            font-size: 12px;
            color: #718096;
            line-height: 1.8;
        }
        .header-meta-cell .meta-line strong {
            color: #2d3748;
        }

        /* ── STATS ── */
        .stats-table {
            width: 100%;
            margin-bottom: 18px;
            border-collapse: separate;
            border-spacing: 6px 0;
        }
        .stats-table td {
            width: 25%;
            padding: 10px 12px;
            border-radius: 6px;
            vertical-align: middle;
        }
        .stat-total    { background: #dbeafe; border-left: 4px solid #1a56db; }
        .stat-actif    { background: #dcfce7; border-left: 4px solid #16a34a; }
        .stat-prospect { background: #fef9c3; border-left: 4px solid #ca8a04; }
        .stat-inactif  { background: #fee2e2; border-left: 4px solid #dc2626; }

        .stat-num {
            font-size: 22px;
            font-weight: bold;
            line-height: 1;
        }
        .stat-total    .stat-num { color: #1a56db; }
        .stat-actif    .stat-num { color: #16a34a; }
        .stat-prospect .stat-num { color: #ca8a04; }
        .stat-inactif  .stat-num { color: #dc2626; }

        .stat-label {
            font-size: 8.5px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 3px;
        }

        /* ── MAIN TABLE ── */
        .main-table {
            width: 100%;
            border-collapse: collapse;
        }
        .main-table thead tr {
            background-color: #244584;
        }
        .main-table thead th {
            padding: 9px 8px;
            color: #ffffff;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: left;
            border: none;
        }
        .main-table thead th:first-child { padding-left: 12px; }
        .main-table thead th.center { text-align: center; }

        .main-table tbody tr.odd  { background-color: #ffffff; }
        .main-table tbody tr.even { background-color: #f0f5ff; }

        .main-table tbody td {
            padding: 8px 8px;
            vertical-align: middle;
            border-bottom: 1px solid #e2e8f0;
            color: #1a202c;
            font-size: 12px;
        }
        .main-table tbody td:first-child {
            padding-left: 12px;
            color: #9ca3af;
            font-size: 12px;
        }
        .main-table tbody td.center { text-align: center; }

        .td-nom-strong {
            font-size: 11px;
            font-weight: bold;
            color: #111827;
        }
        .td-nom-small {
            font-size: 11px;
            color: #9ca3af;
            margin-top: 2px;
        }
        .td-email {
            color: #1d4ed8;
            font-size: 11px;
        }
        .td-phone {
            color: #374151;
            font-size: 11px;
            margin-top: 2px;
        }
        .td-sector {
            color: #6b7280;
            font-style: italic;
        }

        .dossier-pill {
            display: inline-block;
            background: #dbeafe;
            color: #1e40af;
            font-weight: bold;
            font-size: 12px;
            padding: 2px 9px;
            border-radius: 10px;
        }

        /* ── BADGES ── */
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-actif    { background: #bbf7d0; color: #14532d; }
        .badge-inactif  { background: #fecaca; color: #7f1d1d; }
        .badge-prospect { background: #fde68a; color: #78350f; }

        /* ── FOOTER ── */
        .footer-table {
            width: 100%;
            margin-top: 18px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
        }
        .footer-table td {
            vertical-align: middle;
            padding: 0;
            font-size: 10px;
            color: #9ca3af;
        }
        .footer-table td.right { text-align: right; }
        .confidential-badge {
            display: inline-block;
            background: #fecaca;
            color: #b91c1c;
            padding: 2px 9px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>
<div class="page">

    {{-- ══ HEADER ══ --}}
    @php
        $logoPath   = public_path('storage/company/logo_cofima_bon.jpg');
        $logoExists = file_exists($logoPath);
        $now        = \Carbon\Carbon::now('Africa/Porto-Novo');
    @endphp

    <table class="header-table">
        <tr>
            <td class="header-logo-cell">
                @if($logoExists)
                    <img src="{{ $logoPath }}" alt="Logo">
                @else
                    <table style="width:55px;height:55px;background:#244584;border-radius:8px;">
                        <tr>
                            <td style="text-align:center;vertical-align:middle;color:white;font-size:18px;font-weight:bold;">GC</td>
                        </tr>
                    </table>
                @endif
            </td>
            <td class="header-title-cell">
                <h1>Liste des Clients</h1>
                <div class="subtitle">Rapport complet — Gestion de la clientèle</div>
            </td>
            <td class="header-meta-cell">
                <div class="meta-line"><strong>Date d'export :</strong> {{ $now->format('d/m/Y') }}</div>
                <div class="meta-line"><strong>Heure :</strong> {{ $now->format('H:i') }}</div>
                <div class="meta-line"><strong>Total :</strong> {{ $clients->count() }} client(s)</div>
            </td>
        </tr>
    </table>

    {{-- ══ STATS ══ --}}
    <table class="stats-table">
        <tr>
            <td class="stat-total">
                <div class="stat-num">{{ $clients->count() }}</div>
                <div class="stat-label">Total Clients</div>
            </td>
            <td class="stat-actif">
                <div class="stat-num">{{ $clients->where('statut', 'actif')->count() }}</div>
                <div class="stat-label">Actifs</div>
            </td>
            <td class="stat-prospect">
                <div class="stat-num">{{ $clients->where('statut', 'prospect')->count() }}</div>
                <div class="stat-label">Prospects</div>
            </td>
            <td class="stat-inactif">
                <div class="stat-num">{{ $clients->where('statut', 'inactif')->count() }}</div>
                <div class="stat-label">Inactifs</div>
            </td>
        </tr>
    </table>

    {{-- ══ TABLEAU ══ --}}
    <table class="main-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Nom &amp; Siège Social</th>
                <th>Contact Principal</th>
                <th>Email &amp; Téléphone</th>
                <th>Secteur d'activité</th>
                <th class="center">Dossiers</th>
                <th class="center">Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($clients as $client)
            <tr class="{{ $loop->even ? 'even' : 'odd' }}">
                <td>{{ $loop->iteration }}</td>
                <td>
                    <div class="td-nom-strong">{{ $client->nom }}</div>
                    @if($client->siege_social)
                        <div class="td-nom-small">{{ $client->siege_social }}</div>
                    @endif
                </td>
                <td>{{ $client->contact_principal ?? '—' }}</td>
                <td>
                    <div class="td-email">{{ $client->email ?? '—' }}</div>
                    <div class="td-phone">{{ $client->telephone ?? '—' }}</div>
                </td>
                <td class="td-sector">{{ $client->secteur_activite ?? '—' }}</td>
                <td class="center">
                    <span class="dossier-pill">{{ $client->dossiers_count ?? 0 }}</span>
                </td>
                <td class="center">
                    <span class="badge badge-{{ $client->statut }}">
                        {{ ucfirst($client->statut) }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center;padding:25px;color:#9ca3af;font-style:italic;">
                    Aucun client trouvé.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ══ FOOTER ══ --}}
    <table class="footer-table">
        <tr>
            <td>Document généré automatiquement le {{ $now->format('d/m/Y à H:i') }}</td>
            <td class="right">
                <span class="confidential-badge">CONFIDENTIEL</span>
            </td>
        </tr>
    </table>

</div>
</body>
</html>