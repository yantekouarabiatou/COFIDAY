@extends('emails.layouts.email')

@section('content')
    <div class="card">

        {{-- ── Header ─────────────────────────────────────────────────────────── --}}
        <div style="background: linear-gradient(135deg, #1e3a5f 0%, #2d5986 100%);
                    border-radius: 8px 8px 0 0; padding: 28px 30px; text-align: center;">
            <h2 style="color:#fff; margin:0; font-size:1.2rem; font-weight:700;">
                📋 Demande d'attestation reçue
            </h2>
            <p style="color:#b8d4f0; margin:8px 0 0; font-size:.9rem;">
                Réf. : <strong>{{ $demande->numero_reference }}</strong>
            </p>
        </div>

        <div style="padding: 30px;">

            <p style="font-size:1rem; color:#333; margin-bottom:20px;">
                Bonjour <strong>{{ $employe->prenom }} {{ $employe->nom }}</strong>,
            </p>

            <p style="color:#555; line-height:1.8;">
                Nous accusons réception de votre demande d'<strong>{{ $demande->libelleType }}</strong>
                soumise le {{ $demande->created_at->isoFormat('D MMMM YYYY à HH:mm') }}.
            </p>

            <div style="background:#e8f5e8; border:1px solid #28a745; border-radius:6px; padding:16px; margin:20px 0;">
                <p style="margin:0; color:#155724; font-size:.9rem;">
                    <strong>✅ Statut :</strong> Votre demande est en cours de traitement.
                    Vous serez informé(e) par email dès qu'une décision sera prise.
                </p>
            </div>

            <div style="background:#eef6ff; border:1px solid #b6d4ff; border-radius:6px; padding:16px; margin:20px 0;">
                <p style="margin:0; color:#1d4ed8; font-size:.95rem; line-height:1.7;">
                    📎 Votre lettre de demande est jointe à cet email au format PDF.
                    Conservez-la comme preuve de votre requête.
                </p>
            </div>

            {{-- ── Détails de la demande ────────────────────────────────────────── --}}
            {{-- ── Détails de la demande ────────────────────────────────────────── --}}
            <div style="background:#f8f9fa; border-radius:6px; padding:20px; margin:25px 0;">
                <h4 style="margin:0 0 15px; color:#333; font-size:1rem;">Détails de votre demande :</h4>

                <table style="width:100%; border-collapse:collapse; table-layout:fixed;">
                    <colgroup>
                        <col style="width:38%;">
                        <col style="width:62%;">
                    </colgroup>
                    <tr>
                        <td style="padding:10px 12px; border-bottom:1px solid #dee2e6; font-weight:600; color:#555;">Type :
                        </td>
                        <td style="padding:10px 12px; border-bottom:1px solid #dee2e6; color:#333;">
                            {{ $demande->libelleType }}</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 12px; border-bottom:1px solid #dee2e6; font-weight:600; color:#555;">
                            Destinataire :</td>
                        <td style="padding:10px 12px; border-bottom:1px solid #dee2e6; color:#333;">
                            {{ $demande->destinataire ?: 'Non spécifié' }}
                        </td>
                    </tr>
                    @if($demande->inclure_salaire)
                        <tr>
                            <td style="padding:10px 12px; border-bottom:1px solid #dee2e6; font-weight:600; color:#555;">Salaire
                                inclus :</td>
                            <td style="padding:10px 12px; border-bottom:1px solid #dee2e6; color:#333;">
                                {{ number_format($demande->salaire_net, 0, ',', ' ') }} FCFA
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td style="padding:10px 12px; font-weight:600; color:#555;">Motif :</td>
                        <td style="padding:10px 12px; color:#333;">{{ Str::limit($demande->motif, 100) }}</td>
                    </tr>
                </table>
            </div>

            <p style="color:#666; font-size:.9rem; line-height:1.6;">
                Pour suivre l'évolution de votre demande, vous pouvez consulter votre
                <a href="{{ route('attestations.index') }}" style="color:#007bff; text-decoration:none;">espace
                    personnel</a>.
            </p>

            <p style="color:#666; font-size:.9rem; margin-bottom:0;">
                Cordialement,<br>
                <strong>L'équipe COFIMA</strong>
            </p>

        </div>

    </div>
@endsection