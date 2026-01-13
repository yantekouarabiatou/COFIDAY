@php
use App\Helpers\UserHelper;
@endphp

@extends('layaout')

@section('title', 'Feuilles de Temps')

@section('content')
<section class="section">
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
                    <div class="card-header">
                        <h4>Toutes les Feuilles de Temps</h4>
                        <div class="card-header-action">
                            <a href="{{ route('daily-entries.create') }}" class="btn btn-icon icon-left btn-primary">
                                <i class="fas fa-plus"></i> Nouvelle Saisie
                            </a>
                            <div class="dropdown d-inline ml-2">
                                <button class="btn btn-icon icon-left btn-info dropdown-toggle" type="button" data-toggle="dropdown">
                                    <i class="fas fa-filter"></i> Filtrer
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="{{ route('daily-entries.index') }}">Toutes</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="{{ route('daily-entries.index', ['statut' => 'soumis']) }}">Soumises</a>
                                    <a class="dropdown-item" href="{{ route('daily-entries.index', ['statut' => 'validé']) }}">Validées</a>
                                    <a class="dropdown-item" href="{{ route('daily-entries.index', ['statut' => 'refusé']) }}">Refusées</a>
                                    @if(auth()->user()->hasRole('responsable'))
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="{{ route('daily-entries.index', ['user' => auth()->id()]) }}">Mes feuilles</a>
                                        <a class="dropdown-item" href="{{ route('daily-entries.index', ['pending' => true]) }}">À valider</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <form action="{{ route('daily-entries.index') }}" method="GET" class="form-inline">
                                    <div class="input-group w-100">
                                        <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="submit">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-6 text-right">
                                <button type="button" class="btn btn-secondary" onclick="window.print()">
                                    <i class="fas fa-print"></i> Imprimer
                                </button>
                                <button type="button" class="btn btn-success ml-2" data-toggle="modal" data-target="#exportModal">
                                    <i class="fas fa-file-export"></i> Exporter
                                </button>
                            </div>
                        </div>

                        @if($dailyEntries->isEmpty())
                            <div class="text-center py-5">
                                <div class="empty-state-icon mb-4">
                                    <i class="fas fa-clock fa-5x text-muted"></i>
                                </div>
                                <h2>Aucune feuille de temps trouvée</h2>
                                <p class="lead text-muted">
                                    @if(request()->hasAny(['statut', 'date', 'user', 'pending']))
                                        Essayez de modifier les filtres.
                                    @else
                                        Commencez par saisir votre première feuille de temps.
                                    @endif
                                </p>
                                <a href="{{ route('daily-entries.create') }}" class="btn btn-primary mt-3">
                                    <i class="fas fa-plus"></i> Nouvelle saisie
                                </a>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Collaborateur</th>
                                            <th>Heures</th>
                                            <th>Activités</th>
                                            <th>Statut</th>
                                            <th>Créée le</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($dailyEntries as $entry)
                                        <tr>
                                            <td>
                                                <strong>{{ $entry->jour->format('d/m/Y') }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $entry->jour->translatedFormat('l') }}</small>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar mr-3">
                                                        <div class="avatar-title rounded-circle text-white d-flex align-items-center justify-content-center"
                                                            style="width: 45px; height: 45px; background: linear-gradient(135deg, #667eea, #764ba2); font-weight: bold; font-size: 1.1rem;">
                                                            {{ strtoupper(substr($entry->user->prenom, 0, 1)) }}
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div>{{ $entry->user->prenom }} {{ $entry->user->nom }}</div>
                                                        <small class="text-muted">{{ $entry->user->poste->intitule ?? '-' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    $percentage = $entry->heures_theoriques > 0
                                                        ? ($entry->heures_reelles / $entry->heures_theoriques) * 100
                                                        : 0;
                                                    $bgColor = $percentage >= 100 ? 'bg-success' : ($percentage >= 80 ? 'bg-warning' : 'bg-danger');
                                                @endphp
                                                <div class="progress" style="height: 20px; width: 150px;">
                                                    <div class="progress-bar {{ $bgColor }}" style="width: {{ min($percentage, 100) }}%"></div>
                                                </div>
                                                <small class="text-muted d-block mt-1">
                                                    {{ UserHelper::hoursToHoursMinutes($entry->heures_reelles) }} / {{ UserHelper::hoursToHoursMinutes($entry->heures_theoriques) }}
                                                </small>
                                            </td>
                                            <td>
                                                <strong>{{ $entry->timeEntries->count() }} activité{{ $entry->timeEntries->count() > 1 ? 's' : '' }}</strong>
                                                <br>
                                                @if($entry->commentaire)
                                                    <small>{{ Str::limit($entry->commentaire, 50) }}</small>
                                                @else
                                                    <small class="text-muted">Aucun commentaire</small>
                                                @endif
                                            </td>
                                            <td>
                                                @switch($entry->statut)
                                                    @case('soumis')
                                                        <span class="badge badge-info">Soumise</span>
                                                        @break
                                                    @case('validé')
                                                        <span class="badge badge-success">Validée</span>
                                                        @if($entry->valide_le)
                                                            <br><small class="text-muted">{{ $entry->valide_le->format('d/m H:i') }}</small>
                                                        @endif
                                                        @break
                                                    @case('refusé')
                                                        <span class="badge badge-danger">Refusée</span>
                                                        @if($entry->motif_refus)
                                                            <br><small class="text-muted" title="{{ $entry->motif_refus }}">Motif présent</small>
                                                        @endif
                                                        @break
                                                    @default
                                                        <span class="badge badge-secondary">{{ ucfirst($entry->statut) }}</span>
                                                @endswitch
                                            </td>
                                            <td>
                                                <small>{{ $entry->created_at->format('d/m H:i') }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('daily-entries.show', $entry) }}" class="btn btn-sm btn-info" title="Voir">
                                                        <i class="fas fa-eye"></i>
                                                    </a>

                                                    @if($entry->user_id == auth()->id() && $entry->statut == 'soumis')
                                                        <a href="{{ route('daily-entries.edit', $entry) }}" class="btn btn-sm btn-warning" title="Modifier">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endif

                                                    @if(auth()->user()->hasRole('responsable') && $entry->statut == 'soumis')
                                                        <button type="button" class="btn btn-sm btn-success validate-btn"
                                                                data-id="{{ $entry->id }}"
                                                                data-name="{{ $entry->user->prenom }} {{ $entry->user->nom }}"
                                                                data-date="{{ $entry->jour->format('d/m/Y') }}">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-danger reject-btn"
                                                                data-id="{{ $entry->id }}"
                                                                data-name="{{ $entry->user->prenom }} {{ $entry->user->nom }}"
                                                                data-date="{{ $entry->jour->format('d/m/Y') }}">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    @endif

                                                    @if($entry->user_id == auth()->id() || auth()->user()->hasRole('responsable'))
                                                        <button type="button" class="btn btn-sm btn-outline-danger delete-btn"
                                                                data-id="{{ $entry->id }}"
                                                                data-name="{{ $entry->user->prenom }} {{ $entry->user->nom }}"
                                                                data-date="{{ $entry->jour->format('d/m/Y') }}">
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

                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <div class="text-muted">
                                    Affichage de {{ $dailyEntries->firstItem() }} à {{ $dailyEntries->lastItem() }} sur {{ $dailyEntries->total() }} entrée{{ $dailyEntries->total() > 1 ? 's' : '' }}
                                </div>
                                <div>
                                    {{ $dailyEntries->appends(request()->query())->links() }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Cartes de statistiques -->
        <div class="row mt-4">
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
    </div>
</section>

<!-- Modal Export -->
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Exporter les feuilles de temps</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('daily-entries.export') }}" method="GET">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Période</label>
                        <div class="row">
                            <div class="col-6">
                                <input type="date" name="date_debut" class="form-control" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                            </div>
                            <div class="col-6">
                                <input type="date" name="date_fin" class="form-control" value="{{ now()->format('Y-m-d') }}">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Format</label>
                        <select name="format" class="form-control">
                            <option value="excel">Excel</option>
                            <option value="pdf">PDF</option>
                            <option value="csv">CSV</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Exporter</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    $('.validate-btn').on('click', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const date = $(this).data('date');

        Swal.fire({
            title: 'Valider cette feuille ?',
            html: `Confirmez-vous la validation de la feuille de <strong>${name}</strong> du <strong>${date}</strong> ?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Oui, valider',
            cancelButtonText: 'Annuler'
        }).then(result => {
            if (result.isConfirmed) {
                $.post(`/daily-entries/${id}/validate`, { _token: '{{ csrf_token() }}' })
                 .done(() => location.reload())
                 .fail(() => Swal.fire('Erreur', 'Impossible de valider', 'error'));
            }
        });
    });

    $('.reject-btn').on('click', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const date = $(this).data('date');

        Swal.fire({
            title: 'Refuser cette feuille',
            input: 'textarea',
            inputLabel: 'Motif du refus (obligatoire)',
            inputPlaceholder: 'Précisez la raison du refus...',
            showCancelButton: true,
            confirmButtonText: 'Refuser',
            cancelButtonText: 'Annuler',
            inputValidator: value => !value ? 'Le motif est requis' : null
        }).then(result => {
            if (result.isConfirmed) {
                $.post(`/daily-entries/${id}/reject`, {
                    _token: '{{ csrf_token() }}',
                    motif_refus: result.value
                }).done(() => location.reload())
                  .fail(() => Swal.fire('Erreur', 'Impossible de refuser', 'error'));
            }
        });
    });

    $('.delete-btn').on('click', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const date = $(this).data('date');

        Swal.fire({
            title: 'Supprimer définitivement ?',
            html: `La feuille de <strong>${name}</strong> du <strong>${date}</strong> sera supprimée.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler'
        }).then(result => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/daily-entries/${id}`,
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' }
                }).done(() => location.reload())
                  .fail(() => Swal.fire('Erreur', 'Impossible de supprimer', 'error'));
            }
        });
    });
});
</script>
@endpush
