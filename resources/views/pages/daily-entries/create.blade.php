@extends('layaout')

@section('title', 'Nouvelle Feuille de Temps')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1><i class="fas fa-clock"></i> Nouvelle Feuille de Temps</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('daily-entries.index') }}">Feuilles de Temps</a></div>
                <div class="breadcrumb-item">Nouvelle</div>
            </div>
        </div>

        <div class="section-body">
            <div class="card card-primary">
                <div class="card-header">
                    <h4>Saisie du {{ now()->format('d/m/Y') }}</h4>
                </div>

                <div class="card-body">
                    <form action="{{ route('daily-entries.store') }}" method="POST" id="daily-form">
                        @csrf

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Collaborateur</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control"
                                            value="{{ auth()->user()->prenom }} - {{ auth()->user()->nom }} ({{ auth()->user()->poste->intitule ?? '-' }})"
                                            readonly>
                                        <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                                    </div>
                                    <small class="text-muted">Vous êtes automatiquement sélectionné en tant qu'utilisateur
                                        connecté</small>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Date <span class="text-danger">*</span></label>
                                    <input type="date" name="jour" class="form-control"
                                        value="{{ old('jour', now()->format('Y-m-d')) }}" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Heures théoriques <span class="text-danger">*</span></label>
                                    <input type="number" step="0.25" min="0" max="24" name="heures_theoriques"
                                        class="form-control" value="{{ old('heures_theoriques', 8) }}" required>
                                    <small class="text-muted">Durée théorique de travail pour cette journée</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Commentaire général</label>
                            <textarea name="commentaire" class="form-control" rows="2"
                                placeholder="Ex: Réunion clientèle, télétravail, formation interne...">{{ old('commentaire') }}</textarea>
                        </div>

                        <hr>

                        <!-- Titre + bouton global sur la même ligne -->
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <h5 class="mb-0"><i class="fas fa-tasks"></i> Activités de la journée</h5>
                            <button type="button" class="btn btn-outline-primary btn-new-dossier-global"
                                title="Nouveau dossier">
                                <i class="fas fa-plus"></i> Nouveau dossier
                            </button>
                        </div>

                        <!-- Conteneur avec scroll horizontal sur petits écrans -->
                        <div class="table-responsive">
                            <!-- Conteneur des activités -->
                            <div id="time-entries-container">
                                <!-- Première ligne visible par défaut -->
                                <div class="time-entry-row mb-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <!-- Première ligne: Informations principales -->
                                            <div class="row align-items-end mb-3">
                                                <div class="col-lg-3 col-md-4 col-12 mb-2">
                                                    <div class="form-group mb-0">
                                                        <label class="font-weight-bold">Dossier <span class="text-danger">*</span></label>
                                                        <select name="time_entries[0][dossier_id]"
                                                            class="form-control select2 dossier-select" required>
                                                            <option value="">Choisir un dossier...</option>
                                                            @foreach($dossiers as $dossier)
                                                                <option value="{{ $dossier->id }}"
                                                                    data-client="{{ $dossier->client->nom ?? 'Sans client' }}"
                                                                    data-reference="{{ $dossier->reference ?? '' }}">
                                                                    {{ $dossier->nom }} - {{ $dossier->client->nom ?? 'Sans client' }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-lg-2 col-md-3 col-6 mb-2">
                                                    <div class="form-group mb-0">
                                                        <label class="font-weight-bold">Heure début <span class="text-danger">*</span></label>
                                                        <input type="time" name="time_entries[0][heure_debut]"
                                                            class="form-control heure-debut text-center" value="09:00" required>
                                                    </div>
                                                </div>

                                                <div class="col-lg-2 col-md-3 col-6 mb-2">
                                                    <div class="form-group mb-0">
                                                        <label class="font-weight-bold">Heure fin <span class="text-danger">*</span></label>
                                                        <input type="time" name="time_entries[0][heure_fin]"
                                                            class="form-control heure-fin text-center" value="12:00" required>
                                                    </div>
                                                </div>

                                                <div class="col-lg-2 col-md-2 col-6 mb-2">
                                                    <div class="form-group mb-0">
                                                        <label class="font-weight-bold">Heures <span class="text-danger">*</span></label>
                                                        <input type="number" step="0.25" min="0.25"
                                                            name="time_entries[0][heures_reelles]"
                                                            class="form-control heures-input text-center" value="3" readonly required>
                                                    </div>
                                                </div>

                                                <div class="col-lg-3 col-md-12 col-6 mb-2 text-lg-right text-md-left">
                                                    <div class="form-group mb-0">
                                                        <label class="d-none d-lg-block text-white">-</label>
                                                        <button type="button" class="btn btn-danger btn-sm remove-row"
                                                            title="Supprimer cette activité">
                                                            <i class="fas fa-trash"></i> Supprimer
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Deuxième ligne: Descriptions détaillées -->
                                            <div class="row">
                                                <div class="col-md-6 col-12 mb-2">
                                                    <div class="form-group mb-0">
                                                        <label class="font-weight-bold">Travaux réalisés</label>
                                                        <textarea name="time_entries[0][travaux]"
                                                            class="form-control travaux-input"
                                                            rows="3"
                                                            placeholder="Ex: Analyse des documents, rédaction rapport..."></textarea>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 col-12 mb-2">
                                                    <div class="form-group mb-0">
                                                        <label class="font-weight-bold">Rendu</label>
                                                        <textarea name="time_entries[0][rendu]"
                                                            class="form-control"
                                                            rows="3"
                                                            placeholder="Ex: Rapport v1, 5 pages..."></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4 text-center">
                                <button type="button" id="add-row" class="btn btn-outline-primary btn-lg">
                                    <i class="fas fa-plus-circle"></i> Ajouter une activité
                                </button>
                            </div>

                        <!-- Récapitulatif visuel -->
                        <div class="card mt-4">
                            <div class="card-body">
                                <h6>Récapitulatif des heures</h6>
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <div class="progress" style="height: 30px;">
                                            <div class="progress-bar bg-info" role="progressbar" id="progress-bar"
                                                style="width: 0%">
                                                <span class="progress-text">0h / 0h</span>
                                            </div>
                                            <div class="progress-bar bg-success" role="progressbar" id="progress-over"
                                                style="width: 0%"></div>
                                            <div class="progress-bar bg-danger" role="progressbar" id="progress-under"
                                                style="width: 0%"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <h4 id="total-heures">Total : <span class="text-info">0.00</span></h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-right mt-4">
                            <a href="{{ route('daily-entries.index') }}" class="btn btn-secondary btn-lg mr-3">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="fas fa-save"></i> Enregistrer la feuille
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal pour nouveau dossier -->
    <div class="modal fade" id="newDossierModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-folder-plus"></i> Nouveau Dossier</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="new-dossier-form">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nom du dossier <span class="text-danger">*</span></label>
                                    <input type="text" name="nom" class="form-control" required
                                        placeholder="Ex: Audit financier 2024">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Référence</label>
                                    <input type="text" name="reference" class="form-control" placeholder="Ex: REF-2025-001">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Client <span class="text-danger">*</span></label>
                                    <select name="client_id" class="form-control select2" required>
                                        <option value="">Choisir un client...</option>
                                        @foreach($clients as $client)
                                            <option value="{{ $client->id }}">{{ $client->nom }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Type de dossier</label>
                                    <select name="type_dossier" class="form-control">
                                        <option value="">Sélectionner...</option>
                                        <option value="audit">Audit</option>
                                        <option value="conseil">Conseil</option>
                                        <option value="formation">Formation</option>
                                        <option value="expertise">Expertise</option>
                                        <option value="autre">Autre</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Date d'ouverture</label>
                                    <input type="date" name="date_ouverture" class="form-control"
                                        value="{{ now()->format('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Date de clôture prévue</label>
                                    <input type="date" name="date_cloture_prevue" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Budget (€)</label>
                                    <input type="number" step="0.01" name="budget" class="form-control" placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Frais de dossier (€)</label>
                                    <input type="number" step="0.01" name="frais_dossier" class="form-control"
                                        placeholder="0.00">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Statut</label>
                                    <select name="statut" class="form-control">
                                        <option value="ouvert">Ouvert</option>
                                        <option value="en_cours" selected>En cours</option>
                                        <option value="suspendu">Suspendu</option>
                                        <option value="cloture">Clôturé</option>
                                        <option value="archive">Archivé</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Document (fichier)</label>
                                    <input type="file" name="document" class="form-control">
                                    <small class="text-muted">PDF, Word, Excel, etc.</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="3"
                                placeholder="Description détaillée du dossier..."></textarea>
                        </div>

                        <div class="form-group">
                            <label>Notes internes</label>
                            <textarea name="notes" class="form-control" rows="2"
                                placeholder="Notes visibles uniquement en interne..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="save-new-dossier">
                        <i class="fas fa-save"></i> Créer le dossier
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/bundles/select2/dist/css/select2.min.css') }}">
    <style>
        .progress-text {
            position: absolute;
            width: 100%;
            text-align: center;
            font-weight: bold;
            color: #fff;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }

        .select2-container {
            width: 100% !important;
        }

        .time-entry-row textarea {
            resize: vertical;
            min-height: 50px;
        }

        .time-entry-row textarea:focus {
            border-color: #6777ef;
            box-shadow: 0 0 0 0.2rem rgba(103, 119, 239, 0.25);
        }

        .btn-new-dossier-global {
            white-space: nowrap;
        }

        .btn-new-dossier-global i {
            margin-right: 5px;
        }
    </style>

    <style>
/* Styles pour améliorer l'apparence des cartes d'activités */
.time-entry-row .card {
    border-left: 4px solid #6777ef;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}

.time-entry-row .card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.time-entry-row textarea {
    resize: vertical;
    min-height: 80px;
    font-size: 0.9rem;
}

.time-entry-row textarea:focus {
    border-color: #6777ef;
    box-shadow: 0 0 0 0.2rem rgba(103, 119, 239, 0.25);
}

.time-entry-row input[type="time"],
.time-entry-row input[type="number"] {
    font-weight: 500;
}

/* Badge pour le numéro d'activité */
.activity-badge {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    display: inline-block;
    margin-bottom: 10px;
}

/* Amélioration du bouton de suppression */
.remove-row {
    padding: 8px 16px;
    font-weight: 500;
}

.remove-row:hover {
    transform: scale(1.05);
}

/* Responsive: ajustements pour petits écrans */
@media (max-width: 768px) {
    .time-entry-row .card {
        margin-bottom: 1rem;
    }
    
    .remove-row {
        width: 100%;
        margin-top: 10px;
    }
}

/* Style pour les labels */
.time-entry-row label {
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
    color: #495057;
}

/* Animation pour l'ajout de nouvelles lignes */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.time-entry-row {
    animation: slideIn 0.3s ease-out;
}
</style>

@endpush

@push('scripts')
    <script src="{{ asset('assets/bundles/select2/dist/js/select2.full.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function () {
            $('.select2').select2({
                width: '100%',
                placeholder: "Choisir un dossier..."
            });

            let rowIndex = 1;
            let currentDossierSelect = null;

            // Stocker les options des dossiers une seule fois
            const dossierOptionsHTML = $('#time-entries-container .dossier-select:first').html();

            // Ouvrir le modal uniquement via le bouton global
            $('.btn-new-dossier-global').on('click', function () {
                currentDossierSelect = null;
                $('#newDossierModal').modal('show');
            });

            // Création du dossier via AJAX
            $('#save-new-dossier').on('click', function () {
                let form = $('#new-dossier-form');
                let submitBtn = $(this);

                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Création...');

                let formData = new FormData(form[0]);

                $.ajax({
                    url: '{{ route("dossiers.store") }}',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        // Créer la nouvelle option
                        let newOption = `<option value="${response.dossier.id}"
                        data-client="${response.client.nom}"
                        data-reference="${response.dossier.reference || ''}">
                        ${response.dossier.nom} - ${response.client.nom}
                    </option>`;

                        // Ajouter à tous les selects existants
                        $('.dossier-select').append(newOption).trigger('change');

                        // Mettre à jour le HTML des options pour les futures lignes
                        dossierOptionsHTML = $('.dossier-select:first').html();

                        $('#newDossierModal').modal('hide');
                        form[0].reset();
                        $('.select2').val(null).trigger('change');

                        Swal.fire('Succès', 'Dossier créé avec succès!', 'success');
                    },
                    error: function (xhr) {
                        let errors = xhr.responseJSON?.errors;
                        let msg = 'Une erreur est survenue.';
                        if (errors) msg = Object.values(errors).flat().join('<br>');
                        Swal.fire('Erreur', msg, 'error');
                    },
                    complete: function () {
                        submitBtn.prop('disabled', false).html('<i class="fas fa-save"></i> Créer le dossier');
                    }
                });
            });

            $('#add-row').on('click', function () {
                let newRow = `
                <div class="time-entry-row mb-4">
                    <div class="card">
                        <div class="card-body">
                            <!-- Première ligne: Informations principales -->
                            <div class="row align-items-end mb-3">
                                <div class="col-lg-3 col-md-4 col-12 mb-2">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold">Dossier <span class="text-danger">*</span></label>
                                        <select name="time_entries[${rowIndex}][dossier_id]" class="form-control select2 dossier-select" required>
                                            ${dossierOptionsHTML}
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-2 col-md-3 col-6 mb-2">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold">Heure début <span class="text-danger">*</span></label>
                                        <input type="time" name="time_entries[${rowIndex}][heure_debut]" class="form-control heure-debut text-center" required>
                                    </div>
                                </div>

                                <div class="col-lg-2 col-md-3 col-6 mb-2">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold">Heure fin <span class="text-danger">*</span></label>
                                        <input type="time" name="time_entries[${rowIndex}][heure_fin]" class="form-control heure-fin text-center" required>
                                    </div>
                                </div>

                                <div class="col-lg-2 col-md-2 col-6 mb-2">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold">Heures <span class="text-danger">*</span></label>
                                        <input type="number" step="0.25" min="0.25" name="time_entries[${rowIndex}][heures_reelles]"
                                            class="form-control heures-input text-center" readonly required>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-md-12 col-6 mb-2 text-lg-right text-md-left">
                                    <div class="form-group mb-0">
                                        <label class="d-none d-lg-block text-white">-</label>
                                        <button type="button" class="btn btn-danger btn-sm remove-row" title="Supprimer cette activité">
                                            <i class="fas fa-trash"></i> Supprimer
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Deuxième ligne: Descriptions détaillées -->
                            <div class="row">
                                <div class="col-md-6 col-12 mb-2">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold">Travaux réalisés</label>
                                        <textarea name="time_entries[${rowIndex}][travaux]"
                                            class="form-control travaux-input"
                                            rows="3"
                                            placeholder="Ex: Analyse des documents, rédaction rapport..."></textarea>
                                    </div>
                                </div>

                                <div class="col-md-6 col-12 mb-2">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold">Rendu</label>
                                        <textarea name="time_entries[${rowIndex}][rendu]"
                                            class="form-control"
                                            rows="3"
                                            placeholder="Ex: Rapport v1, 5 pages..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;

                $('#time-entries-container').append(newRow);

                // Initialiser Select2 sur le nouveau select
                $('#time-entries-container .dossier-select').last().select2({
                    width: '100%',
                    placeholder: "Choisir un dossier..."
                });

                // Réattacher l'événement de calcul des heures
                $('#time-entries-container .heure-debut, #time-entries-container .heure-fin').last().on('change', function () {
                    calculateHours($(this).closest('.time-entry-row'));
                });

                rowIndex++;
                updateTotal();
            });

            // Suppression ligne
            $(document).on('click', '.remove-row', function () {
                if ($('.time-entry-row').length > 1) {
                    $(this).closest('.time-entry-row').fadeOut(300, function () { $(this).remove(); updateTotal(); });
                } else {
                    Swal.fire('Attention', 'Vous devez avoir au moins une activité', 'warning');
                }
            });

            // Calcul heures auto
            $(document).on('change', '.heure-debut, .heure-fin', function () {
                calculateHours($(this).closest('.time-entry-row'));
            });

            function calculateHours(row) {
                let start = row.find('.heure-debut').val();
                let end = row.find('.heure-fin').val();
                if (start && end) {
                    let startTime = new Date('1970-01-01T' + start + ':00');
                    let endTime = new Date('1970-01-01T' + end + ':00');
                    if (endTime < startTime) endTime.setDate(endTime.getDate() + 1);
                    let diff = (endTime - startTime) / (1000 * 60 * 60);
                    row.find('.heures-input').val(diff.toFixed(2)).attr('title', decimalToHoursMinutes(diff));
                    updateTotal();
                }
            }

            function updateTotal() {
                let total = 0;
                $('.heures-input').each(function () { total += parseFloat($(this).val()) || 0; });
                let theoriques = parseFloat($('input[name="heures_theoriques"]').val()) || 8;

                $('#total-heures span').text(decimalToHoursMinutes(total));
                let percentage = (total / theoriques) * 100;
                let barPercentage = Math.min(percentage, 100);

                $('#progress-bar').css('width', barPercentage + '%');
                $('#progress-bar .progress-text').text(
                    `${decimalToHoursMinutes(total)} / ${decimalToHoursMinutes(theoriques)}`
                );

                $('#progress-over').css('width', percentage > 100 ? (percentage - 100) + '%' : '0%');
                $('#progress-under').css('width', percentage <= 100 ? (100 - percentage) + '%' : '0%');
            }

            // Validation formulaire
            $('#daily-form').on('submit', function (e) {
                let total = 0;
                $('.heures-input').each(function () { total += parseFloat($(this).val()) || 0; });
                if (total === 0) {
                    e.preventDefault();
                    Swal.fire('Erreur', 'Vous devez saisir au moins une activité', 'error');
                    return false;
                }
                if ($('.dossier-select').filter(function () { return !$(this).val(); }).length > 0) {
                    e.preventDefault();
                    Swal.fire('Erreur', 'Veuillez sélectionner un dossier pour chaque activité', 'error');
                    return false;
                }
            });

            updateTotal();

            function decimalToHoursMinutes(decimal) {
                if (!decimal || isNaN(decimal)) return '0h 00min';

                const hours = Math.floor(decimal);
                const minutes = Math.round((decimal - hours) * 60);

                return `${hours}h ${minutes.toString().padStart(2, '0')}min`;
            }
        });
    </script>
@endpush
