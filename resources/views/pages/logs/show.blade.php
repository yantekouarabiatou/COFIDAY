@extends('layaout')

@section('title', 'Détail Log #' . $log->id)

@section('content')
    <section class="section">
        <div class="section-header">
            <h1><i class="fas fa-search"></i> Détail de l'activité #{{ $log->id }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('logs.index') }}">Logs</a></div>
                <div class="breadcrumb-item">Détail</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h4>Détails complets de l'activité</h4>
                            <div class="card-header-action">
                                <a href="{{ route('logs.index') }}" class="btn btn-icon icon-left btn-info">
                                    <i class="fas fa-arrow-left"></i> Retour
                                </a>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <tbody>
                                        <tr>
                                            <th width="250">ID</th>
                                            <td><code>#{{ $log->id }}</code></td>
                                        </tr>
                                        <tr>
                                            <th>Date & Heure</th>
                                            <td>
                                                <strong>{{ $log->created_at->format('d/m/Y à H:i:s') }}</strong>
                                                <small class="text-muted d-block">(il y a
                                                    {{ $log->created_at->diffForHumans() }})</small>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Utilisateur</th>
                                            <td>
                                                @if($log->user)
                                                    <strong>{{ $log->user->nom ?? $log->user->prenom }}</strong><br>
                                                    <small class="text-muted">{{ $log->user->email }}</small>
                                                @else
                                                    <em class="text-muted">Système / Anonyme</em>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Action</th>
                                            <td>
                                                {{-- CORRECTION : $log->icon() → $log->icon --}}
                                                <span class="badge badge-{{ $log->action_color }} badge-lg">
                                                    <i class="fas {{ $log->icon }}"></i>
                                                    {{ ucfirst(__($log->action)) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Description</th>
                                            <td>{{ $log->description ?? '-' }}</td>
                                        </tr>

                                        <!-- Ressource concernée (générique) -->
                                        <tr>
                                            <th>Ressource</th>
                                            <td>
                                                @if($log->loggable && $log->loggable->exists)
                                                    <span
                                                        class="badge badge-info">{{ $log->table_name ?? class_basename($log->loggable_type) }}</span>
                                                    → <strong>{{ $log->reference }}</strong>
                                                    @if($log->url)
                                                        <a href="{{ $log->url }}" target="_blank"
                                                            class="btn btn-sm btn-success ml-2">
                                                            <i class="fas fa-external-link-alt"></i> Voir
                                                        </a>
                                                    @endif
                                                @else
                                                    <span class="text-danger">
                                                        <i class="fas fa-trash"></i> Ressource supprimée
                                                        ({{ class_basename($log->loggable_type) }} #{{ $log->loggable_id }})
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>

                                        <tr>
                                            <th>Adresse IP</th>
                                            <td><code>{{ $log->ip_address ?? '-' }}</code></td>
                                        </tr>
                                        <tr>
                                            <th>User Agent</th>
                                            <td><small class="text-muted text-break">{{ $log->user_agent ?? '-' }}</small>
                                            </td>
                                        </tr>

                                        @if($log->new_values && is_array($log->new_values) && count($log->new_values) > 0)
                                                                    <tr>
                                                                        <th>Nouvelles valeurs</th>
                                                                        <td>
                                                                            <pre class="bg-dark text-white p-3 rounded small text-monospace">
                                                {!! nl2br(e(json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) !!}
                                            </pre>
                                                                        </td>
                                                                    </tr>
                                        @endif

                                        <!-- Nouvelles valeurs -->
                                        @php
    $newValues = is_string($log->new_values) 
        ? json_decode($log->new_values, true) 
        : (is_array($log->new_values) ? $log->new_values : []);
@endphp

@if(!empty($newValues) && is_array($newValues))
    <tr>
        <th>Nouvelles valeurs</th>
        <td>
            <pre class="bg-dark text-white p-3 rounded small text-monospace">
                {!! nl2br(e(json_encode($newValues, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) !!}
            </pre>
        </td>
    </tr>
@endif
                                    </tbody>
                                </table>
                            </div>

                            <div class="text-center mt-4">
                                <a href="{{ route('logs.index') }}" class="btn btn-lg btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Retour à la liste
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
