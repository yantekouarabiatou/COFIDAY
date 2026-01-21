@extends('layaout')

@section('title', 'Règles de Congés')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-cogs"></i> Règles de Congés</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">
                <a href="{{ route('dashboard') }}">Dashboard</a>
            </div>
            <div class="breadcrumb-item">Paramétrage</div>
            <div class="breadcrumb-item">Congés</div>
        </div>
    </div>

    <div class="section-body">

        {{-- Résumé --}}
        <div class="row">

            <div class="col-md-3">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary">
                        <i class="fas fa-calendar-plus"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Jours / mois</h4>
                        </div>
                        <div class="card-body">
                            {{ $regles->getJoursParMoisFormatted() }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-success">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Jours / an</h4>
                        </div>
                        <div class="card-body">
                            {{ $regles->getJoursAcquisAnnuelsFormatted() }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-statistic-1">
                    <div class="card-icon {{ $regles->report_autorise ? 'bg-warning' : 'bg-secondary' }}">
                        <i class="fas fa-retweet"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Report</h4>
                        </div>
                        <div class="card-body">
                            {{ $regles->report_autorise ? 'Autorisé' : 'Non autorisé' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-statistic-1">
                    <div class="card-icon {{ $regles->validation_multiple ? 'bg-danger' : 'bg-info' }}">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Validation</h4>
                        </div>
                        <div class="card-body">
                            {{ $regles->validation_multiple ? 'Multiple' : 'Simple' }}
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Détails --}}
        <div class="row mt-4">
            <div class="col-12">
                <div class="card card-primary">

                    <div class="card-header">
                        <h4>Détails des règles</h4>
                        <div class="card-header-action">
                            <a href="{{ route('admin.regles-conges.edit', $regles) }}"
                               class="btn btn-primary btn-icon icon-left">
                                <i class="fas fa-edit"></i> Modifier les règles
                            </a>
                        </div>
                    </div>

                    <div class="card-body">

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-primary mb-2">
                                    <i class="fas fa-sliders-h"></i> Paramètres généraux
                                </h6>

                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Préavis minimum</span>
                                        <strong>{{ $regles->preavis_minimum }} h</strong>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Délai d'annulation</span>
                                        <strong>{{ $regles->delai_annulation }} h</strong>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Limite de report</span>
                                        <strong>
                                            {{ $regles->limite_report ? $regles->limite_report . ' jours' : '—' }}
                                        </strong>
                                    </li>
                                </ul>
                            </div>

                            <div class="col-md-6">
                                <h6 class="text-primary mb-2">
                                    <i class="fas fa-palette"></i> Affichage & calendrier
                                </h6>

                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>Couleur calendrier</span>
                                        <span class="badge"
                                              style="background-color: {{ $regles->couleur_calendrier }};">
                                            {{ $regles->couleur_calendrier }}
                                        </span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Jours fériés</span>
                                        <strong>{{ count($regles->jours_feries_array) }}</strong>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Périodes bloquées</span>
                                        <strong>{{ count($regles->periodes_bloquees_array) }}</strong>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        {{-- Tableau des jours fériés --}}
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4><i class="fas fa-star text-warning"></i> Jours Fériés</h4>
                                    </div>
                                    <div class="card-body">
                                        @if(count($regles->jours_feries_array) > 0)
                                            <div class="table-responsive">
                                                <table class="table table-striped table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Nom</th>
                                                            <th>Date (JJ-MM)</th>
                                                            <th>Date complète</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($regles->jours_feries_array as $index => $jour)
                                                            @php
                                                                // Format: MM-DD stocké dans la base
                                                                [$month, $day] = explode('-', $jour['date']);
                                                                // Créer une date avec l'année actuelle pour l'affichage
                                                                $currentYear = date('Y');
                                                                $fullDate = \Carbon\Carbon::createFromDate($currentYear, $month, $day);
                                                                
                                                                // Formater la date en français
                                                                \Carbon\Carbon::setLocale('fr');
                                                                
                                                                // Récupérer le nom du jour en français
                                                                $jourSemaine = $fullDate->translatedFormat('l');
                                                                
                                                                // Récupérer le nom du mois en français
                                                                $mois = $fullDate->translatedFormat('F');
                                                                
                                                                // Format pour l'affichage
                                                                $dateFormatee = ucfirst($jourSemaine) . ' ' . $fullDate->format('j') . ' ' . $mois . ' ' . $fullDate->format('Y');
                                                                $dateSimple = $fullDate->format('j') . ' ' . $mois;
                                                            @endphp
                                                            <tr>
                                                                <td>{{ $loop->iteration }}</td>
                                                                <td>
                                                                    <strong>{{ $jour['nom'] }}</strong>
                                                                </td>
                                                                <td>
                                                                    <span class="badge bg-info">
                                                                        {{ sprintf('%02d', $day) }}-{{ sprintf('%02d', $month) }}
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    {{ $dateFormatee }}
                                                                    <small class="text-muted">
                                                                        ({{ $dateSimple }})
                                                                    </small>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle"></i> Aucun jour férié n'a été configuré.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Tableau des périodes bloquées --}}
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4><i class="fas fa-ban text-danger"></i> Périodes Bloquées</h4>
                                    </div>
                                    <div class="card-body">
                                        @if(count($regles->periodes_bloquees_array) > 0)
                                            <div class="table-responsive">
                                                <table class="table table-striped table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Nom</th>
                                                            <th>Début</th>
                                                            <th>Fin</th>
                                                            <th>Durée</th>
                                                            <th>Raison</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($regles->periodes_bloquees_array as $index => $periode)
                                                            @php
                                                                \Carbon\Carbon::setLocale('fr');
                                                                
                                                                $debut = \Carbon\Carbon::parse($periode['debut']);
                                                                $fin = \Carbon\Carbon::parse($periode['fin']);
                                                                $duree = $debut->diffInDays($fin) + 1; // +1 pour inclure le dernier jour
                                                                
                                                                // Formater les dates en français
                                                                $debutFormate = $debut->translatedFormat('l d F Y');
                                                                $finFormatee = $fin->translatedFormat('l d F Y');
                                                            @endphp
                                                            <tr>
                                                                <td>{{ $loop->iteration }}</td>
                                                                <td>
                                                                    <strong>{{ $periode['nom'] }}</strong>
                                                                </td>
                                                                <td>
                                                                    <span class="badge bg-warning" title="{{ $debutFormate }}">
                                                                        {{ $debut->format('d/m/Y') }}
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <span class="badge bg-danger" title="{{ $finFormatee }}">
                                                                        {{ $fin->format('d/m/Y') }}
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <span class="badge bg-info">
                                                                        {{ $duree }} jour(s)
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    @if(!empty($periode['raison']))
                                                                        <small>{{ $periode['raison'] }}</small>
                                                                    @else
                                                                        <span class="text-muted">—</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle"></i> Aucune période bloquée n'a été configurée.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Version alternative avec tooltips pour voir la date complète en français --}}
                        <div class="row mt-4 d-none">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4><i class="fas fa-calendar-alt text-primary"></i> Détails des dates en français</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6>Jours Fériés en français</h6>
                                                <ul class="list-group">
                                                    @foreach($regles->jours_feries_array as $jour)
                                                        @php
                                                            [$month, $day] = explode('-', $jour['date']);
                                                            $currentYear = date('Y');
                                                            $fullDate = \Carbon\Carbon::createFromDate($currentYear, $month, $day);
                                                            \Carbon\Carbon::setLocale('fr');
                                                        @endphp
                                                        <li class="list-group-item d-flex justify-content-between">
                                                            <span>{{ $jour['nom'] }}</span>
                                                            <span class="text-muted">
                                                                {{ $fullDate->translatedFormat('l j F') }}
                                                            </span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <h6>Périodes en français</h6>
                                                <ul class="list-group">
                                                    @foreach($regles->periodes_bloquees_array as $periode)
                                                        @php
                                                            \Carbon\Carbon::setLocale('fr');
                                                            $debut = \Carbon\Carbon::parse($periode['debut']);
                                                            $fin = \Carbon\Carbon::parse($periode['fin']);
                                                        @endphp
                                                        <li class="list-group-item">
                                                            <div><strong>{{ $periode['nom'] }}</strong></div>
                                                            <div class="text-muted small">
                                                                Du {{ $debut->translatedFormat('l j F Y') }}
                                                                au {{ $fin->translatedFormat('l j F Y') }}
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Alerte métier --}}
                        @if($regles->validation_multiple)
                            <div class="alert alert-warning mt-4">
                                <i class="fas fa-exclamation-triangle"></i>
                                La validation multiple est activée : plusieurs niveaux de validation seront requis.
                            </div>
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
    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
        border-top: 2px solid #dee2e6;
    }
    
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(0, 123, 255, 0.02);
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }
    
    .badge {
        padding: 0.4em 0.8em;
        font-weight: 500;
        cursor: help;
    }
    
    .card .card {
        border: 1px solid #dee2e6;
        box-shadow: none;
    }
    
    .card .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        padding: 1rem 1.25rem;
    }
    
    .card .card-header h4 {
        font-size: 1.1rem;
        margin: 0;
        color: #495057;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser Carbon en français globalement
    @php
        \Carbon\Carbon::setLocale('fr');
    @endphp
    
    // Ajouter des tooltips aux badges pour voir la date complète
    $('[title]').tooltip({
        placement: 'top',
        trigger: 'hover'
    });
});
</script>
@endpush