<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport Analyse Mission - {{ $dossier->reference }}</title>
    <style>
        @page {
            margin: 15mm 20mm;
            size: A4;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }

        /* En-tête */
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #002060;
        }

        .title {
            font-size: 24pt;
            color: #002060;
            font-weight: bold;
            margin: 15px 0;
        }

        .mission-ref {
            font-size: 14pt;
            color: #333;
            margin: 10px 0;
        }

        .period {
            font-size: 11pt;
            color: #666;
            margin: 10px 0;
        }

        /* Tableaux */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
            font-size: 10pt;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        th {
            background-color: #002060;
            color: white;
            padding: 12px 10px;
            text-align: left;
            font-weight: bold;
            font-size: 11pt;
        }

        td {
            padding: 10px 10px;
            border: 1px solid #ddd;
            vertical-align: top;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        /* Titres de sections */
        .section-title {
            color: #002060;
            font-size: 16pt;
            font-weight: bold;
            margin: 30px 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #002060;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: bold;
        }

        .badge-success {
            background: #28a745;
            color: white;
        }

        .badge-warning {
            background: #ffc107;
            color: #333;
        }

        .badge-danger {
            background: #dc3545;
            color: white;
        }

        .badge-info {
            background: #17a2b8;
            color: white;
        }

        .badge-primary {
            background: #002060;
            color: white;
        }

        /* Pied de page */
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 2px solid #002060;
            text-align: center;
            font-size: 9pt;
            color: #666;
        }

        .confidential {
            margin-top: 10px;
            font-style: italic;
            color: #dc3545;
        }

        /* Saut de page */
        .page-break {
            page-break-after: always;
        }

        /* Valeurs importantes */
        .important-value {
            font-weight: bold;
            font-size: 12pt;
        }

        .surplus {
            color: #dc3545;
        }

        .economie {
            color: #28a745;
        }

        /* Indicateurs de performance */
        .indicator {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 2px;
            font-size: 8pt;
            font-weight: bold;
            margin-right: 5px;
        }

        .indicator-high {
            background: #dc3545;
            color: white;
        }

        .indicator-medium {
            background: #ffc107;
            color: #333;
        }

        .indicator-low {
            background: #28a745;
            color: white;
        }
    </style>
</head>
<body>

    <!-- En-tête -->
    <div class="header">
        <div class="logo-section">
            @if(isset($logoBase64))
                <img src="{{ $logoBase64 }}" alt="Logo COFIMA" class="logo-image">
            @else
                <div style="font-size: 40px; color: var(--primary-color); font-weight: 700;">COFIMA</div>
            @endif
        </div>

        <div class="title">Analyse des Personnels par Mission</div>
        <div class="mission-ref">
            {{ $dossier->reference }} - {{ $dossier->nom }}
        </div>
        <div class="period">
            Client: {{ $dossier->client->nom }} |
            Généré le {{ $date_export }}
        </div>
    </div>

    <!-- 1. INFORMATIONS SUR LA MISSION (en tableau) -->
    <h3 class="section-title">📋 Informations sur la Mission</h3>

    <table>
        <thead>
            <tr>
                <th style="width: 30%">Information</th>
                <th style="width: 70%">Détails</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Référence</strong></td>
                <td>{{ $dossier->reference }}</td>
            </tr>
            <tr>
                <td><strong>Nom de la mission</strong></td>
                <td>{{ $dossier->nom }}</td>
            </tr>
            <tr>
                <td><strong>Client</strong></td>
                <td>{{ $dossier->client->nom }}</td>
            </tr>
            <tr>
                <td><strong>Type de mission</strong></td>
                <td>
                    @php
                        $typeLabels = [
                            'audit' => 'Audit',
                            'conseil' => 'Conseil',
                            'formation' => 'Formation',
                            'expertise' => 'Expertise',
                            'autre' => 'Autre'
                        ];
                    @endphp
                    {{ $typeLabels[$dossier->type_dossier] ?? ucfirst($dossier->type_dossier) }}
                </td>
            </tr>
            <tr>
                <td><strong>Statut</strong></td>
                <td>
                    @php
                        $statutColors = [
                            'ouvert' => 'info',
                            'en_cours' => 'primary',
                            'suspendu' => 'warning',
                            'cloture' => 'success',
                            'archive' => 'secondary'
                        ];
                        $statutColor = $statutColors[$dossier->statut] ?? 'info';
                    @endphp
                    <span class="badge badge-{{ $statutColor }}">
                        {{ ucfirst(str_replace('_', ' ', $dossier->statut)) }}
                    </span>
                </td>
            </tr>
            <tr>
                <td><strong>Dates</strong></td>
                <td>
                    Ouverture: {{ $dossier->date_ouverture->format('d/m/Y') }}<br>
                    Clôture prévue: {{ $dossier->date_cloture_prevue ? $dossier->date_cloture_prevue->format('d/m/Y') : 'Non définie' }}
                    @if($dossier->date_cloture_reelle)
                        <br>Clôture réelle: {{ $dossier->date_cloture_reelle->format('d/m/Y') }}
                    @endif
                </td>
            </tr>
            <tr>
                <td><strong>Période analysée</strong></td>
                <td>
                    {{ isset($request['date_debut']) && $request['date_debut'] ? date('d/m/Y', strtotime($request['date_debut'])) : 'Début' }}
                    au
                    {{ isset($request['date_fin']) && $request['date_fin'] ? date('d/m/Y', strtotime($request['date_fin'])) : 'Aujourd\'hui' }}
                </td>
            </tr>
            <tr>
                <td><strong>Budget</strong></td>
                <td>
                    @if($dossier->budget)
                        {{ number_format($dossier->budget, 2, ',', ' ') }} FCFA
                        @if($dossier->frais_dossier)
                            <br>Frais de dossier: {{ number_format($dossier->frais_dossier, 2, ',', ' ') }} FCFA
                        @endif
                    @else
                        Non défini
                    @endif
                </td>
            </tr>
            @if($dossier->description)
            <tr>
                <td><strong>Description</strong></td>
                <td>{{ $dossier->description }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <!-- 2. STATISTIQUES DE PERFORMANCE (en tableau) -->
    <h3 class="section-title">📊 Statistiques de Performance</h3>

    <table>
        <thead>
            <tr>
                <th style="width: 40%">Indicateur</th>
                <th style="width: 30%">Valeur</th>
                <th style="width: 30%">Analyse</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Personnels impliqués</strong></td>
                <td class="important-value">{{ $stats['total_personnels'] }} personnes</td>
                <td>
                    @if($stats['total_personnels'] == 0)
                        <span class="indicator indicator-low">⚠️ Aucun personnel</span>
                    @elseif($stats['total_personnels'] <= 2)
                        <span class="indicator indicator-low">🟢 Équipe légère</span>
                    @elseif($stats['total_personnels'] <= 5)
                        <span class="indicator indicator-medium">🟡 Équipe standard</span>
                    @else
                        <span class="indicator indicator-high">🔴 Équipe importante</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td><strong>Temps total consommé</strong></td>
                <td class="important-value">{{ number_format($stats['total_heures'], 2) }} heures</td>
                <td>
                    @if($stats['total_heures'] == 0)
                        <span class="indicator indicator-low">⚠️ Aucune heure</span>
                    @elseif($stats['total_heures'] <= 40)
                        <span class="indicator indicator-low">🟢 Charge légère</span>
                    @elseif($stats['total_heures'] <= 100)
                        <span class="indicator indicator-medium">🟡 Charge moyenne</span>
                    @else
                        <span class="indicator indicator-high">🔴 Charge importante</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td><strong>Heures théoriques allouées</strong></td>
                <td class="important-value">{{ number_format($stats['heure_theorique'], 2) }} heures</td>
                <td>
                    @if($stats['heure_theorique'] == 0)
                        <span class="indicator indicator-low">⚠️ Non définies</span>
                    @else
                        Estimation initiale
                    @endif
                </td>
            </tr>
            <tr>
                <td><strong>Surplus / Déficit</strong></td>
                <td class="important-value {{ $stats['surplus'] >= 0 ? 'surplus' : 'economie' }}">
                    {{ $stats['surplus'] >= 0 ? '+' : '' }}{{ number_format($stats['surplus'], 2) }} heures
                </td>
                <td>
                    @if($stats['surplus'] > 0)
                        <span class="indicator indicator-high">🔴 Dépassement</span>
                        ({{ $stats['surplus_pourcentage'] }}%)
                    @elseif($stats['surplus'] < 0)
                        <span class="indicator indicator-low">🟢 Économie</span>
                        ({{ abs($stats['surplus_pourcentage']) }}%)
                    @else
                        <span class="indicator indicator-low">🎯 Parfait</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td><strong>Moyenne par personnel</strong></td>
                <td class="important-value">{{ number_format($stats['moyenne_par_personnel'], 2) }} heures/pers</td>
                <td>
                    @if($stats['moyenne_par_personnel'] == 0)
                        <span class="indicator indicator-low">⚠️ Aucune répartition</span>
                    @elseif($stats['moyenne_par_personnel'] <= 20)
                        <span class="indicator indicator-low">🟢 Répartition légère</span>
                    @elseif($stats['moyenne_par_personnel'] <= 40)
                        <span class="indicator indicator-medium">🟡 Répartition moyenne</span>
                    @else
                        <span class="indicator indicator-high">🔴 Répartition élevée</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td><strong>Taux d'occupation moyen</strong></td>
                <td class="important-value">
                    @php
                        $tauxOccupation = $stats['moyenne_par_personnel'] > 0 ?
                            round(($stats['moyenne_par_personnel'] / 40) * 100, 1) : 0;
                    @endphp
                    {{ $tauxOccupation }}%
                </td>
                <td>
                    @if($tauxOccupation <= 50)
                        <span class="indicator indicator-low">🟢 Sous-utilisation</span>
                    @elseif($tauxOccupation <= 80)
                        <span class="indicator indicator-medium">🟡 Occupation optimale</span>
                    @elseif($tauxOccupation <= 100)
                        <span class="indicator indicator-high">🔴 Forte occupation</span>
                    @else
                        <span class="indicator indicator-high">🔴 Surcharge</span>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    <!-- 3. ANALYSE DU TEMPS (en tableau) -->
    <h3 class="section-title">⏱️ Analyse Comparative du Temps</h3>

    <table>
        <thead>
            <tr>
                <th style="width: 50%">Composante</th>
                <th style="width: 25%">Heures</th>
                <th style="width: 25%">Pourcentage</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Temps théorique prévisionnel</strong></td>
                <td class="important-value">{{ number_format($stats['heure_theorique'], 2) }}h</td>
                <td>100% (référence)</td>
            </tr>
            <tr>
                <td><strong>Temps réel consommé</strong></td>
                <td class="important-value">{{ number_format($stats['total_heures'], 2) }}h</td>
                <td>
                    @php
                        $pourcentageReel = $stats['heure_theorique'] > 0 ?
                            round(($stats['total_heures'] / $stats['heure_theorique']) * 100, 1) : 0;
                    @endphp
                    {{ $pourcentageReel }}%
                </td>
            </tr>
            <tr>
                <td><strong>Écart (Surplus/Déficit)</strong></td>
                <td class="important-value {{ $stats['surplus'] >= 0 ? 'surplus' : 'economie' }}">
                    {{ $stats['surplus'] >= 0 ? '+' : '' }}{{ number_format($stats['surplus'], 2) }}h
                </td>
                <td>{{ $stats['surplus_pourcentage'] }}%</td>
            </tr>
            <tr>
                <td><strong>Répartition par personnel</strong></td>
                <td colspan="2">
                    @if($stats['total_personnels'] > 0)
                        <strong>{{ number_format($stats['moyenne_par_personnel'], 2) }}h/pers</strong> en moyenne
                        @if($stats['total_personnels'] > 1)
                            ({{ number_format($stats['total_heures'], 2) }}h ÷ {{ $stats['total_personnels'] }} pers.)
                        @endif
                    @else
                        Aucune répartition disponible
                    @endif
                </td>
            </tr>
            <tr>
                <td><strong>Efficacité de l'estimation</strong></td>
                <td colspan="2">
                    @if($stats['heure_theorique'] > 0)
                        @if(abs($stats['surplus_pourcentage']) <= 5)
                            <span class="badge badge-success">🎯 Excellente précision (±5%)</span>
                        @elseif(abs($stats['surplus_pourcentage']) <= 15)
                            <span class="badge badge-warning">📊 Précision acceptable (±15%)</span>
                        @else
                            <span class="badge badge-danger">⚠️ Écart important (>15%)</span>
                        @endif
                        <br><small>Écart de {{ $stats['surplus_pourcentage'] }}% par rapport aux prévisions</small>
                    @else
                        <span class="badge badge-info">ℹ️ Aucune référence théorique</span>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Saut de page si nécessaire -->
    @if(count($personnels) > 10)
        <div class="page-break"></div>
    @endif

    <!-- 4. PERSONNELS AFFECTÉS À LA MISSION (en tableau) -->
    <h3 class="section-title">👥 Personnels Affectés à la Mission</h3>

    <table>
        <thead>
            <tr>
                <th style="width: 20%">Personnel</th>
                <th style="width: 15%">Poste</th>
                <th style="width: 15%">Heures mission</th>
                <th style="width: 20%">Charge totale</th>
                <th style="width: 10%">Autres missions</th>
                <th style="width: 20%">Statut de charge</th>
            </tr>
        </thead>
        <tbody>
            @foreach($personnels as $personnel)
                @php
                    // Déterminer le statut
                    $statutClasse = 'success';
                    $statutIcon = '🟢';
                    $statutText = 'Disponible';
                    $ecart = $personnel['charge_totale']['ecart'];
                    $tauxOccupation = $personnel['charge_totale']['heures_theoriques'] > 0 ?
                        round(($personnel['charge_totale']['heures_reelles'] / $personnel['charge_totale']['heures_theoriques']) * 100, 1) : 0;

                    if ($ecart > 10) {
                        $statutClasse = 'danger';
                        $statutIcon = '🔴';
                        $statutText = 'Surcharge';
                    } elseif ($ecart > 5) {
                        $statutClasse = 'warning';
                        $statutIcon = '🟠';
                        $statutText = 'Charge élevée';
                    }

                    $autresMissionsCount = count($personnel['autres_missions']);
                    $heuresReelles = $personnel['charge_totale']['heures_reelles'];
                    $heuresTheoriques = $personnel['charge_totale']['heures_theoriques'];
                @endphp

                <tr>
                    <td>
                        <strong>{{ $personnel['user']->full_name }}</strong><br>
                        <small style="font-size: 8pt;">{{ $personnel['user']->email }}</small>
                    </td>
                    <td>
                        {{ $personnel['user']->poste->intitule ?? 'Non défini' }}
                    </td>
                    <td style="text-align: center; font-weight: bold;">
                        {{ number_format($personnel['total_heures'], 2) }}h
                    </td>
                    <td>
                        <strong>{{ number_format($heuresReelles, 2) }}h</strong> /
                        {{ number_format($heuresTheoriques, 2) }}h
                        <br>
                        <small style="color: {{ $ecart >= 0 ? '#dc3545' : '#28a745' }}; font-size: 9pt;">
                            {{ $ecart >= 0 ? '+' : '' }}{{ number_format($ecart, 2) }}h
                            ({{ $tauxOccupation }}%)
                        </small>
                    </td>
                    <td style="text-align: center;">
                        @if($autresMissionsCount > 0)
                            <span class="badge badge-warning">
                                {{ $autresMissionsCount }}
                            </span>
                        @else
                            <span class="badge badge-success">0</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-{{ $statutClasse }}">
                            {{ $statutIcon }} {{ $statutText }}
                        </span>
                        <br>
                        <small style="font-size: 8pt;">
                            @if($tauxOccupation > 100)
                                Occupation: {{ $tauxOccupation }}%
                            @endif
                        </small>
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f8f9fa; font-weight: bold;">
                <td colspan="2"><strong>TOTAUX</strong></td>
                <td style="text-align: center;">
                    {{ number_format($stats['total_heures'], 2) }}h
                </td>
                <td colspan="3">
                    {{ $stats['total_personnels'] }} personnel(s) |
                    Moyenne: {{ number_format($stats['moyenne_par_personnel'], 2) }}h/pers
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- 5. RECOMMANDATIONS (en tableau) -->
    <h3 class="section-title">💡 Recommandations</h3>

    <table>
        <thead>
            <tr>
                <th style="width: 20%">Priorité</th>
                <th style="width: 30%">Domaine</th>
                <th style="width: 50%">Action recommandée</th>
            </tr>
        </thead>
        <tbody>
            @php
                $surchargeCount = 0;
                foreach($personnels as $p) {
                    if ($p['charge_totale']['ecart'] > 10) {
                        $surchargeCount++;
                    }
                }
            @endphp

            @if($stats['surplus'] > ($stats['heure_theorique'] * 0.2))
                <tr>
                    <td><span class="badge badge-danger">HAUTE</span></td>
                    <td><strong>Gestion des coûts</strong></td>
                    <td>
                        Dépassement de {{ number_format($stats['surplus_pourcentage'], 1) }}% détecté.
                        Recommandation: Réviser les méthodologies d'estimation et mettre en place
                        des contrôles budgétaires plus fréquents.
                    </td>
                </tr>
            @endif

            @if($surchargeCount > 0)
                <tr>
                    <td><span class="badge badge-danger">HAUTE</span></td>
                    <td><strong>Ressources humaines</strong></td>
                    <td>
                        {{ $surchargeCount }} personnel(s) en surcharge.
                        Recommandation: Redistribuer immédiatement la charge et organiser
                        des entretiens pour évaluer le bien-être.
                    </td>
                </tr>
            @endif

            @if($stats['total_personnels'] > 5)
                <tr>
                    <td><span class="badge badge-warning">MOYENNE</span></td>
                    <td><strong>Collaboration</strong></td>
                    <td>
                        {{ $stats['total_personnels'] }} personnels impliqués.
                        Recommandation: Organiser des points de coordination réguliers
                        et optimiser les outils de collaboration.
                    </td>
                </tr>
            @endif

            @if($stats['moyenne_par_personnel'] > 40)
                <tr>
                    <td><span class="badge badge-warning">MOYENNE</span></td>
                    <td><strong>Charge de travail</strong></td>
                    <td>
                        Charge moyenne de {{ number_format($stats['moyenne_par_personnel'], 1) }}h/pers.
                        Recommandation: Étudier le renforcement de l'équipe ou la délégation de tâches.
                    </td>
                </tr>
            @endif

            <tr>
                <td><span class="badge badge-info">STANDARD</span></td>
                <td><strong>Amélioration continue</strong></td>
                <td>
                    Organiser une réunion de retour d'expérience pour capitaliser sur les
                    apprentissages de cette mission et améliorer les processus futurs.
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Pied de page -->
    <div class="footer">
        <div style="font-weight: bold; color: #002060; margin-bottom: 10px;">
            COFTIME - Système de Gestion des Temps et Missions
        </div>
        <div class="confidential">
            DOCUMENT CONFIDENTIEL - USAGE INTERNE UNIQUEMENT
        </div>
        <div style="margin-top: 10px;">
            Page 1/1 | Généré le {{ $date_export }}
        </div>
    </div>

</body>
</html>
