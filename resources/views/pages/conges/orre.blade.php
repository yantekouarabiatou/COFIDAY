@extends('layaout')

@section('title', 'Calendrier des Congés')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-calendar-alt"></i> Calendrier des Congés</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('conges.index') }}">Congés</a></div>
            <div class="breadcrumb-item active">Calendrier</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Calendrier des congés</h4>
                        <div class="card-header-action d-flex align-items-center">
                            <!-- Navigation mois -->
                            <div class="btn-group mr-3">
                                <button type="button" class="btn btn-primary" id="prev-month">
                                    <i class="fas fa-chevron-left"></i> Mois précédent
                                </button>
                                <button type="button" class="btn btn-primary" id="current-month">
                                    <i class="fas fa-calendar-day"></i> Ce mois
                                </button>
                                <button type="button" class="btn btn-primary" id="next-month">
                                    Mois suivant <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>

                            <!-- Dropdown année -->
                            <div class="dropdown mr-3">
                                <button class="btn btn-secondary dropdown-toggle" type="button" id="yearDropdown"
                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-calendar"></i> {{ date('Y') }}
                                </button>
                                <div class="dropdown-menu" aria-labelledby="yearDropdown">
                                    @for($year = date('Y') - 2; $year <= date('Y') + 2; $year++)
                                        <a class="dropdown-item year-select" href="#" data-year="{{ $year }}">
                                            {{ $year }}
                                        </a>
                                    @endfor
                                </div>
                            </div>

                            <!-- Filtre plage de dates avec Flatpickr -->
                            <div class="mr-3">
                                <input type="text" id="date-range-filter" class="btn btn-outline-info" placeholder="Sélectionner une période" readonly>
                            </div>

                            <!-- Dropdown filtres -->
                            <div class="dropdown">
                                <button class="btn btn-info dropdown-toggle" type="button" id="filterDropdown"
                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-filter"></i> Filtres
                                </button>
                                <div class="dropdown-menu" aria-labelledby="filterDropdown">
                                    <div class="dropdown-header">Filtrer par type</div>
                                    @foreach($typesConges as $type)
                                    <div class="dropdown-item">
                                        <div class="form-check">
                                            <input class="form-check-input type-filter"
                                                   type="checkbox"
                                                   value="{{ $type->id }}"
                                                   id="type-{{ $type->id }}"
                                                   checked
                                                   data-color="{{ $type->couleur ?? '#3B82F6' }}">
                                            <label class="form-check-label" for="type-{{ $type->id }}">
                                                <span class="badge" style="background-color: {{ $type->couleur ?? '#3B82F6' }}; color: white; padding: 2px 8px;">
                                                    {{ $type->libelle }}
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                    <div class="dropdown-divider"></div>
                                    <div class="dropdown-header">Filtrer par statut</div>
                                    <div class="dropdown-item">
                                        <div class="form-check">
                                            <input class="form-check-input status-filter"
                                                   type="checkbox"
                                                   value="approuve"
                                                   id="status-approuve"
                                                   checked>
                                            <label class="form-check-label" for="status-approuve">
                                                <span class="badge badge-success">Approuvés</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="dropdown-item">
                                        <div class="form-check">
                                            <input class="form-check-input status-filter"
                                                   type="checkbox"
                                                   value="en_attente"
                                                   id="status-en_attente">
                                            <label class="form-check-label" for="status-en_attente">
                                                <span class="badge badge-warning">En attente</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="dropdown-divider"></div>
                                    <button class="dropdown-item" type="button" id="reset-filters">
                                        <i class="fas fa-redo"></i> Réinitialiser les filtres
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Calendrier FullCalendar -->
                        <div id="calendar"></div>

                        <!-- Légende -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4><i class="fas fa-key"></i> Légende</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            @foreach($typesConges as $type)
                                            <div class="col-md-2 mb-2">
                                                <div class="d-flex align-items-center">
                                                    <div class="legend-color" style="background-color: {{ $type->couleur ?? '#3B82F6' }};"></div>
                                                    <span class="ml-2">{{ $type->libelle }}</span>
                                                </div>
                                            </div>
                                            @endforeach
                                            <div class="col-md-2 mb-2">
                                                <div class="d-flex align-items-center">
                                                    <div class="legend-color" style="background-color: #28a745;"></div>
                                                    <span class="ml-2">Approuvé</span>
                                                </div>
                                            </div>
                                            <div class="col-md-2 mb-2">
                                                <div class="d-flex align-items-center">
                                                    <div class="legend-color" style="background-color: #ffc107;"></div>
                                                    <span class="ml-2">En attente</span>
                                                </div>
                                            </div>
                                            <div class="col-md-2 mb-2">
                                                <div class="d-flex align-items-center">
                                                    <div class="legend-color" style="background-color: #6c757d; border: 1px solid #dee2e6;"></div>
                                                    <span class="ml-2">Weekend</span>
                                                </div>
                                            </div>
                                            <div class="col-md-2 mb-2">
                                                <div class="d-flex align-items-center">
                                                    <div class="legend-color" style="background-color: #dc3545;"></div>
                                                    <span class="ml-2">Aujourd'hui</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Liste des congés -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4><i class="fas fa-list"></i> Congés du mois</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped" id="conges-list">
                                                <thead>
                                                    <tr>
                                                        <th>Employé</th>
                                                        <th>Type</th>
                                                        <th>Période</th>
                                                        <th>Durée</th>
                                                        <th>Statut</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="conges-list-body">
                                                    <!-- Rempli par JS -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal détails congé -->
<div class="modal fade" id="congeModal" tabindex="-1" role="dialog" aria-labelledby="congeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="congeModalLabel">Détails du congé</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="congeModalBody">
                <!-- Chargé par JS -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                <a href="#" class="btn btn-primary" id="viewDetailsBtn">Voir détails complets</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css"> <!-- Pour le datepicker -->
<style>
    /* Tes styles existants + ajustements pour FullCalendar */
    #calendar {
        height: 600px;
        margin-bottom: 20px;
    }
    .fc-event {
        cursor: pointer;
    }
    .legend-color {
        width: 20px;
        height: 20px;
        border-radius: 4px;
    }
    /* Styles pour l'agenda */
    .agenda-container {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .agenda-day {
        border: 1px solid #e3e6f0;
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.3s ease;
        background-color: white;
    }

    .agenda-day:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .agenda-day.today {
        border-left: 4px solid #dc3545;
    }

    .agenda-day.weekend {
        background-color: #f9f9f9;
    }

    .agenda-day-header {
        background-color: #f8f9fa;
        padding: 15px 20px;
        border-bottom: 1px solid #e3e6f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .agenda-date {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .day-name {
        font-weight: bold;
        font-size: 1.1em;
        color: #495057;
        min-width: 100px;
    }

    .day-number {
        background-color: #3B82F6;
        color: white;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.2em;
    }

    .month-name {
        color: #6c757d;
        font-weight: 500;
    }

    .year {
        color: #adb5bd;
        font-size: 0.9em;
    }

    .today-badge {
        background-color: #dc3545;
        color: white;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.8em;
        font-weight: bold;
    }

    .agenda-day-stats .badge {
        font-size: 0.9em;
        padding: 6px 12px;
    }

    .agenda-conges-list {
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .agenda-empty-state {
        padding: 40px 20px;
        text-align: center;
        color: #6c757d;
    }

    .empty-state-content i {
        font-size: 3em;
        margin-bottom: 15px;
        opacity: 0.5;
    }

    .empty-state-content p {
        font-size: 1.1em;
        margin: 0;
    }

    .agenda-conge-item {
        border: 1px solid #e3e6f0;
        border-radius: 6px;
        overflow: hidden;
        display: flex;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .agenda-conge-item:hover {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .agenda-conge-item.pending {
        opacity: 0.8;
        border-style: dashed;
    }

    .conge-type-bar {
        width: 6px;
        flex-shrink: 0;
    }

    .conge-content {
        flex: 1;
        padding: 15px;
    }

    .conge-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 15px;
    }

    .conge-user {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #3B82F6;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.1em;
        flex-shrink: 0;
    }

    .user-info {
        flex: 1;
    }

    .user-name {
        font-weight: 600;
        margin: 0;
        color: #343a40;
        font-size: 1em;
    }

    .conge-type {
        font-size: 0.85em;
        font-weight: 500;
        margin-top: 2px;
        display: block;
    }

    .conge-status .badge {
        font-size: 0.8em;
        padding: 4px 8px;
    }

    .conge-details {
        margin-bottom: 15px;
    }

    .detail-item {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
        font-size: 0.9em;
        color: #6c757d;
    }

    .detail-item i {
        width: 16px;
        text-align: center;
        color: #adb5bd;
    }

    .conge-motif {
        font-style: italic;
        color: #495057;
        background-color: #f8f9fa;
        padding: 8px 12px;
        border-radius: 4px;
        display: block;
        margin-top: 5px;
        font-size: 0.9em;
        line-height: 1.4;
    }

    .conge-actions {
        display: flex;
        justify-content: flex-end;
    }

    .conge-actions .btn {
        font-size: 0.85em;
        padding: 4px 12px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .agenda-day-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }

        .agenda-date {
            flex-wrap: wrap;
            gap: 8px;
        }

        .day-name {
            min-width: auto;
        }

        .conge-header {
            flex-direction: column;
            gap: 10px;
        }

        .conge-status {
            align-self: flex-start;
        }

        .agenda-conge-item {
            flex-direction: column;
        }

        .conge-type-bar {
            width: 100%;
            height: 4px;
        }
    }

    /* Animation de chargement */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .agenda-day {
        animation: fadeIn 0.3s ease-out;
    }

    .agenda-conge-item {
        animation: fadeIn 0.4s ease-out;
    }

    /* Styles pour le sélecteur d'année */
    .year-selector {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
    }

    .year-selector select {
        width: 120px;
    }

    /* Styles pour les statistiques */
    .month-statistics {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border: 1px solid #e3e6f0;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .stat-card i {
        font-size: 2em;
        margin-bottom: 10px;
        color: #3B82F6;
    }

    .stat-card h4 {
        font-size: 2em;
        font-weight: bold;
        margin: 10px 0;
        color: #343a40;
    }

    .stat-card p {
        color: #6c757d;
        margin: 0;
        font-size: 0.9em;
    }

</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script> <!-- Localisation FR -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Données congés (identique)
    const congesData = {!! $conges->map(function($conge) {
        return [
            'id' => $conge->id,
            'user_id' => $conge->user_id,
            'user_name' => ($conge->user ? $conge->user->prenom . ' ' . $conge->user->nom : 'Utilisateur inconnu'),
            'type_id' => $conge->type_conge_id,
            'type_name' => ($conge->typeConge ? $conge->typeConge->libelle : 'Type inconnu'),
            'type_color' => ($conge->typeConge ? $conge->typeConge->couleur : '#3B82F6'),
            'date_debut' => $conge->date_debut ? \Carbon\Carbon::parse($conge->date_debut)->format('Y-m-d') : null,
            'date_fin' => $conge->date_fin ? \Carbon\Carbon::parse($conge->date_fin)->format('Y-m-d') : null,
            'statut' => $conge->statut,
            'nombre_jours' => $conge->nombre_jours,
            'motif' => $conge->motif,
            'show_url' => route('conges.show', $conge->id)
        ];
    })->toJson() !!};

    let selectedFilters = {
        types: {!! $typesConges->pluck('id')->toJson() !!},
        statuses: ['approuve']
    };

    let dateRange = null; // Pour filtre période

    // Initialiser FullCalendar
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'fr',
        headerToolbar: false, // On gère nous-mêmes
        events: function(fetchInfo, successCallback, failureCallback) {
            successCallback(getFilteredEvents());
        },
        eventClick: function(info) {
            showCongeDetails(info.event.extendedProps.congeId);
        },
        dateClick: function(info) {
            // Optionnel : zoom sur jour
        }
    });
    calendar.render();

    // Initialiser Flatpickr pour filtre période
    flatpickr('#date-range-filter', {
        mode: 'range',
        dateFormat: 'd/m/Y',
        locale: 'fr',
        onChange: function(selectedDates) {
            if (selectedDates.length === 2) {
                dateRange = selectedDates;
                updateCalendar();
            }
        }
    });

    // Fonctions...
    function getFilteredEvents() {
        return congesData
            .filter(conge => shouldShowConge(conge))
            .map(conge => ({
                title: `${conge.user_name} - ${conge.type_name}`,
                start: conge.date_debut,
                end: conge.date_fin ? new Date(conge.date_fin).setDate(new Date(conge.date_fin).getDate() + 1) : null, // All-day
                backgroundColor: conge.type_color,
                borderColor: conge.type_color,
                extendedProps: { congeId: conge.id }
            }));
    }

    function updateCalendar() {
        if (dateRange) {
            calendar.gotoDate(dateRange[0]);
            calendar.setOption('validRange', {
                start: dateRange[0],
                end: dateRange[1]
            });
        }
        calendar.refetchEvents();
        updateStats();
        updateCongesList();
    }

    // Mise à jour liste congés (en bas)
    function updateCongesList() {
        const body = document.getElementById('conges-list-body');
        body.innerHTML = '';
        getFilteredEvents().forEach(event => {
            const conge = congesData.find(c => c.id == event.extendedProps.congeId);
            if (conge) {
                const row = `<tr>
                    <td>${conge.user_name}</td>
                    <td><span class="badge" style="background-color: ${conge.type_color}">${conge.type_name}</span></td>
                    <td>${formatDate(conge.date_debut)} au ${formatDate(conge.date_fin)}</td>
                    <td>${conge.nombre_jours} jours</td>
                    <td>${conge.statut === 'approuve' ? '<span class="badge badge-success">Approuvé</span>' : '<span class="badge badge-warning">En attente</span>'}</td>
                    <td><a href="${conge.show_url}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a></td>
                </tr>`;
                body.innerHTML += row;
            }
        });
    }

    // ... tes autres fonctions (showCongeDetails, formatDate, prevMonth, nextMonth, etc.) restent similaires ...

    // Événements pour navigation
    document.getElementById('prev-month').addEventListener('click', () => { calendar.prev(); updateCalendar(); });
    document.getElementById('next-month').addEventListener('click', () => { calendar.next(); updateCalendar(); });
    document.getElementById('current-month').addEventListener('click', () => { calendar.today(); updateCalendar(); });

    // Filtres (types, statuses, reset) : appellent updateCalendar()

    // Init
    updateCalendar();
});
</script>
@endpush
