@extends('layaout')

@section('title', 'Liste des Utilisateurs')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-file-alt"></i> Gestion des Utilisateurs</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Utilisateurs</div>
        </div>
    </div>

    <div class="section-body">
        <!-- Onglets avec compteurs -->
        <div class="card mb-4">
            <div class="card-body py-3">
                <ul class="nav nav-pills" id="etat-tabs">
                    <li class="nav-item">
                        <a class="nav-link active" data-etat="" href="#">
                            Toutes <span class="badge badge-white ml-1" id="total-count">{{ $users->count() }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-etat="En cours" href="#">
                            Actif <span class="badge badge-success ml-1" id="en-cours-count">{{ $users->where('is_active', 1)->count() }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-etat="Résolue" href="#">
                            Inactif <span class="badge badge-warning ml-1" id="resolues-count">{{ $users->where('is_active', 0)->count() }}</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4>Liste des Utilisateurs</h4>
                <div class="card-header-action">
                @can('créer des utilisateurs')
                    <a href="{{ route('users.create') }}" class="btn btn-primary btn-icon icon-left mr-2">
                        <i class="fas fa-plus"></i> Nouvel Utilisateur
                    </a>
                @endcan
            </div>
            </div>

            <div class="card-body">
                <!-- Filtres -->
                <div class="row mb-4">
                    <div class="col-lg-3">
                        <label>Recherche</label>
                        <input type="text" id="search-input" class="form-control" placeholder="ID, nom, prenom, email...">
                    </div>
                    <div class="col-lg-3">
                        <label>Statut</label>
                        <select id="statut-filter" class="form-control select2">
                            <option value="">Tous les statuts</option>
                            <option value="Actif">Actif</option>
                            <option value="Inactif">Inactif</option>
                        </select>
                    </div>

                    <div class="col-lg-3">
                        <label>Postes</label>
                        <select id="poste-filter" class="form-control select2">
                            <option value="">Tous les postes</option>
                            @foreach($postes as $poste)
                            <option value="{{ $poste->intitule }}">{{ $poste->intitule }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-2 text-right" style="padding-top: 30px;">
                        <button type="button" id="reset-filters" class="btn btn-outline-secondary btn-icon icon-left btn-primary">
                            <i class="fas fa-redo"></i> Réinitialiser
                        </button>
                    </div>
                </div>

                <!-- Tableau avec largeur fixe et scroll -->
                <div class="table-responsive" style="max-height: 600px; overflow: auto;">
                    <table class="table table-striped table-hover" id="users-table" style="width: 100%; min-width: 1000px;">
                        <thead class="thead-dark" style="position: sticky; top: 0; z-index: 10;">
                            <tr>
                                <th width="120">ID</th>
                                <th width="150">Nom et prenom</th>
                                <th width="150">username</th>
                                <th width="200">Satut</th>
                                <th width="100">Email</th>
                                <th width="1o0">Poste</th>
                                <th width="180" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
                                <td><strong class="text-dark">#{{ $user->id }}</strong></td>
                                <td>{{ $user->nom ?? '-'}} {{ $user->prenom ?? '-'}}</td>
                                <td>{{ $user->username ?? '-' }}</td>
                                <td>
                                    <label class="custom-switch mt-2">
                                        <input type="checkbox" class="custom-switch-input toggle-status" data-id="{{ $user->id }}" {{ $user->is_active ? 'checked' : '' }}>
                                        <span class="custom-switch-indicator"></span>
                                        <span class="custom-switch-description">
                                            {{ $user->is_active ? 'Actif' : 'Inactif' }}
                                        </span>
                                    </label>

                                </td>
                                <td>{{ $user->email ?? ""}}</td>
                                <td>
                                    {{ $user->poste?->intitule ?? 'Inconnu' }}
                                </td>
                               <td>
                                    <div class="btn-group btn-group-sm" role="group" style="padding: 4px;">
                                        {{-- Bouton Voir --}}
                                        @can('voir les utilisateurs')
                                            <a href="{{ route('users.show', $user) }}" class="btn btn-info btn-sm" title="Voir" style="padding: 6px 8px; margin: 1px;">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @endcan

                                        {{-- Bouton Modifier --}}
                                        @can('modifier les utilisateurs')
                                            <a href="{{ route('users.edit', $user) }}" class="btn btn-primary btn-sm" title="Modifier" style="padding: 6px 8px; margin: 1px;">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan

                                        {{-- Bouton Supprimer --}}
                                        @can('supprimer les utilisateurs')
                                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline" style="margin: 1px;">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Supprimer"
                                                    onclick="return confirm('Confirmer la suppression ?')"
                                                    style="padding: 6px 8px;">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="{{ asset('assets/bundles/select2/dist/css/select2.min.css') }}">
<style>
    .card-header-action .btn { margin-left: 8px; }
    .card-header-action .dropdown { margin-left: 8px; }
    #etat-tabs .nav-link { font-weight: 600; }
    .select2-container--default .select2-selection--single {
        height: 38px !important;
        border: 1px solid #ced4da;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
    .badge-white {
        background: white;
        color: #495057;
        border: 1px solid #dee2e6;
    }

    /* Styles pour le tableau scrollable */
    .table-responsive {
        border: 1px solid #e3e6f0;
        border-radius: 8px;
    }

    /* En-tête fixe */
    .table thead th {
        background: #343a40;
        color: white;
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        font-size: 0.875rem;
        vertical-align: middle;
    }

    /* Amélioration des boutons */
    .btn-group .btn-sm {
        border-radius: 4px !important;
        transition: all 0.2s ease;
    }

    .btn-group .btn-sm:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    /* Style pour DataTables */
    .dataTables_wrapper {
        position: relative;
    }

    .dataTables_length,
    .dataTables_filter {
        margin-bottom: 15px;
    }

    /* Amélioration des lignes du tableau */
    .table tbody tr {
        transition: background-color 0.2s ease;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .table-responsive {
            max-height: 400px;
        }

        .card-header-action {
            text-align: center;
            margin-top: 10px;
        }

        .card-header-action .btn {
            margin: 2px;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="{{ asset('assets/bundles/select2/dist/js/select2.full.min.js') }}"></script>

<script>
$(document).ready(function() {
    // Initialisation Select2
    $('.select2').select2({
        placeholder: "Tous les états",
        allowClear: true,
        width: '100%'
    });

    var table = $('#users-table').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json',
            search: "Rechercher :",
            lengthMenu: "Afficher _MENU_ éléments",
            info: "Affichage de _START_ à _END_ sur _TOTAL_ éléments",
            paginate: {
                first: "Premier",
                last: "Dernier",
                next: "Suivant",
                previous: "Précédent"
            }
        },
        responsive: false, // Désactiver le responsive intégré car on gère le scroll
        pageLength: 25,
        order: [[1, 'desc']],
        dom: '<"top"lf>rt<"bottom"ip><"clear">',
        scrollX: true,
        scrollY: false, // On gère le scroll manuellement avec le container
        scrollCollapse: false,
        columnDefs: [
            { orderable: false, targets: [6] }, // Désactiver le tri sur la colonne actions
            { width: "120px", targets: 0 }, // Référence
            { width: "100px", targets: 1 }, // Date
            { width: "150px", targets: 2 }, // Client
            { width: "200px", targets: 3 }, // Motif
            { width: "100px", targets: 4 }, // État
            { width: "150px", targets: 5 }, // Créé par
            { width: "180px", targets: 6 } // Actions
        ],
        fixedColumns: false
    });

    // Filtres
    $('#search-input').on('keyup', function() {
        table.search(this.value).draw();
        updateCounts();
    });

    $('#statut-filter').on('change', function() {
        var val = $(this).val();
        table.columns(4).search(val ? '^' + val + '$' : '', true, false).draw();
        updateCounts();
    });


    // Filtre par onglet
    $('#etat-tabs .nav-link').on('click', function(e) {
        e.preventDefault();
        $('#etat-tabs .nav-link').removeClass('active');
        $(this).addClass('active');
        var etat = $(this).data('etat');
        $('#statut-filter').val(etat).trigger('change');
    });

    // Réinitialiser
    $('#reset-filters').on('click', function() {
        $('#search-input').val('');
        $('#statut-filter').val('').trigger('change');
        table.search('').columns().search('').draw();
        updateCounts();
    });

    // Mise à jour des compteurs
    function updateCounts() {
        var data = table.rows({ search: 'applied' }).data();
        var total = data.length;
        var actifs = data.toArray().filter(row => row[4].includes('Actif')).length;
        var inactifs = data.toArray().filter(row => row[4].includes('Inactif')).length;

        $('#total-count').text(total);
        $('#actifs-count').text(actifs);
        $('#inactifs-count').text(inactifs);
    }

    updateCounts();
});
</script>

<script>
$(document).ready(function () {

    function filterUsers() {
        let search = $("#search-input").val().toLowerCase();
        let statut = $("#statut-filter").val();
        let poste = $("#poste-filter").val();

        $("#users-table tbody tr").filter(function () {

            let row = $(this);
            let text = row.text().toLowerCase();
            let statutText = row.find("td:nth-child(4)").text().trim();
            let posteText = row.find("td:nth-child(6)").text().trim();

            let matchSearch = text.indexOf(search) > -1;
            let matchStatut = (statut === "" || statutText === statut);
            let matchPoste = (poste === "" || posteText === poste);

            row.toggle(matchSearch && matchStatut && matchPoste);
        });
    }

    $("#search-input").on("keyup", filterUsers);
    $("#statut-filter").on("change", filterUsers);
    $("#poste-filter").on("change", filterUsers);

    $("#reset-filters").on("click", function () {
        $("#search-input").val("");
        $("#statut-filter").val("").trigger("change");
        $("#poste-filter").val("").trigger("change");
        filterUsers();
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    $('.toggle-status').on('change', function () {
        let userId = $(this).data('id');
        let description = $(this).closest('label').find('.custom-switch-description');

        $.ajax({
            url: "/users/" + userId + "/toggle-status",
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function (response) {
                if (response.success) {
                    description.text(response.status);

                    Swal.fire({
                        icon: 'success',
                        title: 'Statut mis à jour',
                        text: 'Le statut de l’utilisateur a été modifié.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Impossible de modifier le statut.',
                });
            }
        });
    });
});
</script>


@endpush
