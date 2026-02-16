@php
    use App\Helpers\UserHelper;
@endphp

@extends('layaout')  <!-- ou 'layaout' si c'est vraiment ton nom de layout -->

@section('title', 'Feuilles de Temps')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>
            <i class="fas fa-clock"></i> Feuilles de Temps
        </h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">Feuilles de Temps</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Toutes les Feuilles de Temps</h4>
                        <div class="card-header-action">
                            <a href="{{ route('daily-entries.create') }}" class="btn btn-primary btn-icon icon-left">
                                <i class="fas fa-plus"></i> Nouvelle saisie
                            </a>

                            <div class="dropdown d-inline ml-2">
                                <button class="btn btn-info btn-icon icon-left dropdown-toggle" type="button" data-toggle="dropdown">
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
                        <!-- Filtres rapides -->
                        <div class="row mb-4 align-items-end">
                            <div class="col-md-4">
                                <form action="{{ route('daily-entries.index') }}" method="GET" class="form-inline">
                                    <div class="input-group">
                                        <input type="date" name="date" class="form-control" value="{{ request('date') }}" placeholder="Filtrer par date">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="submit">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div class="col-md-8 text-right">
                                <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                                    <i class="fas fa-print"></i> Imprimer
                                </button>
                                <button type="button" class="btn btn-outline-success ml-2" data-toggle="modal" data-target="#exportModal">
                                    <i class="fas fa-file-export"></i> Exporter
                                </button>
                            </div>
                        </div>

                        @if($dailyEntries->isEmpty())
                            <div class="text-center py-5">
                                <i class="fas fa-clock fa-5x text-muted mb-4 d-block"></i>
                                <h4>Aucune feuille de temps trouvée</h4>
                                <p class="lead text-muted mb-4">
                                    @if(request()->hasAny(['statut', 'date', 'user', 'pending']))
                                        Essayez de modifier les filtres.
                                    @else
                                        Commencez par saisir votre première feuille de temps.
                                    @endif
                                </p>
                                <a href="{{ route('daily-entries.create') }}" class="btn btn-primary btn-lg">
                                    <i class="fas fa-plus"></i> Nouvelle saisie
                                </a>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Collaborateur</th>
                                            <th>Heures</th>
                                            <th>Activités</th>
                                            <th>Statut</th>
                                            <th>Créée le</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($dailyEntries as $entry)
                                            <tr>
                                                <td>
                                                    <strong>{{ $entry->jour->format('d/m/Y') }}</strong><br>
                                                    <small class="text-muted">{{ $entry->jour->translatedFormat('l') }}</small>
                                                </td>

                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-sm mr-3 bg-gradient-primary text-white d-flex align-items-center justify-content-center"
                                                             style="width:45px; height:45px; border-radius:50%; font-weight:bold;">
                                                            {{ strtoupper(substr($entry->user->prenom ?? 'U', 0, 1)) }}
                                                        </div>
                                                        <div>
                                                            <div class="font-weight-bold">
                                                                {{ $entry->user->prenom }} {{ $entry->user->nom }}
                                                            </div>
                                                            <small class="text-muted">
                                                                {{ $entry->user->poste->intitule ?? 'Non défini' }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td class="text-center">
                                                    @php
                                                        $percentage = $entry->heures_theoriques > 0
                                                            ? ($entry->heures_reelles / $entry->heures_theoriques) * 100
                                                            : 0;
                                                        $bgColor = $percentage >= 100 ? 'bg-success' : ($percentage >= 80 ? 'bg-warning' : 'bg-danger');
                                                    @endphp
                                                    <div class="progress" style="height:20px; width:140px; margin:0 auto;">
                                                        <div class="progress-bar {{ $bgColor }}" style="width: {{ min($percentage, 100) }}%"></div>
                                                    </div>
                                                    <small class="d-block mt-1 text-muted">
                                                        {{ UserHelper::hoursToHoursMinutes($entry->heures_reelles) }} /
                                                        {{ UserHelper::hoursToHoursMinutes($entry->heures_theoriques) }}
                                                    </small>
                                                </td>

                                                <td>
                                                    <strong>{{ $entry->timeEntries->count() }} activité{{ $entry->timeEntries->count() > 1 ? 's' : '' }}</strong><br>
                                                    @if($entry->commentaire)
                                                        <small>{{ Str::limit($entry->commentaire, 60) }}</small>
                                                    @else
                                                        <small class="text-muted">Aucun commentaire</small>
                                                    @endif
                                                </td>

                                                <td class="text-center">
                                                    @switch($entry->statut)
                                                        @case('soumis')
                                                            <span class="badge badge-info px-3 py-2">Soumise</span>
                                                            @break
                                                        @case('validé')
                                                            <span class="badge badge-success px-3 py-2">Validée</span>
                                                            @if($entry->valide_le)
                                                                <br><small class="text-muted">{{ $entry->valide_le->format('d/m H:i') }}</small>
                                                            @endif
                                                            @break
                                                        @case('refusé')
                                                            <span class="badge badge-danger px-3 py-2">Refusée</span>
                                                            @if($entry->motif_refus)
                                                                <br><small class="text-muted" title="{{ $entry->motif_refus }}">Motif</small>
                                                            @endif
                                                            @break
                                                        @default
                                                            <span class="badge badge-secondary px-3 py-2">{{ ucfirst($entry->statut) }}</span>
                                                    @endswitch
                                                </td>

                                                <td>
                                                    <small>{{ $entry->created_at->format('d/m H:i') }}</small>
                                                </td>

                                                <td class="text-center">
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="{{ route('daily-entries.show', $entry) }}"
                                                           class="btn btn-info"
                                                           title="Voir">
                                                            <i class="fas fa-eye"></i>
                                                        </a>

                                                        @if($entry->user_id == auth()->id() && $entry->statut == 'soumis')
                                                            <a href="{{ route('daily-entries.edit', $entry) }}"
                                                               class="btn btn-warning"
                                                               title="Modifier">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        @endif

                                                        @if(auth()->user()->hasRole('responsable') && $entry->statut == 'soumis')
                                                            <button type="button"
                                                                    class="btn btn-success validate-btn"
                                                                    data-id="{{ $entry->id }}"
                                                                    data-name="{{ $entry->user->prenom }} {{ $entry->user->nom }}"
                                                                    data-date="{{ $entry->jour->format('d/m/Y') }}">
                                                                <i class="fas fa-check"></i>
                                                            </button>

                                                            <button type="button"
                                                                    class="btn btn-danger reject-btn"
                                                                    data-id="{{ $entry->id }}"
                                                                    data-name="{{ $entry->user->prenom }} {{ $entry->user->nom }}"
                                                                    data-date="{{ $entry->jour->format('d/m/Y') }}">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        @endif

                                                        @if($entry->user_id == auth()->id() || auth()->user()->hasRole('responsable'))
                                                            <button type="button"
                                                                    class="btn btn-outline-danger delete-btn"
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

                            <!-- Infos pagination + nombre d'éléments -->
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <div class="text-muted">
                                    Affichage de {{ $dailyEntries->firstItem() }} à {{ $dailyEntries->lastItem() }}
                                    sur {{ $dailyEntries->total() }} entrée{{ $dailyEntries->total() > 1 ? 's' : '' }}
                                </div>

                                <div class="d-flex justify-content-center">
                                    {{ $dailyEntries->appends(request()->query())->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Cartes statistiques -->
        <div class="row mt-5">
            <div class="col-lg-3 col-md-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>Total Heures</h4></div>
                        <div class="card-body">{{ UserHelper::hoursToHoursMinutes($totalHours) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-info">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>Soumises</h4></div>
                        <div class="card-body">{{ $submittedCount }}</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>Validées</h4></div>
                        <div class="card-body">{{ $validatedCount }}</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-danger">
                        <i class="fas fa-times-circle"></i>
                    </div>
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
<div class="modal fade" id="exportModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Exporter les feuilles de temps</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form action="{{ route('daily-entries.export') }}" method="GET">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Période</label>
                        <div class="row">
                            <div class="col-6">
                                <input type="date" name="date_debut" class="form-control"
                                       value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                            </div>
                            <div class="col-6">
                                <input type="date" name="date_fin" class="form-control"
                                       value="{{ now()->format('Y-m-d') }}">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Format d'export</label>
                        <select name="format" class="form-control">
                            <option value="excel">Excel (.xlsx)</option>
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
$(document).ready(function () {
    // Validation / actions AJAX
    $('.validate-btn').on('click', function () {
        const id   = $(this).data('id');
        const name = $(this).data('name');
        const date = $(this).data('date');

        Swal.fire({
            title: 'Valider la feuille ?',
            html: `Confirmez-vous la validation de la feuille de <strong>${name}</strong> du <strong>${date}</strong> ?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Oui, valider',
            cancelButtonText: 'Annuler',
            confirmButtonColor: '#28a745'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(`/daily-entries/${id}/validate`, {
                    _token: '{{ csrf_token() }}'
                })
                .done(() => {
                    Swal.fire('Validée !', '', 'success').then(() => location.reload());
                })
                .fail(() => Swal.fire('Erreur', 'Impossible de valider', 'error'));
            }
        });
    });

    $('.reject-btn').on('click', function () {
        const id   = $(this).data('id');
        const name = $(this).data('name');
        const date = $(this).data('date');

        Swal.fire({
            title: 'Refuser la feuille',
            input: 'textarea',
            inputLabel: 'Motif du refus (obligatoire)',
            inputPlaceholder: 'Expliquez pourquoi...',
            showCancelButton: true,
            confirmButtonText: 'Refuser',
            cancelButtonText: 'Annuler',
            inputValidator: (value) => !value && 'Le motif est obligatoire'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(`/daily-entries/${id}/reject`, {
                    _token: '{{ csrf_token() }}',
                    motif_refus: result.value
                })
                .done(() => {
                    Swal.fire('Refusée', '', 'success').then(() => location.reload());
                })
                .fail(() => Swal.fire('Erreur', 'Impossible de refuser', 'error'));
            }
        });
    });

    $('.delete-btn').on('click', function () {
        const id   = $(this).data('id');
        const name = $(this).data('name');
        const date = $(this).data('date');

        Swal.fire({
            title: 'Supprimer ?',
            html: `Êtes-vous sûr de vouloir supprimer la feuille de <strong>${name}</strong> du <strong>${date}</strong> ?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/daily-entries/${id}`,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' }
                })
                .done(() => {
                    Swal.fire('Supprimée', '', 'success').then(() => location.reload());
                })
                .fail(() => Swal.fire('Erreur', 'Impossible de supprimer', 'error'));
            }
        });
    });
});
</script>
@endpush
