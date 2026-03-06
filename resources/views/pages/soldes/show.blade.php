@extends('layaout')

@section('title', 'Détails du Solde — ' . ($solde->user->prenom ?? '') . ' ' . ($solde->user->nom ?? ''))

@section('content')

@php
    // Tous les soldes de cet utilisateur, triés du plus récent au plus ancien
    $tousLesSoldes = \App\Models\SoldeConge::where('user_id', $solde->user_id)
        ->orderByDesc('annee')
        ->get();

    $user = $solde->user;

    // Calculs pour le solde courant (celui passé par le controller)
    $totalDispo  = $solde->jours_acquis + ($solde->jours_reportes ?? 0);
    $pct         = $totalDispo > 0 ? round(($solde->jours_restants / $totalDispo) * 100) : 0;
    $tauxUtil    = $totalDispo > 0 ? round(($solde->jours_pris / $totalDispo) * 100) : 0;
    $colorPct    = $pct >= 50 ? '#10b981' : ($pct >= 25 ? '#f59e0b' : '#ef4444');
    $colorUtil   = $tauxUtil > 75 ? 'danger' : ($tauxUtil > 50 ? 'warning' : 'success');

    // ---- Totaux cumulés sur toutes les années ----
    $totalAcquis   = $tousLesSoldes->sum('jours_acquis');
    $totalPris     = $tousLesSoldes->sum('jours_pris');
    $totalReportes = $tousLesSoldes->sum('jours_reportes');
    $totalRestants = $tousLesSoldes->sum('jours_restants');
    $totalDispoAll = $totalAcquis + $totalReportes;
    $pctGlobal     = $totalDispoAll > 0 ? round(($totalRestants / $totalDispoAll) * 100) : 0;
    $tauxGlobal    = $totalDispoAll > 0 ? round(($totalPris / $totalDispoAll) * 100) : 0;
    $colorGlobal   = $pctGlobal >= 50 ? 'success' : ($pctGlobal >= 25 ? 'warning' : 'danger');
    $anneeMin      = $tousLesSoldes->min('annee');
    $anneeMax      = $tousLesSoldes->max('annee');
@endphp

<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-wallet"></i> Soldes de Congés</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.soldes.index') }}">Soldes</a></div>
            <div class="breadcrumb-item active">{{ $user->prenom ?? '' }} {{ $user->nom ?? '' }}</div>
        </div>
    </div>

    <div class="section-body">

        {{-- ============================================================
             HERO — Carte profil utilisateur + solde de l'année en cours
        ============================================================ --}}
        <div class="solde-hero mb-4">
            <div class="hero-left">
                {{-- Avatar --}}
                <div class="hero-avatar">
                    @if($user && $user->photo)
                        <img src="{{ asset('storage/' . $user->photo) }}" alt="Photo">
                    @else
                        <span>{{ strtoupper(substr($user->prenom ?? 'U', 0, 1) . substr($user->nom ?? 'N', 0, 1)) }}</span>
                    @endif
                </div>
                <div class="hero-info">
                    <h2>{{ $user->prenom ?? '—' }} {{ $user->nom ?? '' }}</h2>
                    <div class="hero-meta">
                        <span><i class="fas fa-envelope"></i> {{ $user->email ?? '—' }}</span>
                        @if($user->poste ?? false)
                            <span><i class="fas fa-briefcase"></i> {{ $user->poste->libelle ?? '' }}</span>
                        @endif
                    </div>
                    <div class="hero-actions mt-2">
                        <a href="{{ route('admin.soldes.edit', $solde) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i> Modifier le solde {{ $solde->annee }}
                        </a>
                        <a href="{{ route('admin.soldes.index') }}" class="btn btn-sm btn-secondary ml-1">
                            <i class="fas fa-arrow-left"></i> Retour
                        </a>
                    </div>
                </div>
            </div>

            {{-- Jauge solde courant --}}
            <div class="hero-right">
                <div class="hero-gauge-label">Solde {{ $solde->annee }}</div>
                <div class="hero-gauge">
                    <svg viewBox="0 0 120 120" class="gauge-svg">
                        <circle cx="60" cy="60" r="50" fill="none" stroke="#e5e7eb" stroke-width="10"/>
                        <circle cx="60" cy="60" r="50" fill="none"
                                stroke="{{ $colorPct }}"
                                stroke-width="10"
                                stroke-dasharray="{{ round($pct * 3.14) }} 314"
                                stroke-dashoffset="78.5"
                                stroke-linecap="round"
                                style="transition: stroke-dasharray 1s ease;"/>
                    </svg>
                    <div class="gauge-center">
                        <div class="gauge-value">{{ $solde->jours_restants }}</div>
                        <div class="gauge-sub">j. restants</div>
                    </div>
                </div>
                <div class="hero-gauge-stats">
                    <div class="gs-item">
                        <span class="gs-val text-success">{{ $solde->jours_acquis }}</span>
                        <span class="gs-lab">Acquis</span>
                    </div>
                    <div class="gs-sep">·</div>
                    <div class="gs-item">
                        <span class="gs-val text-danger">{{ $solde->jours_pris }}</span>
                        <span class="gs-lab">Pris</span>
                    </div>
                    <div class="gs-sep">·</div>
                    <div class="gs-item">
                        <span class="gs-val text-info">{{ $solde->jours_reportes ?? 0 }}</span>
                        <span class="gs-lab">Reportés</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================
             CARD TOTAL CUMULÉ TOUTES ANNÉES
        ============================================================ --}}
        <div class="card card-total-global mb-4">
            <div class="card-header">
                <h4><i class="fas fa-chart-bar"></i> Récapitulatif global — {{ $anneeMin }} à {{ $anneeMax }} ({{ $tousLesSoldes->count() }} année(s))</h4>
            </div>
            <div class="card-body">
                <div class="row align-items-center">

                    {{-- 4 totaux --}}
                    <div class="col-lg-7">
                        <div class="row">
                            <div class="col-6 col-md-3 mb-3">
                                <div class="total-kpi total-success">
                                    <div class="total-icon"><i class="fas fa-gift"></i></div>
                                    <div class="total-val">{{ $totalAcquis }}</div>
                                    <div class="total-lab">Total acquis</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 mb-3">
                                <div class="total-kpi total-danger">
                                    <div class="total-icon"><i class="fas fa-plane-departure"></i></div>
                                    <div class="total-val">{{ $totalPris }}</div>
                                    <div class="total-lab">Total pris</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 mb-3">
                                <div class="total-kpi total-info">
                                    <div class="total-icon"><i class="fas fa-redo-alt"></i></div>
                                    <div class="total-val">{{ $totalReportes }}</div>
                                    <div class="total-lab">Total reportés</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 mb-3">
                                <div class="total-kpi total-{{ $colorGlobal }}">
                                    <div class="total-icon"><i class="fas fa-coins"></i></div>
                                    <div class="total-val">{{ $totalRestants }}</div>
                                    <div class="total-lab">Total restants</div>
                                </div>
                            </div>
                        </div>

                        {{-- Barres globales --}}
                        <div class="mt-2">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="font-weight-bold text-muted">Solde global restant</small>
                                <small class="font-weight-bold text-{{ $colorGlobal }}">{{ $pctGlobal }}%</small>
                            </div>
                            <div class="progress progress-lg mb-3">
                                <div class="progress-bar bg-{{ $colorGlobal }} progress-animated"
                                     data-width="{{ $pctGlobal }}"
                                     style="width:0%; border-radius:6px;">
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <small class="font-weight-bold text-muted">Taux d'utilisation global</small>
                                <small class="font-weight-bold text-{{ $tauxGlobal > 75 ? 'danger' : ($tauxGlobal > 50 ? 'warning' : 'success') }}">{{ $tauxGlobal }}%</small>
                            </div>
                            <div class="progress progress-lg">
                                <div class="progress-bar bg-{{ $tauxGlobal > 75 ? 'danger' : ($tauxGlobal > 50 ? 'warning' : 'success') }} progress-animated"
                                     data-width="{{ $tauxGlobal }}"
                                     style="width:0%; border-radius:6px;">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Séparateur --}}
                    <div class="col-lg-1 d-none d-lg-flex justify-content-center">
                        <div style="width:1px; height:160px; background:#e5e7eb;"></div>
                    </div>

                    {{-- Synthèse texte --}}
                    <div class="col-lg-4">
                        <div class="global-summary">
                            <div class="gs-row">
                                <span class="gs-label">Disponible total</span>
                                <span class="gs-value text-info">{{ $totalDispoAll }} j</span>
                            </div>
                            <div class="gs-row">
                                <span class="gs-label">Consommé</span>
                                <span class="gs-value text-danger">{{ $totalPris }} j</span>
                            </div>
                            <div class="gs-row">
                                <span class="gs-label">Restant cumulé</span>
                                <span class="gs-value text-{{ $colorGlobal }}">{{ $totalRestants }} j</span>
                            </div>
                            <div class="gs-row gs-row-bold">
                                <span class="gs-label">Nb. années</span>
                                <span class="gs-value">{{ $tousLesSoldes->count() }}</span>
                            </div>
                            <div class="gs-row gs-row-bold">
                                <span class="gs-label">Moy. jours/an</span>
                                <span class="gs-value text-info">
                                    {{ $tousLesSoldes->count() > 0 ? round($totalAcquis / $tousLesSoldes->count(), 1) : 0 }} j
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- ============================================================
             HISTORIQUE COMPLET PAR ANNÉE
             HISTORIQUE COMPLET PAR ANNÉE
        ============================================================ --}}
        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-history"></i> Historique des soldes — {{ $tousLesSoldes->count() }} année(s)</h4>
            </div>
            <div class="card-body p-0">

                {{-- Onglets années --}}
                <ul class="nav nav-tabs px-3 pt-2 border-bottom-0" id="anneesTabs">
                    @foreach($tousLesSoldes as $s)
                    <li class="nav-item">
                        <a class="nav-link {{ $s->id === $solde->id ? 'active' : '' }}"
                           data-toggle="tab"
                           href="#annee-{{ $s->annee }}">
                            <strong>{{ $s->annee }}</strong>
                            @if($s->annee == now()->year)
                                <span class="badge badge-primary badge-sm ml-1">En cours</span>
                            @endif
                        </a>
                    </li>
                    @endforeach
                </ul>

                <div class="tab-content" id="anneesTabsContent">
                    @foreach($tousLesSoldes as $s)
                    @php
                        $td    = $s->jours_acquis + ($s->jours_reportes ?? 0);
                        $p     = $td > 0 ? round(($s->jours_restants / $td) * 100) : 0;
                        $tu    = $td > 0 ? round(($s->jours_pris / $td) * 100) : 0;
                        $cp    = $p >= 50 ? 'success' : ($p >= 25 ? 'warning' : 'danger');
                        $cup   = $tu > 75 ? 'danger' : ($tu > 50 ? 'warning' : 'success');
                        $cpHex = $p >= 50 ? '#10b981' : ($p >= 25 ? '#f59e0b' : '#ef4444');

                        // Solde de l'année précédente
                        $soldePrev     = $tousLesSoldes->firstWhere('annee', $s->annee - 1);
                        $tdPrev        = $soldePrev ? ($soldePrev->jours_acquis + ($soldePrev->jours_reportes ?? 0)) : null;
                        $restantsPrev  = $soldePrev ? $soldePrev->jours_restants : null;
                        $prisPrev      = $soldePrev ? $soldePrev->jours_pris : null;

                        // Cumul N + N-1
                        $cumulRestants = $soldePrev ? ($s->jours_restants + $soldePrev->jours_restants) : null;
                        $cumulPris     = $soldePrev ? ($s->jours_pris + $soldePrev->jours_pris) : null;
                        $cumulDispo    = $soldePrev ? ($td + $tdPrev) : null;

                        // Évolution du solde restant vs année précédente
                        $evolution     = $soldePrev ? ($s->jours_restants - $soldePrev->jours_restants) : null;
                        $evolutionPct  = ($soldePrev && $restantsPrev > 0) ? round((($s->jours_restants - $restantsPrev) / $restantsPrev) * 100) : null;
                        $evoColor      = $evolution === null ? 'secondary' : ($evolution > 0 ? 'success' : ($evolution < 0 ? 'danger' : 'secondary'));
                        $evoIcon       = $evolution === null ? 'minus' : ($evolution > 0 ? 'arrow-up' : ($evolution < 0 ? 'arrow-down' : 'minus'));
                    @endphp
                    <div class="tab-pane fade {{ $s->id === $solde->id ? 'show active' : '' }}"
                         id="annee-{{ $s->annee }}">

                        <div class="row p-4">

                            {{-- Colonne gauche : KPIs --}}
                            <div class="col-lg-8">

                                {{-- 4 KPI cards --}}
                                <div class="row mb-4">
                                    <div class="col-6 col-md-3 mb-3">
                                        <div class="kpi-card kpi-success">
                                            <div class="kpi-icon"><i class="fas fa-gift"></i></div>
                                            <div class="kpi-value">{{ $s->jours_acquis }}</div>
                                            <div class="kpi-label">Jours acquis</div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3 mb-3">
                                        <div class="kpi-card kpi-danger">
                                            <div class="kpi-icon"><i class="fas fa-plane-departure"></i></div>
                                            <div class="kpi-value">{{ $s->jours_pris }}</div>
                                            <div class="kpi-label">Jours pris</div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3 mb-3">
                                        <div class="kpi-card kpi-info">
                                            <div class="kpi-icon"><i class="fas fa-redo-alt"></i></div>
                                            <div class="kpi-value">{{ $s->jours_reportes ?? 0 }}</div>
                                            <div class="kpi-label">Reportés</div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3 mb-3">
                                        <div class="kpi-card kpi-primary">
                                            <div class="kpi-icon"><i class="fas fa-coins"></i></div>
                                            <div class="kpi-value">{{ $s->jours_restants }}</div>
                                            <div class="kpi-label">Restants</div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Barre progression solde restant --}}
                                <div class="progress-section mb-4">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="progress-title">Solde restant</span>
                                        <span class="progress-pct text-{{ $cp }}">{{ $p }}%</span>
                                    </div>
                                    <div class="progress progress-lg">
                                        <div class="progress-bar bg-{{ $cp }} progress-animated"
                                             data-width="{{ $p }}"
                                             style="width: 0%; border-radius: 6px;">
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1">
                                        <small class="text-muted">0 j</small>
                                        <small class="text-muted">{{ $td }} j disponibles</small>
                                    </div>
                                </div>

                                {{-- Barre taux d'utilisation --}}
                                <div class="progress-section mb-4">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="progress-title">Taux d'utilisation</span>
                                        <span class="progress-pct text-{{ $cup }}">{{ $tu }}%</span>
                                    </div>
                                    <div class="progress progress-lg">
                                        <div class="progress-bar bg-{{ $cup }} progress-animated"
                                             data-width="{{ $tu }}"
                                             style="width: 0%; border-radius: 6px;">
                                        </div>
                                    </div>
                                </div>

                                {{-- Tableau détaillé --}}
                                <div class="table-responsive">
                                    <table class="table table-borderless solde-table">
                                        <tbody>
                                            <tr>
                                                <td><i class="fas fa-gift text-success mr-2"></i>Jours acquis {{ $s->annee }}</td>
                                                <td class="text-right"><span class="badge badge-success badge-pill px-3 py-2">{{ $s->jours_acquis }} j</span></td>
                                            </tr>
                                            <tr>
                                                <td><i class="fas fa-redo-alt text-info mr-2"></i>Jours reportés</td>
                                                <td class="text-right"><span class="badge badge-info badge-pill px-3 py-2">{{ $s->jours_reportes ?? 0 }} j</span></td>
                                            </tr>
                                            <tr class="tr-total">
                                                <td><i class="fas fa-layer-group text-primary mr-2"></i><strong>Total disponible</strong></td>
                                                <td class="text-right"><span class="badge badge-primary badge-pill px-3 py-2">{{ $td }} j</span></td>
                                            </tr>
                                            <tr>
                                                <td><i class="fas fa-plane-departure text-danger mr-2"></i>Jours pris</td>
                                                <td class="text-right"><span class="badge badge-danger badge-pill px-3 py-2">- {{ $s->jours_pris }} j</span></td>
                                            </tr>
                                            <tr class="tr-result">
                                                <td><i class="fas fa-coins text-{{ $cp }} mr-2"></i><strong>Solde restant</strong></td>
                                                <td class="text-right">
                                                    <span class="badge badge-{{ $cp }} badge-pill px-3 py-2" style="font-size:1em;">
                                                        {{ $s->jours_restants }} j
                                                    </span>
                                                </td>
                                            </tr>

                                            {{-- ---- Séparateur évolution ---- --}}
                                            @if($soldePrev)
                                            <tr class="tr-separator">
                                                <td colspan="2">
                                                    <span class="separator-label">
                                                        <i class="fas fa-chart-line mr-1"></i>
                                                        Comparaison & cumul avec {{ $soldePrev->annee }}
                                                    </span>
                                                </td>
                                            </tr>

                                            {{-- Solde restant année précédente --}}
                                            <tr class="tr-prev">
                                                <td>
                                                    <i class="fas fa-history text-muted mr-2"></i>
                                                    Solde restant {{ $soldePrev->annee }}
                                                </td>
                                                <td class="text-right">
                                                    <span class="badge badge-light badge-pill px-3 py-2 text-dark border">
                                                        {{ $restantsPrev }} j
                                                    </span>
                                                </td>
                                            </tr>

                                            {{-- Évolution --}}
                                            <tr class="tr-prev">
                                                <td>
                                                    <i class="fas fa-{{ $evoIcon }} text-{{ $evoColor }} mr-2"></i>
                                                    Évolution du solde restant
                                                    @if($evolutionPct !== null)
                                                        <small class="text-muted">(vs {{ $soldePrev->annee }})</small>
                                                    @endif
                                                </td>
                                                <td class="text-right">
                                                    <span class="badge badge-{{ $evoColor }} badge-pill px-3 py-2">
                                                        {{ $evolution > 0 ? '+' : '' }}{{ $evolution }} j
                                                        @if($evolutionPct !== null)
                                                            ({{ $evolutionPct > 0 ? '+' : '' }}{{ $evolutionPct }}%)
                                                        @endif
                                                    </span>
                                                </td>
                                            </tr>

                                            {{-- Cumul jours pris --}}
                                            <tr class="tr-prev">
                                                <td>
                                                    <i class="fas fa-plane text-warning mr-2"></i>
                                                    Cumul jours pris {{ $soldePrev->annee }}–{{ $s->annee }}
                                                </td>
                                                <td class="text-right">
                                                    <span class="badge badge-warning badge-pill px-3 py-2">
                                                        {{ $cumulPris }} j
                                                    </span>
                                                </td>
                                            </tr>

                                            {{-- Cumul solde restant --}}
                                            <tr class="tr-cumul">
                                                <td>
                                                    <i class="fas fa-layer-group text-primary mr-2"></i>
                                                    <strong>Cumul solde restant {{ $soldePrev->annee }}–{{ $s->annee }}</strong>
                                                </td>
                                                <td class="text-right">
                                                    <span class="badge badge-primary badge-pill px-3 py-2" style="font-size:1em;">
                                                        {{ $cumulRestants }} j / {{ $cumulDispo }} j dispo
                                                    </span>
                                                </td>
                                            </tr>
                                            @else
                                            <tr class="tr-separator">
                                                <td colspan="2">
                                                    <span class="separator-label text-muted">
                                                        <i class="fas fa-info-circle mr-1"></i>
                                                        Aucune année précédente disponible pour la comparaison
                                                    </span>
                                                </td>
                                            </tr>
                                            @endif

                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Colonne droite : jauge + actions --}}
                            <div class="col-lg-4">

                                {{-- Jauge circulaire --}}
                                <div class="gauge-card text-center mb-3">
                                    <div class="gauge-wrapper">
                                        <svg viewBox="0 0 120 120" class="gauge-svg-sm">
                                            <circle cx="60" cy="60" r="50" fill="none" stroke="#e5e7eb" stroke-width="10"/>
                                            <circle cx="60" cy="60" r="50" fill="none"
                                                    stroke="{{ $cpHex }}"
                                                    stroke-width="10"
                                                    stroke-dasharray="{{ round($p * 3.14) }} 314"
                                                    stroke-dashoffset="78.5"
                                                    stroke-linecap="round"/>
                                        </svg>
                                        <div class="gauge-center-sm">
                                            <div class="gauge-val-sm">{{ $s->jours_restants }}</div>
                                            <div class="gauge-sub-sm">/ {{ $td }} j</div>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <span class="badge badge-{{ $cp }} px-3 py-2">
                                            {{ $p }}% du solde disponible
                                        </span>
                                    </div>
                                </div>

                                {{-- Alerte solde critique --}}
                                @if($p < 25)
                                <div class="alert alert-danger alert-sm">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    Solde critique — moins de 25% restant
                                </div>
                                @elseif($p < 50)
                                <div class="alert alert-warning alert-sm">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    Solde modéré — moins de 50% restant
                                </div>
                                @endif

                                {{-- Infos techniques --}}
                                <ul class="list-group list-group-flush meta-list">
                                    <li class="list-group-item d-flex justify-content-between px-0">
                                        <span class="text-muted">Année</span>
                                        <strong>{{ $s->annee }}</strong>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between px-0">
                                        <span class="text-muted">Taux d'utilisation</span>
                                        <strong class="text-{{ $cup }}">{{ $tu }}%</strong>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between px-0">
                                        <span class="text-muted">Dernière MAJ</span>
                                        <span>{{ optional($s->updated_at)->format('d/m/Y') ?? '—' }}</span>
                                    </li>
                                </ul>

                                {{-- Actions --}}
                                <div class="mt-3 d-flex flex-column" style="gap: 8px;">
                                    <a href="{{ route('admin.soldes.edit', $s) }}" class="btn btn-warning btn-block">
                                        <i class="fas fa-edit mr-1"></i> Modifier ce solde
                                    </a>
                                    <form action="{{ route('admin.soldes.destroy', $s) }}"
                                          method="POST" class="d-inline delete-solde-form">
                                        @csrf @method('DELETE')
                                        <button type="button"
                                                class="btn btn-outline-danger btn-block delete-solde-btn">
                                            <i class="fas fa-trash mr-1"></i> Supprimer
                                        </button>
                                    </form>
                                </div>
                            </div>

                        </div>
                    </div>
                    @endforeach
                </div>

            </div>
        </div>

    </div>
</section>
@endsection

@push('styles')
<style>
/* ========== HERO ========== */
.solde-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2d6a9f 60%, #3b82c4 100%);
    border-radius: 16px;
    padding: 32px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 24px;
    box-shadow: 0 8px 32px rgba(30,58,95,.25);
    color: white;
}

.hero-left { display: flex; align-items: center; gap: 20px; }

.hero-avatar {
    width: 80px; height: 80px;
    border-radius: 50%;
    background: rgba(255,255,255,.2);
    border: 3px solid rgba(255,255,255,.4);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.8em; font-weight: 700; color: white;
    overflow: hidden; flex-shrink: 0;
}
.hero-avatar img { width: 100%; height: 100%; object-fit: cover; }

.hero-info h2 { margin: 0; font-size: 1.5em; font-weight: 700; color: white; }
.hero-meta { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 6px; }
.hero-meta span { font-size: .85em; opacity: .85; }
.hero-meta i { margin-right: 4px; }

.hero-right { display: flex; flex-direction: column; align-items: center; min-width: 200px; }
.hero-gauge-label { font-size: .9em; opacity: .8; margin-bottom: 8px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; }

.hero-gauge { position: relative; width: 130px; height: 130px; }
.gauge-svg { width: 100%; height: 100%; transform: rotate(-90deg); }
.gauge-center {
    position: absolute; top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
}
.gauge-value { font-size: 2em; font-weight: 800; color: white; line-height: 1; }
.gauge-sub { font-size: .75em; opacity: .75; }

.hero-gauge-stats {
    display: flex; align-items: center; gap: 10px; margin-top: 10px;
}
.gs-item { text-align: center; }
.gs-val { display: block; font-size: 1.3em; font-weight: 700; }
.gs-lab { display: block; font-size: .72em; opacity: .75; }
.gs-sep { opacity: .4; font-size: 1.4em; }

/* ========== KPI CARDS ========== */
.kpi-card {
    border-radius: 12px;
    padding: 18px 14px;
    text-align: center;
    transition: transform .2s, box-shadow .2s;
    height: 100%;
}
.kpi-card:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,.1); }

.kpi-icon { font-size: 1.5em; margin-bottom: 8px; }
.kpi-value { font-size: 2em; font-weight: 800; line-height: 1; }
.kpi-label { font-size: .78em; font-weight: 500; margin-top: 4px; opacity: .75; }

.kpi-success { background: #d1fae5; color: #065f46; }
.kpi-success .kpi-icon { color: #10b981; }
.kpi-danger  { background: #fee2e2; color: #7f1d1d; }
.kpi-danger  .kpi-icon { color: #ef4444; }
.kpi-info    { background: #dbeafe; color: #1e3a8a; }
.kpi-info    .kpi-icon { color: #3b82f6; }
.kpi-primary { background: #ede9fe; color: #4c1d95; }
.kpi-primary .kpi-icon { color: #7c3aed; }

/* ========== PROGRESS ========== */
.progress-section { }
.progress-title { font-weight: 600; font-size: .9em; color: #374151; }
.progress-pct { font-weight: 700; font-size: .9em; }
.progress-lg { height: 12px; border-radius: 6px; background: #e5e7eb; }

/* ========== SOLDE TABLE ========== */
.solde-table td { padding: .6rem .75rem; font-size: .92em; vertical-align: middle; }
.tr-total td { border-top: 2px solid #e5e7eb; background: #f9fafb; }
.tr-result td { border-top: 2px solid #d1d5db; background: #f3f4f6; font-size: 1em; }

/* ========== JAUGE PETITE ========== */
.gauge-card { background: #f9fafb; border-radius: 12px; padding: 20px 16px; }
.gauge-wrapper { position: relative; width: 130px; height: 130px; margin: 0 auto; }
.gauge-svg-sm { width: 100%; height: 100%; transform: rotate(-90deg); }
.gauge-center-sm {
    position: absolute; top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
}
.gauge-val-sm { font-size: 1.8em; font-weight: 800; color: #111827; line-height: 1; }
.gauge-sub-sm { font-size: .72em; color: #6b7280; }

/* ========== MISC ========== */
.meta-list .list-group-item { font-size: .88em; border: none; border-bottom: 1px solid #f3f4f6; }
.alert-sm { padding: .5rem .75rem; font-size: .85em; }
.badge-sm { font-size: .65em; }

/* ========== CARD TOTAL GLOBAL ========== */
.card-total-global { border-top: 4px solid #1e3a5f; }

.total-kpi {
    border-radius: 12px;
    padding: 16px 12px;
    text-align: center;
    transition: transform .2s, box-shadow .2s;
}
.total-kpi:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,.1); }
.total-icon { font-size: 1.4em; margin-bottom: 6px; }
.total-val { font-size: 2.2em; font-weight: 800; line-height: 1; }
.total-lab { font-size: .75em; font-weight: 600; margin-top: 4px; opacity: .7; }

.total-success { background: #d1fae5; color: #065f46; }
.total-success .total-icon { color: #10b981; }
.total-danger  { background: #fee2e2; color: #7f1d1d; }
.total-danger  .total-icon { color: #ef4444; }
.total-info    { background: #dbeafe; color: #1e3a8a; }
.total-info    .total-icon { color: #3b82f6; }
.total-success.total-success { }
.total-warning { background: #fef3c7; color: #78350f; }
.total-warning .total-icon { color: #f59e0b; }

.global-summary {
    background: #f9fafb;
    border-radius: 12px;
    padding: 20px;
    border-left: 4px solid #1e3a5f;
}
.gs-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #e5e7eb;
}
.gs-row:last-child { border-bottom: none; }
.gs-label { color: #6b7280; font-size: .88em; }
.gs-value { font-weight: 700; font-size: 1em; }
.gs-row-bold .gs-label { color: #374151; font-weight: 600; }

/* Tabs */
.nav-tabs .nav-link { font-size: .9em; color: #6b7280; border-radius: 8px 8px 0 0; }
.nav-tabs .nav-link.active { color: #1e3a5f; font-weight: 700; }

/* ========== LIGNES ÉVOLUTION / CUMUL ========== */
.tr-separator td {
    padding: 10px 12px 6px;
    background: transparent;
}
.separator-label {
    display: inline-block;
    font-size: .78em;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #6b7280;
    border-bottom: 2px dashed #d1d5db;
    padding-bottom: 4px;
    width: 100%;
}

.tr-prev td {
    background: #f8faff;
    font-size: .88em;
    color: #6b7280;
    padding: .45rem .75rem;
    border-bottom: 1px solid #f0f4ff;
}

.tr-cumul td {
    background: #eef2ff;
    font-size: .92em;
    font-weight: 600;
    padding: .6rem .75rem;
    border-top: 2px solid #c7d2fe;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function () {

    // Animation des barres de progression au chargement
    function animateVisibleBars() {
        $('.tab-pane.active .progress-animated').each(function () {
            const w = $(this).data('width');
            $(this).animate({ width: w + '%' }, 800);
        });
    }
    animateVisibleBars();

    // Animer aussi les barres globales (hors tab-pane)
    $('.card-total-global .progress-animated').each(function () {
        const w = $(this).data('width');
        $(this).animate({ width: w + '%' }, 1000);
    });

    // Animer les barres quand on change d'onglet
    $('a[data-toggle="tab"]').on('shown.bs.tab', function () {
        const target = $(this).attr('href');
        $(target + ' .progress-animated').each(function () {
            $(this).css('width', '0%');
            const w = $(this).data('width');
            $(this).animate({ width: w + '%' }, 800);
        });
    });

    // Suppression avec confirmation
    $('.delete-solde-btn').on('click', function () {
        const form = $(this).closest('form');
        Swal.fire({
            title: 'Supprimer ce solde ?',
            text: 'Cette action est irréversible.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler'
        }).then(r => { if (r.isConfirmed) form.submit(); });
    });

});
</script>
@endpush
