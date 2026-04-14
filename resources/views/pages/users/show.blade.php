@extends('layaout')

@section('title', 'Profil de ' . $user->nom)

@section('content')
<section class="section">
    <div class="section-body">
        <!-- Breadcrumb -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body py-3">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0 bg-transparent">
                                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Utilisateurs</a></li>
                                    <li class="breadcrumb-item active">{{ $user->nom }}</li>
                                </ol>
                            </nav>

                            <div class="mt-3 mt-md-0">
                                @if(auth()->user()->hasRole('admin|super-admin') && auth()->id() != $user->id)
                                    @if($user->is_active)
                                        <form action="{{ route('user-profile.deactivate', $user->id) }}" method="POST" class="d-inline">
                                            @csrf @method('PUT')
                                            <button type="submit" class="btn btn-warning btn-sm"
                                                    onclick="return confirm('Désactiver cet utilisateur ?')">
                                                <i class="fas fa-user-slash"></i> Désactiver
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('user-profile.activate', $user->id) }}" method="POST" class="d-inline">
                                            @csrf @method('PUT')
                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="fas fa-user-check"></i> Activer
                                            </button>
                                        </form>
                                    @endif
                                @endif

                                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm ml-2">
                                    <i class="fas fa-arrow-left"></i> Retour
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Colonne gauche: Profil utilisateur -->
            <div class="col-lg-4">
                <!-- Carte profil -->
                <div class="card card-primary shadow-sm">
                    <div class="card-body text-center py-5">
                        @if($user->photo)
                            <img src="{{ asset('storage/' . $user->photo) }}"
                                 alt="Photo de {{ $user->nom }}"
                                 class="rounded-circle mb-3 shadow"
                                 style="width: 160px; height: 160px; object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3 shadow"
                                 style="width: 160px; height: 160px; font-size: 60px; border: 3px solid #dee2e6;">
                                <i class="fas fa-user text-muted"></i>
                            </div>
                        @endif

                        <h4 class="mb-1">{{ $user->prenom }} {{ $user->nom }}</h4>
                        <p class="text-muted mb-2">{{ $user->poste?->intitule ?? 'Poste non défini' }}</p>

                        <div class="mb-3">
                            <span class="badge badge-lg {{ $user->is_active ? 'badge-success' : 'badge-danger' }}">
                                {{ $user->is_active ? 'ACTIF' : 'INACTIF' }}
                            </span>

                            @php
                                $role = $user->roles->first();
                                $roleNames = [
                                    'super-admin'            => 'Super Administrateur',
                                    'admin'                  => 'Administrateur',
                                    'responsable-conformite' => 'Responsable Conformité',
                                    'auditeur'               => 'Auditeur Interne',
                                    'gestionnaire-plaintes'  => 'Gestionnaire des Plaintes',
                                    'agent'                  => 'Agent de Traitement',
                                    'user'                   => 'Utilisateur Standard',
                                ];
                                $displayRole = 'Aucun rôle';
                                if ($role) {
                                    $displayRole = $roleNames[$role->name] ?? ucwords(str_replace('-', ' ', $role->name));
                                }
                            @endphp

                            <span class="badge badge-info ml-2">
                                {{ $displayRole }}
                            </span>
                        </div>

                        @if($user->photo)
                            <a href="{{ route('user-profile.download-photo', $user->id) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download"></i> Télécharger la photo
                            </a>
                        @endif
                    </div>

                    <div class="card-footer bg-light">
                        <div class="row text-center small">
                            <div class="col border-right">
                                <div class="text-muted">Téléphone</div>
                                <strong class="d-block">{{ $user->telephone ?? '-' }}</strong>
                            </div>
                            <div class="col">
                                <div class="text-muted">Email</div>
                                <strong class="d-block text-truncate" style="max-width: 150px;">{{ $user->email }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informations du compte -->
                <div class="card card-primary mt-4">
                    <div class="card-header">
                        <h5><i class="fas fa-user-circle mr-2"></i>Informations du compte</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between px-3">
                            <span class="text-muted">Nom d'utilisateur</span>
                            <span class="font-weight-bold">{{ $user->username }}</span>
                        </div>

                        {{-- Supérieur hiérarchique --}}
                        <div class="list-group-item d-flex justify-content-between px-3">
                            <span class="text-muted">Supérieur hiérarchique</span>
                            <span>
                                @if($user->manager)
                                    <a href="{{ route('users.show', $user->manager->id) }}" class="text-dark font-weight-bold">
                                        <i class="fas fa-user-tie mr-1"></i>
                                        {{ $user->manager->prenom }} {{ $user->manager->nom }}
                                        @if($user->manager->roles->isNotEmpty())
                                            <br>
                                            <small class="text-muted">{{ $user->manager->roles->first()->name }}</small>
                                        @endif
                                    </a>
                                @else
                                    <span class="text-muted font-italic">Non défini</span>
                                @endif
                            </span>
                        </div>

                        <div class="list-group-item d-flex justify-content-between px-3">
                            <span class="text-muted">Créé le</span>
                            <span>{{ $user->created_at->format('d/m/Y à H:i') }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-3">
                            <span class="text-muted">Créé par</span>
                            <span>{{ $user->creator->fullName ?? 'Système' }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-3">
                            <span class="text-muted">Dernière modification</span>
                            <span>{{ $user->updated_at->format('d/m/Y à H:i') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Dernier document (certificat ou attestation) -->
                @if(isset($statistiques['dernier_certificat']) || isset($statistiques['derniere_attestation']))
                <div class="card card-primary mt-4">
                    <div class="card-header">
                        <h5><i class="fas fa-file-alt mr-2"></i>Dernier document</h5>
                    </div>
                    <div class="card-body">
                        @if(isset($statistiques['dernier_certificat']))
                            <div class="text-center">
                                <i class="fas fa-file-medical fa-2x text-success mb-2"></i>
                                <p class="mb-1">
                                    <strong>Certificat</strong><br>
                                    {{ $statistiques['dernier_certificat']->created_at->format('d/m/Y') }}
                                </p>
                                <a href="{{ route('certificats.show', $statistiques['dernier_certificat']->id) }}" class="btn btn-sm btn-outline-primary">
                                    Voir le certificat
                                </a>
                            </div>
                        @elseif(isset($statistiques['derniere_attestation']))
                            <div class="text-center">
                                <i class="fas fa-file-contract fa-2x text-info mb-2"></i>
                                <p class="mb-1">
                                    <strong>Attestation de travail</strong><br>
                                    {{ $statistiques['derniere_attestation']->created_at->format('d/m/Y') }}
                                </p>
                                <a href="{{ route('attestations.show', $statistiques['derniere_attestation']->id) }}" class="btn btn-sm btn-outline-primary">
                                    Voir l'attestation
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <!-- Colonne droite: Statistiques et activités -->
            <div class="col-lg-8">
                <!-- Statistiques (congés, certificats, attestations) -->
                <div class="row mb-4">
                    <!-- Congés pris -->
                    <div class="col-xl-4 col-lg-6 col-md-6 mb-3">
                        <div class="card card-statistic-2">
                            <div class="card-icon bg-primary">
                                <i class="fas fa-umbrella-beach"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header"><h6>Congés pris</h6></div>
                                <div class="card-body h4">{{ $statistiques['conges_pris'] ?? 0 }} jours</div>
                            </div>
                        </div>
                    </div>

                    <!-- Congés en attente -->
                    <div class="col-xl-4 col-lg-6 col-md-6 mb-3">
                        <div class="card card-statistic-2">
                            <div class="card-icon bg-warning">
                                <i class="fas fa-hourglass-half"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header"><h6>Congés en attente</h6></div>
                                <div class="card-body h4">{{ $statistiques['conges_en_attente'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Certificats délivrés -->
                    <div class="col-xl-4 col-lg-6 col-md-6 mb-3">
                        <div class="card card-statistic-2">
                            <div class="card-icon bg-success">
                                <i class="fas fa-file-medical"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header"><h6>Certificats délivrés</h6></div>
                                <div class="card-body h4">{{ $statistiques['certificats_count'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Attestations de travail -->
                    <div class="col-xl-4 col-lg-6 col-md-6 mb-3">
                        <div class="card card-statistic-2">
                            <div class="card-icon bg-info">
                                <i class="fas fa-file-contract"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header"><h6>Attestations de travail</h6></div>
                                <div class="card-body h4">{{ $statistiques['attestations_count'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Total documents (optionnel) -->
                    <div class="col-xl-4 col-lg-6 col-md-6 mb-3">
                        <div class="card card-statistic-2">
                            <div class="card-icon bg-secondary">
                                <i class="fas fa-folder-open"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header"><h6>Total documents</h6></div>
                                <div class="card-body h4">
                                    {{ ($statistiques['certificats_count'] ?? 0) + ($statistiques['attestations_count'] ?? 0) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activités récentes : onglets Congés / Certificats / Attestations -->
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-history mr-2"></i>Documents et demandes récentes</h4>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="activityTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="conges-tab" data-toggle="tab" href="#conges" role="tab">
                                    <i class="fas fa-umbrella-beach mr-1"></i> Congés
                                    <span class="badge badge-primary ml-2">{{ $user->conges->count() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="certificats-tab" data-toggle="tab" href="#certificats" role="tab">
                                    <i class="fas fa-file-medical mr-1"></i> Certificats
                                    <span class="badge badge-success ml-2">{{ $user->certificats->count() ?? 0 }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="attestations-tab" data-toggle="tab" href="#attestations" role="tab">
                                    <i class="fas fa-file-contract mr-1"></i> Attestations
                                    <span class="badge badge-info ml-2">{{ $user->attestations->count() ?? 0 }}</span>
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content mt-4">
                            <!-- Onglet Congés -->
                            <div class="tab-pane fade show active" id="conges" role="tabpanel">
                                @if($user->conges->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Type</th>
                                                    <th>Date début</th>
                                                    <th>Date fin</th>
                                                    <th>Nombre de jours</th>
                                                    <th>Statut</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($user->conges as $conge)
                                                    <tr>
                                                        <td>{{ ucfirst($conge->typeConge->libelle ?? $conge->type) }}</td>
                                                        <td>{{ $conge->date_debut->format('d/m/Y') }}</td>
                                                        <td>{{ $conge->date_fin->format('d/m/Y') }}</td>
                                                        <td><strong>{{ $conge->nombre_jours }}</strong></td>
                                                        <td>
                                                            <span class="badge badge-{{ $conge->statut == 'approuvé' ? 'success' : ($conge->statut == 'en_attente' ? 'warning' : 'secondary') }}">
                                                                {{ ucfirst($conge->statut) }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <a href="{{ route('conges.show', $conge->id) }}"
                                                               class="btn btn-sm btn-info" title="Voir détails">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-umbrella-beach fa-3x mb-3"></i>
                                        <p>Aucune demande de congé</p>
                                    </div>
                                @endif
                            </div>

                            <!-- Onglet Certificats -->
                            <div class="tab-pane fade" id="certificats" role="tabpanel">
                                @if(isset($user->certificats) && $user->certificats->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Référence</th>
                                                    <th>Date d'émission</th>
                                                    <th>Type</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($user->certificats as $certificat)
                                                    <tr>
                                                        <td>{{ $certificat->reference ?? 'N°'.$certificat->id }}</td>
                                                        <td>{{ $certificat->date_emission->format('d/m/Y') }}</td>
                                                        <td>{{ ucfirst($certificat->type_certificat ?? 'Médical') }}</td>
                                                        <td>
                                                            <a href="{{ route('certificats.show', $certificat->id) }}"
                                                               class="btn btn-sm btn-info">
                                                                <i class="fas fa-eye"></i> Voir
                                                            </a>
                                                            @if($certificat->fichier)
                                                                <a href="{{ asset('storage/'.$certificat->fichier) }}"
                                                                   class="btn btn-sm btn-secondary" download>
                                                                    <i class="fas fa-download"></i>
                                                                </a>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-file-medical fa-3x mb-3"></i>
                                        <p>Aucun certificat enregistré</p>
                                    </div>
                                @endif
                            </div>

                            <!-- Onglet Attestations -->
                            <div class="tab-pane fade" id="attestations" role="tabpanel">
                                @if(isset($user->attestations) && $user->attestations->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Référence</th>
                                                    <th>Date d'émission</th>
                                                    <th>Motif</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($user->attestations as $attestation)
                                                    <tr>
                                                        <td>{{ $attestation->reference ?? 'N°'.$attestation->id }}</td>
                                                        <td>{{ $attestation->date_emission->format('d/m/Y') }}</td>
                                                        <td>{{ ucfirst($attestation->motif ?? 'Travail') }}</td>
                                                        <td>
                                                            <a href="{{ route('attestations.show', $attestation->id) }}"
                                                               class="btn btn-sm btn-info">
                                                                <i class="fas fa-eye"></i> Voir
                                                            </a>
                                                            @if($attestation->fichier)
                                                                <a href="{{ asset('storage/'.$attestation->fichier) }}"
                                                                   class="btn btn-sm btn-secondary" download>
                                                                    <i class="fas fa-download"></i>
                                                                </a>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-file-contract fa-3x mb-3"></i>
                                        <p>Aucune attestation de travail</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Administration -->
                <div class="card mt-4">
                    <div class="card-header bg-dark text-white">
                        <h5><i class="fas fa-user-cog mr-2"></i>Administration</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('user-profile.edit', $user->id) }}" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Éditer le profil
                            </a>
                            {{-- Les exports ont été supprimés car plus pertinents --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Activation des onglets
        $('#activityTab a').on('click', function (e) {
            e.preventDefault();
            $(this).tab('show');
        });

        // Animation des cartes statistiques au survol
        $('.card-statistic-2').hover(
            function() {
                $(this).addClass('shadow-lg');
            },
            function() {
                $(this).removeClass('shadow-lg');
            }
        );

        // Initialisation des tooltips
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
@endsection

@section('styles')
<style>
    .card-statistic-2 {
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .card-statistic-2:hover {
        transform: translateY(-5px);
    }

    .tab-content {
        min-height: 300px;
    }

    .badge-lg {
        font-size: 0.9rem;
        padding: 0.5rem 0.75rem;
    }

    .gap-2 {
        gap: 0.5rem;
    }

    .table td {
        vertical-align: middle;
    }
</style>
@endsection