<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Feuilles de Temps - {{ $dateDebut->format('d/m/Y') }} au {{ $dateFin->format('d/m/Y') }}</title>
    <style>
        @page {
            margin: 15mm 20mm;
            size: A4 landscape;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 4px double #002060;
        }

        .logo {
            max-width: 200px;
            height: auto;
            margin-bottom: 15px;
        }

        .title {
            font-size: 28pt;
            color: #002060;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 10px 0;
        }

        .subtitle {
            font-size: 18pt;
            color: #002060;
            margin: 10px 0;
        }

        .period {
            font-size: 14pt;
            color: #333;
            margin: 15px 0 5px 0;
        }

        .generated {
            font-size: 11pt;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
            font-size: 10pt;
        }

        th {
            background-color: #002060 !important;
            color: white !important;
            padding: 12px 8px;
            text-align: center;
            font-weight: bold;
            font-size: 11pt;
        }

        td {
            padding: 10px 8px;
            border: 1px solid #ddd;
            vertical-align: top;
        }

        tr:nth-child(even) {
            background-color: #f8fbff;
        }

        .total-row {
            background-color: #002060 !important;
            color: white;
            font-weight: bold;
            font-size: 14pt;
        }

        .total-row td {
            padding: 15px 10px;
            text-align: right;
            border: none;
        }

        .footer {
            margin-top: 60px;
            padding-top: 15px;
            border-top: 2px solid #002060;
            text-align: center;
            font-size: 10pt;
            color: #666;
        }

        .footer strong {
            color: #002060;
        }
    </style>
</head>
<body>

    <!-- En-tête avec titre -->
    <div class="logo-section">
            @if(isset($logoBase64))
                <img src="{{ $logoBase64 }}" alt="Logo COFIMA" class="logo-image">
            @else
                <div style="font-size: 40px; color: var(--primary-color); font-weight: 700;">COFIMA</div>
            @endif
        </div>

        <div class="title">Rapport des Feuilles de Temps</div>
        <div class="subtitle">Suivi des heures travaillées</div>
        <div class="period">
            Période du <strong>{{ $dateDebut->format('d/m/Y') }}</strong> au <strong>{{ $dateFin->format('d/m/Y') }}</strong>
        </div>
        <div class="generated">
            Généré le {{ now()->format('d/m/Y à H:i') }}
        </div>
    </div>

    <!-- Tableau des feuilles de temps -->
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Jour</th>
                <th>Collaborateur</th>
                <th>Poste</th>
                <th>Heures Réelles</th>
                <th>Heures Théoriques</th>
                <th>Activités</th>
                <th>Commentaire</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($entries as $entry)
                <tr>
                    <td style="text-align: center;"><strong>{{ $entry->jour->format('d/m/Y') }}</strong></td>
                    <td style="text-align: center;">{{ $entry->jour->translatedFormat('l') }}</td>
                    <td><strong>{{ $entry->user->prenom }} {{ $entry->user->nom }}</strong></td>
                    <td>{{ $entry->user->poste?->intitule ?? '-' }}</td>
                    <td style="text-align: center; font-weight: bold;">
                        {{ number_format($entry->heures_reelles, 2) }} h
                    </td>
                    <td style="text-align: center;">{{ $entry->heures_theoriques }} h</td>
                    <td style="font-size: 9pt;">
                        @if($entry->timeEntries->count() > 0)
                            @foreach($entry->timeEntries as $te)
                                • {{ $te->dossier?->nom ?? 'Sans dossier' }} ({{ $te->heures }}h)<br>
                            @endforeach
                        @else
                            <em>Aucune activité</em>
                        @endif
                    </td>
                    <td style="font-size: 9pt;">
                        {{ $entry->commentaire ? Str::limit($entry->commentaire, 100) : '-' }}
                    </td>
                    <td style="text-align: center;">
                        @php
                            $statutColor = match($entry->statut) {
                                'validé' => '#28a745',
                                'refusé' => '#dc3545',
                                'soumis' => '#ffc107',
                                default => '#6c757d'
                            };
                        @endphp
                        <span style="background-color: {{ $statutColor }}; color: white; padding: 4px 10px; border-radius: 4px; font-weight: bold;">
                            {{ ucfirst($entry->statut) }}
                        </span>
                        @if($entry->motif_refus)
                            <br><small style="color: #dc3545;">Refus : {{ Str::limit($entry->motif_refus, 50) }}</small>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center; padding: 30px; color: #999;">
                        Aucune feuille de temps enregistrée pour cette période.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Total des heures -->
    @if($entries->count() > 0)
        <table style="margin-top: 30px;">
            <tr class="total-row">
                <td colspan="8" style="text-align: right; padding-right: 20px;">
                    <strong>TOTAL DES HEURES RÉELLES TRAVAILLÉES</strong>
                </td>
                <td style="text-align: center; font-size: 18pt;">
                    {{ number_format($entries->sum('heures_reelles'), 2) }} heures
                </td>
            </tr>
        </table>
    @endif

    <!-- Pied de page -->
    <div class="footer">
        <div>
            @if(isset($companySetting))
                {{ $companySetting->adresse ?? '' }} |
                Tél : {{ $companySetting->telephone ?? '' }} |
                {{ $companySetting->site_web ?? '' }}
            @else
                COFIMA BÉNIN
            @endif
        </div>
        <div style="margin-top: 8px; font-style: italic;">
            Document confidentiel – À usage interne uniquement
        </div>
    </div>

</body>
</html>
