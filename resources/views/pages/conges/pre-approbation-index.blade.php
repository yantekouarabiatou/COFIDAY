@extends('layaout')
@section('title', 'Demandes à pré-approuver')

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

    .conge-card {
        background: #fff;
        border-radius: var(--radius);
        border: 1px solid var(--border);
        box-shadow: 0 4px 16px rgba(26,31,54,0.10);
        overflow: hidden;
    }
    .conge-card-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border);
        display: flex; align-items: center; justify-content: space-between;
        background: linear-gradient(to right, #fff, var(--surface));
    }
    .conge-card-header h4 { font-size: 15px; font-weight: 600; color: var(--primary); margin: 0; }

    .badge-count {
        background: #fef3c7; color: #92400e;
        border: 1px solid rgba(245,158,11,0.2);
        font-size: 12px; font-weight: 600;
        padding: 4px 12px; border-radius: 20px;
    }

    .conge-table { width: 100%; border-collapse: collapse; }
    .conge-table thead tr { background: var(--surface); }
    .conge-table th {
        padding: 12px 16px;
        font-size: 11.5px; font-weight: 600;
        text-transform: uppercase; letter-spacing: .06em;
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
    .conge-table tbody tr:hover { background: var(--surface); }
    .conge-table tbody tr:last-child td { border-bottom: none; }

    .employee-cell { display: flex; align-items: center; gap: 10px; }
    .employee-avatar {
        width: 34px; height: 34px; border-radius: 50%;
        background: linear-gradient(135deg, var(--accent), #7b93ff);
        display: flex; align-items: center; justify-content: center;
        color: white; font-size: 12px; font-weight: 600; flex-shrink: 0;
    }
    .employee-name { font-weight: 600; font-size: 13.5px; }

    .badge-type {
        background: var(--accent-light); color: var(--accent);
        font-size: 11.5px; font-weight: 600;
        padding: 3px 10px; border-radius: 6px; white-space: nowrap;
    }
    .badge-days {
        background: var(--primary); color: white;
        font-size: 11px; font-weight: 700;
        padding: 3px 9px; border-radius: 6px;
    }
    .date-text { font-size: 12.5px; color: #555e7a; }

    .btn-review {
        display: inline-flex; align-items: center; gap: 6px;
        background: var(--accent-light); color: var(--accent);
        border: 1px solid rgba(79,110,247,.25);
        padding: 7px 16px; border-radius: var(--radius-sm);
        font-size: 12.5px; font-weight: 600; text-decoration: none;
        transition: all .18s;
    }
    .btn-review:hover {
        background: var(--accent); color: white;
        box-shadow: 0 4px 12px rgba(79,110,247,.3);
        text-decoration: none;
    }

    .empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); }
    .empty-state i { font-size: 3rem; margin-bottom: 16px; opacity: .4; }
    .empty-state h5 { font-size: 16px; font-weight: 600; color: var(--primary); }
</style>
@endpush

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-user-check"></i> Pré-approbation des congés</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active">Pré-approbation</div>
        </div>
    </div>

    <div class="section-body">
        <div class="conge-card">
            <div class="conge-card-header">
                <h4><i class="fas fa-clock" style="color:#f59e0b; margin-right:8px;"></i>Demandes en attente de pré-approbation</h4>
                <span class="badge-count">{{ $demandes->total() }} en attente</span>
            </div>

            <table class="conge-table">
                <thead>
                    <tr>
                        <th>Employé</th>
                        <th>Type de congé</th>
                        <th>Du</th>
                        <th>Au</th>
                        <th>Jours</th>
                        <th>Soumise le</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($demandes as $demande)
                    <tr>
                        <td>
                            <div class="employee-cell">
                                <div class="employee-avatar">
                                    {{ strtoupper(substr($demande->user->prenom ?? 'U', 0, 1)) }}{{ strtoupper(substr($demande->user->nom ?? 'N', 0, 1)) }}
                                </div>
                                <span class="employee-name">{{ $demande->user->prenom ?? '' }} {{ $demande->user->nom ?? '' }}</span>
                            </div>
                        </td>
                        <td><span class="badge-type">{{ $demande->typeConge->libelle ?? '' }}</span></td>
                        <td><span class="date-text">{{ $demande->date_debut ? \Carbon\Carbon::parse($demande->date_debut)->format('d/m/Y') : '' }}</span></td>
                        <td><span class="date-text">{{ $demande->date_fin ? \Carbon\Carbon::parse($demande->date_fin)->format('d/m/Y') : '' }}</span></td>
                        <td><span class="badge-days">{{ $demande->nombre_jours }}j</span></td>
                        <td><span class="date-text">{{ $demande->created_at->format('d/m/Y') }}</span></td>
                        <td>
                            <a href="{{ route('conges.pre-approbation.show', $demande->id) }}" class="btn-review">
                                <i class="fas fa-eye"></i> Examiner
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <div><i class="fas fa-check-circle" style="color:#0d9e6e;"></i></div>
                                <h5>Aucune demande en attente</h5>
                                <p>Toutes les demandes ont été traitées.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            @if($demandes->hasPages())
            <div class="p-3 border-top">
                {{ $demandes->links() }}
            </div>
            @endif
        </div>
    </div>
</section>
@endsection
