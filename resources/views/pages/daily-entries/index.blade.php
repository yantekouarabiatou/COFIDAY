@php
    use App\Helpers\UserHelper;
    $moisActuel     = \Carbon\Carbon::parse(request('mois', now()->format('Y-m')) . '-01');
    $moisPrecedent  = $moisActuel->copy()->subMonth()->format('Y-m');
    $moisSuivant    = $moisActuel->copy()->addMonth()->format('Y-m');
    $isCurrentMonth = $moisActuel->format('Y-m') === now()->format('Y-m');
    $canBulk        = auth()->user()->hasRole(['responsable', 'directeur-general', 'admin']);
@endphp

@extends('layaout')

@section('title', 'Feuilles de Temps — ' . $moisActuel->translatedFormat('F Y'))

@section('content')
<section class="section">

    {{-- ─── En-tête ─────────────────────────────────────────────── --}}
    <div class="section-header">
        <h1><i class="fas fa-clock"></i> Feuilles de Temps</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">Feuilles de Temps</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">

                    {{-- ─── Card header ─────────────────────────────────── --}}
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">

                        {{-- Navigation mois --}}
                        <div class="d-flex align-items-center gap-3">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ request()->fullUrlWithQuery(['mois' => $moisPrecedent]) }}"
                                   class="btn btn-outline-secondary" title="Mois précédent">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                                <a href="{{ request()->fullUrlWithQuery(['mois' => $moisSuivant]) }}"
                                   class="btn btn-outline-secondary" title="Mois suivant"
                                   @if($isCurrentMonth) style="pointer-events:none;opacity:.4;" @endif>
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </div>
                            <h4 class="mb-0">
                                <i class="fas fa-calendar-alt text-primary mr-1"></i>
                                {{ ucfirst($moisActuel->translatedFormat('F Y')) }}
                                @if($isCurrentMonth)
                                    <span class="badge badge-primary ml-1" style="font-size:.7rem;">Mois en cours</span>
                                @endif
                            </h4>
                        </div>

                        {{-- Actions globales --}}
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <a href="{{ route('daily-entries.create') }}"
                               class="btn btn-primary btn-sm btn-icon icon-left">
                                <i class="fas fa-plus"></i> Nouvelle saisie
                            </a>
                            <button type="button" class="btn btn-outline-success btn-sm"
                                    data-toggle="modal" data-target="#exportModal">
                                <i class="fas fa-file-export"></i> Exporter
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                    onclick="window.print()">
                                <i class="fas fa-print"></i> Imprimer
                            </button>
                        </div>
                    </div>

                    <div class="card-body">

                        {{-- ─── Barre de filtres ────────────────────────── --}}
                        <form action="{{ route('daily-entries.index') }}" method="GET" id="filterForm">
                            <div class="row align-items-end mb-3 g-2">

                                <div class="col-md-2">
                                    <label class="form-label small font-weight-bold text-muted mb-1">
                                        <i class="fas fa-calendar mr-1"></i>Mois
                                    </label>
                                    <input type="month" name="mois" id="filter-mois"
                                           class="form-control"
                                           value="{{ $moisActuel->format('Y-m') }}"
                                           max="{{ now()->format('Y-m') }}">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label small font-weight-bold text-muted mb-1">
                                        <i class="fas fa-calendar-day mr-1"></i>Date précise
                                    </label>
                                    <input type="date" name="date" class="form-control"
                                           value="{{ request('date') }}"
                                           min="{{ $moisActuel->format('Y-m-01') }}"
                                           max="{{ $moisActuel->copy()->endOfMonth()->format('Y-m-d') }}">
                                </div>

                                @if(auth()->user()->hasRole(['admin', 'manager', 'directeur-general']) && $users->isNotEmpty())
                                <div class="col-md-3">
                                    <label class="form-label small font-weight-bold text-muted mb-1">
                                        <i class="fas fa-user mr-1"></i>Collaborateur
                                    </label>
                                    <select name="user" id="userFilter" class="form-control">
                                        <option value="">— Tous —</option>
                                        @foreach($users as $u)
                                            <option value="{{ $u->id }}"
                                                {{ request('user') == $u->id ? 'selected' : '' }}>
                                                {{ $u->prenom }} {{ $u->nom }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif

                                <div class="col-md-2">
                                    <label class="form-label small font-weight-bold text-muted mb-1">
                                        <i class="fas fa-tag mr-1"></i>Statut
                                    </label>
                                    <select name="statut" class="form-control">
                                        <option value="">— Tous —</option>
                                        <option value="soumis"  {{ request('statut') === 'soumis'  ? 'selected' : '' }}>Soumis</option>
                                        <option value="validé"  {{ request('statut') === 'validé'  ? 'selected' : '' }}>Validé</option>
                                        <option value="refusé"  {{ request('statut') === 'refusé'  ? 'selected' : '' }}>Refusé</option>
                                    </select>
                                </div>

                                <div class="col-md-3 d-flex gap-2 flex-wrap">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fas fa-search"></i> Filtrer
                                    </button>
                                    @if(request()->hasAny(['date', 'user', 'statut', 'pending']))
                                        <a href="{{ route('daily-entries.index', ['mois' => $moisActuel->format('Y-m')]) }}"
                                           class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-times"></i> Réinitialiser
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>

                        {{-- ─── Barre d'actions groupées (gestionnaires uniquement) ── --}}
                        @if($canBulk)
                        <div class="alert alert-light border mb-3 py-2 px-3 d-flex align-items-center justify-content-between flex-wrap gap-2"
                             id="bulk-bar" style="display:none!important;">
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted small">
                                    <i class="fas fa-check-square mr-1 text-primary"></i>
                                    <strong id="selected-count">0</strong> sélectionnée(s)
                                </span>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-success btn-sm" id="bulk-validate-btn" disabled>
                                    <i class="fas fa-check-double mr-1"></i> Valider la sélection
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" id="bulk-reject-btn" disabled>
                                    <i class="fas fa-times-circle mr-1"></i> Refuser la sélection
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="deselect-all-btn">
                                    <i class="fas fa-minus-square mr-1"></i> Tout désélectionner
                                </button>
                            </div>
                        </div>

                        {{-- Compteur feuilles en attente + raccourci --}}
                        @if($submittedCount > 0)
                        <div class="alert alert-info border-left-info py-2 px-3 mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <span class="small">
                                <i class="fas fa-hourglass-half mr-1"></i>
                                <strong>{{ $submittedCount }}</strong>
                                feuille{{ $submittedCount > 1 ? 's' : '' }} en attente de validation
                                pour <strong>{{ ucfirst($moisActuel->translatedFormat('F Y')) }}</strong>
                            </span>
                            <div class="d-flex gap-2">
                                <a href="{{ route('daily-entries.index', ['mois' => $moisActuel->format('Y-m'), 'statut' => 'soumis']) }}"
                                   class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-filter mr-1"></i> Voir les soumises
                                </a>
                                <button type="button" class="btn btn-success btn-sm" id="validate-all-btn">
                                    <i class="fas fa-check-double mr-1"></i>
                                    Tout valider ({{ $submittedCount }})
                                </button>
                            </div>
                        </div>
                        @endif
                        @endif

                        {{-- ─── Tableau ──────────────────────────────────── --}}
                        @if($dailyEntries->isEmpty())
                            <div class="text-center py-5">
                                <i class="fas fa-calendar-times fa-5x text-muted mb-4 d-block"></i>
                                <h4>Aucune feuille pour {{ ucfirst($moisActuel->translatedFormat('F Y')) }}</h4>
                                <p class="lead text-muted mb-4">
                                    @if(request()->hasAny(['statut', 'date', 'user', 'pending']))
                                        Essayez de modifier les filtres.
                                    @else
                                        Commencez par saisir votre première feuille du mois.
                                    @endif
                                </p>
                                <a href="{{ route('daily-entries.create') }}" class="btn btn-primary btn-lg">
                                    <i class="fas fa-plus"></i> Nouvelle saisie
                                </a>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-bordered" id="entries-table">
                                    <thead class="thead-light">
                                        <tr>
                                            {{-- Checkbox "Tout sélectionner" (gestionnaires uniquement) --}}
                                            @if($canBulk)
                                            <th class="text-center" style="width:40px;">
                                                <input type="checkbox" id="select-all"
                                                       title="Tout sélectionner"
                                                       style="width:16px;height:16px;cursor:pointer;">
                                            </th>
                                            @endif
                                            <th>Date</th>
                                            <th>Collaborateur</th>
                                            <th class="text-center">Heures</th>
                                            <th>Activités</th>
                                            <th class="text-center">Statut</th>
                                            <th>Créée le</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($dailyEntries as $entry)
                                            @php
                                                $isMine      = $entry->user_id === auth()->id();
                                                $isSamePoste = !$isMine
                                                    && $entry->user->poste_id
                                                    && $entry->user->poste_id === auth()->user()->poste_id;
                                                $canManage   = $canBulk && !$isMine;
                                                $isSubmitted = $entry->statut === 'soumis';
                                            @endphp
                                            <tr class="{{ $isSamePoste ? 'table-light' : '' }}"
                                                data-id="{{ $entry->id }}"
                                                data-statut="{{ $entry->statut }}">

                                                {{-- Checkbox sélection (uniquement si gestionnaire et pas sa propre feuille) --}}
                                                @if($canBulk)
                                                <td class="text-center">
                                                    @if($canManage && in_array($entry->statut, ['soumis', 'refusé']))
                                                        <input type="checkbox"
                                                               class="entry-checkbox"
                                                               value="{{ $entry->id }}"
                                                               data-statut="{{ $entry->statut }}"
                                                               style="width:16px;height:16px;cursor:pointer;">
                                                    @endif
                                                </td>
                                                @endif

                                                {{-- Date --}}
                                                <td>
                                                    <strong>{{ $entry->jour->format('d/m/Y') }}</strong><br>
                                                    <small class="text-muted">
                                                        {{ $entry->jour->translatedFormat('l') }}
                                                    </small>
                                                </td>

                                                {{-- Collaborateur --}}
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="mr-3 d-flex align-items-center justify-content-center
                                                                    text-white font-weight-bold"
                                                             style="width:40px;height:40px;border-radius:50%;
                                                                    flex-shrink:0;font-size:.9rem;
                                                                    background:{{ $isMine
                                                                        ? 'linear-gradient(135deg,#4e73df,#224abe)'
                                                                        : 'linear-gradient(135deg,#36b9cc,#1a8a9f)' }};">
                                                            {{ strtoupper(substr($entry->user->prenom ?? 'U', 0, 1)) }}
                                                        </div>
                                                        <div>
                                                            <div class="font-weight-bold">
                                                                {{ $entry->user->prenom }} {{ $entry->user->nom }}
                                                                @if($isMine)
                                                                    <span class="badge badge-primary ml-1"
                                                                          style="font-size:.65rem;">Moi</span>
                                                                @elseif($isSamePoste)
                                                                    <span class="badge badge-info ml-1"
                                                                          style="font-size:.65rem;">
                                                                        <i class="fas fa-users mr-1"></i>Même poste
                                                                    </span>
                                                                @endif
                                                            </div>
                                                            <small class="text-muted">
                                                                {{ $entry->user->poste->intitule ?? 'Non défini' }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                </td>

                                                {{-- Heures --}}
                                                <td class="text-center">
                                                    @php
                                                        $pct      = $entry->heures_theoriques > 0
                                                            ? ($entry->heures_reelles / $entry->heures_theoriques) * 100
                                                            : 0;
                                                        $barColor = $pct >= 100 ? 'bg-success'
                                                            : ($pct >= 80 ? 'bg-warning' : 'bg-danger');
                                                    @endphp
                                                    <div class="progress mb-1"
                                                         style="height:16px;width:130px;margin:0 auto;">
                                                        <div class="progress-bar {{ $barColor }}"
                                                             style="width:{{ min($pct,100) }}%"
                                                             title="{{ round($pct,1) }}%"></div>
                                                    </div>
                                                    <small class="text-muted">
                                                        {{ UserHelper::hoursToHoursMinutes($entry->heures_reelles) }}
                                                        /
                                                        {{ UserHelper::hoursToHoursMinutes($entry->heures_theoriques) }}
                                                    </small>
                                                </td>

                                                {{-- Activités --}}
                                                <td>
                                                    <strong>
                                                        {{ $entry->timeEntries->count() }}
                                                        activité{{ $entry->timeEntries->count() > 1 ? 's' : '' }}
                                                    </strong><br>
                                                    @if($entry->commentaire)
                                                        <small class="text-muted">
                                                            {{ Str::limit($entry->commentaire, 50) }}
                                                        </small>
                                                    @else
                                                        <small class="text-muted fst-italic">Aucun commentaire</small>
                                                    @endif
                                                </td>

                                                {{-- Statut --}}
                                                <td class="text-center">
                                                    @switch($entry->statut)
                                                        @case('soumis')
                                                            <span class="badge badge-info px-3 py-2">
                                                                <i class="fas fa-hourglass-half mr-1"></i>Soumise
                                                            </span>
                                                            @break
                                                        @case('validé')
                                                            <span class="badge badge-success px-3 py-2">
                                                                <i class="fas fa-check mr-1"></i>Validée
                                                            </span>
                                                            @if($entry->valide_le)
                                                                <br>
                                                                <small class="text-muted">
                                                                    {{ $entry->valide_le->format('d/m H:i') }}
                                                                </small>
                                                            @endif
                                                            @break
                                                        @case('refusé')
                                                            <span class="badge badge-danger px-3 py-2">
                                                                <i class="fas fa-times mr-1"></i>Refusée
                                                            </span>
                                                            @if($entry->motif_refus)
                                                                <br>
                                                                <small class="text-danger"
                                                                       title="{{ $entry->motif_refus }}"
                                                                       style="cursor:help;">
                                                                    <i class="fas fa-info-circle"></i> Voir motif
                                                                </small>
                                                            @endif
                                                            @break
                                                        @default
                                                            <span class="badge badge-secondary px-3 py-2">
                                                                {{ ucfirst($entry->statut) }}
                                                            </span>
                                                    @endswitch
                                                </td>

                                                {{-- Créée le --}}
                                                <td>
                                                    <small class="text-muted">
                                                        {{ $entry->created_at->format('d/m H:i') }}
                                                    </small>
                                                </td>

                                                {{-- ─── Actions par ligne ────────────────────── --}}
                                                <td class="text-center">
                                                    <div class="btn-group btn-group-sm" role="group">

                                                        {{-- Voir --}}
                                                        <a href="{{ route('daily-entries.show', $entry) }}"
                                                           class="btn btn-info" title="Voir le détail">
                                                            <i class="fas fa-eye"></i>
                                                        </a>

                                                        {{-- Modifier (propriétaire + soumis) --}}
                                                        @if($isMine && $isSubmitted)
                                                            <a href="{{ route('daily-entries.edit', $entry) }}"
                                                               class="btn btn-warning" title="Modifier">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        @endif

                                                        {{-- Valider (gestionnaire + pas soi-même + soumis) --}}
                                                        @if($canManage && $isSubmitted)
                                                            <button type="button"
                                                                    class="btn btn-success validate-btn"
                                                                    data-id="{{ $entry->id }}"
                                                                    data-name="{{ $entry->user->prenom }} {{ $entry->user->nom }}"
                                                                    data-date="{{ $entry->jour->format('d/m/Y') }}"
                                                                    title="Valider cette feuille">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        @endif

                                                        {{-- Refuser (gestionnaire + pas soi-même + soumis ou déjà refusé) --}}
                                                        @if($canManage && in_array($entry->statut, ['soumis', 'refusé']))
                                                            <button type="button"
                                                                    class="btn btn-danger reject-btn"
                                                                    data-id="{{ $entry->id }}"
                                                                    data-name="{{ $entry->user->prenom }} {{ $entry->user->nom }}"
                                                                    data-date="{{ $entry->jour->format('d/m/Y') }}"
                                                                    data-motif="{{ $entry->motif_refus }}"
                                                                    title="{{ $entry->statut === 'refusé' ? 'Modifier le motif' : 'Refuser' }}">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        @endif

                                                        {{-- Supprimer --}}
                                                        @if($isMine || auth()->user()->hasRole(['manager', 'admin']))
                                                            <button type="button"
                                                                    class="btn btn-outline-danger delete-btn"
                                                                    data-id="{{ $entry->id }}"
                                                                    data-name="{{ $entry->user->prenom }} {{ $entry->user->nom }}"
                                                                    data-date="{{ $entry->jour->format('d/m/Y') }}"
                                                                    title="Supprimer">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        @endif

                                                    </div>
                                                </td>

                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Pagination --}}
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="text-muted small">
                                    Affichage de {{ $dailyEntries->firstItem() }}
                                    à {{ $dailyEntries->lastItem() }}
                                    sur {{ $dailyEntries->total() }}
                                    entrée{{ $dailyEntries->total() > 1 ? 's' : '' }}
                                    —
                                    <strong>{{ ucfirst($moisActuel->translatedFormat('F Y')) }}</strong>
                                </div>
                                <div>
                                    {{ $dailyEntries->appends(request()->query())->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        @endif

                    </div>{{-- /card-body --}}
                </div>{{-- /card --}}
            </div>
        </div>

        {{-- ─── Statistiques ──────────────────────────────────────────── --}}
        <div class="row mt-4">
            <div class="col-12 mb-2">
                <p class="text-muted small mb-0">
                    <i class="fas fa-chart-bar mr-1"></i>
                    Statistiques pour
                    <strong>{{ ucfirst($moisActuel->translatedFormat('F Y')) }}</strong>
                    @if(request()->hasAny(['statut', 'date', 'user']))
                        <span class="text-warning">· filtres actifs</span>
                    @endif
                </p>
            </div>
            <div class="col-lg-3 col-md-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary"><i class="fas fa-clock"></i></div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>Total Heures</h4></div>
                        <div class="card-body">{{ UserHelper::hoursToHoursMinutes($totalHours) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-info"><i class="fas fa-hourglass-half"></i></div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>Soumises</h4></div>
                        <div class="card-body">{{ $submittedCount }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-success"><i class="fas fa-check-circle"></i></div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>Validées</h4></div>
                        <div class="card-body">{{ $validatedCount }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-danger"><i class="fas fa-times-circle"></i></div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>Refusées</h4></div>
                        <div class="card-body">{{ $rejectedCount }}</div>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /section-body --}}
</section>

{{-- ─── Modal Export ──────────────────────────────────────────────── --}}
<div class="modal fade" id="exportModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-file-export mr-2"></i>Exporter les feuilles de temps
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form action="{{ route('daily-entries.export') }}" method="GET">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Période</label>
                        <div class="row">
                            <div class="col-6">
                                <label class="small text-muted">Du</label>
                                <input type="date" name="date_debut" class="form-control"
                                       value="{{ $moisActuel->format('Y-m-01') }}">
                            </div>
                            <div class="col-6">
                                <label class="small text-muted">Au</label>
                                <input type="date" name="date_fin" class="form-control"
                                       value="{{ $moisActuel->copy()->endOfMonth()->format('Y-m-d') }}">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Format</label>
                        <select name="format" class="form-control">
                            <option value="excel">Excel (.xlsx)</option>
                            <option value="pdf">PDF</option>
                            <option value="csv">CSV</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-download mr-1"></i>Exporter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<style>
    .select2-container .select2-selection--single {
        height: calc(1.5em + .75rem + 2px) !important;
        border: 1px solid #ced4da;
        border-radius: .25rem;
        padding: .375rem .75rem;
        font-size: 1rem;
        line-height: 1.5;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 1.5; padding-left: 0; color: #495057;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 100%; }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #4e73df;
    }
    tr.table-light td { background-color: #f8f9fc !important; }
    tr.row-selected td { background-color: #e8f4fd !important; }
    .entry-checkbox:checked { accent-color: #4e73df; }
    #bulk-bar { transition: all .2s ease; }
    .border-left-info { border-left: 4px solid #36b9cc !important; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function () {

    // ── Select2 ─────────────────────────────────────────────────────
    $('#userFilter').select2({
        placeholder: '— Tous les collaborateurs —',
        allowClear: true,
        width: '100%',
        language: { noResults: () => 'Aucun résultat', searching: () => 'Recherche…' }
    });

    // ── Changement de mois → rechargement ───────────────────────────
    $('#filter-mois').on('change', function () {
        const url = new URL(window.location.href);
        url.searchParams.set('mois', this.value);
        url.searchParams.delete('date');
        window.location.href = url.toString();
    });

    // ════════════════════════════════════════════════════════════════
    // SÉLECTION MULTIPLE
    // ════════════════════════════════════════════════════════════════

    function getSelectedIds() {
        return $('.entry-checkbox:checked').map(function () {
            return $(this).val();
        }).get();
    }

    function updateBulkBar() {
        const ids   = getSelectedIds();
        const count = ids.length;

        $('#selected-count').text(count);

        if (count > 0) {
            $('#bulk-bar').show();
            $('#bulk-validate-btn, #bulk-reject-btn').prop('disabled', false);
        } else {
            $('#bulk-bar').hide();
            $('#bulk-validate-btn, #bulk-reject-btn').prop('disabled', true);
        }
    }

    // Tout sélectionner / désélectionner
    $('#select-all').on('change', function () {
        $('.entry-checkbox').prop('checked', this.checked);
        $('.entry-checkbox').closest('tr').toggleClass('row-selected', this.checked);
        updateBulkBar();
    });

    // Sélection individuelle
    $(document).on('change', '.entry-checkbox', function () {
        $(this).closest('tr').toggleClass('row-selected', this.checked);
        const total   = $('.entry-checkbox').length;
        const checked = $('.entry-checkbox:checked').length;
        $('#select-all').prop('indeterminate', checked > 0 && checked < total);
        $('#select-all').prop('checked', checked === total);
        updateBulkBar();
    });

    // Désélectionner tout
    $('#deselect-all-btn').on('click', function () {
        $('.entry-checkbox').prop('checked', false);
        $('tr').removeClass('row-selected');
        $('#select-all').prop('checked', false).prop('indeterminate', false);
        updateBulkBar();
    });

    // ════════════════════════════════════════════════════════════════
    // VALIDATION INDIVIDUELLE
    // ════════════════════════════════════════════════════════════════
    $(document).on('click', '.validate-btn', function () {
        const id   = $(this).data('id');
        const name = $(this).data('name');
        const date = $(this).data('date');

        Swal.fire({
            title: 'Valider la feuille ?',
            html: `Confirmer la validation de <strong>${name}</strong> — <strong>${date}</strong> ?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Oui, valider',
            cancelButtonText: 'Annuler',
            confirmButtonColor: '#28a745'
        }).then(result => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: `/daily-entries/${id}/validate`,
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: () => {
                    Swal.fire({ icon:'success', title:'Validée !', timer:1500, showConfirmButton:false })
                        .then(() => location.reload());
                },
                error: (xhr) => Swal.fire('Erreur', xhr.responseJSON?.message || 'Impossible de valider.', 'error')
            });
        });
    });

    // ════════════════════════════════════════════════════════════════
    // REFUS INDIVIDUEL (soumis ou re-refus pour modifier le motif)
    // ════════════════════════════════════════════════════════════════
    $(document).on('click', '.reject-btn', function () {
        const id    = $(this).data('id');
        const name  = $(this).data('name');
        const date  = $(this).data('date');
        const motif = $(this).data('motif') || '';

        Swal.fire({
            title: `Refuser — ${name}`,
            html: `<small class="text-muted">Feuille du ${date}</small>`,
            input: 'textarea',
            inputLabel: 'Motif du refus (obligatoire)',
            inputPlaceholder: 'Expliquez pourquoi cette feuille est refusée…',
            inputValue: motif,
            inputAttributes: { rows: 4 },
            showCancelButton: true,
            confirmButtonText: 'Confirmer le refus',
            cancelButtonText: 'Annuler',
            confirmButtonColor: '#e74a3b',
            inputValidator: value => !value?.trim() && 'Le motif est obligatoire.'
        }).then(result => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: `/daily-entries/${id}/reject`,
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', motif_refus: result.value },
                success: (response) => {
                    if (response.success) {
                        Swal.fire({ icon:'success', title:'Refusée', text:response.message, timer:1500, showConfirmButton:false })
                            .then(() => location.reload());
                    } else {
                        Swal.fire('Erreur', response.message, 'error');
                    }
                },
                error: (xhr) => Swal.fire('Erreur', xhr.responseJSON?.message || 'Impossible de refuser.', 'error')
            });
        });
    });

    // ════════════════════════════════════════════════════════════════
    // VALIDATION GROUPÉE (sélection)
    // ════════════════════════════════════════════════════════════════
    $('#bulk-validate-btn').on('click', function () {
        const ids   = getSelectedIds();
        const count = ids.length;
        if (!count) return;

        Swal.fire({
            title: 'Valider la sélection ?',
            html: `Confirmez-vous la validation de <strong>${count}</strong> feuille${count > 1 ? 's' : ''} ?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: `Valider ${count} feuille${count > 1 ? 's' : ''}`,
            cancelButtonText: 'Annuler',
            confirmButtonColor: '#28a745'
        }).then(result => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: '{{ route("daily-entries.bulk-validate") }}',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', ids: ids },
                success: (response) => {
                    Swal.fire({ icon:'success', title:'Validées !', text:response.message, timer:1800, showConfirmButton:false })
                        .then(() => location.reload());
                },
                error: (xhr) => Swal.fire('Erreur', xhr.responseJSON?.message || 'Erreur lors de la validation.', 'error')
            });
        });
    });

    // ════════════════════════════════════════════════════════════════
    // REFUS GROUPÉ (sélection)
    // ════════════════════════════════════════════════════════════════
    $('#bulk-reject-btn').on('click', function () {
        const ids   = getSelectedIds();
        const count = ids.length;
        if (!count) return;

        Swal.fire({
            title: `Refuser ${count} feuille${count > 1 ? 's' : ''} ?`,
            input: 'textarea',
            inputLabel: 'Motif du refus (appliqué à toutes les feuilles sélectionnées)',
            inputPlaceholder: 'Expliquez pourquoi ces feuilles sont refusées…',
            inputAttributes: { rows: 4 },
            showCancelButton: true,
            confirmButtonText: `Refuser ${count} feuille${count > 1 ? 's' : ''}`,
            cancelButtonText: 'Annuler',
            confirmButtonColor: '#e74a3b',
            inputValidator: value => !value?.trim() && 'Le motif est obligatoire.'
        }).then(result => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: '{{ route("daily-entries.bulk-reject") }}',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', ids: ids, motif_refus: result.value },
                success: (response) => {
                    Swal.fire({ icon:'success', title:'Refusées', text:response.message, timer:1800, showConfirmButton:false })
                        .then(() => location.reload());
                },
                error: (xhr) => Swal.fire('Erreur', xhr.responseJSON?.message || 'Erreur lors du refus.', 'error')
            });
        });
    });

    // ════════════════════════════════════════════════════════════════
    // TOUT VALIDER (toutes les soumises du mois)
    // ════════════════════════════════════════════════════════════════
    $('#validate-all-btn').on('click', function () {
        const mois  = '{{ $moisActuel->format('Y-m') }}';
        const count = {{ $submittedCount }};

        Swal.fire({
            title: 'Tout valider ?',
            html: `Confirmez-vous la validation de toutes les <strong>${count}</strong>
                   feuille${count > 1 ? 's' : ''} soumises de
                   <strong>{{ ucfirst($moisActuel->translatedFormat('F Y')) }}</strong> ?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: `Oui, tout valider (${count})`,
            cancelButtonText: 'Annuler',
            confirmButtonColor: '#28a745'
        }).then(result => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: '{{ route("daily-entries.validate-all") }}',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', mois: mois },
                success: (response) => {
                    Swal.fire({ icon:'success', title:'Validées !', text:response.message, timer:2000, showConfirmButton:false })
                        .then(() => location.reload());
                },
                error: (xhr) => Swal.fire('Erreur', xhr.responseJSON?.message || 'Erreur lors de la validation.', 'error')
            });
        });
    });

    // ════════════════════════════════════════════════════════════════
    // SUPPRESSION INDIVIDUELLE
    // ════════════════════════════════════════════════════════════════
    $(document).on('click', '.delete-btn', function () {
        const id   = $(this).data('id');
        const name = $(this).data('name');
        const date = $(this).data('date');

        Swal.fire({
            title: 'Supprimer ?',
            html: `Supprimer la feuille de <strong>${name}</strong> du <strong>${date}</strong> ?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler'
        }).then(result => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: `/daily-entries/${id}`,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: () => {
                    Swal.fire({ icon:'success', title:'Supprimée', timer:1500, showConfirmButton:false })
                        .then(() => location.reload());
                },
                error: () => Swal.fire('Erreur', 'Impossible de supprimer.', 'error')
            });
        });
    });

});
</script>
@endpush