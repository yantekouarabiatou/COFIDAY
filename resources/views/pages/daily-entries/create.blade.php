@extends('layaout')
@section('title', 'Nouvelle Feuille de Temps')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/bundles/select2/dist/css/select2.min.css') }}">
<style>
:root {
    --ts-blue:#2563EB; --ts-blue-lt:#EFF6FF; --ts-green:#059669;
    --ts-amber:#D97706; --ts-red:#DC2626; --ts-slate:#475569;
    --ts-border:#E2E8F0; --ts-radius:12px;
}

body { background:#F1F5F9; font-family:'DM Sans',sans-serif; }

.form-wrap {
    max-width: 960px;
    margin: 0 auto;
    padding: 24px 20px 60px;
}

/* ── Page Header ── */
.page-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 24px; gap: 12px; flex-wrap: wrap;
}
.page-header__title { font-size: 22px; font-weight: 700; color: #0F172A; display: flex; align-items: center; gap: 10px; }
.page-header__title i { color: var(--ts-blue); }

/* ── Cards ── */
.card-ts {
    background: #fff;
    border: 1px solid var(--ts-border);
    border-radius: var(--ts-radius);
    box-shadow: 0 1px 3px rgba(0,0,0,.06);
    margin-bottom: 16px;
    overflow: hidden;
}
.card-ts__head {
    padding: 16px 24px;
    border-bottom: 1px solid #F1F5F9;
    display: flex; align-items: center; gap: 10px;
    font-weight: 700; font-size: 14px; color: #0F172A;
}
.card-ts__head i { color: var(--ts-blue); font-size: 15px; }
.card-ts__body { padding: 24px; }

/* ── Form Elements ── */
.form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px,1fr)); gap: 16px; }
.form-group-ts { display: flex; flex-direction: column; gap: 6px; }
.form-label-ts { font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; color: var(--ts-slate); }
.form-input-ts {
    border: 1.5px solid var(--ts-border);
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 14px;
    font-family: 'DM Sans', sans-serif;
    outline: none;
    transition: border-color .15s, box-shadow .15s;
    background: #fff;
    color: #0F172A;
}
.form-input-ts:focus { border-color: var(--ts-blue); box-shadow: 0 0 0 3px #DBEAFE; }
.form-input-ts:disabled, .form-input-ts[readonly] {
    background: #F8FAFC; color: var(--ts-slate); cursor: not-allowed;
}
textarea.form-input-ts { resize: vertical; min-height: 70px; }

/* ── Activity Cards ── */
.activity-card {
    background: #fff;
    border: 1.5px solid var(--ts-border);
    border-left: 4px solid var(--ts-blue);
    border-radius: var(--ts-radius);
    padding: 20px;
    margin-bottom: 12px;
    animation: slideDown .25s ease;
    position: relative;
    transition: box-shadow .2s, border-left-color .2s;
}
.activity-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.08); }
.activity-card.rejected { border-left-color: var(--ts-red); }

@keyframes slideDown {
    from { opacity:0; transform:translateY(-12px); }
    to   { opacity:1; transform:translateY(0); }
}

.activity-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 16px; gap: 8px;
}
.activity-num {
    background: linear-gradient(135deg, #4F46E5, #7C3AED);
    color: #fff;
    width: 28px; height: 28px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 700;
    flex-shrink: 0;
}

.activity-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 0.8fr; gap: 12px; margin-bottom: 14px; }
.activity-grid-desc { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

@media (max-width: 640px) {
    .activity-grid { grid-template-columns: 1fr 1fr; }
    .activity-grid-desc { grid-template-columns: 1fr; }
}

/* ── Checkbox "Autre" custom ── */
.autre-checkbox-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 6px;
    padding: 8px 12px;
    background: var(--ts-blue-lt);
    border: 1px dashed #93C5FD;
    border-radius: 8px;
    cursor: pointer;
    transition: background .15s;
    user-select: none;
    font-size: 13px;
    color: var(--ts-blue);
    font-weight: 500;
}
.autre-checkbox-wrap:hover { background: #DBEAFE; }
.autre-checkbox-wrap input[type="checkbox"] {
    width: 16px; height: 16px;
    accent-color: var(--ts-blue);
    cursor: pointer;
    flex-shrink: 0;
}

/* ── Progress Bar ── */
.progress-ts {
    height: 10px;
    background: #E2E8F0;
    border-radius: 5px;
    overflow: hidden;
    position: relative;
}
.progress-ts__fill {
    height: 100%;
    border-radius: 5px;
    transition: width .4s ease, background-color .3s;
    position: relative;
}
.progress-ts__text {
    position: absolute; right: 8px; top: 50%; transform: translateY(-50%);
    font-size: 10px; font-weight: 700; color: #fff;
    font-family: 'DM Mono', monospace;
}
.hours-recap {
    display: flex; align-items: center; gap: 16px;
    padding: 16px 20px;
    background: #F8FAFC;
    border-radius: 10px;
    margin-top: 12px;
}
.hours-recap__total {
    font-size: 28px; font-weight: 700;
    font-family: 'DM Mono', monospace;
    color: var(--ts-blue);
}
.hours-recap__label { font-size: 12px; color: var(--ts-slate); }

/* ── Buttons ── */
.btn-ts { display:inline-flex;align-items:center;gap:7px;padding:10px 20px;border-radius:10px;font-size:14px;font-weight:600;border:none;cursor:pointer;transition:all .15s;text-decoration:none;font-family:'DM Sans',sans-serif; }
.btn-ts.primary { background:var(--ts-blue);color:#fff; }
.btn-ts.primary:hover { background:#1D4ED8; }
.btn-ts.success { background:var(--ts-green);color:#fff; }
.btn-ts.outline { background:#fff;color:var(--ts-slate);border:1.5px solid var(--ts-border); }
.btn-ts.outline:hover { background:#F8FAFC; }
.btn-ts.danger-outline { background:#fff;color:var(--ts-red);border:1.5px solid #FCA5A5; }
.btn-ts.danger-outline:hover { background:var(--ts-red);color:#fff; }
.btn-ts.sm { padding:7px 14px;font-size:12px;border-radius:8px; }
.btn-ts.lg { padding:13px 28px;font-size:15px;border-radius:11px; }
.btn-ts:disabled { opacity:.5; cursor:not-allowed; }

/* ── Select2 override ── */
.select2-container .select2-selection--single {
    height: 42px !important;
    border: 1.5px solid var(--ts-border) !important;
    border-radius: 10px !important;
    display: flex; align-items: center;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 42px !important;
    padding: 0 14px !important;
    color: #0F172A !important;
    font-family: 'DM Sans', sans-serif !important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow { height: 42px !important; }
.select2-container--focus .select2-selection--single { border-color: var(--ts-blue) !important; box-shadow: 0 0 0 3px #DBEAFE !important; }
.select2-dropdown { border: 1.5px solid var(--ts-border) !important; border-radius: 10px !important; font-family: 'DM Sans', sans-serif; }
.select2-container--default .select2-results__option--highlighted { background: var(--ts-blue-lt) !important; color: var(--ts-blue) !important; }

/* ── New Activity Modal ── */
.ts-modal-overlay {
    position: fixed; inset: 0;
    background: rgba(15,23,42,.5);
    backdrop-filter: blur(6px);
    z-index: 9999;
    display: none; align-items: center; justify-content: center;
}
.ts-modal-overlay.open { display: flex; }
.ts-modal {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 32px 80px rgba(0,0,0,.2);
    padding: 36px;
    width: 100%; max-width: 520px;
    animation: modalIn .25s cubic-bezier(.34,1.56,.64,1);
    max-height: 90vh; overflow-y: auto;
}
@keyframes modalIn {
    from { opacity:0; transform:scale(.92) translateY(-20px); }
    to   { opacity:1; transform:scale(1) translateY(0); }
}
.ts-modal__head {
    display: flex; align-items: flex-start; gap: 14px; margin-bottom: 28px;
}
.ts-modal__icon {
    width: 48px; height: 48px;
    background: var(--ts-blue-lt);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; color: var(--ts-blue);
    flex-shrink: 0;
}
.ts-modal__title { font-size: 20px; font-weight: 700; color: #0F172A; margin: 0 0 4px; }
.ts-modal__sub   { font-size: 13px; color: var(--ts-slate); margin: 0; }
.ts-modal__grid  { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.ts-modal__footer { display: flex; gap: 10px; justify-content: flex-end; margin-top: 28px; padding-top: 20px; border-top: 1px solid #F1F5F9; }

/* ── Add button ── */
#add-row {
    width: 100%;
    border: 2px dashed #CBD5E1;
    border-radius: 12px;
    background: transparent;
    color: var(--ts-slate);
    padding: 14px;
    font-size: 14px; font-weight: 600; font-family: 'DM Sans', sans-serif;
    cursor: pointer;
    transition: all .2s;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    margin-top: 4px;
}
#add-row:hover { border-color: var(--ts-blue); color: var(--ts-blue); background: var(--ts-blue-lt); }
</style>
@endpush

@section('content')
<div class="form-wrap">

    {{-- Header --}}
    <div class="page-header">
        <div class="page-header__title">
            <i class="fas fa-clock"></i>
            Nouvelle Feuille de Temps
        </div>
        <a href="{{ route('daily-entries.index') }}" class="btn-ts outline sm">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>

    <form action="{{ route('daily-entries.store') }}" method="POST" id="daily-form">
        @csrf

        {{-- ── Infos générales ── --}}
        <div class="card-ts">
            <div class="card-ts__head">
                <i class="fas fa-info-circle"></i> Informations générales
            </div>
            <div class="card-ts__body">
                <div class="form-grid">
                    <div class="form-group-ts">
                        <label class="form-label-ts">Collaborateur</label>
                        <input type="text" class="form-input-ts" readonly
                            value="{{ auth()->user()->prenom }} {{ auth()->user()->nom }} ({{ auth()->user()->poste->intitule ?? '—' }})">
                        <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                    </div>
                    <div class="form-group-ts">
                        <label class="form-label-ts">Date <span style="color:var(--ts-red);">*</span></label>
                        <input type="date" name="jour" class="form-input-ts"
                            value="{{ old('jour', now()->format('Y-m-d')) }}"
                            max="{{ now()->format('Y-m-d') }}" required>
                    </div>
                    <div class="form-group-ts">
                        <label class="form-label-ts">Heures théoriques <span style="color:var(--ts-red);">*</span></label>
                        <input type="number" step="0.25" min="0" max="24" name="heures_theoriques"
                            class="form-input-ts" value="{{ old('heures_theoriques', 8) }}" required>
                    </div>
                </div>
                <div class="form-group-ts" style="margin-top:16px;">
                    <label class="form-label-ts">Commentaire général</label>
                    <textarea name="commentaire" class="form-input-ts"
                        placeholder="Ex: Réunion client, télétravail, déplacement…">{{ old('commentaire') }}</textarea>
                </div>
            </div>
        </div>

        {{-- ── Activités ── --}}
        <div class="card-ts">
            <div class="card-ts__head" style="justify-content:space-between;">
                <span><i class="fas fa-tasks"></i> Activités de la journée</span>
                <button type="button" id="open-new-dossier-global" class="btn-ts outline sm">
                    <i class="fas fa-plus"></i> Nouvelle activité
                </button>
            </div>
            <div class="card-ts__body" style="padding-bottom:8px;">

                <div id="time-entries-container">
                    {{-- Première ligne --}}
                    <div class="activity-card" data-index="0">
                        <div class="activity-header">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div class="activity-num">1</div>
                                <span style="font-size:13px;font-weight:600;color:#0F172A;">Activité 1</span>
                            </div>
                            <button type="button" class="btn-ts danger-outline sm remove-row">
                                <i class="fas fa-trash"></i> Supprimer
                            </button>
                        </div>

                        <div class="activity-grid">
                            <div class="form-group-ts" style="grid-column: 1 / -1;">
                                <label class="form-label-ts">Dossier / Activité <span style="color:var(--ts-red);">*</span></label>
                                <select name="time_entries[0][dossier_id]" class="form-input-ts select2 dossier-select" required>
                                    <option value="">Choisir un dossier…</option>
                                    @foreach($dossiers as $dossier)
                                        <option value="{{ $dossier->id }}"
                                            data-client="{{ $dossier->client->nom ?? 'Sans client' }}">
                                            {{ $dossier->nom }} — {{ $dossier->client->nom ?? 'Sans client' }}
                                        </option>
                                    @endforeach
                                </select>
                                {{-- Checkbox Autre --}}
                                <label class="autre-checkbox-wrap autre-trigger" data-index="0">
                                    <input type="checkbox" class="autre-cb" data-index="0">
                                    <i class="fas fa-plus-circle"></i>
                                    Créer une nouvelle activité maintenant
                                </label>
                            </div>

                            <div class="form-group-ts">
                                <label class="form-label-ts">Heure début <span style="color:var(--ts-red);">*</span></label>
                                <input type="time" name="time_entries[0][heure_debut]"
                                    class="form-input-ts heure-debut" value="08:00" required>
                            </div>
                            <div class="form-group-ts">
                                <label class="form-label-ts">Heure fin <span style="color:var(--ts-red);">*</span></label>
                                <input type="time" name="time_entries[0][heure_fin]"
                                    class="form-input-ts heure-fin" value="12:30" required>
                            </div>
                            <div class="form-group-ts">
                                <label class="form-label-ts">Durée</label>
                                <input type="number" step="0.25" min="0.25"
                                    name="time_entries[0][heures_reelles]"
                                    class="form-input-ts heures-input" value="4.50" readonly required
                                    style="font-family:'DM Mono',monospace;font-weight:600;color:var(--ts-blue);">
                            </div>
                        </div>

                        <div class="activity-grid-desc">
                            <div class="form-group-ts">
                                <label class="form-label-ts">Travaux réalisés</label>
                                <textarea name="time_entries[0][travaux]" class="form-input-ts"
                                    placeholder="Analyse, rédaction, réunion…"></textarea>
                            </div>
                            <div class="form-group-ts">
                                <label class="form-label-ts">Rendu / Livrable</label>
                                <textarea name="time_entries[0][rendu]" class="form-input-ts"
                                    placeholder="Rapport v1, 5 pages, présentation…"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button" id="add-row">
                    <i class="fas fa-plus-circle"></i> Ajouter une activité
                </button>

            </div>
        </div>

        {{-- ── Récapitulatif ── --}}
        <div class="card-ts">
            <div class="card-ts__body">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;gap:12px;flex-wrap:wrap;">
                    <span style="font-size:13px;font-weight:600;color:#0F172A;">Récapitulatif des heures</span>
                    <div class="hours-recap" style="margin:0;">
                        <div>
                            <div class="hours-recap__total" id="total-display">0h 00min</div>
                            <div class="hours-recap__label">saisies / <span id="theoriques-display">8h</span> théoriques</div>
                        </div>
                    </div>
                </div>
                <div class="progress-ts">
                    <div class="progress-ts__fill" id="progress-fill" style="width:0%;background:#2563EB;">
                        <span class="progress-ts__text" id="progress-text"></span>
                    </div>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--ts-slate);margin-top:6px;">
                    <span>0h</span>
                    <span id="theoriques-end">8h</span>
                </div>
            </div>
        </div>

        {{-- ── Actions ── --}}
        <div style="display:flex;justify-content:flex-end;gap:12px;flex-wrap:wrap;">
            <a href="{{ route('daily-entries.index') }}" class="btn-ts outline lg">
                <i class="fas fa-times"></i> Annuler
            </a>
            <button type="submit" class="btn-ts primary lg" id="submit-btn">
                <i class="fas fa-save"></i> Enregistrer la feuille
            </button>
        </div>
    </form>
</div>

{{-- ══════════════════════════════════════════════════════════
     MODAL — Nouvelle Activité
══════════════════════════════════════════════════════════ --}}
<div class="ts-modal-overlay" id="new-dossier-modal">
    <div class="ts-modal">
        <div class="ts-modal__head">
            <div class="ts-modal__icon"><i class="fas fa-folder-plus"></i></div>
            <div>
                <h3 class="ts-modal__title">Nouvelle activité</h3>
                <p class="ts-modal__sub">Créer un dossier et l'associer automatiquement à cette ligne</p>
            </div>
        </div>

        <form id="new-dossier-form">
            @csrf

            <div class="form-group-ts" style="margin-bottom:14px;">
                <label class="form-label-ts">Nom de l'activité <span style="color:var(--ts-red);">*</span></label>
                <input type="text" name="nom" id="modal-nom" class="form-input-ts"
                    placeholder="Ex: Audit financier client X" required>
            </div>

            <div class="form-group-ts" style="margin-bottom:14px;">
                <label class="form-label-ts">Référence <small style="font-weight:400;text-transform:none;letter-spacing:0;">(auto)</small></label>
                <input type="text" name="reference" id="modal-reference" class="form-input-ts" readonly
                    style="background:#F8FAFC;font-family:'DM Mono',monospace;font-size:13px;">
            </div>

            <div class="form-group-ts" style="margin-bottom:14px;">
                <label class="form-label-ts">Client</label>
                <select name="client_id" id="modal-client" class="form-input-ts select2-modal">
                    <option value="">Sans client</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->nom }}</option>
                    @endforeach
                </select>
            </div>

            <div class="ts-modal__grid" style="margin-bottom:14px;">
                <div class="form-group-ts">
                    <label class="form-label-ts">Type <span style="color:var(--ts-red);">*</span></label>
                    <select name="type_dossier" class="form-input-ts" required>
                        <option value="">Sélectionner…</option>
                        <option value="audit">Audit</option>
                        <option value="conseil">Conseil</option>
                        <option value="formation">Formation</option>
                        <option value="expertise">Expertise</option>
                        <option value="autre" selected>Autre</option>
                    </select>
                </div>
                <div class="form-group-ts">
                    <label class="form-label-ts">Statut</label>
                    <select name="statut" class="form-input-ts">
                        <option value="ouvert">Ouvert</option>
                        <option value="en_cours" selected>En cours</option>
                    </select>
                </div>
            </div>

            <div class="form-group-ts">
                <label class="form-label-ts">Description</label>
                <textarea name="description" class="form-input-ts" rows="2"
                    placeholder="Description optionnelle…"></textarea>
            </div>
        </form>

        <div class="ts-modal__footer">
            <button type="button" class="btn-ts outline" id="close-modal-btn">
                <i class="fas fa-times"></i> Annuler
            </button>
            <button type="button" class="btn-ts primary" id="save-dossier-btn">
                <i class="fas fa-check"></i> Créer & sélectionner
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/bundles/select2/dist/js/select2.full.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function() {

    const CSRF = '{{ csrf_token() }}';
    let rowIndex = 1;
    let currentTriggerSelect = null;

    // ── Init Select2 ─────────────────────────────────────────
    function initSelect2(ctx) {
        $(ctx || '.dossier-select').select2({ width: '100%', placeholder: 'Choisir un dossier…' });
    }
    initSelect2();

    // ── Modal open/close ──────────────────────────────────────
    function openModal(triggerSelect) {
        currentTriggerSelect = triggerSelect;
        document.getElementById('new-dossier-modal').classList.add('open');
        generateRef('');
        // Init Select2 dans le modal
        if (!$('#modal-client').data('select2')) {
            $('#modal-client').select2({ dropdownParent: $('#new-dossier-modal'), width: '100%', placeholder: 'Sans client' });
        }
    }

    function closeModal() {
        document.getElementById('new-dossier-modal').classList.remove('open');
        document.getElementById('new-dossier-form').reset();
        // Décocher toutes les checkboxes "autre"
        document.querySelectorAll('.autre-cb').forEach(cb => cb.checked = false);
        currentTriggerSelect = null;
    }

    document.getElementById('close-modal-btn').addEventListener('click', closeModal);
    document.getElementById('new-dossier-modal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });

    // Bouton global
    document.getElementById('open-new-dossier-global').addEventListener('click', () => openModal(null));

    // ── Checkbox "Autre" → ouvre le modal ────────────────────
    $(document).on('change', '.autre-cb', function() {
        if (this.checked) {
            const card    = this.closest('.activity-card');
            const select  = card ? card.querySelector('.dossier-select') : null;
            this.checked  = false; // reset immédiatement pour UX
            openModal(select ? $(select) : null);
        }
    });

    // ── Génération référence ──────────────────────────────────
    function generateRef(nom) {
        const prefix = (nom || '').substring(0, 3).toUpperCase().padEnd(3, 'X');
        const now    = new Date();
        const date   = now.toISOString().slice(2, 10).replace(/-/g, '');
        const time   = now.toTimeString().slice(0, 5).replace(':', '');
        document.getElementById('modal-reference').value = `DOS-${prefix}-${date}${time}`;
    }

    $('#modal-nom').on('blur', function() { generateRef(this.value); });
    $('#modal-nom').on('input', function() {
        if (this.value.length >= 3) generateRef(this.value);
    });

    // ── Enregistrement nouvelle activité ─────────────────────
    document.getElementById('save-dossier-btn').addEventListener('click', function() {
        const btn  = this;
        const form = document.getElementById('new-dossier-form');
        const nom  = form.querySelector('input[name="nom"]').value.trim();

        if (!nom) {
            form.querySelector('input[name="nom"]').style.borderColor = '#DC2626';
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Création…';

        const fd = new FormData(form);

        fetch('{{ route("dossiers.store") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
            body: fd
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                Swal.fire('Erreur', data.message || 'Erreur lors de la création.', 'error');
                return;
            }

            const client  = data.client?.nom ?? 'Sans client';
            const option  = new Option(`${data.dossier.nom} — ${client}`, data.dossier.id, true, true);
            option.dataset.client = client;

            // Ajouter dans tous les selects existants
            document.querySelectorAll('.dossier-select').forEach(sel => {
                const $sel = $(sel);
                if ($sel.find(`option[value="${data.dossier.id}"]`).length === 0) {
                    $sel.append(new Option(`${data.dossier.nom} — ${client}`, data.dossier.id));
                }
            });

            // Sélectionner dans le select déclencheur
            if (currentTriggerSelect) {
                currentTriggerSelect.val(data.dossier.id).trigger('change');
            } else {
                // Sélectionner dans le premier select vide
                $('.dossier-select').each(function() {
                    if (!$(this).val()) {
                        $(this).val(data.dossier.id).trigger('change');
                        return false;
                    }
                });
            }

            closeModal();
            Swal.fire({ icon:'success', title:'Activité créée !', text:`"${data.dossier.nom}" a été ajoutée.`, timer: 2000, showConfirmButton: false });
        })
        .catch(() => Swal.fire('Erreur', 'Impossible de créer le dossier.', 'error'))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i> Créer & sélectionner';
        });
    });

    // ── Ajouter une activité ──────────────────────────────────
    document.getElementById('add-row').addEventListener('click', function() {
        const num = rowIndex + 1;
        const html = `
        <div class="activity-card" data-index="${rowIndex}">
            <div class="activity-header">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div class="activity-num">${num}</div>
                    <span style="font-size:13px;font-weight:600;color:#0F172A;">Activité ${num}</span>
                </div>
                <button type="button" class="btn-ts danger-outline sm remove-row">
                    <i class="fas fa-trash"></i> Supprimer
                </button>
            </div>

            <div class="activity-grid">
                <div class="form-group-ts" style="grid-column: 1 / -1;">
                    <label class="form-label-ts">Dossier / Activité <span style="color:var(--ts-red);">*</span></label>
                    <select name="time_entries[${rowIndex}][dossier_id]" class="form-input-ts select2 dossier-select" required>
                        ${$('.dossier-select').first().html()}
                    </select>
                    <label class="autre-checkbox-wrap autre-trigger" data-index="${rowIndex}">
                        <input type="checkbox" class="autre-cb" data-index="${rowIndex}">
                        <i class="fas fa-plus-circle"></i>
                        Créer une nouvelle activité maintenant
                    </label>
                </div>
                <div class="form-group-ts">
                    <label class="form-label-ts">Heure début <span style="color:var(--ts-red);">*</span></label>
                    <input type="time" name="time_entries[${rowIndex}][heure_debut]" class="form-input-ts heure-debut" required>
                </div>
                <div class="form-group-ts">
                    <label class="form-label-ts">Heure fin <span style="color:var(--ts-red);">*</span></label>
                    <input type="time" name="time_entries[${rowIndex}][heure_fin]" class="form-input-ts heure-fin" required>
                </div>
                <div class="form-group-ts">
                    <label class="form-label-ts">Durée</label>
                    <input type="number" step="0.25" min="0.25" name="time_entries[${rowIndex}][heures_reelles]"
                        class="form-input-ts heures-input" readonly required
                        style="font-family:'DM Mono',monospace;font-weight:600;color:var(--ts-blue);">
                </div>
            </div>

            <div class="activity-grid-desc">
                <div class="form-group-ts">
                    <label class="form-label-ts">Travaux réalisés</label>
                    <textarea name="time_entries[${rowIndex}][travaux]" class="form-input-ts" placeholder="Analyse, rédaction…"></textarea>
                </div>
                <div class="form-group-ts">
                    <label class="form-label-ts">Rendu / Livrable</label>
                    <textarea name="time_entries[${rowIndex}][rendu]" class="form-input-ts" placeholder="Rapport, présentation…"></textarea>
                </div>
            </div>
        </div>`;

        document.getElementById('time-entries-container').insertAdjacentHTML('beforeend', html);

        // Init select2 sur le nouveau select
        const newSelect = document.querySelector(`[data-index="${rowIndex}"] .dossier-select`);
        $(newSelect).select2({ width: '100%', placeholder: 'Choisir un dossier…' });

        rowIndex++;
        updateTotal();
    });

    // ── Supprimer activité ────────────────────────────────────
    $(document).on('click', '.remove-row', function() {
        const cards = document.querySelectorAll('.activity-card');
        if (cards.length <= 1) {
            Swal.fire({ icon:'warning', title:'Minimum requis', text:'Au moins une activité est obligatoire.', timer:2000, showConfirmButton:false });
            return;
        }
        this.closest('.activity-card').style.opacity = '0';
        this.closest('.activity-card').style.transform = 'translateY(-10px)';
        this.closest('.activity-card').style.transition = 'all .2s';
        setTimeout(() => {
            this.closest('.activity-card').remove();
            reNumberCards();
            updateTotal();
        }, 200);
    });

    function reNumberCards() {
        document.querySelectorAll('.activity-card').forEach((card, i) => {
            card.querySelector('.activity-num').textContent = i + 1;
        });
    }

    // ── Calcul heures ─────────────────────────────────────────
    $(document).on('change', '.heure-debut, .heure-fin', function() {
        calcHours(this.closest('.activity-card'));
    });

    function calcHours(card) {
        const start = card.querySelector('.heure-debut').value;
        const end   = card.querySelector('.heure-fin').value;
        if (!start || !end) return;
        let s = new Date('1970-01-01T' + start);
        let e = new Date('1970-01-01T' + end);
        if (e <= s) e.setDate(e.getDate() + 1);
        const diff = (e - s) / 3600000;
        card.querySelector('.heures-input').value = diff.toFixed(2);
        updateTotal();
    }

    function updateTotal() {
        let total = 0;
        document.querySelectorAll('.heures-input').forEach(i => total += parseFloat(i.value) || 0);
        const theoriques = parseFloat(document.querySelector('[name="heures_theoriques"]').value) || 8;
        const pct        = Math.min((total / theoriques) * 100, 110);
        const fill       = document.getElementById('progress-fill');
        const color      = pct >= 100 ? '#059669' : (pct >= 80 ? '#D97706' : '#2563EB');

        fill.style.width          = Math.min(pct, 100) + '%';
        fill.style.backgroundColor = color;
        document.getElementById('total-display').textContent    = decToHM(total);
        document.getElementById('progress-text').textContent    = Math.round(pct) + '%';
        document.getElementById('theoriques-display').textContent = decToHM(theoriques);
        document.getElementById('theoriques-end').textContent   = decToHM(theoriques);
    }

    function decToHM(d) {
        const h = Math.floor(d), m = Math.round((d - h) * 60);
        return `${h}h ${m.toString().padStart(2,'0')}min`;
    }

    $('[name="heures_theoriques"]').on('input', updateTotal);

    // ── Validation formulaire ─────────────────────────────────
    document.getElementById('daily-form').addEventListener('submit', function(e) {
        let total = 0;
        document.querySelectorAll('.heures-input').forEach(i => total += parseFloat(i.value) || 0);
        if (total <= 0) {
            e.preventDefault();
            Swal.fire({ icon:'error', title:'Aucune heure saisie', text:'Vous devez saisir au moins une activité avec des heures.' });
            return;
        }
        let emptySelect = false;
        document.querySelectorAll('.dossier-select').forEach(s => { if (!s.value) emptySelect = true; });
        if (emptySelect) {
            e.preventDefault();
            Swal.fire({ icon:'warning', title:'Dossier manquant', text:'Chaque activité doit avoir un dossier sélectionné.' });
            return;
        }
        document.getElementById('submit-btn').disabled = true;
        document.getElementById('submit-btn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enregistrement…';
    });

    updateTotal();
});
</script>
@endpush