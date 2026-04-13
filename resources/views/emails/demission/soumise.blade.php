@extends('emails.layouts.email')

@section('content')
<div class="card">

    {{-- ── Header ─────────────────────────────────────────────────────────── --}}
    <div style="background: linear-gradient(135deg, #1e3a5f 0%, #2d5986 100%);
                border-radius: 8px 8px 0 0; padding: 28px 30px; text-align: center;">
        <h2 style="color:#fff; margin:0; font-size:1.2rem; font-weight:700;">
            📋 Lettre de démission reçue
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
            Nous accusons réception de votre lettre de démission soumise le
            {{ $demande->created_at->isoFormat('D MMMM YYYY à HH:mm') }}.
        </p>

        <div style="background:#e8f5e8; border:1px solid #28a745; border-radius:6px; padding:16px; margin:20px 0;">
            <p style="margin:0; color:#155724; font-size:.9rem;">
                <strong>✅ Statut :</strong> Votre lettre est en cours d'examen par la Direction Générale.
                Vous serez informé(e) par email dès qu'une décision sera prise.
            </p>
        </div>

        {{-- ── Détails de la demande ────────────────────────────────────────── --}}
        <div style="background:#f8f9fa; border-radius:6px; padding:20px; margin:25px 0;">
            <h4 style="margin:0 0 15px; color:#333; font-size:1rem;">Détails de votre demande :</h4>

            <table style="width:100%; border-collapse:collapse;">
                <tr>
                    <td style="padding:8px 0; border-bottom:1px solid #dee2e6; font-weight:600; color:#555;">Départ souhaité :</td>
                    <td style="padding:8px 0; border-bottom:1px solid #dee2e6; color:#dc3545; font-weight:600;">
                        {{ $demande->date_depart_souhaitee->isoFormat('D MMMM YYYY') }}
                    </td>
                </tr>
                <tr>
                    <td style="padding:8px 0; font-weight:600; color:#555;">Extrait de la lettre :</td>
                    <td style="padding:8px 0; color:#333;">{{ Str::limit($demande->lettre, 150) }}</td>
                </tr>
            </table>
        </div>

        <div style="background:#fff3cd; border:1px solid #ffc107; border-radius:6px; padding:16px; margin:20px 0;">
            <p style="margin:0; color:#856404; font-size:.9rem;">
                <strong>ℹ️ Information importante :</strong>
                Pendant l'examen de votre demande, votre statut reste actif.
                En cas d'approbation, un certificat de travail sera généré et vous sera transmis.
            </p>
        </div>

        <p style="color:#666; font-size:.9rem; line-height:1.6;">
            Pour suivre l'évolution de votre demande, vous pouvez consulter votre
            <a href="{{ route('demissions.index') }}" style="color:#007bff; text-decoration:none;">espace personnel</a>.
        </p>

        <p style="color:#666; font-size:.9rem; margin-bottom:0;">
            Cordialement,<br>
            <strong>L'équipe COFIMA</strong>
        </p>

    </div>

</div>
@endsection
