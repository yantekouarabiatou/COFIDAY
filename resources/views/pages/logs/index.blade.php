@extends('layaout')

@section('title', 'Journal d\'activité')

@section('content')
    <section class="section-body">
        <div class="section-header">
            <h1><i class="fas fa-history"></i> Journal d'activité</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Logs</div>
            </div>
        </div>

        <div class="section-body">
            <h2 class="section-title">Toutes les activités du système</h2>
            <p class="section-lead">Suivi complet des actions des utilisateurs sur toutes les ressources</p>

            <div class="row">
                <div class="col-12">
                    <div class="activities">

                        @forelse($logs as $log)
                            <div class="activity">
                                <div class="activity-icon bg-{{ $log->action_color }} text-white shadow-primary">
                                    <i class="fas {{ $log->icon }}"></i>
                                </div>

                                <div class="activity-detail">
                                    <div class="mb-2">
                                        <span class="text-job text-{{ $log->action_color }}">
                                            {{ $log->created_at->diffForHumans() }}
                                        </span>
                                        <span class="bullet"></span>
                                        <a href="{{ route('logs.show', $log) }}" class="text-job">
                                            Voir le détail
                                        </a>

                                        <div class="float-right dropdown">
                                            <a href="#" data-toggle="dropdown"><i class="fas fa-ellipsis-h"></i></a>
                                            <div class="dropdown-menu">
                                                <div class="dropdown-title">Options</div>
                                                <a href="{{ route('logs.show', $log) }}" class="dropdown-item has-icon">
                                                    <i class="fas fa-eye"></i> Détail complet
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <p>
                                        <strong>{{ $log->user?->full_name ?? 'Système' }}</strong>
                                        a <strong>{{ __($log->action) }}</strong>

                                        @if($log->loggable)
                                            <a href="{{ $log->url }}" class="text-primary font-weight-bold" target="_blank">
                                                {{ $log->reference }}
                                            </a>
                                        @else
                                            <span class="text-muted font-italic">
                                                {{ $log->reference ?? 'une ressource supprimée' }}
                                            </span>
                                        @endif

                                        @if($log->description)
                                            <br><small class="text-muted">{{ $log->description }}</small>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>Aucune activité enregistrée pour le moment.</p>
                            </div>
                        @endforelse

                        <div class="mt-4 d-flex justify-content-center"style="flex-direction: column; align-items: center; background-color: #244584; padding: 10px; border-radius: 5px;">
                            {{ $logs->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
    <style>
        .activity {
            display: flex;
            margin-bottom: 20px;
            align-items: flex-start;
        }
        .activity-icon {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            text-align: center;
            line-height: 45px;
            margin-right: 15px;
            font-size: 18px;
        }
        .activity-detail {
            flex: 1;
        }

        /* Styles pour les écrans mobiles */
        @media (max-width: 768px) {
            .pagination {
                gap: 4px;
            }

            .pagination .page-link {
                min-width: 36px;
                height: 36px;
                padding: 0 8px;
                font-size: 14px;
            }

            .pagination .page-item:not(.active):not(.disabled) .page-link span {
                display: none;
            }

            .pagination .page-item:not(.active):not(.disabled) .page-link::before {
                content: attr(data-short);
            }

            .page-link[data-short="Previous"]::before {
                content: "«";
            }

            .page-link[data-short="Next"]::before {
                content: "»";
            }
        }

        /* Style pour le texte d'information de pagination */
        .pagination-info {
            text-align: center;
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 15px;
        }

        /* Animation de chargement */
        .pagination .page-link {
            position: relative;
            overflow: hidden;
        }

        .pagination .page-link:active {
            transform: translateY(0);
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Ajouter des attributs data-short pour la version mobile
        document.addEventListener('DOMContentLoaded', function() {
            const pageLinks = document.querySelectorAll('.page-link');
            pageLinks.forEach(link => {
                if (link.textContent.includes('Previous')) {
                    link.setAttribute('data-short', 'Previous');
                } else if (link.textContent.includes('Next')) {
                    link.setAttribute('data-short', 'Next');
                }
            });
        });

        // Animation au clic sur la pagination
        document.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', function(e) {
                if (!this.parentElement.classList.contains('disabled') &&
                    !this.parentElement.classList.contains('active')) {
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 150);
                }
            });
        });
    </script>
@endpush
