@extends('layaout')
@section('title', 'Détail de la démission')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-door-open"></i> Détail de la démission</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('demissions.index') }}">Démissions</a></div>
            <div class="breadcrumb-item">Détail</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">

                    <div class="card-header" style="border-left: 4px solid
                        @switch($demission->statut)
                            @case('approuve') #28a745 @break
                            @case('refuse')   #dc3545 @break
                            @default          #ffc107
                        @endswitch;">
                        <h4><i class="fas fa-door-open"></i> Lettre de démission</h4>
                        <div class="card-header-action">
                            {!! $demission->statut_badge !!}
                        </div>
                    </div>

                    <div class="card-body">

                        {{-- Infos employé --}}
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <p class="text-muted small mb-1">Employé(e)</p>
                                <p class="font-weight-bold mb-0">{{ $demission->user->prenom }} {{ $demission->user->nom }}</p>
                                <small class="text-muted">{{ $demission->user->email }}</small>
                            </div>
                            <div class="col-md-6">
                                <p class="text-muted small mb-1">Poste</p>
                                <p class="font-weight-bold mb-0">{{ $demission->user->poste->libelle ?? '—' }}</p>
                            </div>
                        </div>

                        <hr>

                        {{-- Dates --}}
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <p class="text-muted small mb-1">Date de soumission</p>
                                <p class="mb-0">{{ $demission->created_at->isoFormat('D MMMM YYYY') }}</p>
                            </div>
                            <div class="col-md-4">
                                <p class="text-muted small mb-1">Départ souhaité</p>
                                <p class="mb-0 font-weight-bold">{{ $demission->date_depart_souhaitee->isoFormat('D MMMM YYYY') }}</p>
                            </div>
                            @if($demission->date_depart_effective)
                                <div class="col-md-4">
                                    <p class="text-muted small mb-1">Départ effectif</p>
                                    <p class="mb-0 font-weight-bold text-success">{{ $demission->date_depart_effective->isoFormat('D MMMM YYYY') }}</p>
                                </div>
                            @endif
                        </div>

                        {{-- Corps de la lettre --}}
                        <div class="mb-4">
                            <p class="text-muted small mb-1">Corps de la lettre</p>
                            <div class="border rounded p-4 bg-light" style="white-space: pre-wrap; line-height: 1.9; font-size: .95rem;">{{ $demission->lettre }}</div>
                        </div>

                        {{-- Validation --}}
                        @if($demission->validateur)
                            <div class="mb-3">
                                <p class="text-muted small mb-1">Validé par</p>
                                <p class="mb-0">{{ $demission->validateur->prenom }} {{ $demission->validateur->nom }}
                                    <small class="text-muted ml-1">— {{ $demission->date_validation->isoFormat('D MMMM YYYY [à] HH:mm') }}</small>
                                </p>
                            </div>
                        @endif

                        @if($demission->commentaire_dg)
                            <div class="alert alert-info">
                                <i class="fas fa-comment"></i>
                                <strong>Commentaire Direction :</strong> {{ $demission->commentaire_dg }}
                            </div>
                        @endif

                        {{-- Certificat --}}
                        @if($demission->certificat_genere)
                            <div class="alert alert-success">
                                <i class="fas fa-certificate"></i>
                                <strong>Certificat de travail généré</strong><br>
                                Référence : <strong>{{ $demission->certificat_reference }}</strong><br>
                                <small>Généré le {{ $demission->certificat_genere_le->isoFormat('D MMMM YYYY [à] HH:mm') }}</small>
                            </div>
                            <div class="alert alert-warning">
                                <i class="fas fa-info-circle"></i>
                                Passez chez la <strong>secrétaire</strong> pour retirer la version originale cachetée et signée de votre certificat.
                            </div>
                        @endif

                    </div>

                    <div class="card-footer">
                        <a href="{{ route('demissions.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Retour
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>
@endsection
