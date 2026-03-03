@extends('layaout')

@section('title', 'Détails de la Demande de Congé')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-calendar-alt"></i> Détails de la Demande de Congé</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('conges.index') }}">Congés</a></div>
            <div class="breadcrumb-item active">Détails #{{ $demande->id }}</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <!-- Carte principale -->
                <div class="card">
                    <div class="card-header">
                        <h4>Informations de la demande</h4>
                        <div class="card-header-action">
                            <a href="{{ route('conges.index') }}" class="btn btn-icon icon-left btn-secondary">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>

                            <!-- Actions selon statut et permissions -->
                            <div class="btn-group ml-2">
                                @if($demande->statut === 'en_attente' &&
                                    (auth()->user()->hasRole('admin') || auth()->id() === $demande->user_id))
                                    <a href="{{ route('conges.edit', $demande) }}"
                                       class="btn btn-icon icon-left btn-primary">
                                        <i class="fas fa-edit"></i> Modifier
                                    </a>
                                @endif

                                @if(auth()->user()->hasRole('admin|manager') && $demande->statut === 'en_attente')
                                    <div class="dropdown d-inline">
                                        <button class="btn btn-icon icon-left btn-success dropdown-toggle"
                                                type="button" id="actionDropdown" data-toggle="dropdown">
                                            <i class="fas fa-cogs"></i> Actions
                                        </button>
                                        <div class="dropdown-menu">
                                            <form action="{{ route('conges.traiter', $demande) }}" method="POST"
                                                  class="d-inline approve-form">
                                                @csrf
                                                <input type="hidden" name="action" value="approuver">
                                                <button type="button" class="dropdown-item approve-btn">
                                                    <i class="fas fa-check text-success"></i> Approuver
                                                </button>
                                            </form>
                                            <div class="dropdown-divider"></div>
                                            <form action="{{ route('conges.traiter', $demande) }}" method="POST"
                                                  class="d-inline refuse-form">
                                                @csrf
                                                <input type="hidden" name="action" value="refuser">
                                                <button type="button" class="dropdown-item refuse-btn">
                                                    <i class="fas fa-times text-danger"></i> Refuser
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endif

                                @if($demande->statut === 'en_attente' &&
                                    (auth()->user()->hasRole('admin') || auth()->id() === $demande->user_id))
                                    <form action="{{ route('conges.annuler', $demande) }}" method="POST"
                                          class="d-inline cancel-form ml-1">
                                        @csrf
                                        <button type="button" class="btn btn-icon icon-left btn-warning cancel-btn">
                                            <i class="fas fa-ban"></i> Annuler
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Bannière de statut -->
                        <div class="alert alert-{{ $statutColor }} alert-has-icon">
                            <div class="alert-icon">
                                @switch($demande->statut)
                                    @case('en_attente')
                                        <i class="fas fa-clock"></i>
                                        @break
                                    @case('approuve')
                                        <i class="fas fa-check-circle"></i>
                                        @break
                                    @case('refuse')
                                        <i class="fas fa-times-circle"></i>
                                        @break
                                    @case('annule')
                                        <i class="fas fa-ban"></i>
                                        @break
                                @endswitch
                            </div>
                            <div class="alert-body">
                                <div class="alert-title">
                                    @switch($demande->statut)
                                        @case('en_attente')
                                            Demande en attente de validation
                                            @break
                                        @case('approuve')
                                            Demande approuvée
                                            @break
                                        @case('refuse')
                                            Demande refusée
                                            @break
                                        @case('annule')
                                            Demande annulée
                                            @break
                                    @endswitch
                                </div>
                                <div class="alert-text">
                                    @if($demande->statut === 'en_attente')
                                        Soumise le {{ $demande->created_at->format('d/m/Y à H:i') }}
                                        @if($demande->created_at->diffInDays(now()) > 0)
                                            (il y a {{ $demande->created_at->diffInDays(now()) }} jours)
                                        @endif
                                    @elseif($demande->date_validation)
                                        Traitée le {{ $demande->date_validation->format('d/m/Y à H:i') }}
                                        @if($demande->validePar)
                                            par {{ $demande->validePar->name ?? $demande->validePar->prenom . ' ' . $demande->validePar->nom }}
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Informations principales -->
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h4><i class="fas fa-info-circle"></i> Détails de la demande</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">Demandeur</label>
                                                    <div class="d-flex align-items-center">
                                                        @if($demande->user && $demande->user->photo)
                                                            <img src="{{ asset('storage/' . $demande->user->photo) }}"
                                                                class="rounded-circle mr-2" width="40" height="40"
                                                                alt="Photo">
                                                        @elseif($demande->user)
                                                            <div class="avatar bg-primary text-white rounded-circle mr-2"
                                                                style="width: 40px; height: 40px; line-height: 40px; text-align: center;">
                                                                {{ strtoupper(substr($demande->user->prenom ?? 'U', 0, 1) . substr($demande->user->nom ?? 'N', 0, 1)) }}
                                                            </div>
                                                        @else
                                                            <div class="avatar bg-secondary text-white rounded-circle mr-2"
                                                                style="width: 40px; height: 40px; line-height: 40px; text-align: center;">
                                                                <i class="fas fa-user-slash"></i>
                                                            </div>
                                                        @endif
                                                        <div>
                                                            @if($demande->user)
                                                                <strong>{{ $demande->user->prenom ?? 'Prénom' }} {{ $demande->user->nom ?? 'Nom' }}</strong><br>
                                                                <small class="text-muted">{{ $demande->user->email ?? 'Email non disponible' }}</small>
                                                            @else
                                                                <strong class="text-danger">Utilisateur supprimé</strong><br>
                                                                <small class="text-muted">ID: {{ $demande->user_id }}</small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">Type de congé</label>
                                                    <div class="mt-2">
                                                        @if($demande->typeConge)
                                                            <span class="badge badge-{{ $demande->typeConge->est_paye ? 'success' : 'warning' }} p-2" style="font-size: 1em;">
                                                                {{ $demande->typeConge->libelle }}
                                                            </span>
                                                            @if($demande->typeConge->est_paye)
                                                                <span class="badge badge-info ml-2">Payé</span>
                                                            @else
                                                                <span class="badge badge-secondary ml-2">Non payé</span>
                                                            @endif
                                                            @if($demande->typeConge->nombre_jours_max)
                                                                <div class="mt-2">
                                                                    <small class="text-muted">
                                                                        Maximum {{ $demande->typeConge->nombre_jours_max }} jours
                                                                    </small>
                                                                </div>
                                                            @endif
                                                        @else
                                                            <span class="badge badge-secondary p-2">Type inconnu</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">Période</label>
                                                    <div class="mt-2">
                                                        @if($demande->date_debut)
                                                            <div class="d-flex align-items-center mb-2">
                                                                <i class="fas fa-calendar-day text-primary mr-2"></i>
                                                                <div>
                                                                    <strong>Date de début :</strong><br>
                                                                    {{ $demande->date_debut->format('d/m/Y') }}
                                                                    <small class="text-muted">
                                                                        ({{ $demande->date_debut->locale('fr')->isoFormat('dddd') }})
                                                                    </small>
                                                                </div>
                                                            </div>
                                                        @endif
                                                        @if($demande->date_fin)
                                                            <div class="d-flex align-items-center">
                                                                <i class="fas fa-calendar-check text-primary mr-2"></i>
                                                                <div>
                                                                    <strong>Date de fin :</strong><br>
                                                                    {{ $demande->date_fin->format('d/m/Y') }}
                                                                    <small class="text-muted">
                                                                        ({{ $demande->date_fin->locale('fr')->isoFormat('dddd') }})
                                                                    </small>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">Durée</label>
                                                    <div class="mt-2">
                                                        <div class="d-flex align-items-center">
                                                            <i class="fas fa-clock text-primary mr-2 fa-2x"></i>
                                                            <div>
                                                                <h3 class="mb-0">{{ $demande->nombre_jours ?? 0 }} jour(s)</h3>
                                                                @if($demande->date_debut && $demande->date_fin)
                                                                    <small class="text-muted">
                                                                        {{ $demande->date_debut->diffInDays($demande->date_fin) + 1 }} jours calendaires
                                                                    </small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        @if($demande->motif)
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">Motif</label>
                                                    <div class="card bg-light">
                                                        <div class="card-body">
                                                            <p class="mb-0">{{ $demande->motif }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        <!-- Informations de validation -->
                                        @if($demande->statut !== 'en_attente' && $demande->statut !== 'annule')
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">Décision</label>
                                                    <div class="card border-{{ $demande->statut === 'approuve' ? 'success' : 'danger' }}">
                                                        <div class="card-body">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <div>
                                                                    <h5 class="mb-0">
                                                                        @if($demande->statut === 'approuve')
                                                                            <i class="fas fa-check-circle text-success"></i> Approuvé
                                                                        @else
                                                                            <i class="fas fa-times-circle text-danger"></i> Refusé
                                                                        @endif
                                                                    </h5>
                                                                    @if($demande->validePar)
                                                                        <small class="text-muted">
                                                                            Par {{ $demande->validePar->name ?? $demande->validePar->prenom . ' ' . $demande->validePar->nom }}
                                                                        </small>
                                                                    @endif
                                                                </div>
                                                                <div class="text-right">
                                                                    @if($demande->date_validation)
                                                                        <small class="text-muted">
                                                                            Le {{ $demande->date_validation->format('d/m/Y à H:i') }}
                                                                        </small>
                                                                    @else
                                                                        <small class="text-muted">
                                                                            Date de validation non disponible
                                                                        </small>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            @if($demande->historiques && $demande->historiques->isNotEmpty())
                                                                @php
                                                                    $historiqueValidation = $demande->historiques
                                                                        ->whereIn('action', ['demande_approuvee', 'demande_refusee'])
                                                                        ->first();
                                                                @endphp
                                                                @if($historiqueValidation && $historiqueValidation->commentaire)
                                                                <hr>
                                                                <div class="mt-2">
                                                                    <strong>Commentaire :</strong>
                                                                    <p class="mb-0 mt-1">{{ $historiqueValidation->commentaire }}</p>
                                                                </div>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Informations complémentaires -->
                            <div class="col-md-4">
                                <!-- Carte d'information -->
                                <div class="card">
                                    <div class="card-header">
                                        <h4><i class="fas fa-chart-bar"></i> Informations</h4>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <span>Référence</span>
                                                <span class="badge badge-light">#{{ $demande->id }}</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <span>Soumis le</span>
                                                <span>
                                                    {{ optional($demande->created_at)->format('d/m/Y H:i') ?? '—' }}
                                                </span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <span>Dernière mise à jour</span>
                                                <span>
                                                    {{ optional($demande->updated_at)->format('d/m/Y H:i') ?? '—' }}
                                                </span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <span>Statut</span>
                                                <span class="badge badge-{{ $statutColor }}">
                                                    @if($demande->statut === 'en_attente')
                                                        En attente
                                                    @elseif($demande->statut === 'approuve')
                                                        Approuvé
                                                    @elseif($demande->statut === 'refuse')
                                                        Refusé
                                                    @else
                                                        Annulé
                                                    @endif
                                                </span>
                                            </li>
                                            @if($demande->typeConge && $demande->typeConge->est_paye)
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <span>Impact solde</span>
                                                <span class="text-{{ $demande->statut === 'approuve' ? 'success' : 'secondary' }}">
                                                    @if($demande->statut === 'approuve')
                                                        -{{ $demande->nombre_jours }} jours
                                                    @else
                                                        Aucun impact
                                                    @endif
                                                </span>
                                            </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>

                    <!-- Carte de solde -->
                    @php
                        $solde = \App\Models\SoldeConge::where('user_id', $demande->user_id)
                            ->where('annee', now()->year)
                            ->first();
                    @endphp
                    @if($solde)
                    <div class="card mt-3">
                        <div class="card-header">
                            <h4><i class="fas fa-wallet"></i> Solde de congés {{ now()->year }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                @php
                                    $totalDisponible = $solde->jours_acquis + ($solde->jours_reportes ?? 0);
                                    $pourcentage = $totalDisponible > 0 ? min(100, ($solde->jours_restants / $totalDisponible) * 100) : 0;
                                @endphp
                                <div class="progress-circle-wrapper">
                                    <div class="circular-progress" data-percent="{{ $pourcentage }}">
                                        <span class="progress-value">{{ $solde->jours_restants }}</span>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">jours restants sur {{ $totalDisponible }} disponibles</small>
                                </div>
                            </div>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Jours acquis ({{ now()->year }})</span>
                                    <span class="badge badge-success">{{ $solde->jours_acquis }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Jours reportés (années antérieures)</span>
                                    <span class="badge badge-info">{{ $solde->jours_reportes ?? 0 }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Total disponible</span>
                                    <span class="badge badge-primary">{{ $totalDisponible }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Jours déjà pris</span>
                                    <span class="badge badge-danger">{{ $solde->jours_pris }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><strong>Solde restant</strong></span>
                                    <span class="badge badge-success" style="font-size: 1.1em;">{{ $solde->jours_restants }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    @endif

                                            <!-- Historique -->
                                                @if($demande->historiques && $demande->historiques->isNotEmpty())
                                                <div class="row mt-4">
                                                    <div class="col-md-12">
                                                        <div class="card">
                                                            <div class="card-header">
                                                                <h4><i class="fas fa-history"></i> Historique des actions</h4>
                                                            </div>
                                                            <div class="card-body">
                                                                <div class="activities">
                                                                    @foreach($demande->historiques->sortByDesc('date_action') as $historique)
                                                                    <div class="activity">
                                                                        <div class="activity-icon bg-{{ $historique->action_class ?? 'primary' }}">
                                                                            @switch($historique->action)
                                                                                @case('demande_soumise')
                                                                                    <i class="fas fa-paper-plane"></i>
                                                                                    @break
                                                                                @case('demande_modifiee')
                                                                                    <i class="fas fa-edit"></i>
                                                                                    @break
                                                                                @case('demande_approuvee')
                                                                                    <i class="fas fa-check"></i>
                                                                                    @break
                                                                                @case('demande_refusee')
                                                                                    <i class="fas fa-times"></i>
                                                                                    @break
                                                                                @case('demande_annulee')
                                                                                    <i class="fas fa-ban"></i>
                                                                                    @break
                                                                                @default
                                                                                    <i class="fas fa-history"></i>
                                                                            @endswitch
                                                                        </div>
                                                                        <div class="activity-detail">
                                                                            <div class="mb-2">
                                                                                <span class="text-job text-muted">
                                                                                    {{ $historique->date_action->format('d/m/Y à H:i') }}
                                                                                    ({{ $historique->date_action->diffForHumans() }})
                                                                                </span>
                                                                                <span class="bullet"></span>
                                                                                <span class="badge badge-{{ $historique->action_class ?? 'primary' }}">
                                                                                    {{ $historique->action_label ?? ucfirst(str_replace('_', ' ', $historique->action)) }}
                                                                                </span>
                                                                            </div>
                                                                            <p>
                                                                                @if($historique->effectuePar)
                                                                                    <strong>{{ $historique->effectuePar->name ?? $historique->effectuePar->prenom . ' ' . $historique->effectuePar->nom }}</strong>
                                                                                @else
                                                                                    <strong>Système</strong>
                                                                                @endif
                                                                                @if($historique->commentaire)
                                                                                    : {{ $historique->commentaire }}
                                                                                @endif
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>

                    <div class="card-footer text-right">
                        <a href="{{ route('conges.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Retour à la liste
                        </a>
                        @if($demande->statut === 'en_attente' &&
                            (auth()->user()->hasRole('admin') || auth()->id() === $demande->user_id))
                            <a href="{{ route('conges.edit', $demande) }}" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                        @endif
                        @if(auth()->user()->hasRole('admin') ||
                            (auth()->id() === $demande->user_id &&
                             in_array($demande->statut, ['en_attente', 'annule'])))
                            <form action="{{ route('conges.destroy', $demande) }}" method="POST"
                                  class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-danger delete-btn">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    /* Styles pour les badges */
    .badge {
        font-size: 0.85em;
        font-weight: 500;
        padding: 0.5em 0.75em;
    }

    /* Avatar */
    .avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }

    /* Historique */
    .activities {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .activity {
        position: relative;
        padding-left: 60px;
        margin-bottom: 30px;
    }

    .activity-icon {
        position: absolute;
        left: 0;
        top: 0;
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2em;
    }

    .activity-detail {
        background-color: #f8f9fa;
        border-radius: 5px;
        padding: 15px;
        border-left: 4px solid #e9ecef;
    }

    .activity-detail .text-job {
        font-size: 0.85em;
    }

    .activity-detail .bullet {
        width: 5px;
        height: 5px;
        background-color: #6c757d;
        border-radius: 50%;
        display: inline-block;
        margin: 0 10px;
    }

    /* Progress circle */
    .progress-circle {
        width: 100px;
        height: 100px;
        margin: 0 auto;
        border-radius: 50%;
        position: relative;
        background: conic-gradient(
            #28a745 0% {{ min(100, ($solde->jours_restants / $solde->jours_acquis) * 100) }}%,
            #e9ecef {{ min(100, ($solde->jours_restants / $solde->jours_acquis) * 100) }}% 100%
        );
    }

    .progress-circle:before {
        content: '';
        position: absolute;
        width: 80px;
        height: 80px;
        background: white;
        border-radius: 50%;
        top: 10px;
        left: 10px;
    }

    .progress-circle.progress-warning {
        background: conic-gradient(
            #ffc107 0% {{ min(100, ($solde->jours_restants / $solde->jours_acquis) * 100) }}%,
            #e9ecef {{ min(100, ($solde->jours_restants / $solde->jours_acquis) * 100) }}% 100%
        );
    }

    .progress-circle.progress-danger {
        background: conic-gradient(
            #dc3545 0% {{ min(100, ($solde->jours_restants / $solde->jours_acquis) * 100) }}%,
            #e9ecef {{ min(100, ($solde->jours_restants / $solde->jours_acquis) * 100) }}% 100%
        );
    }

    .progress-circle-value {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 1.5em;
        font-weight: bold;
        color: #343a40;
        z-index: 1;
    }

    /* Alert colors */
    .alert-success {
        border-left-color: #28a745;
    }

    .alert-warning {
        border-left-color: #ffc107;
    }

    .alert-danger {
        border-left-color: #dc3545;
    }

    .alert-secondary {
        border-left-color: #6c757d;
    }

    /* Badge colors */
    .bg-demande_soumise { background-color: #3b82f6 !important; }
    .bg-demande_modifiee { background-color: #f59e0b !important; }
    .bg-demande_approuvee { background-color: #10b981 !important; }
    .bg-demande_refusee { background-color: #ef4444 !important; }
    .bg-demande_annulee { background-color: #6b7280 !important; }

    .badge-demande_soumise { background-color: #3b82f6 !important; color: white; }
    .badge-demande_modifiee { background-color: #f59e0b !important; color: white; }
    .badge-demande_approuvee { background-color: #10b981 !important; color: white; }
    .badge-demande_refusee { background-color: #ef4444 !important; color: white; }
    .badge-demande_annulee { background-color: #6b7280 !important; color: white; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    /* ==========================
        CONFIRMATION SUPPRESSION
    =========================== */
    $('.delete-btn').on('click', function(e) {
        e.preventDefault();
        const form = $(this).closest('form');

        Swal.fire({
            title: 'Confirmer la suppression',
            text: 'Êtes-vous sûr de vouloir supprimer cette demande ? Cette action est irréversible.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    /* ==========================
        ANNULATION DE DEMANDE
    =========================== */
    $('.cancel-btn').on('click', function(e) {
        e.preventDefault();
        const form = $(this).closest('form');

        Swal.fire({
            title: 'Confirmer l\'annulation',
            text: 'Êtes-vous sûr de vouloir annuler cette demande ?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#f39c12',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Oui, annuler',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    /* ==========================
        APPROBATION DE DEMANDE
    =========================== */
    $('.approve-btn').on('click', function(e) {
        e.preventDefault();
        const form = $(this).closest('form');

        Swal.fire({
            title: 'Confirmer l\'approbation',
            text: 'Êtes-vous sûr de vouloir approuver cette demande de congé ?',
            icon: 'question',
            input: 'textarea',
            inputLabel: 'Commentaire (optionnel)',
            inputPlaceholder: 'Ajouter un commentaire...',
            inputAttributes: {
                maxlength: 500
            },
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Oui, approuver',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                if (result.value) {
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'commentaire',
                        value: result.value
                    }).appendTo(form);
                }
                form.submit();
            }
        });
    });

    /* ==========================
        REFUS DE DEMANDE
    =========================== */
    $('.refuse-btn').on('click', function(e) {
        e.preventDefault();
        const form = $(this).closest('form');

        Swal.fire({
            title: 'Confirmer le refus',
            text: 'Êtes-vous sûr de vouloir refuser cette demande de congé ?',
            icon: 'question',
            input: 'textarea',
            inputLabel: 'Motif du refus (requis)',
            inputPlaceholder: 'Expliquez le motif du refus...',
            inputAttributes: {
                maxlength: 500,
                required: true
            },
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Oui, refuser',
            cancelButtonText: 'Annuler',
            preConfirm: (value) => {
                if (!value) {
                    Swal.showValidationMessage('Veuillez indiquer le motif du refus');
                }
                return value;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'commentaire',
                    value: result.value
                }).appendTo(form);
                form.submit();
            }
        });
    });

    /* ==========================
        IMPRIMER LA DEMANDE
    =========================== */
    $('#print-btn').on('click', function() {
        window.print();
    });
});
</script>
@endpush
