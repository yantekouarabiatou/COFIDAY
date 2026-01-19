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
                                                    <div class="legend-color" style="background-color: #e78d96;"></div>
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
    #calendar {
        height: 650px;
        margin-bottom: 20px;
    }

    .fc-event {
        cursor: pointer;
        border-radius: 4px;
        padding: 2px 5px;
        font-size: 0.9em;
        margin: 1px;
    }

    .fc-event:hover {
        opacity: 0.9;
        transform: scale(1.02);
        transition: all 0.2s;
    }

    .fc-event.approuve {
        font-weight: 600;
    }

    .fc-event.en_attente {
        opacity: 0.7;
        border-style: dashed;
        border-width: 2px;
    }

    .weekend-day {
        background-color: #f8f9fa !important;
    }

    .fc-day-today {
        background-color: #ffebee !important;
    }

    .fc-day-today .fc-daygrid-day-number {
        font-weight: bold;
        color: #dc3545;
    }

    .legend-color {
        width: 20px;
        height: 20px;
        border-radius: 4px;
        margin-right: 8px;
        display: inline-block;
        vertical-align: middle;
    }

    .fc-toolbar {
        flex-wrap: wrap !important;
    }

    .fc-header-toolbar {
        margin-bottom: 1.5em !important;
    }

    .fc-button {
        background-color: #3B82F6 !important;
        border-color: #3B82F6 !important;
        color: white !important;
    }

    .fc-button:hover {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
    }

    .fc-button-active {
        background-color: #1d4ed8 !important;
        border-color: #1d4ed8 !important;
    }

    .fc-today-button {
        background-color: #10B981 !important;
        border-color: #10B981 !important;
    }

    .fc-today-button:hover {
        background-color: #059669 !important;
        border-color: #059669 !important;
    }

    /* Animation */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .fc-view {
        animation: fadeIn 0.3s ease-out;
    }

    /* Responsive */
    @media (max-width: 768px) {
        #calendar {
            height: 500px;
        }

        .fc-toolbar {
            flex-direction: column;
            gap: 10px;
        }

        .fc-toolbar .fc-center {
            order: 1;
            margin: 10px 0;
        }

        .fc-toolbar .fc-left,
        .fc-toolbar .fc-right {
            order: 2;
        }
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
    // Données congés
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

    // Couleurs selon votre modèle
    const typeColors = {
        @foreach($typesConges as $type)
        {{ $type->id }}: "{{ $type->couleur ?? '#3B82F6' }}",
        @endforeach
    };

    // Statut colors
    const statusColors = {
        'approuve': '#28a745', // Vert
        'en_attente': '#ffc107', // Jaune
        'refuse': '#dc3545' // Rouge
    };

    let selectedFilters = {
        types: {!! $typesConges->pluck('id')->toJson() !!},
        statuses: ['approuve']
    };

    let dateRange = null;
    let currentViewDate = new Date();

    // Initialiser FullCalendar
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'fr',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listMonth'
        },
        buttonText: {
            today: 'Aujourd\'hui',
            month: 'Mois',
            week: 'Semaine',
            list: 'Liste'
        },
        events: function(fetchInfo, successCallback) {
            successCallback(getFilteredEvents(fetchInfo.start, fetchInfo.end));
        },
        eventClick: function(info) {
            showCongeDetails(info.event.extendedProps.congeId);
        },
        eventDidMount: function(info) {
            // Ajouter un tooltip personnalisé
            const conge = congesData.find(c => c.id === info.event.extendedProps.congeId);
            if (conge) {
                info.el.setAttribute('title',
                    `${conge.user_name}\n${conge.type_name}\n${formatDate(conge.date_debut)} - ${formatDate(conge.date_fin)}\nStatut: ${conge.statut === 'approuve' ? 'Approuvé' : 'En attente'}`
                );
            }

            // Ajouter une icône selon le statut
            const icon = info.event.extendedProps.statut === 'en_attente' ?
                '⏳' : '✅';
            info.el.innerHTML = `${icon} ${info.event.title}`;
        },
        eventDisplay: 'block',
        height: 650,
        weekends: true,
        navLinks: true,
        nowIndicator: true,
        dayMaxEvents: 3,
        dayCellClassNames: function(arg) {
            const day = arg.date.getDay();
            if (day === 0 || day === 6) {
                return ['weekend-day'];
            }
        },
        dayCellDidMount: function(arg) {
            if (arg.isToday) {
                arg.el.style.backgroundColor = '#ffebee';
            }
        }
    });
    calendar.render();

    // Initialiser Flatpickr
    flatpickr('#date-range-filter', {
        mode: 'range',
        dateFormat: 'd/m/Y',
        locale: 'fr',
        onChange: function(selectedDates) {
            if (selectedDates.length === 2) {
                dateRange = selectedDates;
                calendar.gotoDate(dateRange[0]);
                calendar.setOption('validRange', {
                    start: dateRange[0],
                    end: dateRange[1]
                });
                updateCalendar();
            }
        }
    });

    // Listeners
    document.getElementById('prev-month').addEventListener('click', () => {
        calendar.prev();
        updateCurrentDate();
    });
    document.getElementById('next-month').addEventListener('click', () => {
        calendar.next();
        updateCurrentDate();
    });
    document.getElementById('current-month').addEventListener('click', () => {
        calendar.today();
        updateCurrentDate();
        updateCalendar();
    });

    document.querySelectorAll('.year-select').forEach(a => {
        a.addEventListener('click', (e) => {
            e.preventDefault();
            const year = e.target.dataset.year;
            calendar.gotoDate(new Date(year, 0, 1));
            updateCurrentDate();
            document.getElementById('yearDropdown').innerHTML = '<i class="fas fa-calendar"></i> ' + year;
            updateCalendar();
        });
    });

    // Filtrer les événements en fonction des sélections
    function getFilteredEvents(start, end) {
        const startDate = start ? new Date(start) : new Date();
        const endDate = end ? new Date(end) : new Date();

        return congesData
            .filter(conge => {
                // Vérifier si le congé est dans la période
                if (!conge.date_debut || !conge.date_fin) return false;
                const congeStart = new Date(conge.date_debut);
                const congeEnd = new Date(conge.date_fin);

                // Vérifier chevauchement de dates
                const isInPeriod = (congeStart <= endDate && congeEnd >= startDate);

                // Vérifier les filtres
                const typeMatch = selectedFilters.types.includes(conge.type_id);
                const statusMatch = selectedFilters.statuses.includes(conge.statut);

                return isInPeriod && typeMatch && statusMatch;
            })
            .map(conge => {
                // Couleur selon type et statut
                let backgroundColor = typeColors[conge.type_id] || '#3B82F6';
                let borderColor = backgroundColor;

                // Ajouter transparence si en attente
                if (conge.statut === 'en_attente') {
                    backgroundColor = adjustColorAlpha(backgroundColor, 0.6);
                }

                // Ajuster la couleur pour le statut refusé
                if (conge.statut === 'refuse') {
                    backgroundColor = '#dc3545';
                    borderColor = '#dc3545';
                }

                return {
                    id: conge.id,
                    title: `${conge.user_name.split(' ')[0]} - ${conge.type_name}`,
                    start: conge.date_debut,
                    end: new Date(new Date(conge.date_fin).setDate(new Date(conge.date_fin).getDate() + 1)),
                    allDay: true,
                    backgroundColor: backgroundColor,
                    borderColor: borderColor,
                    textColor: getContrastColor(backgroundColor),
                    extendedProps: {
                        congeId: conge.id,
                        statut: conge.statut,
                        user_name: conge.user_name,
                        type_name: conge.type_name,
                        date_debut: conge.date_debut,
                        date_fin: conge.date_fin,
                        nombre_jours: conge.nombre_jours
                    },
                    classNames: ['conge-event', conge.statut]
                };
            });
    }

    // Fonction pour ajuster la transparence d'une couleur
    function adjustColorAlpha(color, alpha) {
        if (color.startsWith('#')) {
            // Convertir hex à rgba
            const r = parseInt(color.slice(1, 3), 16);
            const g = parseInt(color.slice(3, 5), 16);
            const b = parseInt(color.slice(5, 7), 16);
            return `rgba(${r}, ${g}, ${b}, ${alpha})`;
        }
        return color;
    }

    // Fonction pour déterminer la couleur du texte selon le fond
    function getContrastColor(hexcolor) {
        if (!hexcolor.startsWith('#')) {
            // Si c'est rgba, extraire la couleur
            if (hexcolor.startsWith('rgba')) {
                const rgb = hexcolor.match(/\d+/g);
                if (rgb) {
                    hexcolor = rgbToHex(rgb[0], rgb[1], rgb[2]);
                }
            } else {
                return '#ffffff'; // Par défaut
            }
        }

        // Convertir hex à RGB
        const r = parseInt(hexcolor.substr(1, 2), 16);
        const g = parseInt(hexcolor.substr(3, 2), 16);
        const b = parseInt(hexcolor.substr(5, 2), 16);

        // Calculer la luminance
        const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;

        // Retourner noir ou blanc selon la luminance
        return luminance > 0.5 ? '#000000' : '#ffffff';
    }

    function rgbToHex(r, g, b) {
        return "#" + ((1 << 24) + (parseInt(r) << 16) + (parseInt(g) << 8) + parseInt(b)).toString(16).slice(1);
    }

    function updateCurrentDate() {
        currentViewDate = calendar.getDate();
        updateMonthYearDisplay();
    }

    function updateMonthYearDisplay() {
        const monthNames = [
            'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
            'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
        ];
        const monthName = monthNames[currentViewDate.getMonth()];
        document.getElementById('current-month-year').textContent = `${monthName} ${currentViewDate.getFullYear()}`;
    }

    document.querySelectorAll('.type-filter').forEach(filter => {
        filter.addEventListener('change', updateFilters);
    });

    document.querySelectorAll('.status-filter').forEach(filter => {
        filter.addEventListener('change', updateFilters);
    });

    document.getElementById('reset-filters').addEventListener('click', resetFilters);

    function updateFilters() {
        selectedFilters.types = Array.from(document.querySelectorAll('.type-filter:checked')).map(f => parseInt(f.value));
        selectedFilters.statuses = Array.from(document.querySelectorAll('.status-filter:checked')).map(f => f.value);
        calendar.refetchEvents();
        updateCongesList();
        updateStats();
    }

    function resetFilters() {
        document.querySelectorAll('.type-filter').forEach(f => f.checked = true);
        document.querySelectorAll('.status-filter').forEach(f => f.checked = f.value === 'approuve');
        selectedFilters.types = {!! $typesConges->pluck('id')->toJson() !!};
        selectedFilters.statuses = ['approuve'];
        dateRange = null;
        document.getElementById('date-range-filter').value = '';
        calendar.setOption('validRange', null);
        calendar.refetchEvents();
        updateCongesList();
        updateStats();
    }

    function updateCongesList() {
        const body = document.getElementById('conges-list-body');
        body.innerHTML = '';

        const currentView = calendar.view;
        const viewStart = currentView.currentStart;
        const viewEnd = currentView.currentEnd;

        const congesInView = congesData.filter(conge => {
            if (!conge.date_debut || !conge.date_fin) return false;
            const congeStart = new Date(conge.date_debut);
            const congeEnd = new Date(conge.date_fin);
            return (congeStart <= viewEnd && congeEnd >= viewStart) && shouldShowConge(conge);
        });

        if (congesInView.length === 0) {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td colspan="6" class="text-center py-5">
                    <i class="fas fa-calendar-times fa-2x text-muted mb-3"></i>
                    <p class="text-muted">Aucun congé dans cette période</p>
                </td>
            `;
            body.appendChild(row);
            return;
        }

        congesInView.sort((a, b) => new Date(a.date_debut) - new Date(b.date_debut))
            .forEach(conge => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-primary text-white rounded-circle mr-2"
                                 style="width: 32px; height: 32px; line-height: 32px; text-align: center; font-weight: bold;">
                                ${conge.user_name.split(' ').map(n => n[0]).join('').toUpperCase()}
                            </div>
                            <span>${conge.user_name}</span>
                        </div>
                    </td>
                    <td>
                        <span class="badge" style="background-color: ${typeColors[conge.type_id] || '#3B82F6'}; color: white;">
                            ${conge.type_name}
                        </span>
                    </td>
                    <td>
                        ${formatDate(conge.date_debut)} <br>
                        <small class="text-muted">au</small><br>
                        ${formatDate(conge.date_fin)}
                    </td>
                    <td>
                        <span class="badge badge-info">${conge.nombre_jours} jour(s)</span>
                    </td>
                    <td>
                        ${conge.statut === 'approuve' ?
                          '<span class="badge badge-success"><i class="fas fa-check"></i> Approuvé</span>' :
                          conge.statut === 'en_attente' ?
                          '<span class="badge badge-warning"><i class="fas fa-clock"></i> En attente</span>' :
                          '<span class="badge badge-danger"><i class="fas fa-times"></i> Refusé</span>'}
                    </td>
                    <td>
                        <a href="${conge.show_url}" class="btn btn-sm btn-info" title="Voir détails">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                `;
                body.appendChild(row);
            });
    }

    function shouldShowConge(conge) {
        return selectedFilters.types.includes(conge.type_id) && selectedFilters.statuses.includes(conge.statut);
    }

    function updateStats() {
        const currentView = calendar.view;
        const viewStart = currentView.currentStart;
        const viewEnd = currentView.currentEnd;

        const congesInView = congesData.filter(conge => {
            if (!conge.date_debut || !conge.date_fin) return false;
            const congeStart = new Date(conge.date_debut);
            const congeEnd = new Date(conge.date_fin);
            return (congeStart <= viewEnd && congeEnd >= viewStart) && shouldShowConge(conge);
        });

        const today = new Date().toISOString().split('T')[0];
        const absentsToday = congesData.filter(conge => {
            return conge.date_debut <= today && conge.date_fin >= today &&
                   conge.statut === 'approuve' && shouldShowConge(conge);
        }).length;

        const enAttente = congesData.filter(conge =>
            conge.statut === 'en_attente' && shouldShowConge(conge)
        ).length;

        const joursAvecConges = new Set();
        congesInView.forEach(conge => {
            if (conge.date_debut && conge.date_fin) {
                const start = new Date(conge.date_debut);
                const end = new Date(conge.date_fin);
                let current = new Date(start);
                while (current <= end) {
                    if (current >= viewStart && current <= viewEnd) {
                        joursAvecConges.add(current.toISOString().split('T')[0]);
                    }
                    current.setDate(current.getDate() + 1);
                }
            }
        });

        document.getElementById('stats-conges-mois').textContent = congesInView.length;
        document.getElementById('stats-absents').textContent = absentsToday;
        document.getElementById('stats-en-attente').textContent = enAttente;
        document.getElementById('stats-jours-conges').textContent = joursAvecConges.size;
    }

    function showCongeDetails(congeId) {
        const conge = congesData.find(c => c.id === congeId);
        if (!conge) return;

        const modalBody = document.getElementById('congeModalBody');
        modalBody.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6>Employé</h6>
                    <p class="mb-2"><strong>${conge.user_name}</strong></p>
                </div>
                <div class="col-md-6">
                    <h6>Type de congé</h6>
                    <p>
                        <span class="badge" style="background-color: ${typeColors[conge.type_id] || '#3B82F6'}; color: white;">
                            ${conge.type_name}
                        </span>
                    </p>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <h6>Période</h6>
                    <p>
                        <i class="fas fa-calendar-day text-primary mr-2"></i>
                        ${formatDate(conge.date_debut)} - ${formatDate(conge.date_fin)}
                    </p>
                </div>
                <div class="col-md-6">
                    <h6>Durée</h6>
                    <p>
                        <i class="fas fa-clock text-primary mr-2"></i>
                        <span class="badge badge-info">${conge.nombre_jours} jour(s)</span>
                    </p>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <h6>Statut</h6>
                    <p>
                        ${conge.statut === 'approuve' ?
                          '<span class="badge badge-success"><i class="fas fa-check"></i> Approuvé</span>' :
                          conge.statut === 'en_attente' ?
                          '<span class="badge badge-warning"><i class="fas fa-clock"></i> En attente</span>' :
                          '<span class="badge badge-danger"><i class="fas fa-times"></i> Refusé</span>'}
                    </p>
                </div>
                <div class="col-md-6">
                    <h6>Motif</h6>
                    <div class="bg-light p-3 rounded">
                        ${conge.motif || '<span class="text-muted">Aucun motif fourni</span>'}
                    </div>
                </div>
            </div>
        `;
        document.getElementById('viewDetailsBtn').href = conge.show_url;
        $('#congeModal').modal('show');
    }

    function formatDate(dateStr) {
        if (!dateStr) return 'N/A';
        const [year, month, day] = dateStr.split('-');
        return `${day}/${month}/${year}`;
    }

    // Initialisation
    updateCurrentDate();
    updateCongesList();
    updateStats();

    // Écouter les changements de vue
    calendar.on('datesSet', function() {
        updateCurrentDate();
        updateCongesList();
        updateStats();
    });
});
</script>
@endpush
