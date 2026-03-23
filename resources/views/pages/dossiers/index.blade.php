@extends('layaout')

@section('title', 'Gestion des Dossiers')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1><i class="fas fa-folder"></i> Gestion des Dossiers</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active">Dossiers</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Tous les Dossiers</h4>
                            {{-- Bouton de synchronisation --}}
                            <form action="{{ route('admin.missions.import') }}" method="POST" id="syncForm">
                                @csrf
                                <button type="submit" class="btn btn-primary" id="syncBtn">
                                    <i class="fas fa-sync-alt me-2"></i>Synchroniser depuis Cofplan
                                </button>
                            </form>
                            {{-- Bouton d'import des clients --}}
                            <form action="{{ route('clients.import') }}" method="POST" id="syncClientsForm"
                                style="display:inline-block;">
                                @csrf
                                <button type="submit" class="btn btn-info" id="syncClientsBtn">
                                    <i class="fas fa-users me-2"></i>Importer clients depuis cofigistre
                                </button>
                            </form>
                            <div class="card-header-action">
                                <a href="{{ route('dossiers.create') }}" class="btn btn-icon icon-left btn-primary">
                                    <i class="fas fa-plus"></i> Nouveau Dossier
                                </a>

                                <!-- Bouton Export (à activer plus tard si tu ajoutes DataTables Buttons) -->
                                <a href="#" class="btn btn-icon icon-left btn-success ml-2" style="display:none;">
                                    <i class="fas fa-file-export"></i> Exporter
                                </a>
                            </div>
                        </div>

                        {{-- Affichage des messages flash --}}
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <div class="card-body">
                            <!-- Barre de recherche et filtres -->
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <input type="text" id="search-input" class="form-control"
                                            placeholder="Rechercher par nom, référence, description...">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" id="search-btn" type="button">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-8 text-right">
                                    <div class="dropdown d-inline">
                                        <button class="btn btn-info dropdown-toggle" type="button" data-toggle="dropdown">
                                            <i class="fas fa-filter"></i> Filtres
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item filter-link" href="#" data-filter="all">Tous</a>
                                            <a class="dropdown-item filter-link" href="#" data-statut="en_cours">En cours /
                                                Ouverts</a>
                                            <a class="dropdown-item filter-link" href="#" data-statut="cloture">Clôturés</a>
                                            <a class="dropdown-item filter-link" href="#" data-retard="1">En retard</a>
                                            <div class="dropdown-divider"></div>
                                            <h6 class="dropdown-header">Par type</h6>
                                            @foreach(['audit', 'conseil', 'formation', 'expertise', 'autre'] as $type)
                                                <a class="dropdown-item filter-link" href="#" data-type="{{ $type }}">
                                                    {{ ucfirst($type) }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tableau DataTables -->
                            <div class="table-responsive">
                                <table id="dossiers-table" class="table table-striped table-hover" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Référence</th>
                                            <th>Nom</th>
                                            <th>Client</th>
                                            <th>Type</th>
                                            <th>Statut</th>
                                            <th>Dates</th>
                                            <th>Budget</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Chargé en AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="row mt-4">
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-primary">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Dossiers</h4>
                            </div>
                            <div class="card-body">{{ $totalDossiers }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-success">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>En cours</h4>
                            </div>
                            <div class="card-body">{{ $dossiersEnCours }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-warning">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>En retard</h4>
                            </div>
                            <div class="card-body">{{ $dossiersEnRetard }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-info">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Clôturés</h4>
                            </div>
                            <div class="card-body">{{ $dossiersClotures }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

<!-- Styles DataTables -->
@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">

    <style>
        .table td {
            vertical-align: middle;
        }

        .badge {
            font-size: 0.75em;
        }
    </style>
@endpush

<!-- Scripts DataTables + Initialisation AJAX -->
@push('scripts')
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function () {
            let table = $('#dossiers-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: '{{ route('dossiers.index') }}',
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json'
                },
                order: [[0, 'desc']],
                columns: [
                    { data: 'reference', name: 'reference' },
                    { data: 'nom', name: 'nom' },
                    { data: 'client.nom', name: 'client.nom' },
                    { data: 'type_dossier_badge', name: 'type_dossier' },
                    { data: 'statut_badge', name: 'statut' },
                    { data: 'date_ouverture', name: 'date_ouverture' },
                    { data: 'budget', name: 'budget' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ]
            });

            // Recherche
            $('#search-btn').on('click', function () {
                table.search($('#search-input').val()).draw();
            });

            $('#search-input').on('keyup', function (e) {
                if (e.key === 'Enter') {
                    table.search(this.value).draw();
                }
            });

            // Filtres dropdown
            $('.filter-link').on('click', function (e) {
                e.preventDefault();

                // Reset tous les filtres
                table.search('').columns().search('').draw();

                let statut = $(this).data('statut');
                let type = $(this).data('type');
                let retard = $(this).data('retard');

                if (statut === 'en_cours') {
                    table.column('statut:name').search('ouvert|en_cours', true, false).draw();
                } else if (statut === 'cloture') {
                    table.column('statut:name').search('cloture').draw();
                } else if (type) {
                    table.column('type_dossier:name').search('^' + type + '$', true, false).draw();
                } else if (retard === '1') {
                    table.column('en_retard:name').search('1').draw();
                }
                // "Tous" → déjà reset
            });
        });

        // Suppression avec SweetAlert (délégation d'événement pour les lignes AJAX)
        $(document).on('click', '.delete-dossier-btn', function () {
            const url = $(this).data('url');
            const row = $(this).closest('tr');

            Swal.fire({
                title: 'Êtes-vous sûr ?',
                text: "Cette action supprimera définitivement le dossier !",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Oui, supprimer !',
                cancelButtonText: 'Annuler',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        body: JSON.stringify({ _method: 'DELETE' })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (!data.success) {
                                Swal.showValidationMessage(data.message);
                                return false;
                            }
                            return data;
                        })
                        .catch(() => {
                            Swal.showValidationMessage('Erreur lors de la suppression.');
                        });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Supprimé !',
                        text: 'Le dossier a été supprimé avec succès.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        table.ajax.reload(null, false); // ← recharge DataTable sans reset la page
                    });
                }
            });
        });
    </script>

    <script>
        document.getElementById('syncForm').addEventListener('submit', function () {
            const btn = document.getElementById('syncBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Synchronisation en cours...';
        });
    </script>
    <script>
        document.getElementById('syncClientsForm').addEventListener('submit', function () {
            const btn = document.getElementById('syncClientsBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Import des clients en cours...';
        });
    </script>
@endpush