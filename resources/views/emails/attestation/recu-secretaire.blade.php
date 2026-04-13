@extends('emails.layouts.email')

@section('content')
    <div class="card">

        {{-- ── Header ─────────────────────────────────────────────────────────── --}}
        <div style="background: linear-gradient(135deg, #244584 0%, #4573c9 100%);
                    border-radius: 8px 8px 0 0; padding: 28px 30px; text-align: center;">
            <h2 style="color:#fff; margin:0; font-size:1.2rem; font-weight:700;">
                🔔 Nouvelle demande d'attestation
            </h2>
            <p style="color:#fde5c8; margin:8px 0 0; font-size:.9rem;">
                Pour vérification et signature
            </p>
        </div>

        <div style="padding: 30px;">

            <p style="font-size:1rem; color:#333; margin-bottom:20px;">
                Madame, Monsieur,
            </p>

            <p style="color:#555; line-height:1.8;">
                Une nouvelle demande d'<strong>{{ $demande->libelleType }}</strong> a été soumise par
                <strong>{{ $employe->prenom }} {{ $employe->nom }}</strong>
                le {{ $demande->created_at->isoFormat('D MMMM YYYY à HH:mm') }}.
            </p>

            <div style="background:#fff3cd; border:1px solid #083790; border-radius:6px; padding:16px; margin:20px 0;">
                <p style="margin:0; color:#244584; font-size:.9rem;">
                    <strong>⚠️ Action requise :</strong> Cette demande est en attente de votre validation/signature.
                </p>
            </div>

            {{-- ── Informations de la demande ────────────────────────────────── --}}
            <div style="background:#f8f9fa; border-left:4px solid #13284e; padding:16px; margin:20px 0; border-radius:4px;">
                <h4 style="margin:0 0 12px; color:#333; font-size:0.95rem;">Détails de la demande :</h4>
                <table style="width:100%; font-size:0.9rem; color:#555;">
                    <tr>
                        <td style="padding:6px 0;"><strong>Employé :</strong></td>
                        <td style="padding:6px 0;">{{ $employe->prenom }} {{ $employe->nom }}</td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0;"><strong>Type :</strong></td>
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
                    📎 Le document PDF est joint à cet e-mail pour votre consultation.
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
