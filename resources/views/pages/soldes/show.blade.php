@extends('layaout')

@section('title', 'Détails du solde')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-info-circle"></i> Détails du solde</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.soldes.index') }}">Soldes</a></div>
            <div class="breadcrumb-item active">Détails</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Solde de {{ $solde->user->prenom }} {{ $solde->user->nom }} pour {{ $solde->annee }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-success">
                                        <i class="fas fa-gift"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header"><h4>Jours acquis</h4></div>
                                        <div class="card-body">{{ $solde->jours_acquis }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-warning">
                                        <i class="fas fa-plane"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header"><h4>Jours pris</h4></div>
                                        <div class="card-body">{{ $solde->jours_pris }}</div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-info">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header"><h4>Jours reportés</h4></div>
                                        <div class="card-body">{{ $solde->jours_reportes ?? 0 }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 ">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-primary">
                                        <i class="fas fa-coins"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header"><h4>Jours restants</h4></div>
                                        <div class="card-body">{{ $solde->jours_restants }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <a href="{{ route('admin.soldes.edit', $solde) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Modifier
                        </a>
                        <a href="{{ route('admin.soldes.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Retour
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
