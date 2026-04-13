@extends('layaout')

@section('title','Activités')

@section('content')
<div class="card">
  <div class="card-header">
    <h4>Journal d'activités</h4>
  </div>
  <div class="card-body">
    @if($logs->count())
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>Utilisateur</th>
              <th>Action</th>
              <th>Cible</th>
              <th>Description</th>
              <th>IP</th>
              <th>Quand</th>
            </tr>
          </thead>
          <tbody>
            @foreach($logs as $log)
            <tr>
              <td>{{ $log->id }}</td>
              <td>{{ $log->user ? $log->user->prenom . ' ' . $log->user->nom : 'Système' }}</td>
              <td>{{ $log->action }}</td>
              <td>{{ $log->table_cible ?? '-' }} {{ $log->enregistrement_id ? '#'.$log->enregistrement_id : '' }}</td>
              <td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;">{{ Str::limit($log->description ?? '', 120) }}</td>
              <td>{{ $log->ip_address }}</td>
              <td>{{ $log->created_at->diffForHumans() }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="mt-3">
        {{ $logs->links() }}
      </div>
    @else
      <p class="text-muted">Aucun log trouvé.</p>
    @endif
  </div>
</div>
@endsection
