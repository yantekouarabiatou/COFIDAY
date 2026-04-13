@extends('layaout')

@section('title', 'Gestion des Postes')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-briefcase"></i> Gestion des Postes</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Postes</div>
        </div>
    </div>

    <div class="section-body">

        <div class="card">
            <div class="card-header">
                <h4>Liste des Postes</h4>
                <div class="card-header-action">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#createPosteModal">
                        <i class="fas fa-plus"></i> Nouveau Poste
                    </button>
                </div>
            </div>

            <div class="card-body">
                <table class="table table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>Intitulé</th>
                            <th>Description</th>
                            <th width="150" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($postes as $poste)
                        <tr>
                            <td>{{ $poste->intitule }}</td>
                            <td>{{ $poste->description ?? '-' }}</td>
                            <td class="text-center">
                                <button class="btn btn-warning btn-sm editPosteBtn"
                                        data-id="{{ $poste->id }}"
                                        data-intitule="{{ $poste->intitule }}"
                                        data-description="{{ $poste->description }}"
                                        data-toggle="modal"
                                        data-target="#editPosteModal">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <form action="{{ route('postes.destroy', $poste) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm confirm-delete" data-reference="{{ $poste->id }}" style="padding: 6px 8px; margin: 1px;" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</section>


{{-- MODAL CREATION --}}
<div class="modal fade" id="createPosteModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('postes.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Créer un Poste</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                <div class="form-group">
                    <label>Intitulé *</label>
                    <input type="text" name="intitule" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Description (facultatif)</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <button class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>


{{-- MODAL EDIT --}}
<div class="modal fade" id="editPosteModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="editPosteForm" method="POST" class="modal-content">
            @csrf @method('PUT')

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Modifier le Poste</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                <div class="form-group">
                    <label>Intitulé *</label>
                    <input type="text" name="intitule" id="editIntitule" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Description (facultatif)</label>
                    <textarea name="description" id="editDescription" class="form-control" rows="3"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                <button class="btn btn-primary">Modifier</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* Fix pour que les modals s'affichent au-dessus de tout */
    /* Fix pour que les modals s'affichent au-dessus de tout */
    .modal {
        z-index: 9999 !important;
        margin-top: 70px;
    }
    .modal-backdrop {
        z-index: 9998 !important;
    }
    body:before {
        z-index: 1040 !important; /* inférieur à la modal-backdrop */
    }


</style>
@endpush

@push('scripts')
<script>
    // Remplir le modal d'édition
    $('.editPosteBtn').on('click', function () {
        let id = $(this).data('id');
        let intitule = $(this).data('intitule');
        let description = $(this).data('description');

        $('#editIntitule').val(intitule);
        $('#editDescription').val(description);

        let url = "/postes/" + id;
        $('#editPosteForm').attr('action', url);
    });

    $(document).on('click', '.confirm-delete', function(e) {
        e.preventDefault();
        let poste_id = $(this).data('poste_id');
        const form = $(this).closest('form');

        Swal.fire({
            title: 'Êtes-vous sûr ?',
            text: "La plainte #"+ poste_id +"sera supprimée définitivement !",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
</script>


@endpush
