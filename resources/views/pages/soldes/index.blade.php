@extends('layaout')

@section('title', 'Gestion des soldes de congés')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<style>
    .table td, .table th {
        vertical-align: middle;
    }
</style>
@endpush

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-wallet"></i> Soldes de congés</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active">Soldes</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Liste des soldes</h4>
                        <div class="card-header-action">
                            <a href="{{ route('admin.soldes.create') }}" class="btn btn-icon icon-left btn-success">
                                <i class="fas fa-plus"></i> Nouveau solde
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="soldes-table">
                                <thead>
                                    <tr>
                                        <th>Année</th>
                                        <th>Utilisateur</th>
                                        <th>Acquis</th>
                                        <th>Pris</th>
                                        <th>Reportés</th>
                                        <th>Restants</th>
                                        <th style="width: 120px">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    var table = $('#soldes-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.soldes.index') }}",
        columns: [
            { data: 'annee', name: 'annee' },
            { data: 'user_name', name: 'user_name', orderable: false },
            { data: 'jours_acquis', name: 'jours_acquis', className: 'text-center' },
            { data: 'jours_pris', name: 'jours_pris', className: 'text-center' },
            { data: 'jours_reportes', name: 'jours_reportes', className: 'text-center' },
            { data: 'jours_restants', name: 'jours_restants', className: 'text-center' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
        ],
        order: [[0, 'desc']],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
        }
    });

    // Gestion de la suppression avec SweetAlert
    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Êtes-vous sûr ?',
            text: "Cette action est irréversible !",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.soldes.index") }}/' + id,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            table.ajax.reload();
                            Swal.fire('Supprimé!', 'Le solde a été supprimé.', 'success');
                        } else {
                            Swal.fire('Erreur!', 'Une erreur est survenue.', 'error');
                        }
                    }
                });
            }
        });
    });
});
</script>
@endpush
