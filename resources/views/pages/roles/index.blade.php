@extends('layaout')

@section('title', 'Gestion des Rôles')

@section('content')
    <section class="section">
        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h4>
                                <i class="fas fa-user-shield"></i>
                                Liste des Rôles
                            </h4>
                            <div class="card-header-action">
                                @can('créer des rôles')
                                    <a href="{{ route('admin.roles.create') }}" class="btn btn-success">
                                        <i class="fas fa-plus"></i>
                                        Nouveau rôle
                                    </a>
                                @endcan
                            </div>
                        </div>

                        <div class="card-body">
                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show">
                                    <button type="button" class="close" data-dismiss="alert">×</button>
                                    {{ session('success') }}
                                </div>
                            @endif

                            @if(session('errors'))
                                <div class="alert alert-danger alert-dismissible fade show">
                                    <button type="button" class="close" data-dismiss="alert">×</button>
                                    {{ session('errors')->first() }}
                                </div>
                            @endif

                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="rolesTable">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Nom</th>
                                            <th>Description</th>
                                            <th>Permissions</th>
                                            <th>Utilisateurs</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($roles as $role)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <strong>{{ $role->name }}</strong>
                                                </td>
                                                <td>{!! $role->description ?: '<em class="text-muted">Aucune</em>' !!}</td>
                                                <td>
                                                    <span class="badge badge-info badge-pill">
                                                        {{ $role->permissions->count() }}
                                                    </span>
                                                </td>
                                                <td>
                                                    {{-- CORRECTION ICI : Utilisation du compte personnalisé --}}
                                                    <span class="badge badge-primary badge-pill">
                                                        {{ $role->users_custom_count ?? 0 }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group btn-group-sm">
                                                        @if($role->name === 'super-admin')
                                                            <button class="btn btn-secondary disabled"
                                                                title="Accès total par défaut">
                                                                <i class="fas fa-lock"></i>
                                                            </button>
                                                        @else
                                                            @can('gérer les permissions des rôles')
                                                                <a href="{{ route('admin.roles.permissions.show', $role) }}"
                                                                    class="btn btn-outline-info" title="Gérer les permissions">
                                                                    <i class="fas fa-key"></i>
                                                                </a>
                                                            @endcan
                                                        @endif

                                                        @can('modifier des rôles')
                                                            <a href="{{ route('admin.roles.edit', $role) }}"
                                                                class="btn btn-outline-warning" title="Modifier">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        @endcan

                                                        @can('supprimer des rôles')
                                                            {{-- On vérifie le compte personnalisé ici aussi pour la sécurité --}}
                                                            @if(!in_array($role->name, ['super-admin', 'admin']) && ($role->users_custom_count ?? 0) === 0)
                                                                <form action="{{ route('admin.roles.destroy', $role) }}" method="POST"
                                                                    class="d-inline">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-outline-danger"
                                                                        onclick="return confirm('Supprimer le rôle {{ addslashes($role->name) }} ?')"
                                                                        title="Supprimer">
                                                                        <i class="fas fa-trash-alt"></i>
                                                                    </button>
                                                                </form>
                                                            @else
                                                                <button class="btn btn-secondary btn-sm" disabled
                                                                    title="Rôle système ou utilisé par des utilisateurs">
                                                                    <i class="fas fa-lock"></i>
                                                                </button>
                                                            @endif
                                                        @endcan
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-5">
                                                    <i class="fas fa-inbox fa-3x mb-3"></i><br>
                                                    Aucun rôle créé pour le moment
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            $('#rolesTable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json'
                },
                pageLength: 15,
                order: [
                    [1, 'asc']
                ],
                columnDefs: [
                    {
                        orderable: false,
                        targets: 5
                    }
                ]
            });

            $('[title]').tooltip();
        });
    </script>
@endpush
