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

                <!-- Dernière activité -->
                @if($statistiques['derniere_entree'])
                <div class="card card-primary mt-4">
                    <div class="card-header">
                        <h5><i class="fas fa-clock mr-2"></i>Dernière saisie</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            <h4 class="text-info mb-2">
                                {{ $statistiques['derniere_entree']->jour->format('d/m/Y') }}
                            </h4>
                            <p class="mb-1">
                                <strong>{{ $statistiques['derniere_entree']->heures_reelles }}h</strong>
                                / {{ $statistiques['derniere_entree']->heures_theoriques }}h
                            </p>
                            <span class="badge badge-{{ $statistiques['derniere_entree']->statut == 'validé' ? 'success' : ($statistiques['derniere_entree']->statut == 'soumis' ? 'info' : 'warning') }}">
                                {{ ucfirst($statistiques['derniere_entree']->statut) }}
                            </span>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Colonne droite: Statistiques et activités -->
            <div class="col-lg-8">
                <!-- Statistiques de temps -->
                <div class="row mb-4">
                    <!-- Heures du mois -->
                    <div class="col-xl-4 col-lg-6 col-md-6 mb-3">
                        <div class="card card-statistic-2">
                            <div class="card-icon bg-primary">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header"><h6>Heures ce mois</h6></div>
                                <div class="card-body h4">{{ $statistiques['heures_mois_en_cours'] }}h</div>
                            </div>
                        </div>
                    </div>

                    <!-- Taux de réalisation -->
                    <div class="col-xl-4 col-lg-6 col-md-6 mb-3">
                        <div class="card card-statistic-2">
                            <div class="card-icon {{ $statistiques['taux_realisation'] >= 100 ? 'bg-success' : 'bg-warning' }}">
                                <i class="fas fa-percentage"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header"><h6>Taux réalisation</h6></div>
                                <div class="card-body h4">{{ $statistiques['taux_realisation'] }}%</div>
                            </div>
                        </div>
                    </div>

                    <!-- Écart heures -->
                    <div class="col-xl-4 col-lg-6 col-md-6 mb-3">
                        <div class="card card-statistic-2">
                            <div class="card-icon {{ $statistiques['ecart_heures'] >= 0 ? 'bg-success' : 'bg-danger' }}">
                                <i class="fas fa-balance-scale"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header"><h6>Écart heures</h6></div>
                                <div class="card-body h4">
                                    {{ $statistiques['ecart_heures'] > 0 ? '+' : '' }}{{ $statistiques['ecart_heures'] }}h
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Journées validées -->
                    <div class="col-xl-4 col-lg-6 col-md-6 mb-3">
                        <div class="card card-statistic-2">
                            <div class="card-icon bg-success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header"><h6>Journées validées</h6></div>
                                <div class="card-body h4">{{ $statistiques['journees_validees'] }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Journées en attente -->
                    <div class="col-xl-4 col-lg-6 col-md-6 mb-3">
                        <div class="card card-statistic-2">
                            <div class="card-icon bg-info">
                                <i class="fas fa-hourglass-half"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header"><h6>En attente</h6></div>
                                <div class="card-body h4">{{ $statistiques['journees_en_attente'] }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Congés pris -->
                    <div class="col-xl-4 col-lg-6 col-md-6 mb-3">
                        <div class="card card-statistic-2">
                            <div class="card-icon bg-warning">
                                <i class="fas fa-umbrella-beach"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header"><h6>Congés pris</h6></div>
                                <div class="card-body h4">{{ $statistiques['conges_pris'] }} jours</div>
                            </div>
                        </div>
                    </div>

                    <!-- Total saisies -->
                    <div class="col-xl-4 col-lg-6 col-md-6 mb-3">
                        <div class="card card-statistic-2">
                            <div class="card-icon bg-secondary">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header"><h6>Total journées</h6></div>
                                <div class="card-body h4">{{ $statistiques['total_daily_entries'] }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Total TimeEntries -->
                    <div class="col-xl-4 col-lg-6 col-md-6 mb-3">
                        <div class="card card-statistic-2">
                            <div class="card-icon bg-info">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header"><h6>Saisies temps</h6></div>
                                <div class="card-body h4">{{ $statistiques['total_time_entries'] }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Congés en attente -->
                    <div class="col-xl-4 col-lg-6 col-md-6 mb-3">
                        <div class="card card-statistic-2">
                            <div class="card-icon bg-danger">
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header"><h6>Congés en attente</h6></div>
                                <div class="card-body h4">{{ $statistiques['conges_en_attente'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activités récentes -->
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-history mr-2"></i>Activités récentes</h4>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="activityTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="daily-tab" data-toggle="tab" href="#daily" role="tab">
                                    <i class="fas fa-calendar-day mr-1"></i> Journées
                                    <span class="badge badge-primary ml-2">{{ $user->dailyEntries->count() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="time-tab" data-toggle="tab" href="#time" role="tab">
                                    <i class="fas fa-clock mr-1"></i> Saisies temps
                                    <span class="badge badge-info ml-2">{{ $user->timeEntries->count() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="conges-tab" data-toggle="tab" href="#conges" role="tab">
                                    <i class="fas fa-umbrella-beach mr-1"></i> Congés
                                    <span class="badge badge-warning ml-2">{{ $user->conges->count() }}</span>
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content mt-4">
                            <!-- Onglet Journées -->
                            <div class="tab-pane fade show active" id="daily" role="tabpanel">
                                @if($user->dailyEntries->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Heures réelles</th>
                                                    <th>Heures théoriques</th>
                                                    <th>Écart</th>
                                                    <th>Statut</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($user->dailyEntries as $entry)
                                                    <tr>
                                                        <td>{{ $entry->jour->format('d/m/Y') }}</td>
                                                        <td><strong>{{ $entry->heures_reelles }}h</strong></td>
                                                        <td>{{ $entry->heures_theoriques }}h</td>
                                                        <td>
                                                            <span class="badge badge-{{ $entry->ecart >= 0 ? 'success' : 'danger' }}">
                                                                {{ $entry->ecart > 0 ? '+' : '' }}{{ $entry->ecart }}h
                                                            </span>
                                                        </td>
                                                        <td>{!! $entry->statut_badge !!}</td>
                                                        <td>
                                                            <a href="{{ route('daily-entries.show', $entry->id) }}"
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
                                        <i class="fas fa-calendar-times fa-3x mb-3"></i>
                                        <p>Aucune journée enregistrée</p>
                                    </div>
                                @endif
                            </div>

                            <!-- Onglet Saisies temps -->
                            <div class="tab-pane fade" id="time" role="tabpanel">
                                @if($user->timeEntries->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Dossier</th>
                                                    <th>Heures</th>
                                                    <th>Plage horaire</th>
                                                    <th>Travaux</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($user->timeEntries as $timeEntry)
                                                    <tr>
                                                        <td>{{ $timeEntry->dailyEntry->jour->format('d/m/Y') }}</td>
                                                        <td>
                                                            @if($timeEntry->dossier)
                                                                <a href="{{ route('dossiers.show', $timeEntry->dossier_id) }}">
                                                                    {{ $timeEntry->dossier->nom }}
                                                                </a>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                        <td><strong>{{ $timeEntry->heures_reelles }}h</strong></td>
                                                        <td>{{ $timeEntry->plage }}</td>
                                                        <td>
                                                            <small>{{ Str::limit($timeEntry->travaux, 50) }}</small>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-clock fa-3x mb-3"></i>
                                        <p>Aucune saisie de temps enregistrée</p>
                                    </div>
                                @endif
                            </div>

                            <!-- Onglet Congés -->
                            <div class="tab-pane fade" id="conges" role="tabpanel">
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
                                                        <td>{{ ucfirst($conge->typeConge->libelle) }}</td>
                                                        <td>{{ $conge->date_debut->format('d/m/Y') }}</td>
                                                        <td>{{ $conge->date_fin->format('d/m/Y') }}</td>
                                                        <td><strong>{{ $conge->nombre_jours }}</strong></td>
                                                        <td>
                                                            <span class="badge badge-{{ $conge->statut == 'approuvé' ? 'success' : ($conge->statut == 'en_attente' ? 'warning' : 'success') }}">
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
                                        <p>Aucun congé enregistré</p>
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

                            @if(auth()->user()->hasRole('admin|super-admin'))
                                <a href="{{ route('user-profile.export-temps', ['id' => $user->id, 'format' => 'pdf']) }}"
                                   class="btn btn-danger">
                                    <i class="fas fa-file-pdf"></i> Export PDF
                                </a>
                                <a href="{{ route('user-profile.export-temps', ['id' => $user->id, 'format' => 'excel']) }}"
                                   class="btn btn-success">
                                    <i class="fas fa-file-excel"></i> Export Excel
                                </a>
                            @endif
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

        // Animation du pourcentage de réalisation
        const tauxElement = $('.card-statistic-2 .h4:contains("%")');
        if (tauxElement.length > 0) {
            const taux = parseFloat(tauxElement.text());
            if (taux >= 100) {
                tauxElement.closest('.card-statistic-2').addClass('border-success');
            } else if (taux >= 80) {
                tauxElement.closest('.card-statistic-2').addClass('border-warning');
            }
        }
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

    .border-success {
        border-left: 4px solid #28a745 !important;
    }

    .border-warning {
        border-left: 4px solid #ffc107 !important;
    }
</style>
@endsection
