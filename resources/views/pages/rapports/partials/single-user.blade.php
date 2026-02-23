@php
use App\Helpers\UserHelper;
@endphp

<div class="card mb-4 shadow-sm border-0">
    <div class="card-header bg-gradient-primary text-white py-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1 font-weight-bold">
                    <i class="fas fa-user-circle mr-2"></i>{{ $user->nom . ' ' . $user->prenom ?? $user->prenom }}
                </h5>
                <small class="text-white-50">
                    @if($entries->count() > 0)
                        Période du {{ $entries->first()->jour->format('d/m/Y') }} au
                        {{ $entries->last()->jour->format('d/m/Y') }}
                    @else
                        Aucune donnée pour cette période
                    @endif
                </small>
            </div>
            <div class="text-right">
                @if($entries->count() > 0)
                    <div class="mb-1">
                        <span class="badge badge-light badge-lg px-3 py-2">
                            <i class="fas fa-clock mr-1"></i>
                            Total : <strong>{{ UserHelper::hoursToHoursMinutes($entries->sum('heures_reelles')) }}</strong>
                        </span>
                    </div>
                    <div>
                        <span class="badge badge-light-subtle px-3 py-2">
                            Théorique : {{ UserHelper::hoursToHoursMinutes($entries->sum('heures_theoriques')) }}
                        </span>
                    </div>
                @else
                    <span class="badge badge-warning badge-lg px-3 py-2">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Aucune activité
                    </span>
                @endif
            </div>
        </div>
    </div>

    @if($entries->count() > 0)
        <div class="card-body p-0">
            <!-- Statistiques rapides -->
            <div class="row text-center py-3 bg-light border-bottom">
                @php
                    $totalTheorique = $entries->sum('heures_theoriques');
                    $totalReel = $entries->sum('heures_reelles');
                    $ecartTotal = $totalReel - $totalTheorique;
                    $tauxRealisation = $totalTheorique > 0 ? round(($totalReel / $totalTheorique) * 100, 1) : 0;
                    $joursTravailles = $entries->filter(fn($e) => $e->heures_reelles > 0)->count();
                @endphp

                <div class="col-md-3 border-right">
                    <div class="stat-item">
                        <i class="fas fa-calendar-check text-primary fa-2x mb-2"></i>
                        <h4 class="font-weight-bold mb-0">{{ $joursTravailles }}</h4>
                        <small class="text-muted">Jours travaillés</small>
                    </div>
                </div>

                <div class="col-md-3 border-right">
                    <div class="stat-item">
                        <i class="fas fa-chart-line text-info fa-2x mb-2"></i>
                        <h4 class="font-weight-bold mb-0">{{ $tauxRealisation }}%</h4>
                        <small class="text-muted">Taux de réalisation</small>
                    </div>
                </div>

                <div class="col-md-3 border-right">
                    <div class="stat-item">
                        <i class="fas fa-hourglass-half text-warning fa-2x mb-2"></i>
                        <h4 class="font-weight-bold mb-0">{{ UserHelper::hoursToHoursMinutes($totalTheorique) }}</h4>
                        <small class="text-muted">Heures théoriques</small>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="stat-item">
                        <i class="fas fa-{{ $ecartTotal >= 0 ? 'arrow-up text-success' : 'arrow-down text-danger' }} fa-2x mb-2"></i>
                        <h4 class="font-weight-bold mb-0 {{ $ecartTotal >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $ecartTotal >= 0 ? '+' : '' }}{{ UserHelper::hoursToHoursMinutes($ecartTotal) }}
                        </h4>
                        <small class="text-muted">Écart total</small>
                    </div>
                </div>
            </div>

            <div class="table-responsive p-3">
                <table class="table table-hover rapport-table align-middle rapport-datatable"
                    data-user-id="{{ $user->id }}">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 100px;"><i class="fas fa-calendar-alt mr-1"></i>Date</th>
                            <th class="text-center" style="width: 90px;"><i class="fas fa-hourglass-start mr-1"></i>Théorique</th>
                            <th class="text-center" style="width: 90px;"><i class="fas fa-clock mr-1"></i>Réel</th>
                            <th class="text-center" style="width: 90px;"><i class="fas fa-balance-scale mr-1"></i>Écart</th>
                            <th><i class="fas fa-folder-open mr-1"></i>Dossier / Client</th>
                            <th class="text-center" style="width: 90px;"><i class="fas fa-stopwatch mr-1"></i>Heures</th>
                            <th><i class="fas fa-tasks mr-1"></i>Travaux effectués</th>
                            <th><i class="fas fa-tasks mr-1">Rendus</i></th>                        </tr>
                    </thead>
                    <tbody>
                        @foreach($entries as $entry)
                            @php
                                $entryEcart = $entry->heures_reelles - $entry->heures_theoriques;
                                $rowspan = $entry->timeEntries->count() > 0 ? $entry->timeEntries->count() : 1;
                            @endphp

                            @if($entry->timeEntries->isEmpty())
                                <tr class="table-warning">
                                    <td class="text-center font-weight-bold">
                                        <span class="badge badge-warning">{{ $entry->jour->format('d/m/Y') }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-light">{{ UserHelper::hoursToHoursMinutes($entry->heures_theoriques) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-secondary">0h 0min</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-danger">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            -{{ UserHelper::hoursToHoursMinutes($entry->heures_theoriques) }}
                                        </span>
                                    </td>
                                    <td colspan="3" class="text-muted font-italic">
                                        <i class="fas fa-info-circle mr-1"></i>Aucune activité saisie pour cette journée
                                    </td>
                                </tr>
                            @else
                                @foreach($entry->timeEntries as $index => $te)
                                    <tr>
                                        @if($index === 0)
                                            <td class="text-center font-weight-bold align-middle" rowspan="{{ $rowspan }}">
                                                <div class="date-badge">
                                                    <div class="date-day">{{ $entry->jour->format('d') }}</div>
                                                    <div class="date-month">{{ $entry->jour->translatedFormat('M') }}</div>
                                                    <div class="date-year">{{ $entry->jour->format('Y') }}</div>
                                                </div>
                                            </td>
                                            <td class="text-center align-middle" rowspan="{{ $rowspan }}">
                                                <span class="badge badge-light badge-lg">{{ UserHelper::hoursToHoursMinutes($entry->heures_theoriques) }}</span>
                                            </td>
                                            <td class="text-center align-middle" rowspan="{{ $rowspan }}">
                                                <span class="badge badge-primary badge-lg">{{ UserHelper::hoursToHoursMinutes($entry->heures_reelles) }}</span>
                                            </td>
                                            <td class="text-center align-middle" rowspan="{{ $rowspan }}">
                                                <span class="badge badge-{{ $entryEcart >= 0 ? 'success' : 'danger' }} badge-lg">
                                                    <i class="fas fa-{{ $entryEcart >= 0 ? 'arrow-up' : 'arrow-down' }} mr-1"></i>
                                                    {{ $entryEcart >= 0 ? '+' : '' }}{{ UserHelper::hoursToHoursMinutes($entryEcart) }}
                                                </span>
                                            </td>
                                        @endif

                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="dossier-icon mr-2">
                                                    <i class="fas fa-folder text-primary"></i>
                                                </div>
                                                <div>
                                                    <div class="font-weight-bold">{{ $te->dossier->nom ?? '-' }}</div>
                                                    <small class="text-muted">
                                                        <i class="fas fa-building mr-1"></i>
                                                        {{ $te->dossier->client->nom ?? 'Sans client' }}
                                                    </small>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="text-center">
                                            <span class="badge badge-info badge-lg">
                                                <i class="fas fa-clock mr-1"></i>
                                                {{ UserHelper::hoursToHoursMinutes($te->heures_reelles) }}
                                            </span>
                                        </td>

                                        <td>
                                            <div class="travaux-text">{{ $te->travaux ?? '-' }}</div>
                                        </td>

                                        <td>
                                            <div class="travaux-text">{{ $te->rendu ?? '-' }}</div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot class="bg-light font-weight-bold">
                        <tr>
                            <td colspan="4" class="text-right">TOTAUX :</td>
                            <td class="text-center">
                                <span class="badge badge-secondary badge-lg">{{ $entries->flatMap->timeEntries->count() }} activités</span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-primary badge-lg">{{ UserHelper::hoursToHoursMinutes($totalReel) }}</span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $ecartTotal >= 0 ? 'success' : 'danger' }} badge-lg">
                                    Écart: {{ $ecartTotal >= 0 ? '+' : '' }}{{ UserHelper::hoursToHoursMinutes($ecartTotal) }}
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @else
        <div class="card-body text-center py-5">
            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">Aucune activité enregistrée pour cette période</h5>
            <p class="text-muted">L'utilisateur n'a pas saisi de feuille de temps pour le mois sélectionné.</p>
        </div>
    @endif
</div>

<style>
    /* Styles DataTables */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        padding: 1rem;
        background: #f8f9fc;
        border-bottom: 1px solid #e3e6f0;
    }

    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #e3e6f0;
        border-radius: 0.35rem;
        padding: 0.375rem 0.75rem;
        background-color: white;
    }

    .dataTables_wrapper .dataTables_paginate {
        margin-top: 1rem;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border: 1px solid #e3e6f0 !important;
        border-radius: 0.35rem !important;
        margin: 0 2px !important;
        padding: 0.375rem 0.75rem !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        border-color: #667eea !important;
        color: white !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #667eea !important;
        color: white !important;
        border-color: #667eea !important;
    }

    .dataTables_wrapper .dataTables_info {
        padding: 1rem;
        font-size: 0.875rem;
        color: #858796;
    }

    /* Masquer la colonne d'écart dans les lignes vides pour DataTables */
    .table-warning td[colspan] {
        white-space: nowrap;
    }

    .bg-gradient-primary {
        background: linear-gradient(135deg, #244584 0%, #764ba2 100%);
    }

    .badge-light-subtle {
        background-color: rgba(255, 255, 255, 0.2);
        color: white;
    }

    .badge-lg {
        padding: 0.5rem 0.75rem;
        font-size: 0.9rem;
    }

    .stat-item {
        padding: 0.5rem;
    }

    .stat-item h4 {
        font-size: 1.8rem;
        margin: 0.5rem 0;
    }

    .date-badge {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 8px;
        padding: 8px;
        min-width: 60px;
        text-align: center;
    }

    .date-day {
        font-size: 1.5rem;
        font-weight: bold;
        line-height: 1;
    }

    .date-month {
        font-size: 0.75rem;
        text-transform: uppercase;
        opacity: 0.9;
    }

    .date-year {
        font-size: 0.7rem;
        opacity: 0.8;
    }

    .dossier-icon {
        width: 35px;
        height: 35px;
        background: #f0f3ff;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .travaux-text {
        max-width: 400px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .rapport-table thead th {
        background: #f8f9fc;
        border-bottom: 2px solid #e3e6f0;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        padding: 1rem 0.75rem;
        vertical-align: middle;
    }

    .rapport-table tbody tr {
        transition: all 0.3s ease;
    }

    .rapport-table tbody tr:hover {
        background-color: #f8f9fc;
        transform: scale(1.01);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .rapport-table td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
        border-bottom: 1px solid #f0f0f0;
    }

    .table-warning {
        background-color: #fff3cd !important;
    }

    .table-warning:hover {
        background-color: #ffe69c !important;
    }

    /* DataTables personnalisé */
    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #e3e6f0;
        border-radius: 0.35rem;
        padding: 0.375rem 0.75rem;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        border-color: #667eea !important;
        color: white !important;
    }

    .dataTables_wrapper .dataTables_info {
        padding-top: 1rem;
        font-size: 0.875rem;
        color: #858796;
    }
</style>

<script>
    $(document).ready(function () {
        // Configuration simplifiée
        $('.rapport-datatable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json'
            },
            paging: true,
            searching: true,
            ordering: true,
            info: true,
            autoWidth: false,
            responsive: true,
            destroy: true, // Permet de réinitialiser si le tableau est rechargé
            pageLength: 25,
            order: [[0, 'desc']],
            columnDefs: [
                {
                    // Désactiver le tri sur certaines colonnes
                    targets: [6],
                    orderable: false
                }
            ],
            // Désactiver le tri automatique pour certaines colonnes
            "columns": [
                null, // Date - tri activé
                null, // Théorique
                null, // Réel
                null, // Écart
                { "orderable": false }, // Dossier/Client
                null, // Heures
                { "orderable": false } // Travaux effectués
            ]
        });
    });
</script>
