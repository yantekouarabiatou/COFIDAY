@extends('layaout')

@section('title', 'Permissions du rôle : ' . $role->name)

@section('content')
<section class="section">
    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h4>
                            <i class="fas fa-key text-warning"></i>
                            Permissions du rôle : <strong>{{ strtoupper($role->name) }}</strong>
                        </h4>
                        <div class="card-header-action">
                            <button type="button" id="check-all" class="btn btn-outline-primary btn-sm">Tout cocher</button>
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('admin.roles.permissions.update', $role) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                @foreach($groupedPermissions as $group => $permissions)
                                    @php
                                        $label = $groupLabels[$group] ?? ucfirst(str_replace('_', ' ', $group));
                                    @endphp

                                    <div class="col-12 col-lg-6 mb-4">
                                        <div class="card border shadow-sm h-100">
                                            <div class="card-header bg-light d-flex justify-content-between cursor-pointer group-header" style="cursor: pointer;">
                                                <h6 class="m-0 font-weight-bold text-primary">
                                                    <i class="fas fa-folder"></i> {{ $label }}
                                                </h6>
                                                <span class="badge badge-primary">
                                                    {{ $permissions->count() }}
                                                </span>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    @foreach($permissions as $permission)
                                                        <div class="col-12 mb-2">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" 
                                                                       class="custom-control-input permission-checkbox" 
                                                                       id="perm-{{ $permission->id }}" 
                                                                       name="permissions[]" 
                                                                       value="{{ $permission->name }}"
                                                                       {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                                                                
                                                                <label class="custom-control-label" for="perm-{{ $permission->id }}">
                                                                    {{-- Utilisation de la description avec fallback sur le nom --}}
                                                                    {{ $permission->description ?: $permission->name }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="text-center mt-4">
                                @can('gérer les permissions')
                                    <button type="submit" class="btn btn-primary btn-lg px-5 shadow">
                                        <i class="fas fa-save"></i> Enregistrer les modifications
                                    </button>
                                @endcan
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Cocher/Décocher tout le groupe en cliquant sur le header de la carte
        $('.group-header').on('click', function() {
            const $card = $(this).closest('.card');
            const checkboxes = $card.find('.permission-checkbox');
            const allChecked = checkboxes.length === checkboxes.filter(':checked').length;
            checkboxes.prop('checked', !allChecked);
        });

        // Bouton "Tout cocher" global
        $('#check-all').on('click', function() {
            const checkboxes = $('.permission-checkbox');
            const allChecked = checkboxes.length === checkboxes.filter(':checked').length;
            checkboxes.prop('checked', !allChecked);
            $(this).text(allChecked ? 'Tout cocher' : 'Tout décocher');
        });
    });
</script>
@endsection