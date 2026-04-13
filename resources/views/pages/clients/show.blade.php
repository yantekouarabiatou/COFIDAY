@extends('layaout')

@section('title', 'Détails du Client')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-user"></i> Détails du Client</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('clients.index') }}">Clients</a></div>
            <div class="breadcrumb-item active">{{ $client->nom }}</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Informations du Client</h4>
                        <div class="card-header-action">
                            <a href="{{ route('clients.edit', $client) }}" class="btn btn-icon icon-left btn-warning">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                            <a href="{{ route('clients.index') }}" class="btn btn-icon icon-left btn-primary">
                                <i class="fas fa-arrow-left"></i> Retour à la liste
                            </a>
                            <button type="button" class="btn btn-icon icon-left btn-danger" data-toggle="modal" data-target="#deleteModal">
                                <i class="fas fa-trash"></i> Supprimer
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <!-- Colonne de gauche : Informations principales -->
                            <div class="col-md-8">
                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <div class="d-flex align-items-center">
                                            @if($client->logo)
                                                <div class="mr-3">
                                                    <img src="{{ asset('storage/' . $client->logo) }}"
                                                         alt="Logo {{ $client->nom }}"
                                                         class="img-fluid rounded-circle"
                                                         style="width: 80px; height: 80px; object-fit: cover;">
                                                </div>
                                            @endif
                                            <div>
                                                <h2 class="mb-0">{{ $client->nom }}</h2>
                                                <span class="badge badge-{{ $client->statut == 'actif' ? 'success' : ($client->statut == 'prospect' ? 'warning' : 'secondary') }}">
                                                    {{ ucfirst($client->statut) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-box mb-4">
                                            <div class="info-box-content">
                                                <span class="info-box-text">Contact Principal</span>
                                                <span class="info-box-number">
                                                    @if($client->contact_principal)
                                                        {{ $client->contact_principal }}
                                                    @else
                                                        <span class="text-muted">Non spécifié</span>
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="info-box-icon bg-info">
                                                <i class="fas fa-user-tie"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="info-box mb-4">
                                            <div class="info-box-content">
                                                <span class="info-box-text">Téléphone</span>
                                                <span class="info-box-number">
                                                    @if($client->telephone)
                                                        <a href="tel:{{ $client->telephone }}">{{ $client->telephone }}</a>
                                                    @else
                                                        <span class="text-muted">Non spécifié</span>
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="info-box-icon bg-success">
                                                <i class="fas fa-phone"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-box mb-4">
                                            <div class="info-box-content">
                                                <span class="info-box-text">Email</span>
                                                <span class="info-box-number">
                                                    @if($client->email)
                                                        <a href="mailto:{{ $client->email }}">{{ $client->email }}</a>
                                                    @else
                                                        <span class="text-muted">Non spécifié</span>
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="info-box-icon bg-danger">
                                                <i class="fas fa-envelope"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="info-box mb-4">
                                            <div class="info-box-content">
                                                <span class="info-box-text">Site Web</span>
                                                <span class="info-box-number">
                                                    @if($client->site_web)
                                                        <a href="{{ $client->site_web }}" target="_blank">
                                                            {{ str_replace(['https://', 'http://', 'www.'], '', $client->site_web) }}
                                                        </a>
                                                    @else
                                                        <span class="text-muted">Non spécifié</span>
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="info-box-icon bg-warning">
                                                <i class="fas fa-globe"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if($client->adresse || $client->siege_social)
                                <div class="card card-primary mb-4">
                                    <div class="card-header">
                                        <h4><i class="fas fa-map-marker-alt"></i> Adresse</h4>
                                    </div>
                                    <div class="card-body">
                                        @if($client->adresse)
                                            <p class="mb-2">{{ $client->adresse }}</p>
                                        @endif
                                        @if($client->siege_social)
                                            <p class="mb-0">
                                                <strong>Siège social :</strong> {{ $client->siege_social }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                @endif

                                @if($client->secteur_activite || $client->numero_siret || $client->code_naf)
                                <div class="card card-secondary mb-4">
                                    <div class="card-header">
                                        <h4><i class="fas fa-briefcase"></i> Informations Professionnelles</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            @if($client->secteur_activite)
                                                <div class="col-md-4">
                                                    <strong>Secteur d'activité :</strong>
                                                    <p>{{ $client->secteur_activite }}</p>
                                                </div>
                                            @endif
                                            @if($client->numero_siret)
                                                <div class="col-md-4">
                                                    <strong>Numéro IFU :</strong>
                                                    <p>{{ $client->numero_siret }}</p>
                                                </div>
                                            @endif
                                            <!-- @if($client->code_naf)
                                                <div class="col-md-4">
                                                    <strong>Code NAF :</strong>
                                                    <p>{{ $client->code_naf }}</p>
                                                </div>
                                            @endif -->
                                        </div>
                                    </div>
                                </div>
                                @endif

                                @if($client->notes)
                                <div class="card card-success mb-4">
                                    <div class="card-header">
                                        <h4><i class="fas fa-sticky-note"></i> Notes</h4>
                                    </div>
                                    <div class="card-body">
                                        <p>{{ $client->notes }}</p>
                                    </div>
                                </div>
                                @endif
                            </div>

                            <!-- Colonne de droite : Informations techniques et actions -->
                            <div class="col-md-4">
                                <div class="card card-statistic-1 mb-4">
                                    <div class="card-icon bg-primary">
                                        <i class="fas fa-calendar"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Date de création</h4>
                                        </div>
                                        <div class="card-body">
                                            {{ $client->created_at->format('d/m/Y') }}
                                        </div>
                                    </div>
                                </div>

                                <div class="card card-statistic-1 mb-4">
                                    <div class="card-icon bg-info">
                                        <i class="fas fa-sync"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Dernière modification</h4>
                                        </div>
                                        <div class="card-body">
                                            {{ $client->updated_at->format('d/m/Y H:i') }}
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-header">
                                        <h4>Actions rapides</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="list-group">
                                            <a href="mailto:{{ $client->email ?? '#' }}"
                                               class="list-group-item list-group-item-action {{ !$client->email ? 'disabled' : '' }}">
                                                <i class="fas fa-envelope mr-2"></i> Envoyer un email
                                            </a>
                                            <a href="tel:{{ $client->telephone ?? '#' }}"
                                               class="list-group-item list-group-item-action {{ !$client->telephone ? 'disabled' : '' }}">
                                                <i class="fas fa-phone mr-2"></i> Appeler
                                            </a>
                                            <a href="#" class="list-group-item list-group-item-action" onclick="window.print()">
                                                <i class="fas fa-print mr-2"></i> Imprimer cette page
                                            </a>
                                            <a href="{{ route('clients.create') }}" class="list-group-item list-group-item-action">
                                                <i class="fas fa-user-plus mr-2"></i> Ajouter un autre client
                                            </a>
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

<!-- Modal de confirmation de suppression -->
<div class="modal fade" tabindex="-1" role="dialog" id="deleteModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmation de suppression</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer le client <strong>{{ $client->nom }}</strong> ?</p>
                <p class="text-danger">Cette action est irréversible.</p>
            </div>
            <div class="modal-footer bg-whitesmoke br">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <form action="{{ route('clients.destroy', $client) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Supprimer définitivement</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .info-box {
        min-height: 90px;
    }
    .info-box-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 70px;
        font-size: 30px;
    }
    .info-box-content {
        padding: 10px;
    }
    .badge {
        font-size: 0.8em;
        padding: 5px 10px;
    }
    .card-statistic-1 .card-icon {
        width: 60px;
        height: 60px;
        line-height: 60px;
        font-size: 24px;
    }
    .list-group-item.disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Gestion de la suppression avec SweetAlert
        $('form[action*="destroy"]').on('submit', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Êtes-vous sûr ?',
                text: "Cette action supprimera définitivement le client !",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Oui, supprimer !',
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });

        // Print functionality
        function printPage() {
            window.print();
        }

        // Email validation for disabled links
        $('a[href^="mailto:#"]').on('click', function(e) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Email non disponible',
                text: 'Aucune adresse email n\'est enregistrée pour ce client.'
            });
        });

        // Phone validation for disabled links
        $('a[href^="tel:#"]').on('click', function(e) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Téléphone non disponible',
                text: 'Aucun numéro de téléphone n\'est enregistré pour ce client.'
            });
        });
    });
</script>
@endpush
