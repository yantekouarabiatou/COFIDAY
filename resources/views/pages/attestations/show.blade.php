@extends('layaout')
@section('title', 'Détail de la demande')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-file-alt"></i> Détail de la demande</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('attestations.index') }}">Attestations</a></div>
            <div class="breadcrumb-item">Détail</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="card">

                    {{-- En-tête coloré selon le statut --}}
                    <div class="card-header" style="border-left: 4px solid
                        @switch($attestation->statut)
                            @case('approuve') #28a745 @break
                            @case('refuse')   #dc3545 @break
                            @default          #ffc107
                        @endswitch;">
                        <h4>
                            @switch($attestation->type)
                                @case('attestation_simple')
                                    <i class="fas fa-file-alt text-primary"></i> Attestation simple @break
                                @case('attestation_banque')
                                    <i class="fas fa-university text-success"></i> Attestation banque / crédit @break
                                @case('attestation_ambassade')
                                    <i class="fas fa-passport text-info"></i> Attestation ambassade / visa @break
                                @case('attestation_autre')
                                    <i class="fas fa-ellipsis-h text-secondary"></i> Format spécifique @break
                            @endswitch
                        </h4>
                        <div class="card-header-action">
                            {!! $attestation->statut_badge !!}
                        </div>
                    </div>

                    <div class="card-body">

                        {{-- Infos employé --}}
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <p class="text-muted small mb-1">Demandé par</p>
                                <p class="font-weight-bold mb-0">{{ $attestation->user->prenom }} {{ $attestation->user->nom }}</p>
                                <small class="text-muted">{{ $attestation->user->email }}</small>
                            </div>
                            <div class="col-md-6">
                                <p class="text-muted small mb-1">Poste</p>
                                <p class="font-weight-bold mb-0">{{ $attestation->user->poste->libelle ?? '—' }}</p>
                            </div>
                        </div>

                        <hr>

                        {{-- Infos demande --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="text-muted small mb-1">Date de la demande</p>
                                <p class="mb-0">{{ $attestation->created_at->isoFormat('D MMMM YYYY [à] HH:mm') }}</p>
                            </div>
                            @if($attestation->date_validation)
                                <div class="col-md-6">
                                    <p class="text-muted small mb-1">Date de traitement</p>
                                    <p class="mb-0">{{ $attestation->date_validation->isoFormat('D MMMM YYYY [à] HH:mm') }}</p>
                                </div>
                            @endif
                        </div>

                        @if($attestation->destinataire)
                            <div class="mb-3">
                                <p class="text-muted small mb-1">Destinataire</p>
                                <p class="font-weight-bold mb-0">{{ $attestation->destinataire }}</p>
                            </div>
                        @endif

                        @if($attestation->inclure_salaire && $attestation->salaire_net)
                            <div class="mb-3">
                                <p class="text-muted small mb-1">Salaire net mentionné</p>
                                <p class="font-weight-bold mb-0">{{ number_format($attestation->salaire_net, 0, ',', ' ') }} FCFA</p>
                            </div>
                        @endif

                        {{-- Motif --}}
                        <div class="mb-3">
                            <p class="text-muted small mb-1">Corps de la demande</p>
                            <div class="border rounded p-3 bg-light" style="white-space: pre-wrap; line-height: 1.8; font-size: .95rem;">{{ $attestation->motif }}</div>
                        </div>

                        {{-- Résultat --}}
                        @if($attestation->validateur)
                            <div class="mb-3">
                                <p class="text-muted small mb-1">Traité par</p>
                                <p class="mb-0">{{ $attestation->validateur->prenom }} {{ $attestation->validateur->nom }}</p>
                            </div>
                        @endif

                        @if($attestation->numero_reference)
                            <div class="alert alert-success">
                                <i class="fas fa-hashtag"></i>
                                <strong>Numéro de référence :</strong> {{ $attestation->numero_reference }}
                            </div>
                        @endif

                        @if($attestation->commentaire_dg)
                            <div class="alert alert-info">
                                <i class="fas fa-comment"></i>
                                <strong>Commentaire Direction :</strong> {{ $attestation->commentaire_dg }}
                            </div>
                        @endif

                        @if($attestation->statut === 'approuve' && $attestation->type !== 'attestation_autre')
                            <div class="alert alert-warning">
                                <i class="fas fa-info-circle"></i>
                                <strong>Rappel :</strong> Passez chez la <strong>secrétaire</strong> pour retirer la version originale cachetée et signée.
                            </div>
                        @endif

                        @if($attestation->statut === 'approuve' && $attestation->type === 'attestation_autre')
                            <div class="alert alert-info">
                                <i class="fas fa-user-tie"></i>
                                Le service <strong>RH</strong> va prendre en charge votre demande et vous contactera directement.
                            </div>
                        @endif

                    </div>

                    <div class="card-footer">
                        <a href="{{ route('attestations.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Retour
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>
@endsection
