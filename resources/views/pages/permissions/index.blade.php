@extends('layaout')

@section('title', 'Rôles & Permissions')

@section('content')
    <section class="section">
        <div class="section-body">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb bg-transparent p-0">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-info"><i
                                            class="fas fa-home"></i> Tableau de bord</a></li>
                                <li class="breadcrumb-item active text-muted" aria-current="page">Gestion des accès</li>
                            </ol>
                        </nav>
                        <h1 class="h2 mb-1 text-gray-900">
                            <i class="fas fa-user-shield text-info mr-2"></i>
                            Gestion des Rôles & Permissions
                        </h1>
                        <p class="text-muted mb-0">
                            Administrez les droits d'accès et les privilèges des utilisateurs dans votre application.
                            <span class="d-block small mt-1">
                                <i class="fas fa-info-circle text-primary"></i>
                                Le modèle RBAC (Role-Based Access Control) garantit la sécurité et la conformité des accès.
                            </span>
                        </p>
                    </div>

                    @can('créer des rôles')
                        <div class="text-right">
                            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-lg shadow-sm">
                                <i class="fas fa-plus-circle mr-2"></i> Créer un nouveau rôle
                            </a>
                        </div>
                    @endcan
                </div>

                @php
                    // Calcul du nombre total d'utilisateurs ayant un rôle via Spatie
                    $usersWithRole = \App\Models\User::whereHas('roles')->count();
                    $totalPermissions = $groupedPermissions->flatten()->count();
                    $totalRoles = $roles->count();
                @endphp

                <div class="row mb-5">

                    {{-- Carte 1 : Rôles Définis --}}
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 card-stat">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Rôles Définis
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalRoles }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-user-tag fa-2x text-primary-light"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Carte 2 : Permissions totales --}}
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 card-stat">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Permissions Uniques
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalPermissions }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-key fa-2x text-success-light"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Carte 3 : Groupes de permissions --}}
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 card-stat">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Catégories
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            {{ $groupedPermissions->keys()->count() }}
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-layer-group fa-2x text-warning-light"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Carte 4 : Utilisateurs assignés --}}
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 card-stat">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Utilisateurs Assignés
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            {{ $usersWithRole }}
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users fa-2x text-info-light"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-5">

                    {{-- Graphique de Répartition --}}
                    <div class="col-xl-8">
                        <div class="card shadow border-0">
                            <div class="card-header border-0 py-3">
                                <h6 class="m-0 font-weight-bold text-info-900">
                                    <i class="fas fa-chart-pie text-info"></i>
                                    Répartition des Permissions par Groupe (Total: {{ $totalPermissions }})
                                </h6>
                                <p class="text-muted small mb-0 mt-1">
                                    Visualisez la distribution des permissions entre les différentes catégories.
                                </p>
                            </div>
                            <div class="card-body">
                                <div class="chart-container" style="position: relative; height: 300px;">
                                    <canvas id="permissionsChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Légende Dynamique et Guide des Actions --}}
                    <div class="col-xl-4">
                        <div class="card shadow border-0 h-100">
                            <div class="card-header bg-white border-0 py-3">
                                <h6 class="m-0 font-weight-bold text-dark">
                                    <i class="fas fa-info-circle text-dark mr-2"></i>
                                    Légende & Guide des Actions
                                </h6>
                            </div>
                            <div class="card-body">

                                {{-- Contenu injecté par JS pour la légende --}}
                                <div id="chart-legend-container" class="mb-4">
                                    <h6 class="font-weight-bold text-gray-700 mb-3">Groupes de Permissions (Répartition) :
                                    </h6>
                                    <div id="dynamic-legend" class="small">
                                        <p class="text-muted small">Chargement de la légende...</p>
                                    </div>
                                </div>

                                <div class="border-top pt-3">
                                    <h6 class="font-weight-bold text-gray-700 mb-3">Actions rapides :</h6>
                                    <ul class="list-unstyled small">
                                        <li class="d-flex align-items-center mb-2">
                                            <button class="btn btn-light btn-sm border mr-2" disabled><i
                                                    class="fas fa-key text-info"></i></button>
                                            <span class="text-muted">Gérer les permissions du rôle.</span>
                                        </li>
                                        <li class="d-flex align-items-center mb-2">
                                            <button class="btn btn-light btn-sm border mr-2" disabled><i
                                                    class="fas fa-edit text-warning"></i></button>
                                            <span class="text-muted">Modifier le rôle.</span>
                                        </li>
                                        <li class="d-flex align-items-center">
                                            <button class="btn btn-light btn-sm border mr-2" disabled><i
                                                    class="fas fa-trash-alt text-danger"></i></button>
                                            <span class="text-muted">Supprimer le rôle (sous conditions).</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow border-0">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="m-0 font-weight-bold text-gray-900">
                                    <i class="fas fa-list-alt text-info mr-2"></i>
                                    Liste des Rôles
                                </h6>
                                <p class="text-muted small mb-0 mt-1">
                                    Gérer tous les rôles de votre système.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="rolesTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="pl-4"><i class="fas fa-user-tag mr-2 text-info"></i> Rôle</th>
                                        <th><i class="fas fa-align-left mr-2 text-info"></i> Description</th>
                                        <th class="text-center"><i class="fas fa-key mr-2 text-info"></i> Permissions</th>
                                        <th class="text-center"><i class="fas fa-users mr-2 text-info"></i> Utilisateurs
                                        </th>
                                        <th class="text-center pr-4"><i class="fas fa-cogs mr-2 text-info"></i> Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($roles as $role)
                                        <tr class="border-bottom">
                                            <td class="pl-4 align-middle">
                                                <div class="font-weight-bold text-gray-900">{{ $role->name }}</div>
                                                <small class="text-muted">ID: {{ $role->id }}</small>
                                            </td>
                                            <td class="align-middle">
                                                <div class="text-gray-800 small">
                                                    @if($role->description)
                                                        {{ $role->description }}
                                                    @else
                                                        <span class="text-muted font-italic">Aucune description fournie</span>
                                                    @endif
                                                </div>
                                                @if(in_array($role->name, ['super-admin', 'admin']))
                                                    <small class="text-danger">
                                                        <i class="fas fa-shield-alt"></i> Rôle système protégé
                                                    </small>
                                                @endif
                                            </td>
                                            <td class="text-center align-middle">
                                                <span class="badge badge-info badge-pill px-3 py-2" data-toggle="tooltip"
                                                    data-placement="top"
                                                    title="{{ $role->permissions->pluck('description')->join(', ') }}">
                                                    {{ $role->permissions->count() }}
                                                    <i class="fas fa-key ml-1"></i>
                                                </span>
                                            </td>
                                            <td class="text-center align-middle">
                                                <span class="badge badge-primary badge-pill px-3 py-2">
                                                    {{ $role->users_custom_count ?? 0 }}
                                                    <i class="fas fa-user ml-1"></i>
                                                </span>
                                            </td>
                                            <td class="text-center pr-4 align-middle">
                                                <div class="btn-group btn-group-sm shadow-sm" role="group">

                                                        <a href="{{ route('admin.roles.permissions.show', $role) }}"
                                                            class="btn btn-light border" data-toggle="tooltip" data-placement="top"
                                                            title="Configurer les permissions de ce rôle">
                                                            <i class="fas fa-key text-info"></i>
                                                        </a>

                                                    @can('modifier des rôles')
                                                        <a href="{{ route('admin.roles.edit', $role) }}"
                                                            class="btn btn-light border" data-toggle="tooltip" data-placement="top"
                                                            title="Modifier ce rôle">
                                                            <i class="fas fa-edit text-warning"></i>
                                                        </a>
                                                    @endcan

                                                    @can('supprimer des rôles')
                                                        @if(!in_array($role->name, ['super-admin', 'admin']) && $role->users->count() === 0)
                                                            <form action="{{ route('admin.roles.destroy', $role) }}" method="POST"
                                                                class="d-inline confirm-delete-role">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-light border" data-toggle="tooltip"
                                                                    data-placement="top" title="Supprimer ce rôle">
                                                                    <i class="fas fa-trash-alt text-danger"></i>
                                                                </button>
                                                            </form>
                                                        @else
                                                            <button class="btn btn-light border" disabled data-toggle="tooltip"
                                                                data-placement="top"
                                                                title="{{ $role->users->count() > 0 ? 'Rôle utilisé par des utilisateurs' : 'Rôle système protégé' }}">
                                                                <i class="fas fa-lock text-secondary"></i>
                                                            </button>
                                                        @endif
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-footer bg-white border-0 py-3">
                        <div class="alert alert-light border-left-info border-left-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-lightbulb text-info fa-lg mr-3"></i>
                                <div>
                                    <h6 class="mb-1 text-info">Conseil de sécurité</h6>
                                    <p class="mb-0 small text-muted">
                                        Attribuez toujours le minimum de permissions nécessaires à chaque rôle (principe du
                                        moindre privilège).
                                        Évitez d'assigner le rôle "super-admin" sauf pour l'administrateur principal.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">
    <style>
        /* Styles spécifiques */
        .card {
            border-radius: 12px;
            transition: transform 0.2s ease-in-out;
        }

        .card-stat:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
        }

        .border-left-primary {
            border-left: 4px solid #4e73df !important;
        }

        .border-left-success {
            border-left: 4px solid #1cc88a !important;
        }

        .border-left-warning {
            border-left: 4px solid #f6c23e !important;
        }

        .border-left-info {
            border-left: 4px solid #36b9cc !important;
        }

        /* Couleurs claires pour les icônes de stat (visuel) */
        .text-primary-light {
            color: #879af1 !important;
        }

        .text-success-light {
            color: #72ecbf !important;
        }

        .text-warning-light {
            color: #f9d885 !important;
        }

        .text-info-light {
            color: #77d0de !important;
        }

        .badge-pill {
            border-radius: 20px;
            min-width: 60px;
        }

        table.dataTable thead th {
            border-bottom: 2px solid #e3e6f0;
            font-weight: 700;
            color: #5a5c69;
        }

        .table-hover tbody tr:hover {
            background-color: #f8f9fc;
        }

        .confirm-delete-role {
            display: inline-block;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialiser les tooltips Bootstrap
            $('[data-toggle="tooltip"]').tooltip();

            // Configuration des données du graphique
            const chartColors = [
                '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e',
                '#e74a3b', '#858796', '#6f42c1', '#20c997',
                '#fd7e14', '#17a2b8', '#28a745', '#dc3545',
                // Ajouter plus de couleurs si nécessaire, sinon Chart.js les réutilisera
            ];

            const permissionLabels = @json(array_values($groupLabels));
            const permissionCounts = @json(collect($groupedPermissions)->map->count()->values());

            // --- Graphique Doughnut ---
            const ctx = document.getElementById('permissionsChart').getContext('2d');
            const doughnutChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: permissionLabels,
                    datasets: [{
                        data: permissionCounts,
                        backgroundColor: chartColors,
                        borderWidth: 2,
                        borderColor: '#fff',
                        hoverOffset: 15
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        // Désactiver la légende intégrée pour la générer manuellement
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} permissions (${percentage}%)`;
                                }
                            }
                        }
                    },
                    cutout: '65%'
                }
            });

            // --- Génération de la Légende HTML Dynamique ---
            const legendContainer = document.getElementById('dynamic-legend');
            legendContainer.innerHTML = '';

            const labels = doughnutChart.data.labels;
            const colors = doughnutChart.data.datasets[0].backgroundColor;
            const dataCounts = doughnutChart.data.datasets[0].data;

            labels.forEach((label, index) => {
                const count = dataCounts[index];
                const color = colors[index % colors.length]; // Utilise le modulo pour réutiliser les couleurs

                // Créer l'élément de légende
                const legendItem = document.createElement('div');
                legendItem.className = 'd-flex justify-content-between align-items-center mb-1';
                legendItem.innerHTML = `
                <div style="font-size: 0.9rem; white-space: nowrap;">
                    <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background-color: ${color}; margin-right: 8px;"></span>
                    ${label}
                </div>
                <span class="badge badge-light shadow-sm text-dark">${count}</span>
            `;
                legendContainer.appendChild(legendItem);
            });

            // --- Configuration DataTables ---
            const rolesTable = $('#rolesTable');

            if ($.fn.DataTable.isDataTable(rolesTable)) {
                rolesTable.DataTable().destroy();
            }

            new DataTable(rolesTable, {
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json'
                },
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100],
                order: [[0, 'asc']],
                columnDefs: [
                    { orderable: true, targets: [0, 1, 2, 3] },
                    { orderable: false, targets: [4] }
                ],
                dom: '<"top"f>rt<"bottom"ilp><"clear">',
                initComplete: function () {
                    $('.dataTables_filter input').attr('placeholder', 'Rechercher un rôle...');
                    $('.dataTables_filter label').addClass('font-weight-bold text-gray-700');
                }
            });

            // --- Gestion de la suppression (SweetAlert) ---
            $(document).on('submit', '.confirm-delete-role', function (e) {
                e.preventDefault();
                const form = $(this);
                const roleName = form.closest('tr').find('.font-weight-bold').text();

                Swal.fire({
                    title: 'Confirmation de suppression',
                    text: `Voulez-vous vraiment supprimer le rôle « ${roleName} » ? Cette action est irréversible.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Oui, supprimer !',
                    cancelButtonText: 'Annuler',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.off('submit').submit(); // Soumettre le formulaire sans redéclencher SweetAlert
                    }
                });
            });
        });
    </script>
@endpush
