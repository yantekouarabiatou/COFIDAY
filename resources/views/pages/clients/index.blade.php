@extends('layaout')

@section('title', 'Gestion des Clients')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-users"></i> Gestion des Clients</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Clients</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h4>Liste des Clients</h4>
                        <div class="card-header-action">
                            <a href="{{ route('clients.create') }}" class="btn btn-primary btn-icon icon-left">
                                <i class="fas fa-plus"></i> Nouveau Client
                            </a>
                            <a href="{{ route('clients.export.pdf') }}" class="btn btn-success btn-icon icon-left ml-2">
                                <i class="fas fa-file-pdf"></i> Exporter PDF
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Filtres -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Statut</label>
                                    <select id="statut-filter" class="form-control select2">
                                        <option value="">Tous les statuts</option>
                                        <option value="actif">Actif</option>
                                        <option value="inactif">Inactif</option>
                                        <option value="prospect">Prospect</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Recherche</label>
                                    <input type="text" id="search-input" class="form-control"
                                           placeholder="Nom, email, téléphone, contact...">
                                </div>
                            </div>
                            <div class="col-md-3 text-right" style="padding-top: 30px;">
                                <button type="button" id="reset-filters" class="btn btn-outline-secondary btn-icon icon-left">
                                    <i class="fas fa-redo"></i> Réinitialiser
                                </button>
                            </div>
                        </div>

                        <!-- Tableau -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="clients-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        {{-- <th>Logo</th> --}}
                                        <th>Nom</th>
                                        <th>Contact</th>
                                        <th>Téléphone</th>
                                        <th>Email</th>
                                        <th>Dossiers</th>
                                        <th>Statut</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($clients as $client)
                                    <tr data-statut="{{ $client->statut }}">
                                        <td>{{ $loop->iteration }}</td>
                                        {{-- <td>
                                            @if($client->logo)
                                                <div class="mr-3">
                                                    <img src="{{ asset('storage/' . $client->logo) }}"
                                                         alt="Logo {{ $client->nom }}"
                                                         class="img-fluid"
                                                         style="width: 40px; height: 40px; object-fit: cover;">
                                                </div>
                                            @endif
                                        </td> --}}
                                        <td>
                                            <strong>{{ $client->nom }}</strong>
                                            @if($client->siege_social)
                                                <br><small class="text-muted">{{ $client->siege_social }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $client->contact_principal ?? '-' }}</td>
                                        <td>{{ $client->telephone ?? '-' }}</td>
                                        <td>
                                            @if($client->email)
                                                <a href="mailto:{{ $client->email }}">{{ $client->email }}</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-info">{{ $client->dossiers_count }}</span>
                                        </td>
                                        <td>{!! $client->statut_badge !!}</td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('clients.show', $client) }}"
                                                   class="btn btn-info btn-sm" title="Voir">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('clients.edit', $client) }}"
                                                   class="btn btn-primary btn-sm" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('clients.destroy', $client) }}"
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-danger btn-sm delete-btn"
                                                            title="Supprimer" data-id="{{ $client->id }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Statistiques -->
                        <div class="row mt-4">
                            <div class="col-md-3">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-primary">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Total Clients</h4>
                                        </div>
                                        <div class="card-body">
                                            {{ $clients->count() }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-success">
                                        <i class="fas fa-user-check"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Actifs</h4>
                                        </div>
                                        <div class="card-body" id="actif-count">
                                            {{ $clients->where('statut', 'actif')->count() }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-warning">
                                        <i class="fas fa-user-clock"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Prospects</h4>
                                        </div>
                                        <div class="card-body" id="prospect-count">
                                            {{ $clients->where('statut', 'prospect')->count() }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-danger">
                                        <i class="fas fa-user-times"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Inactifs</h4>
                                        </div>
                                        <div class="card-body" id="inactif-count">
                                            {{ $clients->where('statut', 'inactif')->count() }}
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
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/bundles/select2/dist/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/bundles/datatables/datatables.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css') }}">
<style>
    .table img {
        border: 2px solid #f0f0f0;
    }
    .btn-group .btn {
        margin: 0 2px;
    }
    .card-statistic-1 {
        margin-bottom: 0;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/bundles/select2/dist/js/select2.full.min.js') }}"></script>
<script src="{{ asset('assets/bundles/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Initialiser Select2
    $('.select2').select2({
        placeholder: "Sélectionner...",
        allowClear: true
    });

    // Initialiser DataTable
    var table = $('#clients-table').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
        },
        pageLength: 25,
        order: [[2, 'asc']],
        columnDefs: [
            { orderable: false, targets: [0, 1, 8] },
            { searchable: false, targets: [0, 1, 6, 8] }
        ]
    });

    // Filtre personnalisé
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        var row = settings.aoData[dataIndex].nTr;
        var rowStatut = $(row).data('statut') || '';
        var searchText = $('#search-input').val().toLowerCase().trim();
        var statutFilter = $('#statut-filter').val();

        // Filtre par recherche
        if (searchText) {
            var rowText = $(row).text().toLowerCase();
            if (rowText.indexOf(searchText) === -1) {
                return false;
            }
        }

        // Filtre par statut
        if (statutFilter && rowStatut !== statutFilter) {
            return false;
        }

        return true;
    });

    // Appliquer les filtres
    function applyFilters() {
        table.draw();
        updateCounts();
    }

    $('#search-input').on('keyup', applyFilters);
    $('#statut-filter').on('change', applyFilters);

    // Réinitialiser les filtres
    $('#reset-filters').on('click', function() {
        $('#search-input').val('');
        $('#statut-filter').val('').trigger('change');
        applyFilters();
    });

    // Mettre à jour les compteurs
    function updateCounts() {
        var visibleRows = table.rows({ search: 'applied' }).data();

        var actif = 0, inactif = 0, prospect = 0;

        visibleRows.each(function(row, index) {
            var tr = table.row(index).node();
            var statut = $(tr).data('statut');

            if (statut === 'actif') actif++;
            else if (statut === 'inactif') inactif++;
            else if (statut === 'prospect') prospect++;
        });

        $('#actif-count').text(actif);
        $('#inactif-count').text(inactif);
        $('#prospect-count').text(prospect);
    }

    // Suppression avec SweetAlert
    $(document).on('click', '.delete-btn', function(e) {
        e.preventDefault();
        var form = $(this).closest('form');
        var clientId = $(this).data('id');

        Swal.fire({
            title: 'Êtes-vous sûr ?',
            text: "Cette action supprimera définitivement le client !",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Oui, supprimer !',
            cancelButtonText: 'Annuler',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return fetch(form.attr('action'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    body: JSON.stringify({ _method: 'DELETE' })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(response.statusText);
                    }
                    return response.json();
                })
                .catch(error => {
                    Swal.showValidationMessage(
                        `Erreur: ${error}`
                    );
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire(
                    'Supprimé !',
                    'Le client a été supprimé avec succès.',
                    'success'
                ).then(() => {
                    location.reload();
                });
            }
        });
    });

    // Messages flash SweetAlert
    @if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Succès !',
        text: '{{ session('success') }}',
        timer: 3000,
        showConfirmButton: false
    });
    @endif

    @if(session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Erreur !',
        text: '{{ session('error') }}'
    });
    @endif

    // Initialiser
    updateCounts();
});
</script>
@endpush
