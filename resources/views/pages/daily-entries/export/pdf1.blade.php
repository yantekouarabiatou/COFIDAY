@php
function hoursToHoursMinutesShort($decimal)
{
    if (!$decimal || $decimal <= 0) {
        return '0h';
    }

    $hours = floor($decimal);
    $minutes = round(($decimal - $hours) * 60);

    if ($minutes >= 60) {
        $hours++;
        $minutes -= 60;
    }

    if ($hours == 0 && $minutes > 0) {
        return $minutes . 'min';
    }

    if ($minutes == 0) {
        return $hours . 'h';
    }

    return $hours . 'h ' . str_pad($minutes, 2, '0', STR_PAD_LEFT) . 'min';
}
@endphp

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>
    Feuille de Temps - 
        {{ $entry->jour?->format('d/m/Y') ?? 'Date inconnue' }} - 
        {{ $entry->user?->prenom ?? 'Utilisateur' }} {{ $entry->user?->nom ?? 'inconnu' }}    </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 40px 30px;
            background: #fff;
        }

        :root {
            --primary: #002060;
            --info: #0c346d;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
        }

        h1, h2, h3 {
            margin: 0 0 12px 0;
            color: var(--primary);
            font-weight: bold;
        }

        /* En-tête */
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid var(--primary);
        }

        .logo {
            max-width: 200px;
            height: auto;
            margin-bottom: 15px;
        }

        .header h1 {
            font-size: 26px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .header h2 {
            font-size: 19px;
            margin-top: 8px;
        }

        .reference {
            margin-top: 12px;
            font-size: 15px;
            font-weight: bold;
            color: var(--primary);
            text-transform: uppercase;
        }

        /* Tableau principal */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
            vertical-align: top;
            font-size: 11.5px;
        }

        th {
            background-color: #f0f4f8;
            font-weight: bold;
            width: 30%;
            color: #333;
        }

        /* Barre de progression heures */
        .progress-bar {
            height: 25px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            margin: 8px 0;
            position: relative;
        }

        .progress-fill {
            height: 100%;
            text-align: center;
            color: white;
            font-weight: bold;
        }

        .progress-text {
            margin-top: 5px;
            font-size: 12px;
            font-weight: bold;
        }

        /* Tableau des activités */
        .activities-table th {
            background-color: var(--info);
            color: white;
            text-align: center;
        }

        .activities-table td {
            text-align: center;
        }

        /* Statut avec couleur */
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 14px;
        }

        /* Bloc commentaire */
        .comment-block {
            border: 2px solid var(--primary);
            border-radius: 6px;
            padding: 15px;
            margin: 25px 0;
            background-color: #f8fbff;
        }

        .comment-block h3 {
            color: var(--primary);
            font-size: 15px;
            margin-bottom: 10px;
            border-bottom: 1px dashed var(--info);
            padding-bottom: 5px;
        }

        /* Infos complémentaires */
        .info-section {
            margin-top: 30px;
            padding: 15px;
            border-left: 6px solid var(--primary);
            background-color: #f8f9fa;
            font-size: 11.5px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 10px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-weight: bold;
            color: var(--primary);
            margin-bottom: 4px;
        }

        /* Pied de page */
        .footer {
            margin-top: 50px;
            padding-top: 15px;
            border-top: 1px solid #aaa;
            text-align: center;
            font-size: 10px;
            color: #666;
        }

        .footer strong {
            color: var(--primary);
        }
    </style>
</head>
<body>

    <!-- En-tête -->
    <div class="header">
        @if(isset($logoBase64) && $logoBase64)
            <img src="{{ $logoBase64 }}" alt="Logo entreprise" class="logo">
        @else
            <div style="font-size: 48px; font-weight: 900; color: var(--primary); letter-spacing: 2px;">
                COFIMA
            </div>
        @endif

        <h1>Feuille de Temps Journalière</h1>
        <h2>{{ $entry->jour->format('l d F Y') }}</h2>
        <div class="reference">
            Référence : FT-{{ str_pad($entry->id, 6, '0', STR_PAD_LEFT) }}
        </div>
    </div>

    <!-- Informations collaborateur et heures -->
    <table>
        <tr>
            <th>Collaborateur</th>
            <td>
                <strong>{{ $entry->user->prenom }} {{ $entry->user->nom }}</strong><br>
                <small>{{ $entry->user->poste?->intitule ?? 'Poste non défini' }}</small>
            </td>
            <th>Date de saisie</th>
            <td>{{ $entry->created_at->format('d/m/Y à H:i') }}</td>
        </tr>
        <tr>
            <th>Heures Réelles / Théoriques</th>
            <td colspan="3">
                <div class="progress-bar">
                    @php
                        $percentage = $entry->heures_theoriques > 0 
                            ? ($entry->heures_reelles / $entry->heures_theoriques) * 100 
                            : 0;
                        $bg = $percentage >= 100 ? 'var(--success)' 
                            : ($percentage >= 80 ? 'var(--warning)' : 'var(--danger)');
                    @endphp
                    <div class="progress-fill" style="width: {{ min($percentage, 100) }}%; background-color: {{ $bg }};">
                        {{ number_format($percentage, 1) }}%
                    </div>
                </div>
                <div class="progress-text">
                    <strong>{{ hoursToHoursMinutesShort($entry->heures_reelles) }}</strong> travaillées 
                    sur <strong>{{ hoursToHoursMinutesShort($entry->heures_theoriques) }}</strong> prévues
                </div>
            </td>
        </tr>
    </table>

    <!-- Tableau des activités -->
    @if($entry->timeEntries->count() > 0)
        <h3 style="color: var(--primary); margin: 25px 0 10px;">Activités du jour ({{ $entry->timeEntries->count() }})</h3>
        <table class="activities-table">
            <thead>
                <tr>
                    <th>Dossier / Projet</th>
                    <th>Heures</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                @foreach($entry->timeEntries as $te)
                    <tr>
                        <td><strong>{{ $te->dossier?->nom ?? 'Sans dossier' }}</strong></td>
                        <td><strong>{{ hoursToHoursMinutesShort($te->heures_reelles) }}</strong></td>
                        <td>{{ $te->description ?: '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p><em>Aucune activité saisie pour cette journée.</em></p>
    @endif

    <!-- Commentaire général -->
    @if($entry->commentaire)
        <div class="comment-block">
            <h3>Commentaire du collaborateur</h3>
            <div>{!! nl2br(e($entry->commentaire)) !!}</div>
        </div>
    @endif

    <!-- Statut de validation -->
    <table>
        <tr>
            <th style="text-align: center; font-size: 15px;">Statut de la Feuille</th>
            <td style="text-align: center; font-size: 16px; font-weight: bold;">
                @php
                    $statusColor = match($entry->statut) {
                        'validé' => 'var(--success)',
                        'refusé' => 'var(--danger)',
                        'soumis' => 'var(--warning)',
                        default => '#6c757d'
                    };
                @endphp
                <span class="status-badge" style="background-color: {{ $statusColor }}; color: white;">
                    {{ ucfirst($entry->statut) }}
                </span>
                @if($entry->valide_le)
                    <br><small style="color: #333;">Validée le {{ $entry->valide_le->format('d/m/Y à H:i') }}</small>
                @endif
                @if($entry->motif_refus)
                    <br><small style="color: var(--danger);"><strong>Motif du refus :</strong> {{ $entry->motif_refus }}</small>
                @endif
            </td>
        </tr>
    </table>

    <!-- Métadonnées -->
    <div class="info-section">
        <strong>Informations système</strong>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Créée le</div>
                <div>{{ $entry->created_at->format('d/m/Y à H:i') }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Dernière modification</div>
                <div>{{ $entry->updated_at->format('d/m/Y à H:i') }}</div>
            </div>
        </div>
    </div>

    <!-- Pied de page -->
    <div class="footer">
        <div><strong>Document généré le {{ now()->format('d/m/Y à H:i') }}</strong></div>
        <div style="margin-top: 8px;">
            @if(isset($companySetting))
                {{ $companySetting->adresse ?? 'Adresse non définie' }} |
                Tél : {{ $companySetting->telephone ?? 'Non défini' }} |
                {{ $companySetting->site_web ?? '' }}
            @else
                COFIMA BÉNIN
            @endif
        </div>
        <div style="margin-top: 6px; font-style: italic;">
            Document confidentiel – Gestion interne des temps de travail
        </div>
    </div>

</body>
</html>