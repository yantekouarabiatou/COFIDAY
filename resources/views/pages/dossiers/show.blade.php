@php
use App\Helpers\UserHelper;
use App\Models\User;
@endphp

@extends('layaout')

@section('title', 'Dossier - ' . $dossier->nom)

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-folder-open"></i> Dossier: {{ $dossier->nom }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('dossiers.index') }}">Dossiers</a></div>
            <div class="breadcrumb-item active">{{ $dossier->reference }}</div>
        </div>
    </div>

    <div class="section-body">
        <!-- Header avec actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card gradient-card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h2 class="text-white mb-1">{{ $dossier->nom }}</h2>
                                <div class="d-flex align-items-center">
                                    <span class="badge badge-light mr-2">{{ $dossier->reference }}</span>
                                    {!! $dossier->type_dossier_badge !!}
                                    {!! $dossier->statut_badge !!}
                                    @if($dossier->en_retard)
                                        <span class="badge badge-danger ml-2">
                                            <i class="fas fa-exclamation-triangle"></i> En retard
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4 text-right">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('dossiers.edit', $dossier) }}" class="btn btn-light btn-icon" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('missions.analyse.show', $dossier->id) }}" class="btn btn-light btn-icon" title="Analyser les performances">
                                        <i class="fas fa-chart-bar"></i>
                                    </a>
                                    <a href="{{ route('dossiers.index') }}" class="btn btn-light btn-icon" title="Retour">
                                        <i class="fas fa-arrow-left"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Colonne principale -->
            <div class="col-lg-8">
                <!-- Informations client -->
                <div class="card card-statistic">
                    <div class="card-header">
                        <h4><i class="fas fa-user-tie"></i> Client</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h5 class="mb-1">{{ $dossier->client->nom }}</h5>
                                @if($dossier->client->contact_principal)
                                <p class="text-muted mb-1">{{ $dossier->client->contact_principal }}</p>
                                @endif
                                <div class="contact-info">
                                    @if($dossier->client->telephone)
                                    <span class="mr-3"><i class="fas fa-phone mr-1"></i>{{ $dossier->client->telephone }}</span>
                                    @endif
                                    @if($dossier->client->email)
                                    <span><i class="fas fa-envelope mr-1"></i>{{ $dossier->client->email }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4 text-right">
                                <a href="{{ route('clients.show', $dossier->client) }}" class="btn btn-outline-primary">
                                    <i class="fas fa-external-link-alt"></i> Voir fiche
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tableau récapitulatif des heures par collaborateur -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4><i class="fas fa-clock"></i> Récapitulatif des heures par collaborateur</h4>
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="exportHours">
                                <i class="fas fa-download"></i> Exporter
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @php
                            $personnelsAvecTemps = $dossier->personnelsAvecTemps();
                            $totalHeuresGlobal = 0;
                            $totalInterventions = 0;
                        @endphp

                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="hoursTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Collaborateur</th>
                                        <th class="text-center">Heures totales</th>
                                        <th class="text-center">Interventions</th>
                                        <th class="text-center">Heure moyenne</th>
                                        <th class="text-center">Dernière activité</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($personnelsAvecTemps as $personnelData)
                                        @php
                                            $personnel = $personnelData->user;
                                            $chargeTotal = $personnelData->total_heures ?? 0;
                                            $nbInterventions = $personnelData->nb_interventions ?? 0;
                                            $heuresMoyennes = $nbInterventions > 0 ? $chargeTotal / $nbInterventions : 0;
                                            $derniereActivite = \App\Models\TimeEntry::where('user_id', $personnel->id)
                                                ->where('dossier_id', $dossier->id)
                                                ->latest()
                                                ->first();

                                            $totalHeuresGlobal += $chargeTotal;
                                            $totalInterventions += $nbInterventions;

                                            // Calcul du pourcentage par rapport aux heures théoriques
                                            $heuresTheoriques = $dossier->heure_theorique_sans_weekend ?? $dossier->heure_theorique_avec_weekend ?? 1;
                                            $pourcentage = $heuresTheoriques > 0 ? ($chargeTotal / $heuresTheoriques) * 100 : 0;
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm mr-3">
                                                        @if($personnel->photo && Storage::exists('public/' . $personnel->photo))
                                                            <img src="{{ Storage::url($personnel->photo) }}"
                                                                 class="rounded-circle"
                                                                 alt="{{ $personnel->prenom }} {{ $personnel->nom }}">
                                                        @else
                                                            @php
                                                                $colors = ['primary', 'success', 'warning', 'info', 'danger'];
                                                                $colorIndex = crc32($personnel->email) % count($colors);
                                                                $color = $colors[$colorIndex];
                                                            @endphp
                                                            <div class="avatar-initial rounded-circle bg-{{ $color }}">
                                                                {{ strtoupper(substr($personnel->prenom, 0, 1) . substr($personnel->nom, 0, 1)) }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <div class="font-weight-bold">{{ $personnel->prenom }} {{ $personnel->nom }}</div>
                                                        <small class="text-muted">{{ $personnel->poste->intitule ?? 'Non défini' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center align-middle">
                                                <div class="d-flex flex-column align-items-center">
                                                    <span class="badge badge-primary badge-pill px-3 py-2 mb-1">
                                                        {{ number_format($chargeTotal, 2) }}h
                                                    </span>
                                                    <div class="progress" style="height: 6px; width: 80px;">
                                                        <div class="progress-bar bg-primary" role="progressbar"
                                                             style="width: {{ min($pourcentage, 100) }}%"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center align-middle">
                                                <span class="badge badge-info">{{ $nbInterventions }}</span>
                                            </td>
                                            <td class="text-center align-middle">
                                                <span class="text-muted">{{ number_format($heuresMoyennes, 1) }}h</span>
                                            </td>
                                            <td class="text-center align-middle">
                                                @if($derniereActivite)
                                                    <small class="text-muted" title="{{ $derniereActivite->created_at->format('d/m/Y H:i') }}">
                                                        {{ $derniereActivite->created_at->diffForHumans() }}
                                                    </small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center align-middle">
                                                <a href="{{ route('missions.utilisateur.dossier', ['user' => $personnel->id, 'dossier' => $dossier->id]) }}"
                                                   class="btn btn-sm btn-outline-primary" title="Détails">
                                                    <i class="fas fa-chart-pie"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <div class="empty-state">
                                                    <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                                                    <h5 class="text-muted">Aucune heure enregistrée</h5>
                                                    <p class="text-muted">Aucun collaborateur n'a encore travaillé sur ce dossier.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if($personnelsAvecTemps->count() > 0)
                                <tfoot class="bg-light">
                                    <tr>
                                        <td><strong>Totaux</strong></td>
                                        <td class="text-center">
                                            <strong class="text-primary">{{ number_format($totalHeuresGlobal, 2) }}h</strong>
                                        </td>
                                        <td class="text-center">
                                            <strong>{{ $totalInterventions }}</strong>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $moyenneGenerale = $totalInterventions > 0 ? $totalHeuresGlobal / $totalInterventions : 0;
                                            @endphp
                                            <span class="text-muted">{{ number_format($moyenneGenerale, 1) }}h</span>
                                        </td>
                                        <td colspan="2">
                                            <small class="text-muted">{{ $personnelsAvecTemps->count() }} collaborateur(s)</small>
                                        </td>
                                    </tr>
                                </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Cartes d'informations -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-statistic">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="card-icon bg-primary">
                                        <i class="fas fa-calendar-plus"></i>
                                    </div>
                                    <div class="ml-3">
                                        <div class="card-title">Date d'ouverture</div>
                                        <div class="card-value">{{ $dossier->date_ouverture->format('d/m/Y') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-statistic">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="card-icon bg-warning">
                                        <i class="fas fa-calendar-check"></i>
                                    </div>
                                    <div class="ml-3">
                                        <div class="card-title">Clôture prévue</div>
                                        <div class="card-value">
                                            @if($dossier->date_cloture_prevue)
                                                {{ $dossier->date_cloture_prevue->format('d/m/Y') }}
                                            @else
                                                <span class="text-muted">Non définie</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Budget et heures -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="card card-statistic">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="card-icon bg-success">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </div>
                                    <div class="ml-3">
                                        <div class="card-title">Budget</div>
                                        <div class="card-value">{{ $dossier->budget_formate }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-statistic">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="card-icon bg-info">
                                        <i class="fas fa-briefcase-clock"></i>
                                    </div>
                                    <div class="ml-3">
                                        <div class="card-title">Heures théo. (sans WE)</div>
                                        <div class="card-value">{{ UserHelper::hoursToHoursMinutes($dossier->heure_theorique_sans_weekend) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-statistic">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="card-icon bg-secondary">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="ml-3">
                                        <div class="card-title">Heures réelles</div>
                                        <div class="card-value text-dark">{{ number_format($totalHeuresGlobal, 2) }}h</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description et notes -->
                @if($dossier->description || $dossier->notes)
                <div class="row">
                    @if($dossier->description)
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-align-left"></i> Description</h4>
                            </div>
                            <div class="card-body">
                                <p class="mb-0">{{ $dossier->description }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                    @if($dossier->notes)
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-sticky-note"></i> Notes</h4>
                            </div>
                            <div class="card-body">
                                <p class="mb-0">{{ $dossier->notes }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Collaborateurs assignés -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fas fa-users"></i> Collaborateurs</h4>
                        @if(auth()->user()->id == $dossier->created_by || auth()->user()->hasRole(['admin', 'super-admin']))
                            <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#gestionCollaborateursModal">
                                <i class="fas fa-user-plus"></i>
                            </button>
                        @endif
                    </div>
                    <div class="card-body">
                        @php
                            $createur = User::find($dossier->created_by);
                        @endphp

                        @if($createur)
                        <div class="user-item mb-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm mr-3">
                                    @if($createur->photo && Storage::exists('public/' . $createur->photo))
                                        <img src="{{ Storage::url($createur->photo) }}" class="rounded-circle">
                                    @else
                                        <div class="avatar-initial rounded-circle bg-primary">
                                            {{ strtoupper(substr($createur->prenom, 0, 1) . substr($createur->nom, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="font-weight-bold">{{ $createur->prenom }} {{ $createur->nom }}</div>
                                            <small class="text-muted">{{ $createur->poste->intitule ?? 'Non défini' }}</small>
                                        </div>
                                        <span class="badge badge-primary badge-pill">Créateur</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        @forelse($dossier->collaborateurs as $collaborateur)
                            @if($collaborateur->id != $dossier->created_by)
                                <div class="user-item mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm mr-3">
                                            @if($collaborateur->photo && Storage::exists('public/' . $collaborateur->photo))
                                                <img src="{{ Storage::url($collaborateur->photo) }}" class="rounded-circle">
                                            @else
                                                @php
                                                    $colors = ['success', 'warning', 'info', 'danger'];
                                                    $colorIndex = crc32($collaborateur->email) % count($colors);
                                                    $color = $colors[$colorIndex];
                                                @endphp
                                                <div class="avatar-initial rounded-circle bg-{{ $color }}">
                                                    {{ strtoupper(substr($collaborateur->prenom, 0, 1) . substr($collaborateur->nom, 0, 1)) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="font-weight-bold">{{ $collaborateur->prenom }} {{ $collaborateur->nom }}</div>
                                                    <small class="text-muted">{{ $collaborateur->poste->intitule ?? 'Non défini' }}</small>
                                                </div>
                                                <div class="btn-group">
                                                    <a href="{{ route('user-profile.show', $collaborateur->id) }}"
                                                       class="btn btn-sm btn-outline-info" title="Voir profil">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if(auth()->user()->id == $dossier->created_by || auth()->user()->hasRole(['admin', 'super-admin']))
                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-danger remove-collaborateur"
                                                                data-user-id="{{ $collaborateur->id }}"
                                                                data-user-name="{{ $collaborateur->prenom }} {{ $collaborateur->nom }}">
                                                            <i class="fas fa-user-minus"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @empty
                            <div class="text-center py-4">
                                <i class="fas fa-user-friends fa-2x text-muted mb-3"></i>
                                <p class="text-muted mb-0">Aucun collaborateur supplémentaire</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Document attaché -->
                @if($dossier->document)
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-file"></i> Document</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <i class="fas fa-file-pdf fa-2x text-danger mr-3"></i>
                                <span>{{ $dossier->document_name }}</span>
                            </div>
                            <a href="{{ $dossier->document_url }}"
                               class="btn btn-sm btn-outline-primary"
                               target="_blank"
                               download>
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Informations techniques -->
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-info-circle"></i> Informations</h4>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="fas fa-hashtag text-muted mr-2"></i>
                                <strong>Référence:</strong> {{ $dossier->reference }}
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-calendar-alt text-muted mr-2"></i>
                                <strong>Créé le:</strong> {{ $dossier->created_at->format('d/m/Y H:i') }}
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-sync text-muted mr-2"></i>
                                <strong>Modifié le:</strong> {{ $dossier->updated_at->format('d/m/Y H:i') }}
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-clock text-muted mr-2"></i>
                                <strong>Durée:</strong> {{ $dossier->duree }} jours
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Actions rapides -->
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-bolt"></i> Actions rapides</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('daily-entries.create') }}?dossier_id={{ $dossier->id }}"
                               class="btn btn-outline-primary btn-block text-left">
                                <i class="fas fa-plus-circle mr-2"></i> Nouvelle feuille de temps
                            </a>
                            <a href="{{ route('missions.analyse.show', $dossier->id) }}"
                               class="btn btn-outline-info btn-block text-left">
                                <i class="fas fa-chart-bar mr-2"></i> Analyse des performances
                            </a>
                            <a href="{{ route('dossiers.edit', $dossier) }}"
                               class="btn btn-outline-warning btn-block text-left">
                                <i class="fas fa-edit mr-2"></i> Modifier le dossier
                            </a>
                            <button type="button" class="btn btn-outline-danger btn-block text-left" data-toggle="modal" data-target="#deleteModal">
                                <i class="fas fa-trash mr-2"></i> Supprimer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modals -->
@if(auth()->user()->id == $dossier->created_by || auth()->user()->hasRole(['admin', 'super-admin']))
<!-- Modal Gestion Collaborateurs -->
<div class="modal fade" id="gestionCollaborateursModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-users-cog mr-2"></i>Gérer les collaborateurs</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="gestionCollaborateursForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Ajouter/Retirer des collaborateurs</label>
                        <select name="collaborateurs[]" class="form-control select2" multiple>
                            @php
                                $tousUtilisateurs = \App\Models\User::where('is_active', 'actif')
                                    ->orderBy('nom')
                                    ->get();
                                $collaborateursExistants = $dossier->collaborateurs->pluck('id')->toArray();
                            @endphp
                            @foreach($tousUtilisateurs as $user)
                                <option value="{{ $user->id }}" {{ in_array($user->id, $collaborateursExistants) ? 'selected' : '' }}>
                                    {{ $user->nom }} {{ $user->prenom }}
                                    @if($user->poste)
                                        ({{ $user->poste->intitule }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Modal Suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmation de suppression</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer le dossier <strong>{{ $dossier->nom }}</strong> ?</p>
                <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> Cette action est irréversible.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <form action="{{ route('dossiers.destroy', $dossier) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .gradient-card {
        background: linear-gradient(135deg, #244584 0%, #5d77bd 100%);
        color: white;
        border: none;
    }

    .card-statistic {
        border: none;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: transform 0.3s;
    }

    .card-statistic:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.1);
    }

    .card-statistic .card-icon {
        width: 60px;
        height: 60px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
    }

    .card-statistic .card-title {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
        margin-bottom: 5px;
    }

    .card-statistic .card-value {
        font-size: 18px;
        font-weight: 600;
        color: #343a40;
    }

    .avatar {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .avatar-sm {
        width: 36px;
        height: 36px;
    }

    .avatar-initial {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        color: white;
        font-size: 14px;
    }

    .user-item {
        padding: 12px;
        border-radius: 8px;
        background: #f8f9fa;
        transition: background 0.3s;
    }

    .user-item:hover {
        background: #e9ecef;
    }

    .empty-state {
        padding: 40px 20px;
        text-align: center;
    }

    .empty-state i {
        font-size: 48px;
        color: #dee2e6;
        margin-bottom: 20px;
    }

    .contact-info {
        font-size: 14px;
        color: #6c757d;
    }

    .contact-info i {
        width: 16px;
    }

    #hoursTable th {
        border-top: none;
        font-weight: 600;
        color: #495057;
        background: #f8f9fa;
    }

    #hoursTable td {
        vertical-align: middle;
    }

    .progress {
        border-radius: 3px;
        overflow: hidden;
    }

    .badge-pill {
        border-radius: 50rem;
        padding: 0.35em 0.65em;
        font-size: 0.75em;
        font-weight: 600;
    }

    .btn-outline-primary, .btn-outline-info, .btn-outline-danger {
        border-width: 1px;
    }

    .btn-group .btn {
        margin-right: 2px;
    }

    .btn-group .btn:last-child {
        margin-right: 0;
    }

    .d-grid {
        display: grid;
    }

    .gap-2 {
        gap: 8px;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialiser Select2
        $('#gestionCollaborateursModal .select2').select2({
            placeholder: "Sélectionner des collaborateurs",
            allowClear: true,
            width: '100%'
        });

        // Gestion du formulaire de collaborateurs
        $('#gestionCollaborateursForm').on('submit', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Mise à jour en cours...',
                text: 'Veuillez patienter',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '{{ route("dossiers.update", $dossier) }}',
                method: 'PUT',
                data: $(this).serialize(),
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    Swal.close();
                    Swal.fire({
                        icon: 'success',
                        title: 'Succès',
                        text: 'Les collaborateurs ont été mis à jour.'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: 'Une erreur est survenue lors de la mise à jour.'
                    });
                }
            });
        });

        // Retirer un collaborateur
        $('.remove-collaborateur').on('click', function() {
            const userId = $(this).data('user-id');
            const userName = $(this).data('user-name');

            Swal.fire({
                title: 'Retirer le collaborateur ?',
                html: `Voulez-vous vraiment retirer <strong>${userName}</strong> de ce dossier ?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Oui, retirer',
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("dossiers.collaborateurs.gestion", $dossier) }}',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            collaborateur_id: userId,
                            action: 'remove'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Succès',
                                    text: response.message
                                }).then(() => {
                                    location.reload();
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erreur',
                                text: 'Une erreur est survenue.'
                            });
                        }
                    });
                }
            });
        });

        // Exporter les heures
        $('#exportHours').on('click', function() {
            const table = $('#hoursTable');
            const rows = table.find('tr');
            let csv = [];

            // En-têtes
            const headers = [];
            table.find('th').each(function() {
                headers.push($(this).text().trim());
            });
            csv.push(headers.join(','));

            // Données
            table.find('tbody tr').each(function() {
                const row = [];
                $(this).find('td').each(function() {
                    let text = $(this).text().trim();
                    text = text.replace(/,/g, ';');
                    row.push(text);
                });
                csv.push(row.join(','));
            });

            // Totaux
            table.find('tfoot tr').each(function() {
                const row = [];
                $(this).find('td').each(function() {
                    let text = $(this).text().trim();
                    text = text.replace(/,/g, ';');
                    row.push(text);
                });
                csv.push(row.join(','));
            });

            // Créer et télécharger le fichier
            const csvContent = csv.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', 'heures-dossier-{{ $dossier->reference }}-{{ date("Y-m-d") }}.csv');
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            Swal.fire({
                icon: 'success',
                title: 'Export réussi',
                text: 'Le fichier CSV a été téléchargé.',
                timer: 2000,
                showConfirmButton: false
            });
        });

        // Tri du tableau
        $('#hoursTable th').on('click', function() {
            const table = $(this).parents('table');
            const index = $(this).index();
            const rows = table.find('tbody tr').toArray().sort(comparer(index));

            this.asc = !this.asc;
            if (!this.asc) {
                rows.reverse();
            }

            for (let i = 0; i < rows.length; i++) {
                table.children('tbody').append(rows[i]);
            }

            // Mettre à jour les icônes de tri
            table.find('th i.fa-sort').removeClass('fa-sort-up fa-sort-down').addClass('fa-sort');
            if (this.asc) {
                $(this).find('i').removeClass('fa-sort').addClass('fa-sort-up');
            } else {
                $(this).find('i').removeClass('fa-sort').addClass('fa-sort-down');
            }
        });

        function comparer(index) {
            return function(a, b) {
                const valA = $(a).children('td').eq(index).text().toUpperCase();
                const valB = $(b).children('td').eq(index).text().toUpperCase();

                if ($.isNumeric(valA) && $.isNumeric(valB)) {
                    return parseFloat(valA) - parseFloat(valB);
                } else {
                    return valA.localeCompare(valB);
                }
            };
        }

        // Initialiser les icônes de tri
        $('#hoursTable th').append(' <i class="fas fa-sort text-muted"></i>');
    });
</script>
@endpush
