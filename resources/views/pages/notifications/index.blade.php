@extends('layaout')

@section('title', 'Mes Notifications')

@section('content')
<section class="section">
    <div class="section-body">
        <h1><i class="far fa-bell"></i> Mes Notifications</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Notifications</div>
        </div>
    </div>

    <div class="section-body">
        <h2 class="section-title">Historique des notifications</h2>
        <p class="section-lead">Toutes vos alertes et actions importantes</p>

        <!-- Actions groupées -->
        <div class="card mb-4">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <strong>{{ $notifications->whereNull('read_at')->count() }}</strong> notification(s) non lue(s)
                </div>
                <div class="btn-group">
                    @if($notifications->whereNull('read_at')->count() > 0)
                        <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-check-double"></i> Tout marquer comme lu
                            </button>
                        </form>
                    @endif
                    <form action="{{ route('notifications.destroy-all') }}" method="POST"
                          onsubmit="return confirm('Supprimer TOUTES vos notifications ?')" class="d-inline ml-2">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fas fa-trash"></i> Tout supprimer
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="activities">
            @forelse($notifications as $notif)
                <div class="activity {{ $notif->read_at ? '' : 'activity-unread' }}">
                    <div class="activity-icon bg-{{ $notif->data['color'] }} text-white">
                        <i class="{{ $notif->data['icon'] }}"></i>
                    </div>
                    <div class="activity-detail">
                        <div class="mb-2">
                            <span class="text-job text-muted">
                                {{ $notif->created_at->format('d/m/Y à H:i') }}
                            </span>
                            <span class="bullet"></span>
                            @if(!$notif->read_at)
                                <span class="badge badge-primary">Nouveau</span>
                            @endif

                            <div class="float-right dropdown">
                                <a href="#" data-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></a>
                                <div class="dropdown-menu">
                                    @if(!$notif->read_at)
                                        <a href="#" class="dropdown-item has-icon" onclick="markAsRead('{{ $notif->id }}')">
                                            <i class="fas fa-check text-success"></i> Marquer comme lu
                                        </a>
                                    @endif
                                    <form action="{{ route('notifications.destroy', $notif) }}" method="POST"
                                          onsubmit="return confirm('Supprimer cette notification ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="dropdown-item has-icon text-danger">
                                            <i class="fas fa-trash"></i> Supprimer
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <p>
                            <strong>{{ $notif->data['message'] }}</strong><br>
                            {{ $notif->data['message'] }}

                            @if($notif->data['url'] ?? false)
                                <br>
                                <a href="{{ $notif->data['url'] }}" class="btn btn-sm btn-outline-primary mt-2">
                                    <i class="fas fa-external-link-alt"></i> Voir la ressource
                                </a>
                            @endif
                        </p>
                    </div>
                </div>
            @empty
                <div class="text-center py-5 text-muted">
                    <i class="far fa-bell-slash fa-4x mb-4"></i>
                    <h5>Aucune notification</h5>
                    <p>Vous êtes à jour !</p>
                </div>
            @endforelse
        </div

        <div class="mt-4 d-flex justify-content-center">
                            {{ $notifications->links('pagination::bootstrap-4') }}
            </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    .activity {
        display: flex;
        padding: 16px 0;
        border-bottom: 1px solid #eee;
        transition: background 0.2s;
    }
    .activity-unread {
        background: #f0f9ff;
        border-left: 4px solid #3b82f6;
        padding-left: 12px;
    }
    .activity-icon {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 16px;
        flex-shrink: 0;
    }
    .activity-detail {
        flex: 1;
    }
    .bullet {
        width: 6px; height: 6px;
        background: #ccc;
        border-radius: 50%;
        display: inline-block;
        margin: 0 10px;
        vertical-align: middle;
    }
</style>
@endpush

@push('scripts')
<script>
function markAsRead(id, element = null) {
    fetch(`/notifications/${id}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
    })
    .then(() => {
        location.reload();
    })
    .catch(() => alert('Erreur'));
}
</script>
@endpush
