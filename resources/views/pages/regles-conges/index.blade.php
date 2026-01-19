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

                        <div class="row">

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
                                        <span>Délai d’annulation</span>
                                        <strong>{{ $regles->delai_annulation }} h</strong>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Limite de report</span>
                                        <strong>
                                            {{ $regles->limite_report ?? '—' }}
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
