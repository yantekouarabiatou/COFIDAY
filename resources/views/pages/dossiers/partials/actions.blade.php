<div class="btn-group" role="group">
    <a href="{{ route('dossiers.show', $id) }}" class="btn btn-sm btn-info" title="Voir">
        <i class="fas fa-eye"></i>
    </a>
    <a href="{{ route('dossiers.edit', $id) }}" class="btn btn-sm btn-warning" title="Modifier">
        <i class="fas fa-edit"></i>
    </a>
    <button type="button"
            class="btn btn-sm btn-danger delete-dossier-btn"
            title="Supprimer"
            data-url="{{ route('dossiers.destroy', $id) }}">
        <i class="fas fa-trash"></i>
    </button>
</div>
