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
                    <div class="card-header">
                        <h4>Calendrier des congés</h4>
                        <div class="card-header-action">
                            <div class="btn-group">
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
                            <div class="dropdown d-inline ml-2">
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
                        <!-- Contrôles du calendrier -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h3 id="current-month-year" class="text-primary"></h3>
                                <p class="text-muted" id="current-month-info"></p>
                            </div>
                            <div class="col-md-6 text-right">
                                <div class="form-group mb-0">
                                    <label for="view-mode" class="mr-2">Affichage :</label>
                                    <select id="view-mode" class="form-control d-inline-block" style="width: auto;">
                                        <option value="month">Mensuel</option>
                                        <option value="week">Hebdomadaire</option>
                                        <option value="day">Quotidien</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Statistiques du mois -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-primary">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Congés ce mois</h4>
                                        </div>
                                        <div class="card-body" id="stats-conges-mois">
                                            0
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-success">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Personnes absentes</h4>
                                        </div>
                                        <div class="card-body" id="stats-absents">
                                            0
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-warning">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>En attente</h4>
                                        </div>
                                        <div class="card-body" id="stats-en-attente">
                                            0
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-info">
                                        <i class="fas fa-calendar-check"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Jours avec congés</h4>
                                        </div>
                                        <div class="card-body" id="stats-jours-conges">
                                            0
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Calendrier -->
                        <div class="calendar-container">
                            <!-- En-têtes des jours -->
                            <div class="calendar-header">
                                <div class="calendar-cell calendar-day-header">Lun</div>
                                <div class="calendar-cell calendar-day-header">Mar</div>
                                <div class="calendar-cell calendar-day-header">Mer</div>
                                <div class="calendar-cell calendar-day-header">Jeu</div>
                                <div class="calendar-cell calendar-day-header">Ven</div>
                                <div class="calendar-cell calendar-day-header">Sam</div>
                                <div class="calendar-cell calendar-day-header">Dim</div>
                            </div>

                            <!-- Grille du calendrier -->
                            <div class="calendar-grid" id="calendar-grid">
                                <!-- Le calendrier sera généré par JavaScript -->
                            </div>
                        </div>

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

                        <!-- Liste des congés du mois -->
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
                                                    <!-- Rempli par JavaScript -->
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

<!-- Modal pour les détails du congé -->
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
                <!-- Les détails seront chargés ici -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                <a href="#" class="btn btn-primary" id="viewDetailsBtn">Voir les détails complets</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
<style>
    /* Styles pour le calendrier personnalisé */
    .calendar-container {
        border: 1px solid #e3e6f0;
        border-radius: 5px;
        overflow: hidden;
        margin-bottom: 20px;
    }

    .calendar-header {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        background-color: #f8f9fa;
        border-bottom: 1px solid #e3e6f0;
    }

    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        grid-auto-rows: 120px;
    }

    .calendar-cell {
        border-right: 1px solid #e3e6f0;
        border-bottom: 1px solid #e3e6f0;
        padding: 5px;
        position: relative;
        min-height: 120px;
    }

    .calendar-cell:nth-child(7n) {
        border-right: none;
    }

    .calendar-day-header {
        text-align: center;
        font-weight: bold;
        padding: 10px 5px;
        color: #495057;
        background-color: #f1f3f4;
    }

    .calendar-day {
        position: relative;
    }

    .calendar-day-number {
        position: absolute;
        top: 5px;
        right: 5px;
        font-weight: bold;
        font-size: 1.1em;
        color: #495057;
    }

    .calendar-day.other-month .calendar-day-number {
        color: #adb5bd;
    }

    .calendar-day.today .calendar-day-number {
        background-color: #dc3545;
        color: white;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        right: 5px;
        top: 5px;
    }

    .calendar-day.weekend {
        background-color: #f8f9fa;
    }

    .calendar-event {
        margin: 2px 0;
        padding: 3px 6px;
        border-radius: 3px;
        font-size: 0.85em;
        color: white;
        cursor: pointer;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        transition: all 0.2s;
        position: relative;
        border-left: 3px solid rgba(0,0,0,0.2);
    }

    .calendar-event:hover {
        transform: translateX(2px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .calendar-event.pending {
        opacity: 0.7;
        border-left: 3px dashed rgba(255,255,255,0.5);
    }

    .event-user {
        font-weight: bold;
        font-size: 0.9em;
    }

    .event-type {
        font-size: 0.8em;
        opacity: 0.9;
    }

    .legend-color {
        width: 20px;
        height: 20px;
        border-radius: 3px;
        display: inline-block;
    }

    /* Tooltip personnalisé */
    .calendar-event-tooltip {
        position: absolute;
        background: white;
        border: 1px solid #ddd;
        padding: 10px;
        border-radius: 5px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        z-index: 1000;
        min-width: 200px;
        display: none;
    }

    /* Scroll pour les événements */
    .calendar-events-container {
        margin-top: 30px;
        max-height: calc(100% - 30px);
        overflow-y: auto;
        padding-right: 5px;
    }

    .calendar-events-container::-webkit-scrollbar {
        width: 4px;
    }

    .calendar-events-container::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .calendar-events-container::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 2px;
    }

    /* Styles pour la vue semaine */
    .calendar-week-view .calendar-grid {
        grid-template-columns: 100px repeat(7, 1fr);
        grid-auto-rows: 60px;
    }

    .calendar-week-view .calendar-header {
        grid-template-columns: 100px repeat(7, 1fr);
    }

    .time-slot {
        border-right: 1px solid #e3e6f0;
        border-bottom: 1px solid #e3e6f0;
        padding: 5px;
        text-align: center;
        background-color: #f8f9fa;
        font-weight: bold;
    }

    /* Styles pour la vue jour */
    .calendar-day-view .calendar-grid {
        grid-template-columns: 100px 1fr;
        grid-auto-rows: 60px;
    }

    .calendar-day-view .calendar-header {
        grid-template-columns: 100px 1fr;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .calendar-grid {
            grid-auto-rows: 100px;
        }

        .calendar-event {
            font-size: 0.7em;
            padding: 2px 4px;
        }

        .calendar-day-number {
            font-size: 0.9em;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/fr.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentDate = new Date();
    let currentMonth = currentDate.getMonth();
    let currentYear = currentDate.getFullYear();
    let selectedFilters = {
        types: @json($typesConges->pluck('id')->toArray()),
        statuses: ['approuve']
    };

    // Données des congés depuis le contrôleur
    const congesData = @json($conges->map(function($conge) {
        return [
            'id' => $conge->id,
            'user_id' => $conge->user_id,
            'user_name' => optional($conge->user)->prenom . ' ' . optional($conge->user)->nom,
            'type_id' => $conge->type_conge_id,
            'type_name' => optional($conge->typeConge)->libelle ?? 'Type inconnu',
            'type_color' => optional($conge->typeConge)->couleur ?? '#3B82F6',
            'date_debut' => $conge->date_debut ? \Carbon\Carbon::parse($conge->date_debut)->format('Y-m-d') : null,
            'date_fin' => $conge->date_fin ? \Carbon\Carbon::parse($conge->date_fin)->format('Y-m-d') : null,
            'statut' => $conge->statut,
            'nombre_jours' => $conge->nombre_jours,
            'motif' => $conge->motif,
            'show_url' => route('conges.show', $conge->id)
        ];
    }));

    // Types de congés
    const typesConges = @json($typesConges->mapWithKeys(function($type) {
        return [$type->id => [
            'libelle' => $type->libelle,
            'couleur' => $type->couleur ?? '#3B82F6',
            'est_paye' => $type->est_paye
        ]];
    }));

    // Initialisation
    initCalendar();
    updateStats();
    updateCongesList();

    // Événements
    document.getElementById('prev-month').addEventListener('click', prevMonth);
    document.getElementById('next-month').addEventListener('click', nextMonth);
    document.getElementById('current-month').addEventListener('click', goToCurrentMonth);
    document.getElementById('view-mode').addEventListener('change', changeViewMode);
    document.getElementById('reset-filters').addEventListener('click', resetFilters);

    // Filtres
    document.querySelectorAll('.type-filter').forEach(filter => {
        filter.addEventListener('change', updateTypeFilters);
    });

    document.querySelectorAll('.status-filter').forEach(filter => {
        filter.addEventListener('change', updateStatusFilters);
    });

    // Fonctions principales
    function initCalendar() {
        renderCalendar(currentMonth, currentYear);
        updateMonthYearDisplay();
    }

    function renderCalendar(month, year) {
        const calendarGrid = document.getElementById('calendar-grid');
        calendarGrid.innerHTML = '';

        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const startingDay = firstDay.getDay();
        const monthLength = lastDay.getDate();

        // Ajuster le premier jour pour commencer à lundi (1 au lieu de 0 pour dimanche)
        let day = (startingDay === 0) ? 6 : startingDay - 1;

        // Jours du mois précédent
        const prevMonthLastDay = new Date(year, month, 0).getDate();
        for (let i = day; i > 0; i--) {
            const date = new Date(year, month - 1, prevMonthLastDay - i + 1);
            createDayCell(date, true);
        }

        // Jours du mois courant
        const today = new Date();
        for (let i = 1; i <= monthLength; i++) {
            const date = new Date(year, month, i);
            const isToday = date.getDate() === today.getDate() &&
                           date.getMonth() === today.getMonth() &&
                           date.getFullYear() === today.getFullYear();
            createDayCell(date, false, isToday);
        }

        // Jours du mois suivant
        let nextMonthDay = 1;
        while ((day + monthLength) % 7 !== 0) {
            const date = new Date(year, month + 1, nextMonthDay);
            createDayCell(date, true);
            nextMonthDay++;
            day++;
        }
    }

    function createDayCell(date, isOtherMonth, isToday = false) {
        const day = date.getDate();
        const month = date.getMonth();
        const year = date.getFullYear();
        const dayOfWeek = date.getDay();
        const isWeekend = dayOfWeek === 0 || dayOfWeek === 6;
        const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

        const dayCell = document.createElement('div');
        dayCell.className = 'calendar-cell calendar-day';

        if (isOtherMonth) {
            dayCell.classList.add('other-month');
        }

        if (isToday) {
            dayCell.classList.add('today');
        }

        if (isWeekend) {
            dayCell.classList.add('weekend');
        }

        // Numéro du jour
        const dayNumber = document.createElement('div');
        dayNumber.className = 'calendar-day-number';
        dayNumber.textContent = day;
        dayCell.appendChild(dayNumber);

        // Conteneur pour les événements
        const eventsContainer = document.createElement('div');
        eventsContainer.className = 'calendar-events-container';
        dayCell.appendChild(eventsContainer);

        // Ajouter les événements du jour
        const dayConges = getCongesForDate(dateStr);
        dayConges.forEach(conge => {
            if (shouldShowConge(conge)) {
                const eventElement = createEventElement(conge);
                eventsContainer.appendChild(eventElement);
            }
        });

        // Ajouter au calendrier
        document.getElementById('calendar-grid').appendChild(dayCell);
    }

    function createEventElement(conge) {
        const eventElement = document.createElement('div');
        eventElement.className = `calendar-event ${conge.statut === 'en_attente' ? 'pending' : ''}`;
        eventElement.style.backgroundColor = conge.type_color;
        eventElement.title = `${conge.user_name} - ${conge.type_name}`;
        eventElement.dataset.congeId = conge.id;

        // Contenu de l'événement
        const userSpan = document.createElement('div');
        userSpan.className = 'event-user';
        userSpan.textContent = conge.user_name.split(' ')[0]; // Juste le prénom

        const typeSpan = document.createElement('div');
        typeSpan.className = 'event-type';
        typeSpan.textContent = conge.type_name;

        eventElement.appendChild(userSpan);
        eventElement.appendChild(typeSpan);

        // Événement click pour afficher les détails
        eventElement.addEventListener('click', function(e) {
            e.stopPropagation();
            showCongeDetails(conge.id);
        });

        return eventElement;
    }

    function getCongesForDate(dateStr) {
        return congesData.filter(conge => {
            if (!conge.date_debut || !conge.date_fin) return false;

            const startDate = new Date(conge.date_debut);
            const endDate = new Date(conge.date_fin);
            const checkDate = new Date(dateStr);

            // Vérifier si la date est dans l'intervalle
            return checkDate >= startDate && checkDate <= endDate;
        });
    }

    function shouldShowConge(conge) {
        // Vérifier les filtres
        const typeMatch = selectedFilters.types.includes(parseInt(conge.type_id));
        const statusMatch = selectedFilters.statuses.includes(conge.statut);

        return typeMatch && statusMatch;
    }

    function updateMonthYearDisplay() {
        const monthNames = [
            'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
            'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
        ];

        const monthName = monthNames[currentMonth];
        document.getElementById('current-month-year').textContent = `${monthName} ${currentYear}`;

        // Info supplémentaire
        const today = new Date();
        const isCurrentMonth = currentMonth === today.getMonth() && currentYear === today.getFullYear();
        document.getElementById('current-month-info').textContent = isCurrentMonth ?
            '(Mois en cours)' :
            `(${Math.abs(currentMonth - today.getMonth() + (currentYear - today.getFullYear()) * 12)} mois ${currentMonth < today.getMonth() ? 'après' : 'avant'})`;
    }

    function updateStats() {
        const currentMonthStr = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}`;

        // Congés ce mois
        const congesCeMois = congesData.filter(conge => {
            if (!conge.date_debut) return false;
            const congeMonth = conge.date_debut.substring(0, 7);
            return congeMonth === currentMonthStr && shouldShowConge(conge);
        });

        // Personnes absentes aujourd'hui
        const today = new Date().toISOString().split('T')[0];
        const absentsAujourdhui = congesData.filter(conge => {
            if (!conge.date_debut || !conge.date_fin || conge.statut !== 'approuve') return false;

            const startDate = new Date(conge.date_debut);
            const endDate = new Date(conge.date_fin);
            const checkDate = new Date(today);

            return checkDate >= startDate && checkDate <= endDate && shouldShowConge(conge);
        });

        // Congés en attente
        const congesEnAttente = congesData.filter(conge =>
            conge.statut === 'en_attente' && shouldShowConge(conge)
        );

        // Jours avec congés ce mois
        const daysWithConges = new Set();
        congesCeMois.forEach(conge => {
            if (conge.date_debut && conge.date_fin) {
                const start = new Date(conge.date_debut);
                const end = new Date(conge.date_fin);
                let current = new Date(start);

                while (current <= end) {
                    if (current.getMonth() === currentMonth && current.getFullYear() === currentYear) {
                        daysWithConges.add(current.toISOString().split('T')[0]);
                    }
                    current.setDate(current.getDate() + 1);
                }
            }
        });

        // Mettre à jour les stats
        document.getElementById('stats-conges-mois').textContent = congesCeMois.length;
        document.getElementById('stats-absents').textContent = absentsAujourdhui.length;
        document.getElementById('stats-en-attente').textContent = congesEnAttente.length;
        document.getElementById('stats-jours-conges').textContent = daysWithConges.size;
    }

    function updateCongesList() {
        const tbody = document.getElementById('conges-list-body');
        tbody.innerHTML = '';

        const currentMonthStr = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}`;

        const congesCeMois = congesData
            .filter(conge => {
                if (!conge.date_debut) return false;
                const congeMonth = conge.date_debut.substring(0, 7);
                return congeMonth === currentMonthStr && shouldShowConge(conge);
            })
            .sort((a, b) => new Date(a.date_debut) - new Date(b.date_debut));

        if (congesCeMois.length === 0) {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td colspan="6" class="text-center py-4">
                    <div class="empty-state">
                        <i class="fas fa-calendar-times fa-2x text-muted mb-3"></i>
                        <h5>Aucun congé ce mois-ci</h5>
                        <p class="text-muted">Aucun congé ne correspond aux filtres appliqués.</p>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
            return;
        }

        congesCeMois.forEach(conge => {
            const tr = document.createElement('tr');
            const statutBadge = conge.statut === 'approuve' ?
                '<span class="badge badge-success">Approuvé</span>' :
                conge.statut === 'en_attente' ?
                '<span class="badge badge-warning">En attente</span>' :
                '<span class="badge badge-danger">Refusé</span>';

            tr.innerHTML = `
                <td>
                    <div class="d-flex align-items-center">
                        <div class="avatar bg-primary text-white rounded-circle mr-2"
                             style="width: 30px; height: 30px; line-height: 30px; text-align: center;">
                            ${conge.user_name.split(' ').map(n => n[0]).join('').toUpperCase()}
                        </div>
                        <div>
                            <strong>${conge.user_name}</strong>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="badge" style="background-color: ${conge.type_color}; color: white;">
                        ${conge.type_name}
                    </span>
                </td>
                <td>
                    ${formatDate(conge.date_debut)} - ${formatDate(conge.date_fin)}
                </td>
                <td>
                    <span class="badge badge-info">${conge.nombre_jours} jour(s)</span>
                </td>
                <td>${statutBadge}</td>
                <td>
                    <a href="${conge.show_url}" class="btn btn-sm btn-info" title="Voir détails">
                        <i class="fas fa-eye"></i>
                    </a>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function formatDate(dateStr) {
        if (!dateStr) return 'N/A';
        const date = new Date(dateStr);
        return `${String(date.getDate()).padStart(2, '0')}/${String(date.getMonth() + 1).padStart(2, '0')}/${date.getFullYear()}`;
    }

    function showCongeDetails(congeId) {
        const conge = congesData.find(c => c.id == congeId);
        if (!conge) return;

        const modalBody = document.getElementById('congeModalBody');
        const viewDetailsBtn = document.getElementById('viewDetailsBtn');

        modalBody.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6>Employé</h6>
                    <p><strong>${conge.user_name}</strong></p>
                </div>
                <div class="col-md-6">
                    <h6>Type de congé</h6>
                    <p>
                        <span class="badge" style="background-color: ${conge.type_color}; color: white;">
                            ${conge.type_name}
                        </span>
                    </p>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <h6>Période</h6>
                    <p>${formatDate(conge.date_debut)} au ${formatDate(conge.date_fin)}</p>
                </div>
                <div class="col-md-6">
                    <h6>Durée</h6>
                    <p><span class="badge badge-info">${conge.nombre_jours} jour(s)</span></p>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-12">
                    <h6>Statut</h6>
                    <p>
                        ${conge.statut === 'approuve' ?
                          '<span class="badge badge-success">Approuvé</span>' :
                          conge.statut === 'en_attente' ?
                          '<span class="badge badge-warning">En attente</span>' :
                          '<span class="badge badge-danger">Refusé</span>'}
                    </p>
                </div>
            </div>
            ${conge.motif ? `
            <div class="row mt-3">
                <div class="col-md-12">
                    <h6>Motif</h6>
                    <p class="bg-light p-3 rounded">${conge.motif}</p>
                </div>
            </div>
            ` : ''}
        `;

        viewDetailsBtn.href = conge.show_url;

        $('#congeModal').modal('show');
    }

    // Navigation
    function prevMonth() {
        currentMonth--;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
        updateCalendar();
    }

    function nextMonth() {
        currentMonth++;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        updateCalendar();
    }

    function goToCurrentMonth() {
        const today = new Date();
        currentMonth = today.getMonth();
        currentYear = today.getFullYear();
        updateCalendar();
    }

    function changeViewMode() {
        const viewMode = document.getElementById('view-mode').value;
        // Pour l'instant, on garde la vue mensuelle
        // Tu pourrais implémenter les vues semaine/jour plus tard
        alert(`Vue ${viewMode} sera implémentée dans une version future`);
    }

    function updateCalendar() {
        renderCalendar(currentMonth, currentYear);
        updateMonthYearDisplay();
        updateStats();
        updateCongesList();
    }

    // Filtres
    function updateTypeFilters() {
        const checkedFilters = Array.from(document.querySelectorAll('.type-filter:checked'))
            .map(filter => parseInt(filter.value));

        selectedFilters.types = checkedFilters.length > 0 ? checkedFilters : [];
        updateCalendar();
    }

    function updateStatusFilters() {
        const checkedFilters = Array.from(document.querySelectorAll('.status-filter:checked'))
            .map(filter => filter.value);

        selectedFilters.statuses = checkedFilters.length > 0 ? checkedFilters : [];
        updateCalendar();
    }

    function resetFilters() {
        // Réinitialiser tous les filtres
        document.querySelectorAll('.type-filter').forEach(filter => {
            filter.checked = true;
        });

        document.querySelectorAll('.status-filter').forEach(filter => {
            filter.checked = filter.value === 'approuve';
        });

        selectedFilters.types = @json($typesConges->pluck('id')->toArray());
        selectedFilters.statuses = ['approuve'];

        updateCalendar();
    }

    // Export CSV
    function exportToCSV() {
        const currentMonthStr = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}`;
        const congesCeMois = congesData.filter(conge => {
            if (!conge.date_debut) return false;
            const congeMonth = conge.date_debut.substring(0, 7);
            return congeMonth === currentMonthStr && shouldShowConge(conge);
        });

        if (congesCeMois.length === 0) {
            alert('Aucune donnée à exporter');
            return;
        }

        const headers = ['Employé', 'Type', 'Date début', 'Date fin', 'Durée', 'Statut', 'Motif'];
        const csvData = [
            headers,
            ...congesCeMois.map(conge => [
                conge.user_name,
                conge.type_name,
                formatDate(conge.date_debut),
                formatDate(conge.date_fin),
                conge.nombre_jours,
                conge.statut,
                conge.motif || ''
            ])
        ];

        const csvContent = csvData.map(row =>
            row.map(cell => `"${cell}"`).join(',')
        ).join('\n');

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);

        link.setAttribute('href', url);
        link.setAttribute('download', `conges_${currentMonth + 1}_${currentYear}.csv`);
        link.style.visibility = 'hidden';

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
});
</script>
@endpush
