@extends('emails.layouts.email')

@section('content')
    <div class="card">

        {{-- ── Header ─────────────────────────────────────────────────────────── --}}
        <div style="background: linear-gradient(135deg, #d32f2f 0%, #c62828 100%);
                    border-radius: 8px 8px 0 0; padding: 28px 30px; text-align: center;">
            <h2 style="color:#fff; margin:0; font-size:1.2rem; font-weight:700;">
                📜 Demande d'attestation en attente
            </h2>
            <p style="color:#ffcdd2; margin:8px 0 0; font-size:.9rem;">
                Validation requise du Directeur Général
            </p>
        </div>

        <div style="padding: 30px;">

            <p style="font-size:1rem; color:#333; margin-bottom:20px;">
                Monsieur le Directeur Général,
            </p>

            <p style="color:#555; line-height:1.8;">
                Une demande d'<strong>{{ $demande->libelleType }}</strong> de la part de
                <strong>{{ $employe->prenom }} {{ $employe->nom }}</strong>
                est en attente de votre approbation depuis le {{ $demande->created_at->isoFormat('D MMMM YYYY à HH:mm') }}.
            </p>

            <div style="background:#ffebee; border:1px solid #d32f2f; border-radius:6px; padding:16px; margin:20px 0;">
                <p style="margin:0; color:#b71c1c; font-size:.9rem;">
                    <strong>🔴 Priorité :</strong> Cette demande nécessite votre signature pour être finalisée.
                </p>
            </div>

            {{-- ── Informations de la demande ────────────────────────────────── --}}
            <div style="background:#f8f9fa; border-left:4px solid #d32f2f; padding:16px; margin:20px 0; border-radius:4px;">
                <h4 style="margin:0 0 12px; color:#333; font-size:0.95rem;">Détails de la demande :</h4>
                <table style="width:100%; font-size:0.9rem; color:#555;">
                    <tr>
                        <td style="padding:6px 0;"><strong>Employé :</strong></td>
                        <td style="padding:6px 0;">{{ $employe->prenom }} {{ $employe->nom }}</td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0;"><strong>Poste :</strong></td>
                        <td style="padding:6px 0;">{{ $employe->poste?->intitule ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0;"><strong>Type d'attestation :</strong></td>
                        <td style="padding:6px 0;">{{ $demande->libelleType }}</td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0;"><strong>Motif :</strong></td>
                        <td style="padding:6px 0;">{{ $demande->motif ?? 'Non spécifié' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0;"><strong>Référence :</strong></td>
                        <td style="padding:6px 0;">{{ $demande->numero_reference }}</td>
                    </tr>
                </table>
            </div>

            <div style="background:#e3f2fd; border:1px solid #2196f3; border-radius:6px; padding:16px; margin:20px 0;">
                <p style="margin:0; color:#1565c0; font-weight:600;">
                    📎 Le document PDF est joint à cet e-mail pour consultation et signature.
                </p>
            </div>

        </div>

        {{-- ── Footer ──────────────────────────────────────────────────────────── --}}
        <hr style="border:none; border-top:1px solid #ddd; margin:20px 0;">
        <div style="text-align:center; padding:0 30px 20px; font-size:0.85rem; color:#888;">
            <p style="margin:10px 0;">
                Cet e-mail a été généré automatiquement par le système de gestion des attestations COFIMA.
            </p>
        </div>

    </div>
@endsection
