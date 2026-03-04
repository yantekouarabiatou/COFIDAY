@extends('layaout')

@section('title', 'Paramètres de l\'Entreprise')

@section('content')
    <section class="section">
        <div class="section-body">
            <div class="row justify-content-center">
                <div class="col-12 col-xl-12">

                    <div class="card card-primary shadow-lg">
                        <div class="card-header text-white d-flex justify-content-between align-items-center py-3"
                            style="background-color:#244584">
                            <h4 class="mb-0 text-white"><i class="fas fa-cogs mr-2"></i> Paramètres de l'Entreprise</h4>
                            <a href="{{ route('settings.edit') }}" class="btn btn-warning btn-icon icon-left">
                                <i class="fas fa-pencil-alt"></i> Modifier les Paramètres
                            </a>
                        </div>

                        <div class="card-body">
                            <div class="row">

                                {{-- Colonne gauche : Identité --}}
                                <div class="col-md-4 text-center border-right">
                                    <h6 class="text-primary mb-3">Identité</h6>

                                    @if($setting->logo)
                                        <img src="{{ asset('storage/' . $setting->logo) }}"
                                            alt="Logo {{ $setting->company_name }}" class="img-fluid mb-3"
                                            style="object-fit: contain; display:inline-block; max-width: 150px; max-height: 150px; border: 2px solid #dee2e6; padding: 5px;">
                                    @else
                                        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3 shadow"
                                            style="width: 120px; height: 120px; font-size: 50px; border: 2px solid #dee2e6;">
                                            <i class="fas fa-building text-muted"></i>
                                        </div>
                                    @endif

                                    <h5 class="mb-1 text-dark">{{ $setting->company_name }}</h5>
                                    <p class="text-muted small">{{ $setting->slogan }}</p>

                                    {{-- Section Documentation --}}
                                    @if($setting->guide)
                                        <h6 class="text-primary mt-4 mb-3">
                                            <i class="fas fa-book-open mr-1"></i> Documentation
                                        </h6>
                                        <ul class="list-group list-group-flush detail-list">
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <span class="text-muted">Guide d'utilisation</span>
                                                <div>
                                                    <a href="{{ route('settings.guide.view') }}" target="_blank"
                                                        class="btn btn-outline-info btn-sm mr-1">
                                                        <i class="fas fa-eye mr-1"></i> Voir
                                                    </a>
                                                    <a href="{{ route('settings.guide.download') }}"
                                                        class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-download mr-1"></i> Télécharger
                                                    </a>
                                                </div>
                                            </li>
                                        </ul>
                                    @endif
                                </div>

                                {{-- Colonne droite : Informations --}}
                                <div class="col-md-8">
                                    <h6 class="text-primary mb-3">
                                        <i class="fas fa-info-circle mr-1"></i> Informations de Base
                                    </h6>
                                    <ul class="list-group list-group-flush mb-4 detail-list">
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span class="text-muted">Nom Légal</span>
                                            <span class="font-weight-bold">{{ $setting->company_name }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span class="text-muted">Email de Contact</span>
                                            <span>
                                                <i class="fas fa-envelope text-info mr-1"></i>
                                                {{ $setting->email }}
                                            </span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span class="text-muted">Téléphone</span>
                                            <span>
                                                <i class="fas fa-phone text-info mr-1"></i>
                                                {{ $setting->telephone ?? '-' }}
                                            </span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span class="text-muted">Site Web</span>
                                            <span>
                                                @if($setting->site_web)
                                                    <a href="{{ $setting->site_web }}" target="_blank">
                                                        <i class="fas fa-globe mr-1"></i> {{ $setting->site_web }}
                                                    </a>
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        </li>
                                    </ul>

                                    <h6 class="text-primary mt-4 mb-3">
                                        <i class="fas fa-map-marker-alt mr-1"></i> Localisation
                                    </h6>
                                    <ul class="list-group list-group-flush detail-list">
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span class="text-muted">Adresse Complète</span>
                                            <span class="font-weight-bold">{{ $setting->adresse ?? '-' }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span class="text-muted">Ville / Pays</span>
                                            <span class="font-weight-bold">
                                                {{ $setting->ville ?? '-' }} / {{ $setting->pays ?? '-' }}
                                            </span>
                                        </li>
                                    </ul>

                                   {{-- Section Documentation --}}
                                    @if($setting->guide)
                                        <h6 class="text-primary mt-4 mb-3">
                                            <i class="fas fa-book-open mr-1"></i> Documentation
                                        </h6>
                                        <ul class="list-group list-group-flush detail-list">
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <span class="text-muted">Guide d'utilisation</span>
                                                <div>
                                                    <a href="{{ route('settings.guide.view') }}"
                                                        target="_blank"
                                                        class="btn btn-outline-info btn-sm mr-1">
                                                        <i class="fas fa-eye mr-1"></i> Voir
                                                    </a>
                                                    <a href="{{ route('settings.guide.download') }}"
                                                        class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-download mr-1"></i> Télécharger
                                                    </a>
                                                </div>
                                            </li>
                                        </ul>
                                    @endif
                                </div>

                            </div>
                        </div>

                        <div class="card-footer bg-whitesmoke text-right">
                            <small class="text-muted">
                                <i class="fas fa-history mr-1"></i> Dernière mise à jour :
                                <strong>{{ $setting->updated_at?->format('d/m/Y à H:i') ?? 'Non disponible' }}</strong>
                            </small>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
@endsection

@section('styles')
    <style>
        .detail-list .list-group-item {
            padding: .75rem 0;
            border-bottom: 1px dashed #eee;
        }

        .detail-list .list-group-item:last-child {
            border-bottom: none;
        }

        .list-group-flush .list-group-item {
            background-color: transparent;
        }
    </style>
@endsection
