@extends('layaout')

@section('title', 'Nouveau Rôle')

@section('content')
<section class="section">
    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h4><i class="fas fa-user-shield"></i> Nouveau Rôle</h4>
                        <div class="card-header-action">
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-danger">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('admin.roles.store') }}" method="POST">
                            @csrf

                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>Nom du rôle <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                               value="{{ old('name') }}" placeholder="Ex: Auditeur, Manager RH, Consultant..." required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Couleur (facultatif)</label>
                                        <input type="color" name="color" class="form-control" value="{{ old('color', '#4e73df') }}"
                                               style="height: 50px;">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror"
                                          placeholder="Décrivez à quoi sert ce rôle dans l'organisation...">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="alert alert-info mt-4">
                                <i class="fas fa-info-circle"></i>
                                <strong>Info :</strong> Vous pourrez ajouter les permissions après la création en cliquant sur
                                <i class="fas fa-key"></i> dans la liste des rôles.
                            </div>

                            <div class="text-right mt-4">
                                <button type="submit" class="btn btn-primary btn-lg px-5">
                                    <i class="fas fa-save"></i> Créer le rôle
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
