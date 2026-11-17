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

        {{-- ── Bandeau d'identité + statut ─────────────────────────────────── --}}
        <div class="card conge-hero border-left-{{ $statutColor }}">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center justify-content-between">
                    <div class="d-flex align-items-center flex-wrap conge-hero-identity">
                        @if($demande->user && $demande->user->photo)
                            <img src="{{ asset('storage/' . $demande->user->photo) }}"
                                 class="rounded-circle conge-hero-avatar" width="56" height="56" alt="Photo">
                        @elseif($demande->user && $demande->user->exists)
                            <div class="avatar bg-primary text-white rounded-circle conge-hero-avatar">
                                {{ strtoupper(substr($demande->user->prenom ?? 'U', 0, 1) . substr($demande->user->nom ?? 'N', 0, 1)) }}
                            </div>
                        @else
                            <div class="avatar bg-secondary text-white rounded-circle conge-hero-avatar">
                                <i class="fas fa-user-slash"></i>
                            </div>
                        @endif

                        <div class="ml-3">
                            @if($demande->user && $demande->user->exists)
                                <h4 class="mb-0">{{ $demande->user->prenom ?? 'Prénom' }} {{ $demande->user->nom ?? 'Nom' }}</h4>
                                <div class="text-muted small">{{ $demande->user->email ?? 'Email non disponible' }}</div>
                            @else
                                <h4 class="mb-0 text-danger">Utilisateur supprimé</h4>
                                <div class="text-muted small">ID : {{ $demande->user_id }}</div>
                            @endif

                            <div class="mt-2 d-flex flex-wrap align-items-center conge-hero-meta">
                                <span class="badge badge-{{ $statutColor }} badge-pill conge-status-pill">
                                    @switch($demande->statut)
                                        @case('en_attente') <i class="fas fa-clock"></i> En attente @break
                                        @case('pre_approuve') <i class="fas fa-hourglass-half"></i> Pré-approuvé @break
                                        @case('approuve') <i class="fas fa-check-circle"></i> Approuvé @break
                                        @case('refuse') <i class="fas fa-times-circle"></i> Refusé @break
                                        @case('annule') <i class="fas fa-ban"></i> Annulé @break
                                        @default {{ ucfirst($demande->statut) }}
                                    @endswitch
                                </span>

                                @if($demande->typeConge)
                                    <span class="badge badge-{{ $demande->typeConge->est_paye ? 'success' : 'warning' }} ml-2">
                                        {{ $demande->typeConge->libelle }}
                                    </span>
                                @endif

                                <span class="text-muted small ml-2">
                                    @if($demande->statut === 'en_attente')
                                        Soumise le {{ $demande->created_at->format('d/m/Y à H:i') }}
                                        @if($demande->created_at->diffInDays(now()) > 0)
                                            (il y a {{ $demande->created_at->diffInDays(now()) }} j.)
                                        @endif
                                    @elseif($demande->date_validation)
                                        Traitée le {{ $demande->date_validation->format('d/m/Y à H:i') }}
                                        @if($demande->validePar)
                                            par {{ $demande->validePar->name ?? $demande->validePar->prenom . ' ' . $demande->validePar->nom }}
                                        @endif
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="conge-hero-actions mt-3 mt-md-0">
                        <a href="{{ route('conges.index') }}" class="btn btn-icon icon-left btn-secondary">
                            <i class="fas fa-arrow-left"></i> Retour
                        </a>
                        <button type="button" id="print-btn" class="btn btn-icon icon-left btn-light ml-1">
                            <i class="fas fa-print"></i> Imprimer
                        </button>

                        <div class="btn-group ml-1">
                            @if($demande->statut === 'en_attente' &&
                                (auth()->user()->hasRole('admin') || auth()->id() === $demande->user_id))
                                <a href="{{ route('conges.edit', $demande) }}"
                                   class="btn btn-icon icon-left btn-primary">
                                    <i class="fas fa-edit"></i> Modifier
                                </a>
                            @endif

                            @if(auth()->user()->hasAnyRole(['admin', 'manager', 'directeur-general']) && $demande->statut === 'en_attente')
                                <div class="dropdown d-inline">
                                    <button class="btn btn-icon icon-left btn-success dropdown-toggle"
                                            type="button" id="actionDropdown" data-toggle="dropdown">
                                        <i class="fas fa-cogs"></i> Actions
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <form action="{{ route('conges.traiter', $demande) }}" method="POST"
                                              class="d-inline approve-form">
                                            @csrf
                                            <input type="hidden" name="action" value="pre_approuve">
                                            <button type="button" class="dropdown-item approve-btn">
                                                <i class="fas fa-check text-success"></i> Pre-approuver
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
            </div>
        </div>

        <div class="row">
            {{-- ── Colonne principale ──────────────────────────────────────── --}}
            <div class="col-lg-8">

                {{-- Chiffres clés --}}
                <div class="row conge-stat-row">
                    <div class="col-sm-4">
                        <div class="conge-stat-tile">
                            <div class="conge-stat-icon text-primary"><i class="fas fa-calendar-day"></i></div>
                            <div>
                                <div class="conge-stat-label">Début</div>
                                <div class="conge-stat-value">
                                    {{ optional($demande->date_debut)->format('d/m/Y') }}
                                    <small class="text-muted d-block">{{ optional($demande->date_debut)->locale('fr')->isoFormat('dddd') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="conge-stat-tile">
                            <div class="conge-stat-icon text-primary"><i class="fas fa-calendar-check"></i></div>
                            <div>
                                <div class="conge-stat-label">Fin</div>
                                <div class="conge-stat-value">
                                    {{ optional($demande->date_fin)->format('d/m/Y') }}
                                    <small class="text-muted d-block">{{ optional($demande->date_fin)->locale('fr')->isoFormat('dddd') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="conge-stat-tile">
                            <div class="conge-stat-icon text-primary"><i class="fas fa-clock"></i></div>
                            <div>
                                <div class="conge-stat-label">Durée</div>
                                <div class="conge-stat-value">
                                    {{ $demande->nombre_jours ?? 0 }} jour(s)
                                    @if($demande->date_debut && $demande->date_fin)
                                        <small class="text-muted d-block">
                                            {{ $demande->date_debut->diffInDays($demande->date_fin) + 1 }} j. calendaires
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($demande->motif)
                <div class="card conge-panel">
                    <div class="card-body">
                        <div class="conge-panel-title">
                            <span class="conge-panel-icon bg-primary"><i class="fas fa-comment-alt"></i></span>
                            Motif
                        </div>
                        <p class="mb-0">{{ $demande->motif }}</p>
                    </div>
                </div>
                @endif

                {{-- Fichier justificatif soumis à la demande --}}
                @if($demande->fichier_justificatif)
                <div class="card conge-panel">
                    <div class="card-body">
                        <div class="conge-panel-title">
                            <span class="conge-panel-icon bg-primary"><i class="fas fa-paperclip"></i></span>
                            Justificatif joint à la demande
                        </div>
                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                            <span class="text-muted"><i class="fas fa-file mr-2"></i>Document fourni par l'employé</span>
                            <a href="{{ Storage::url($demande->fichier_justificatif) }}"
                               target="_blank" class="btn btn-sm btn-outline-primary mt-2 mt-sm-0">
                                <i class="fas fa-eye mr-1"></i> Consulter
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Justificatif au retour --}}
                @if($demande->justificatif_retour)
                <div class="card conge-panel border-left-success">
                    <div class="card-body">
                        <div class="conge-panel-title">
                            <span class="conge-panel-icon bg-success"><i class="fas fa-file-medical"></i></span>
                            Justificatif médical (déposé au retour)
                        </div>
                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                            <span class="text-muted">
                                <i class="fas fa-check-circle text-success mr-2"></i>
                                Déposé le {{ optional($demande->date_depot_justificatif)->format('d/m/Y') }}
                            </span>
                            <a href="{{ Storage::url($demande->justificatif_retour) }}"
                               target="_blank" class="btn btn-sm btn-outline-success mt-2 mt-sm-0">
                                <i class="fas fa-eye mr-1"></i> Consulter
                            </a>
                        </div>
                    </div>
                </div>

                @elseif(auth()->id() === $demande->user_id
                    && in_array($demande->statut, ['approuve', 'pre_approuve'])
                    && optional($demande->typeConge)->justificatif_requis
                    && $demande->date_fin && $demande->date_fin->lte(now()))
                {{-- L'employé doit encore déposer son justificatif --}}
                <div class="card conge-panel border-left-warning">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                            <div>
                                <div class="conge-panel-title mb-1">
                                    <span class="conge-panel-icon bg-warning"><i class="fas fa-exclamation-triangle"></i></span>
                                    Justificatif médical requis
                                </div>
                                <small class="text-muted">Votre congé est terminé. Veuillez déposer votre certificat médical.</small>
                            </div>
                            <button class="btn btn-warning btn-sm mt-2 mt-sm-0"
                                    data-toggle="collapse" data-target="#form-justif-retour">
                                <i class="fas fa-upload mr-1"></i> Déposer
                            </button>
                        </div>
                        <div class="collapse mt-3" id="form-justif-retour">
                            <form action="{{ route('conges.justificatif-retour', $demande) }}"
                                  method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="input-group">
                                    <input type="file" name="justificatif_retour"
                                           class="form-control @error('justificatif_retour') is-invalid @enderror"
                                           accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-check"></i> Valider
                                        </button>
                                    </div>
                                </div>
                                @error('justificatif_retour')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                <small class="text-muted d-block mt-1">PDF, Word ou image max 5 Mo</small>
                            </form>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Retour anticipé : bouton pour admin/manager/DG --}}
                @if(auth()->user()->hasAnyRole(['admin', 'manager', 'directeur-general', 'rh'])
                    && $demande->statut === 'approuve'
                    && $demande->date_debut && $demande->date_fin
                    && now()->between($demande->date_debut, $demande->date_fin)
                    && ($demande->statut_suspension ?? 'actif') !== 'repris')
                <div class="card conge-panel border-left-info">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                            <div>
                                <div class="conge-panel-title mb-1">
                                    <span class="conge-panel-icon bg-info"><i class="fas fa-undo"></i></span>
                                    Retour anticipé
                                </div>
                                <small class="text-muted">L'employé est actuellement en congé. Enregistrez un retour avant la date prévue pour restituer les jours non utilisés.</small>
                            </div>
                            <button class="btn btn-info btn-sm mt-2 mt-sm-0"
                                    data-toggle="collapse" data-target="#form-retour-anticipe">
                                <i class="fas fa-sign-in-alt mr-1"></i> Enregistrer le retour
                            </button>
                        </div>
                        <div class="collapse mt-3" id="form-retour-anticipe">
                            <form action="{{ route('conges.retour-anticipe', $demande) }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-5">
                                        <label class="small font-weight-bold">Date de retour effectif</label>
                                        <input type="date" name="date_retour_effectif"
                                               class="form-control form-control-sm"
                                               min="{{ $demande->date_debut->toDateString() }}"
                                               max="{{ $demande->date_fin->toDateString() }}"
                                               value="{{ now()->toDateString() }}" required>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="small font-weight-bold">Motif (optionnel)</label>
                                        <input type="text" name="motif_suspension"
                                               class="form-control form-control-sm"
                                               placeholder="Ex : Rétablissement plus rapide que prévu">
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="submit" class="btn btn-success btn-sm w-100">
                                            <i class="fas fa-check"></i> Confirmer
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    Les jours non consommés (du retour à la fin du congé) seront automatiquement restitués au solde.
                                </small>
                            </form>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Attestation de congé --}}
                <div class="card conge-panel">
                    <div class="card-body">
                        <div class="conge-panel-title">
                            <span class="conge-panel-icon bg-primary"><i class="fas fa-file-alt"></i></span>
                            Attestation de congé
                        </div>

                        @if($demande->demande_attestation)
                            @if($demande->attestation_remise_le)
                                <span class="badge badge-success p-2">
                                    <i class="fas fa-check-double mr-1"></i>
                                    Remise à l'employé le {{ $demande->attestation_remise_le->format('d/m/Y à H:i') }}
                                    @if($demande->attestationRemisePar)
                                        par {{ $demande->attestationRemisePar->name ?? $demande->attestationRemisePar->prenom . ' ' . $demande->attestationRemisePar->nom }}
                                    @endif
                                </span>
                            @elseif($demande->statut === 'approuve')
                                <div class="d-flex align-items-center justify-content-between flex-wrap">
                                    <span class="badge badge-warning p-2 mb-2 mb-md-0">
                                        <i class="fas fa-clock mr-1"></i>
                                        Approuvée: en attente de signature DG / remise au secrétariat
                                    </span>
                                    @if(Auth::user()->hasAnyRole(['admin', 'rh', 'directeur-general']))
                                        <form action="{{ route('conges.attestation.remise', $demande) }}" method="POST"
                                              onsubmit="return confirm('Confirmer que l\'attestation signée a été remise en main propre à l\'employé ?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="fas fa-hand-holding mr-1"></i>
                                                Marquer comme remise (décharge)
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @else
                                <span class="badge badge-info p-2">
                                    <i class="fas fa-file-alt mr-1"></i>
                                    Attestation demandée - sera générée à l'approbation
                                </span>
                            @endif
                        @else
                            <span class="badge badge-secondary p-2">
                                <i class="fas fa-minus-circle mr-1"></i>
                                Pas d'attestation demandée
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Décision --}}
                @if($demande->statut !== 'en_attente' && $demande->statut !== 'annule')
                <div class="card conge-panel border-left-{{ $demande->statut === 'approuve' ? 'success' : 'danger' }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start flex-wrap">
                            <div>
                                <div class="conge-panel-title mb-1">
                                    <span class="conge-panel-icon bg-{{ $demande->statut === 'approuve' ? 'success' : 'danger' }}">
                                        <i class="fas fa-{{ $demande->statut === 'approuve' ? 'check-circle' : 'times-circle' }}"></i>
                                    </span>
                                    {{ $demande->statut === 'approuve' ? 'Approuvé' : 'Refusé' }}
                                </div>
                                @if($demande->validePar)
                                    <small class="text-muted">
                                        Par {{ $demande->validePar->name ?? $demande->validePar->prenom . ' ' . $demande->validePar->nom }}
                                    </small>
                                @endif
                            </div>
                            <small class="text-muted">
                                @if($demande->date_validation)
                                    Le {{ $demande->date_validation->format('d/m/Y à H:i') }}
                                @else
                                    Date de validation non disponible
                                @endif
                            </small>
                        </div>
                        @if($demande->historiques && $demande->historiques->isNotEmpty())
                            @php
                                $historiqueValidation = $demande->historiques
                                    ->whereIn('action', ['demande_approuvee', 'demande_refusee'])
                                    ->first();
                            @endphp
                            @if($historiqueValidation && $historiqueValidation->commentaire)
                            <hr>
                            <div>
                                <strong>Commentaire :</strong>
                                <p class="mb-0 mt-1">{{ $historiqueValidation->commentaire }}</p>
                            </div>
                            @endif
                        @endif
                    </div>
                </div>
                @endif

                {{-- Historique --}}
                @if($demande->historiques && $demande->historiques->isNotEmpty())
                <div class="card conge-panel">
                    <div class="card-header">
                        <h4><i class="fas fa-history"></i> Historique des actions</h4>
                    </div>
                    <div class="card-body">
                        <div class="activities">
                            @php
                                $historiqueColors = [
                                    'demande_soumise' => 'primary',
                                    'demande_modifiee' => 'warning',
                                    'demande_annulee' => 'secondary',
                                    'demande_approuvee' => 'success',
                                    'demande_approuvee_finale' => 'success',
                                    'demande_refusee' => 'danger',
                                    'demande_refusee_finale' => 'danger',
                                    'solde_ajuste' => 'info',
                                    'relance_envoyee' => 'info',
                                    'rappel_automatique' => 'warning',
                                    'attestation_remise' => 'success',
                                ];
                            @endphp
                            @foreach($demande->historiques->sortByDesc('date_action') as $historique)
                                @php $couleur = $historiqueColors[$historique->action] ?? 'primary'; @endphp
                                <div class="activity">
                                    <div class="activity-icon bg-{{ $couleur }}">
                                        <i class="{{ $historique->action_icon ?? 'fas fa-history' }}"></i>
                                    </div>
                                    <div class="activity-detail">
                                        <div class="mb-2">
                                            <span class="text-job text-muted">
                                                {{ $historique->date_action->format('d/m/Y à H:i') }}
                                                ({{ $historique->date_action->diffForHumans() }})
                                            </span>
                                            <span class="bullet"></span>
                                            <span class="badge badge-{{ $couleur }}">
                                                {{ $historique->action_label ?? ucfirst(str_replace('_', ' ', $historique->action)) }}
                                            </span>
                                        </div>
                                        <p class="mb-0">
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
                @endif

            </div>

            {{-- ── Colonne latérale ────────────────────────────────────────── --}}
            <div class="col-lg-4">

                <div class="card conge-panel">
                    <div class="card-header">
                        <h4><i class="fas fa-info-circle"></i> Informations</h4>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Référence</span>
                                <span class="badge badge-light">#{{ $demande->id }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Soumis le</span>
                                <span>{{ optional($demande->created_at)->format('d/m/Y H:i') }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Dernière mise à jour</span>
                                <span>{{ optional($demande->updated_at)->format('d/m/Y H:i') }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Statut</span>
                                <span class="badge badge-{{ $statutColor }}">
                                    @if($demande->statut === 'en_attente') En attente
                                    @elseif($demande->statut === 'approuve') Approuvé
                                    @elseif($demande->statut === 'refuse') Refusé
                                    @else Annulé
                                    @endif
                                </span>
                            </li>
                            @if($demande->typeConge && $demande->typeConge->est_paye)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Impact solde</span>
                                <span class="text-{{ $demande->statut === 'approuve' ? 'success' : 'secondary' }} font-weight-bold">
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

                {{-- Carte de solde --}}
                @php
                    $solde = \App\Models\SoldeConge::where('user_id', $demande->user_id)
                        ->where('annee', now()->year)
                        ->first();
                @endphp
                @if($solde)
                    @php
                        $totalDisponible = $solde->jours_acquis + ($solde->jours_reportes ?? 0);
                        $pourcentage = $totalDisponible > 0 ? min(100, ($solde->jours_restants / $totalDisponible) * 100) : 0;
                        $soldeCouleur = $pourcentage >= 50 ? '#28a745' : ($pourcentage >= 20 ? '#ffc107' : '#dc3545');
                    @endphp
                    <div class="card conge-panel">
                        <div class="card-header">
                            <h4><i class="fas fa-wallet"></i> Solde de congés {{ now()->year }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <div class="circular-progress"
                                     style="background: conic-gradient({{ $soldeCouleur }} {{ $pourcentage }}%, #e9ecef {{ $pourcentage }}% 100%);">
                                    <div class="circular-progress-inner">
                                        <span class="circular-progress-value">{{ $solde->jours_restants }}</span>
                                        <span class="circular-progress-label">restants</span>
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

            </div>
        </div>

        {{-- ── Barre d'actions bas de page ─────────────────────────────────── --}}
        <div class="card conge-panel">
            <div class="card-body text-right">
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
</section>
@endsection

@push('styles')
<style>
    /* ── Bandeau d'identité ──────────────────────────────────────────── */
    .conge-hero { border-left: 4px solid transparent; }
    .conge-hero-avatar {
        width: 56px; height: 56px; line-height: 56px; text-align: center;
        font-weight: bold; font-size: 1.1rem; flex-shrink: 0;
    }
    .conge-hero-identity { min-width: 0; }
    .conge-status-pill { font-size: 0.85em; padding: 0.45em 0.9em; }
    .conge-hero-actions { display: flex; flex-wrap: wrap; align-items: center; }

    .border-left-primary   { border-left: 4px solid #467fcf !important; }
    .border-left-success   { border-left: 4px solid #28a745 !important; }
    .border-left-warning   { border-left: 4px solid #ffc107 !important; }
    .border-left-danger    { border-left: 4px solid #dc3545 !important; }
    .border-left-info      { border-left: 4px solid #17a2b8 !important; }
    .border-left-secondary { border-left: 4px solid #6c757d !important; }

    /* ── Chiffres clés ───────────────────────────────────────────────── */
    .conge-stat-row { margin-bottom: 0.5rem; }
    .conge-stat-tile {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 0.5rem;
        padding: 1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        height: 100%;
        margin-bottom: 1rem;
    }
    .conge-stat-icon {
        font-size: 1.4rem;
        width: 42px; height: 42px;
        border-radius: 50%;
        background: rgba(70, 127, 207, 0.1);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .conge-stat-label { font-size: 0.78rem; color: #8898aa; text-transform: uppercase; letter-spacing: .03em; }
    .conge-stat-value { font-size: 1.05rem; font-weight: 600; color: #343a40; }

    /* ── Panneaux de contenu ─────────────────────────────────────────── */
    .conge-panel { border-left: 4px solid transparent; }
    .conge-panel-title {
        display: flex; align-items: center; gap: 0.6rem;
        font-weight: 700; margin-bottom: 0.75rem; color: #343a40;
    }
    .conge-panel-icon {
        width: 32px; height: 32px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        color: #fff; font-size: 0.85rem; flex-shrink: 0;
    }

    /* ── Badges ───────────────────────────────────────────────────────── */
    .badge { font-size: 0.85em; font-weight: 500; padding: 0.5em 0.75em; }

    /* ── Avatar générique (fallback list) ────────────────────────────── */
    .avatar { display: inline-flex; align-items: center; justify-content: center; font-weight: bold; border-radius: 50%; }

    /* ── Historique (timeline) ───────────────────────────────────────── */
    .activities { margin: 0; padding: 0; list-style: none; }
    .activity { position: relative; padding-left: 60px; margin-bottom: 24px; }
    .activity:last-child { margin-bottom: 0; }
    .activity-icon {
        position: absolute; left: 0; top: 0;
        width: 42px; height: 42px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: white; font-size: 1.05em;
    }
    .activity-detail {
        background-color: #f8f9fa;
        border-radius: 0.5rem;
        padding: 12px 15px;
        border-left: 3px solid #e9ecef;
    }
    .activity-detail .text-job { font-size: 0.82em; }
    .activity-detail .bullet {
        width: 4px; height: 4px; background-color: #6c757d; border-radius: 50%;
        display: inline-block; margin: 0 8px;
    }

    /* ── Cercle de progression du solde ──────────────────────────────── */
    .circular-progress {
        width: 110px; height: 110px;
        border-radius: 50%;
        margin: 0 auto;
        display: flex; align-items: center; justify-content: center;
    }
    .circular-progress-inner {
        width: 86px; height: 86px;
        background: #fff;
        border-radius: 50%;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
    }
    .circular-progress-value { font-size: 1.5rem; font-weight: 700; color: #343a40; line-height: 1.1; }
    .circular-progress-label { font-size: 0.7rem; color: #8898aa; text-transform: uppercase; letter-spacing: .03em; }

    @media print {
        .conge-hero-actions, .section-header-breadcrumb, .card-footer, .btn { display: none !important; }
    }
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
            title: 'Confirmer la pré-approbation',
            text: 'La demande sera transmise au Directeur Général / RH pour validation finale.',
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
            confirmButtonText: 'Oui, pré-approuver',
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
