@extends('layaout')
@section('title', 'Validation finale des congés')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap');

    :root {
        --primary: #1a1f36;
        --accent: #4f6ef7;
        --accent-light: #eef1ff;
        --success: #0d9e6e;
        --success-light: #e6f9f3;
        --danger: #e53e3e;
        --danger-light: #fff0f0;
        --warning: #f59e0b;
        --warning-light: #fffbeb;
        --border: #e8ecf4;
        --text-muted: #8892a4;
        --surface: #f7f8fc;
        --white: #ffffff;
        --shadow-sm: 0 1px 3px rgba(26,31,54,0.08), 0 1px 2px rgba(26,31,54,0.04);
        --shadow-md: 0 4px 16px rgba(26,31,54,0.10), 0 2px 6px rgba(26,31,54,0.06);
        --shadow-lg: 0 12px 40px rgba(26,31,54,0.14);
        --radius: 12px;
        --radius-sm: 8px;
    }

    body {  background: var(--surface); }

    /* ── Header ── */
    .page-header {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 28px;
        padding-bottom: 20px;
        border-bottom: 1px solid var(--border);
    }
    .page-header-icon {
        width: 48px; height: 48px;
        background: linear-gradient(135deg, var(--accent), #7b93ff);
        border-radius: var(--radius-sm);
        display: flex; align-items: center; justify-content: center;
        color: white; font-size: 20px;
        box-shadow: 0 4px 12px rgba(79,110,247,0.35);
    }
    .page-header h1 {
        font-size: 22px; font-weight: 700;
        color: var(--primary); margin: 0;
        letter-spacing: -0.3px;
    }
    .page-header p { margin: 2px 0 0; color: var(--text-muted); font-size: 13.5px; }

    /* ── Card ── */
    .conge-card {
        background: var(--white);
        border-radius: var(--radius);
        box-shadow: var(--shadow-md);
        border: 1px solid var(--border);
        overflow: hidden;
    }
    .conge-card-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border);
        display: flex; align-items: center; justify-content: space-between;
        background: linear-gradient(to right, var(--white), var(--surface));
    }
    .conge-card-header h4 {
        font-size: 15px; font-weight: 600;
        color: var(--primary); margin: 0;
    }
    .badge-count {
        background: var(--warning-light);
        color: var(--warning);
        border: 1px solid rgba(245,158,11,0.2);
        font-size: 12px; font-weight: 600;
        padding: 4px 12px; border-radius: 20px;
        font-family: 'JetBrains Mono', monospace;
    }

    /* ── Table ── */
    .conge-table { width: 100%; border-collapse: collapse; }
    .conge-table thead tr {
        background: var(--surface);
    }
    .conge-table th {
        padding: 12px 16px;
        font-size: 11.5px; font-weight: 600;
        text-transform: uppercase; letter-spacing: 0.6px;
        color: var(--text-muted);
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
    }
    .conge-table td {
        padding: 14px 16px;
        font-size: 13.5px; color: var(--primary);
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
    }
    .conge-table tbody tr {
        transition: background 0.15s ease;
    }
    .conge-table tbody tr:hover { background: var(--surface); }
    .conge-table tbody tr:last-child td { border-bottom: none; }

    /* Row exit animation */
    .conge-table tbody tr.removing {
        animation: rowFadeOut 0.4s ease forwards;
    }
    @keyframes rowFadeOut {
        to { opacity: 0; transform: translateX(20px); max-height: 0; padding: 0; }
    }

    /* ── Employee cell ── */
    .employee-cell { display: flex; align-items: center; gap: 10px; }
    .employee-avatar {
        width: 34px; height: 34px; border-radius: 50%;
        background: linear-gradient(135deg, var(--accent), #7b93ff);
        display: flex; align-items: center; justify-content: center;
        color: white; font-size: 12px; font-weight: 600;
        flex-shrink: 0;
    }
    .employee-name { font-weight: 600; font-size: 13.5px; }

    /* ── Badges ── */
    .badge-type {
        background: var(--accent-light); color: var(--accent);
        font-size: 11.5px; font-weight: 600;
        padding: 3px 10px; border-radius: 6px;
        white-space: nowrap;
    }
    .badge-days {
        background: var(--primary); color: white;
        font-size: 11px; font-weight: 700;
        padding: 3px 9px; border-radius: 6px;
        font-family: 'JetBrains Mono', monospace;
    }
    .date-text {
        font-family: 'JetBrains Mono', monospace;
        font-size: 12.5px; color: #555e7a;
    }
    .preapproved-by {
        display: flex; align-items: center; gap: 6px;
        color: var(--text-muted); font-size: 13px;
    }
    .preapproved-by i { font-size: 11px; }

    /* ── Action buttons ── */
    .action-group { display: flex; gap: 8px; }
    .btn-approve, .btn-refuse {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 7px 14px; border-radius: var(--radius-sm);
        font-size: 12.5px; font-weight: 600;
        border: none; cursor: pointer;
        transition: all 0.18s ease;
        font-family: 'Sora', sans-serif;
    }
    .btn-approve {
        background: var(--success-light); color: var(--success);
    }
    .btn-approve:hover {
        background: var(--success); color: white;
        box-shadow: 0 4px 12px rgba(13,158,110,0.3);
        transform: translateY(-1px);
    }
    .btn-refuse {
        background: var(--danger-light); color: var(--danger);
    }
    .btn-refuse:hover {
        background: var(--danger); color: white;
        box-shadow: 0 4px 12px rgba(229,62,62,0.3);
        transform: translateY(-1px);
    }
    .btn-approve:disabled, .btn-refuse:disabled {
        opacity: 0.5; cursor: not-allowed; transform: none;
    }

    /* ── Empty state ── */
    .empty-state {
        padding: 64px 24px; text-align: center;
    }
    .empty-icon {
        width: 64px; height: 64px; margin: 0 auto 16px;
        background: var(--surface);
        border-radius: 50%; display: flex;
        align-items: center; justify-content: center;
        font-size: 28px; color: var(--text-muted);
        border: 2px dashed var(--border);
    }
    .empty-state h5 { font-size: 16px; font-weight: 600; color: var(--primary); margin-bottom: 6px; }
    .empty-state p { color: var(--text-muted); font-size: 14px; margin: 0; }

    /* ── Pagination ── */
    .pagination-wrapper {
        padding: 16px 24px;
        border-top: 1px solid var(--border);
        display: flex; align-items: center; justify-content: space-between;
        background: var(--surface);
    }
    .pagination-info {
        font-size: 12.5px; color: var(--text-muted);
        font-family: 'JetBrains Mono', monospace;
    }
    .pagination-controls { display: flex; align-items: center; gap: 4px; }
    .page-btn {
        width: 34px; height: 34px; border-radius: var(--radius-sm);
        border: 1px solid var(--border); background: white;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px; color: var(--primary); cursor: pointer;
        transition: all 0.15s; font-family: 'Sora', sans-serif;
        font-weight: 500;
    }
    .page-btn:hover:not(:disabled) {
        border-color: var(--accent); color: var(--accent);
        background: var(--accent-light);
    }
    .page-btn.active {
        background: var(--accent); color: white;
        border-color: var(--accent);
        box-shadow: 0 2px 8px rgba(79,110,247,0.35);
    }
    .page-btn:disabled { opacity: 0.4; cursor: not-allowed; }

    /* ── Loading spinner overlay ── */
    .table-loading {
        position: relative;
    }
    .table-loading::after {
        content: '';
        position: absolute; inset: 0;
        background: rgba(255,255,255,0.7);
        display: flex; align-items: center; justify-content: center;
        border-radius: 0 0 var(--radius) var(--radius);
        z-index: 10;
    }
</style>
@endpush

@section('content')
<section class="section">
    <div class="section-body">

        {{-- Header --}}
        <div class="page-header">
            <div class="page-header-icon">
                <i class="fas fa-check-double"></i>
            </div>
            <div>
                <h1>Validation finale des congés</h1>
                <p>Demandes pré-approuvées en attente de décision finale</p>
            </div>
        </div>

        {{-- Card --}}
        <div class="conge-card">
            <div class="conge-card-header">
                <h4><i class="fas fa-clock" style="color:var(--warning);margin-right:8px;"></i>Demandes en attente</h4>
                <span class="badge-count" id="total-badge">{{ $demandes->total() }} en attente</span>
            </div>

            {{-- Table --}}
            <div id="table-wrapper">
                <table class="conge-table">
                    <thead>
                        <tr>
                            <th>Employé</th>
                            <th>Type de congé</th>
                            <th>Du</th>
                            <th>Au</th>
                            <th>Jours</th>
                            <th>Pré-approuvé par</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="demandes-tbody">
                        @forelse($demandes as $demande)
                        <tr id="row-{{ $demande->id }}">
                            <td>
                                <div class="employee-cell">
                                    <div class="employee-avatar">
                                        {{ strtoupper(substr($demande->user->prenom, 0, 1)) }}{{ strtoupper(substr($demande->user->nom, 0, 1)) }}
                                    </div>
                                    <span class="employee-name">{{ $demande->user->prenom }} {{ $demande->user->nom }}</span>
                                </div>
                            </td>
                            <td><span class="badge-type">{{ $demande->typeConge->libelle }}</span></td>
                            <td><span class="date-text">{{ $demande->date_debut->format('d/m/Y') }}</span></td>
                            <td><span class="date-text">{{ $demande->date_fin->format('d/m/Y') }}</span></td>
                            <td><span class="badge-days">{{ $demande->nombre_jours }}j</span></td>
                            <td>
                                <div class="preapproved-by">
                                    <i class="fas fa-user-check"></i>
                                    {{ $demande->validePar->prenom ?? '-' }} {{ $demande->validePar->nom ?? '' }}
                                </div>
                            </td>
                            <td>
                                <div class="action-group">
                                    <button class="btn-approve btn-valider"
                                        data-id="{{ $demande->id }}"
                                        data-url="{{ route('conges.valider-finale', $demande) }}">
                                        <i class="fas fa-check"></i> Approuver
                                    </button>
                                    <button class="btn-refuse btn-refuser"
                                        data-id="{{ $demande->id }}"
                                        data-url="{{ route('conges.valider-finale', $demande) }}">
                                        <i class="fas fa-times"></i> Refuser
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr id="empty-row">
                            <td colspan="7">
                                <div class="empty-state">
                                    <div class="empty-icon"><i class="fas fa-inbox"></i></div>
                                    <h5>Aucune demande en attente</h5>
                                    <p>Toutes les demandes ont été traitées.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="pagination-wrapper" id="pagination-wrapper">
                <span class="pagination-info" id="pagination-info">
                    Affichage <strong>{{ $demandes->firstItem() ?? 0 }}</strong>–<strong>{{ $demandes->lastItem() ?? 0 }}</strong>
                    sur <strong id="total-count">{{ $demandes->total() }}</strong>
                </span>
                <div class="pagination-controls" id="pagination-controls">
                    @php
                        $currentPage = $demandes->currentPage();
                        $lastPage = $demandes->lastPage();
                    @endphp
                    <button class="page-btn" id="btn-prev" {{ $currentPage <= 1 ? 'disabled' : '' }} onclick="goToPage({{ $currentPage - 1 }})">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    @for($p = max(1, $currentPage - 2); $p <= min($lastPage, $currentPage + 2); $p++)
                        <button class="page-btn {{ $p == $currentPage ? 'active' : '' }}" onclick="goToPage({{ $p }})">{{ $p }}</button>
                    @endfor
                    <button class="page-btn" id="btn-next" {{ $currentPage >= $lastPage ? 'disabled' : '' }} onclick="goToPage({{ $currentPage + 1 }})">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>

    </div>
</section>
@endsection

@push('scripts')
<script>
$(document).ready(function () {

    let totalCount = {{ $demandes->total() }};
    let perPage = {{ $demandes->perPage() }};
    let currentPage = {{ $demandes->currentPage() }};
    let lastPage = {{ $demandes->lastPage() }};

    // ── Délégation d'événements (fonctionne après rechargement AJAX des lignes) ──
    $(document).on('click', '.btn-valider', function () {
        const url = $(this).data('url');
        const id = $(this).data('id');
        Swal.fire({
            title: 'Approuver définitivement ?',
            text: 'Le solde de congé sera déduit immédiatement.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d9e6e',
            confirmButtonText: '<i class="fas fa-check"></i> Oui, approuver',
            cancelButtonText: 'Annuler',
            input: 'textarea',
            inputPlaceholder: 'Commentaire optionnel...',
            inputAttributes: { style: 'font-family: Sora, sans-serif; font-size: 13px;' },
            customClass: { popup: 'swal-conge-popup' }
        }).then(result => {
            if (result.isConfirmed) {
                soumettrValidation(url, 'approuve', result.value || '', id);
            }
        });
    });

    $(document).on('click', '.btn-refuser', function () {
        const url = $(this).data('url');
        const id = $(this).data('id');
        Swal.fire({
            title: 'Refuser ce congé ?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e53e3e',
            confirmButtonText: '<i class="fas fa-times"></i> Oui, refuser',
            cancelButtonText: 'Annuler',
            input: 'textarea',
            inputPlaceholder: 'Motif du refus (optionnel)...',
            inputAttributes: { style: 'font-family: Sora, sans-serif; font-size: 13px;' },
            customClass: { popup: 'swal-conge-popup' }
        }).then(result => {
            if (result.isConfirmed) {
                soumettrValidation(url, 'refuse', result.value || '', id);
            }
        });
    });

    // ── Soumission AJAX + suppression immédiate de la ligne ──
    function soumettrValidation(url, action, commentaire, id) {
        const row = $('#row-' + id);
        const buttons = row.find('button');
        buttons.prop('disabled', true);

        $.post(url, {
            _token: '{{ csrf_token() }}',
            action: action,
            commentaire: commentaire
        })
        .done(function () {
            // Supprimer la ligne avec animation
            row.addClass('removing');
            setTimeout(function () {
                row.remove();
                totalCount--;
                updateCounters();

                // Si la page est vide et ce n'est pas la première page, aller à la page précédente
                const rowsLeft = $('#demandes-tbody tr').length;
                if (rowsLeft === 0) {
                    if (currentPage > 1) {
                        goToPage(currentPage - 1);
                    } else {
                        showEmptyState();
                    }
                }
            }, 420);

            // Toast de confirmation
            const label = action === 'approuve' ? 'approuvée' : 'refusée';
            const color = action === 'approuve' ? '#0d9e6e' : '#e53e3e';
            Swal.fire({
                toast: true, position: 'top-end',
                icon: action === 'approuve' ? 'success' : 'info',
                title: `Demande ${label} avec succès`,
                showConfirmButton: false, timer: 3000,
                timerProgressBar: true,
                iconColor: color
            });
        })
        .fail(function (xhr) {
            buttons.prop('disabled', false);
            Swal.fire('Erreur', xhr.responseJSON?.message || 'Une erreur est survenue.', 'error');
        });
    }

    // ── Pagination AJAX ──
    window.goToPage = function(page) {
        if (page < 1 || page > lastPage) return;
        currentPage = page;

        const wrapper = $('#table-wrapper');
        wrapper.css('opacity', '0.5');

        $.get(window.location.pathname, { page: page }, function (html) {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            // Remplacer le tbody
            const newTbody = doc.querySelector('#demandes-tbody');
            if (newTbody) $('#demandes-tbody').html(newTbody.innerHTML);

            // Mettre à jour la pagination
            const newLastPage = parseInt(doc.querySelector('[data-last-page]')?.dataset.lastPage) || lastPage;
            updatePagination(page, newLastPage);

            wrapper.css('opacity', '1');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    };

    function updateCounters() {
        $('#total-badge').text(totalCount + ' en attente');
        $('#total-count').text(totalCount);
    }

    function updatePagination(page, total) {
        lastPage = total;
        const controls = $('#pagination-controls');
        let html = `<button class="page-btn" ${page <= 1 ? 'disabled' : ''} onclick="goToPage(${page - 1})"><i class="fas fa-chevron-left"></i></button>`;
        for (let p = Math.max(1, page - 2); p <= Math.min(total, page + 2); p++) {
            html += `<button class="page-btn ${p === page ? 'active' : ''}" onclick="goToPage(${p})">${p}</button>`;
        }
        html += `<button class="page-btn" ${page >= total ? 'disabled' : ''} onclick="goToPage(${page + 1})"><i class="fas fa-chevron-right"></i></button>`;
        controls.html(html);
    }

    function showEmptyState() {
        $('#demandes-tbody').html(`
            <tr id="empty-row">
                <td colspan="7">
                    <div class="empty-state">
                        <div class="empty-icon"><i class="fas fa-inbox"></i></div>
                        <h5>Aucune demande en attente</h5>
                        <p>Toutes les demandes ont été traitées.</p>
                    </div>
                </td>
            </tr>
        `);
        $('#pagination-wrapper').hide();
    }
});
</script>
@endpush