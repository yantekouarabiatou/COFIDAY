@extends('layaout')

@section('title', 'Gestion des Permissions - ' . $role->name)

@section('styles')
<style>
    /* Bordure colorée pour identifier les cartes facilement */
    .border-left-primary {
        border-left: 4px solid #244584 !important;
    }

    /* Couleur du checkbox quand coché */
    .custom-checkbox .custom-control-input:checked ~ .custom-control-label::before {
        background-color: #244584;
        border-color: #265cbf;
    }

    /* Style subtil pour le header de la carte */
    .card-header {
        background-color: #244584;
        border-bottom: 1px solid #e3e6f0;
    }

    /* Effet de survol sur les cartes */
    .card:hover {
        transform: translateY(-2px);
        transition: transform 0.2s ease-in-out;
    }
</style>
@endsection

@section('content')
<section class="section">
    <div class="section-body">
        <div class="container-fluid">

            {{-- En-tête de la page --}}
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body d-flex align-items-center justify-content-between py-3">
                            <div>
                                <h4 class="m-0 font-weight-bold text-primary">
                                    <i class="fas fa-user-shield mr-2"></i> Rôle : <span class="text-dark">{{ $role->name }}</span>
                                </h4>
                                <small class="text-muted">Cochez les cases pour accorder des permissions à ce rôle.</small>
                            </div>
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary shadow-sm">
                                <i class="fas fa-arrow-left mr-1"></i> Retour à la liste
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Formulaire --}}
            <form action="{{ route('admin.roles.permissions.update', $role) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    @forelse($groupedPermissions as $groupKey => $permissions)

                        {{-- Récupération du nom lisible via le tableau associatif du Service --}}
                        @php
                            $groupLabel = $groupLabels[$groupKey] ?? ucfirst(str_replace('_', ' ', $groupKey));
                        @endphp

                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 border-left-primary shadow-sm">

                                {{-- Header du Groupe avec "Tout cocher" --}}
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">{{ $groupLabel }}</h6>

                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox"
                                               class="custom-control-input group-checkbox"
                                               id="group-{{ $groupKey }}"
                                               data-group="{{ $groupKey }}">
                                        <label class="custom-control-label" for="group-{{ $groupKey }}">
                                            <small class="font-weight-bold text-muted">Tout</small>
                                        </label>
                                    </div>
                                </div>

                                {{-- Liste des Permissions --}}
                                <div class="card-body">
                                    @foreach($permissions as $permission)
                                        <div class="custom-control custom-checkbox mb-2">
                                            {{-- Note: On utilise in_array pour vérifier si le rôle a déjà la permission --}}
                                            <input type="checkbox"
                                                   class="custom-control-input permission-checkbox"
                                                   id="perm-{{ $permission->id }}"
                                                   name="permissions[]"
                                                   value="{{ $permission->name }}"
                                                   data-group="{{ $groupKey }}"
                                                   {{ in_array($permission->name, $rolePermissions) ? 'checked' : '' }}>

                                            <label class="custom-control-label text-dark" for="perm-{{ $permission->id }}" style="cursor: pointer;">
                                                {{ $permission->description ?? $permission->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert alert-warning text-center">
                                <i class="fas fa-exclamation-triangle"></i> Aucune permission n'a été trouvée dans la base de données. Veuillez lancer le Seeder.
                            </div>
                        </div>
                    @endforelse
                </div>

                {{-- Bouton d'enregistrement flottant ou fixe en bas --}}
                <div class="row mt-3 mb-5">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-body text-center">
                                <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm">
                                    <i class="fas fa-save mr-2"></i> Enregistrer les permissions
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {

        // --- LOGIQUE JAVASCRIPT POUR LES CHECKBOXES ---

        // 1. Clic sur le header de groupe (Tout cocher/décocher)
        $('.group-checkbox').on('change', function() {
            const groupKey = $(this).data('group');
            const isChecked = $(this).is(':checked');

            // Met à jour toutes les permissions enfants
            $(`.permission-checkbox[data-group="${groupKey}"]`).prop('checked', isChecked);
        });

        // 2. Clic sur une permission individuelle (Mise à jour du parent)
        $('.permission-checkbox').on('change', function() {
            const groupKey = $(this).data('group');
            updateGroupCheckboxState(groupKey);
        });

        // 3. Fonction : Calculer l'état du checkbox parent (Coché / Non / Indéterminé)
        function updateGroupCheckboxState(groupKey) {
            const $groupCheckbox = $(`#group-${groupKey}`);
            const $allPermissions = $(`.permission-checkbox[data-group="${groupKey}"]`);
            const $checkedPermissions = $allPermissions.filter(':checked');

            if ($checkedPermissions.length === 0) {
                // Aucune cochée
                $groupCheckbox.prop('checked', false);
                $groupCheckbox.prop('indeterminate', false);
            } else if ($checkedPermissions.length === $allPermissions.length) {
                // Toutes cochées
                $groupCheckbox.prop('checked', true);
                $groupCheckbox.prop('indeterminate', false);
            } else {
                // Certaines cochées (mixte)
                $groupCheckbox.prop('checked', false);
                $groupCheckbox.prop('indeterminate', true); // Visuel : tiret "-"
            }
        }

        // 4. Initialisation au chargement de la page
        // Pour que les cases "Tout" soient correctes si on arrive sur une page déjà remplie
        $('.group-checkbox').each(function() {
            const groupKey = $(this).data('group');
            updateGroupCheckboxState(groupKey);
        });
    });
</script>
@endsection
