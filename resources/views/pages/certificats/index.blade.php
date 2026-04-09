
@extends('layaout')
@section('title', 'Mes démissions')

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Erreur de validation</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif
@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-door-open"></i> Démission</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Démission</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">

                    <div class="card-header">
                        <h4>Mes lettres de démission</h4>
                        <div class="card-header-action">
                            @can('soumettre une démission')
                                @php
                                    $dejaEnCours = \App\Models\DemandeDemission::where('user_id', auth()->id())
                                        ->whereIn('statut', ['en_attente', 'approuve'])->exists();
                                @endphp
                                @unless($dejaEnCours)
                                    <a href="{{ route('demissions.create') }}" class="btn btn-danger btn-icon icon-left">
                                        <i class="fas fa-pen"></i> Soumettre ma démission
                                    </a>
                                @endunless
                            @endcan

                            @hasanyrole('directeur-general|rh|admin')
                                <a href="{{ route('demissions.validation.index') }}" class="btn btn-warning btn-icon icon-left ml-2">
                                    <i class="fas fa-check-double"></i> Validation DG
                                    @php $nbD = \App\Models\DemandeDemission::enAttente()->count(); @endphp
                                    @if($nbD > 0)
                                        <span class="badge badge-danger ml-1">{{ $nbD }}</span>
                                    @endif
                                </a>
                            @endhasanyrole
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        @if($isAdmin)<th>Collaborateur</th>@endif
                                        <th>Départ souhaité</th>
                                        <th>Départ effectif</th>
                                        <th>Statut</th>
                                        <th>Certificat</th>
                                        <th>Date soumission</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($demandes as $demande)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>

                                            @if($isAdmin)
                                                <td>
                                                    <strong>{{ $demande->user->prenom }} {{ $demande->user->nom }}</strong><br>
                                                    <small class="text-muted">{{ $demande->user->poste->libelle ?? '' }}</small>
                                                </td>
                                            @endif

                                            <td>{{ $demande->date_depart_souhaitee->format('d/m/Y') }}</td>

                                            <td>
                                                @if($demande->date_depart_effective)
                                                    <strong>{{ $demande->date_depart_effective->format('d/m/Y') }}</strong>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>

                                            <td>{!! $demande->statut_badge !!}</td>

                                            <td>
                                                @if($demande->certificat_genere)
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check"></i> Généré
                                                    </span>
                                                    <br><small class="text-muted">Réf. {{ $demande->certificat_reference }}</small>
                                                @else
                                                    <span class="badge badge-secondary">—</span>
                                                @endif
                                            </td>

                                            <td>
                                                {{ $demande->created_at->format('d/m/Y') }}<br>
                                                <small class="text-muted">{{ $demande->created_at->diffForHumans() }}</small>
                                            </td>

                                            <td class="text-center">
                                                <a href="{{ route('demissions.show', $demande) }}"
                                                   class="btn btn-info btn-sm" title="Voir détails">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ $isAdmin ? 8 : 7 }}" class="text-center py-5">
                                                <i class="fas fa-door-open fa-4x text-muted mb-3 d-block"></i>
                                                <h5>Aucune démission</h5>
                                                <p class="text-muted">Aucune demande de démission enregistrée.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted small">
                                @if($demandes->total() > 0)
                                    {{ $demandes->firstItem() }} à {{ $demandes->lastItem() }} sur {{ $demandes->total() }}
                                @endif
                            </div>
                            {{ $demandes->links('vendor.pagination.stisla') }}
                        </div>

                        {{-- Stats --}}
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-warning"><i class="fas fa-clock"></i></div>
                                    <div class="card-wrap">
                                        <div class="card-header"><h4>En attente</h4></div>
                                        <div class="card-body">{{ $enAttente }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-success"><i class="fas fa-check-circle"></i></div>
                                    <div class="card-wrap">
                                        <div class="card-header"><h4>Approuvées</h4></div>
                                        <div class="card-body">{{ $approuvees }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
