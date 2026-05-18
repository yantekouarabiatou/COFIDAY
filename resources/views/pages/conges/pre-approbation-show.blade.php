@extends('layaout')
@section('title', 'Pré-approbation de la demande de congé')

@push('styles')
<style>
    :root {
        --primary: #1a1f36;
        --accent: #4f6ef7;
        --accent-light: #eef1ff;
        --success: #0d9e6e;
        --success-light: #e6f9f3;
        --danger: #e53e3e;
        --danger-light: #fff0f0;
        --border: #e8ecf4;
        --text-muted: #8892a4;
        --surface: #f7f8fc;
        --radius: 12px;
        --radius-sm: 8px;
    }

    .review-card {
        background: #fff;
        border-radius: var(--radius);
        border: 1px solid var(--border);
        box-shadow: 0 4px 16px rgba(26,31,54,0.10);
        max-width: 680px;
        margin: 0 auto;
        overflow: hidden;
    }

    .review-header {
        background: linear-gradient(135deg, #244584, #4f6ef7);
        color: white;
        padding: 28px 32px;
    }
    .review-header h2 { margin: 0; font-size: 20px; font-weight: 700; }
    .review-header p  { margin: 6px 0 0; opacity: .85; font-size: 13.5px; }

    .review-body { padding: 28px 32px; }

    .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 24px;
    }
    .info-item {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 12px 16px;
    }
    .info-label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: var(--text-muted);
        margin-bottom: 4px;
    }
    .info-value {
        font-size: 14px;
        font-weight: 600;
        color: var(--primary);
    }

    .motif-box {
        background: #fffbeb;
        border-left: 4px solid #f59e0b;
        border-radius: var(--radius-sm);
        padding: 14px 16px;
        margin-bottom: 24px;
        font-size: 13.5px;
        color: #444;
    }

    .action-buttons {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        padding-top: 16px;
        border-top: 1px solid var(--border);
    }

    .btn-approve {
        background: var(--success);
        color: white;
        border: none;
        padding: 11px 24px;
        border-radius: var(--radius-sm);
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all .18s;
    }
    .btn-approve:hover { background: #0a8a5e; box-shadow: 0 4px 12px rgba(13,158,110,.3); }

    .btn-refuse {
        background: var(--danger-light);
        color: var(--danger);
        border: 1px solid rgba(229,62,62,.25);
        padding: 11px 24px;
        border-radius: var(--radius-sm);
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all .18s;
    }
    .btn-refuse:hover { background: var(--danger); color: white; box-shadow: 0 4px 12px rgba(229,62,62,.3); }

    .already-treated {
        background: #f0fdf4;
        border: 1px solid #86efac;
        border-radius: var(--radius-sm);
        padding: 16px 20px;
        color: #166534;
        font-size: 14px;
        text-align: center;
    }
</style>
@endpush

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-user-check"></i> Pré-approbation</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active">Pré-approbation</div>
        </div>
    </div>

    <div class="section-body">
        <div class="review-card">

            <div class="review-header">
                <h2><i class="fas fa-clipboard-check mr-2"></i>Demande de congé — Pré-approbation</h2>
                <p>Veuillez examiner la demande ci-dessous avant de prendre votre décision.</p>
            </div>

            <div class="review-body">

                @if($demande->statut !== 'en_attente')
                    <div class="already-treated">
                        <i class="fas fa-check-circle mr-2"></i>
                        Cette demande a déjà été traitée (statut : <strong>{{ $demande->statut }}</strong>).
                    </div>
                @else

                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-user mr-1"></i> Employé</div>
                            <div class="info-value">{{ $demande->user->prenom ?? '' }} {{ $demande->user->nom ?? '' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-tag mr-1"></i> Type de congé</div>
                            <div class="info-value">{{ $demande->typeConge->libelle ?? '—' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-calendar-alt mr-1"></i> Date de début</div>
                            <div class="info-value">{{ $demande->date_debut ? \Carbon\Carbon::parse($demande->date_debut)->isoFormat('D MMMM YYYY') : '—' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-calendar-check mr-1"></i> Date de fin</div>
                            <div class="info-value">{{ $demande->date_fin ? \Carbon\Carbon::parse($demande->date_fin)->isoFormat('D MMMM YYYY') : '—' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-sun mr-1"></i> Nombre de jours</div>
                            <div class="info-value">{{ $demande->nombre_jours }} jour(s)</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label"><i class="fas fa-clock mr-1"></i> Soumise le</div>
                            <div class="info-value">{{ $demande->created_at->isoFormat('D MMMM YYYY') }}</div>
                        </div>
                    </div>

                    @if(!empty($demande->motif))
                    <div class="motif-box">
                        <strong>Motif :</strong><br>
                        {{ $demande->motif }}
                    </div>
                    @endif

                    <form method="POST" action="{{ route('conges.pre-approbation.submit', $demande->id) }}">
                        @csrf
                        <div class="action-buttons">
                            <button type="submit" name="action" value="refuser" class="btn-refuse">
                                <i class="fas fa-times"></i> Refuser
                            </button>
                            <button type="submit" name="action" value="pre_approuver" class="btn-approve">
                                <i class="fas fa-check"></i> Pré-approuver
                            </button>
                        </div>
                    </form>

                @endif

            </div>
        </div>
    </div>
</section>
@endsection
