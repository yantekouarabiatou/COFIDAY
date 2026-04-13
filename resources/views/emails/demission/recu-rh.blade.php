@extends('emails.layouts.email')

@section('content')
    <div class="card">

        {{-- ── Header ─────────────────────────────────────────────────────────── --}}
        <div style="background: linear-gradient(135deg, #7b1fa2 0%, #512da8 100%);
                    border-radius: 8px 8px 0 0; padding: 28px 30px; text-align: center;">
            <h2 style="color:#fff; margin:0; font-size:1.2rem; font-weight:700;">
                👥 Demande de démission - Suivi RH
            </h2>
            <p style="color:#e1bee7; margin:8px 0 0; font-size:.9rem;">
                À archiver et traiter
            </p>
        </div>

        <div style="padding: 30px;">

            <p style="font-size:1rem; color:#333; margin-bottom:20px;">
                Équipe RH,
            </p>

            <p style="color:#555; line-height:1.8;">
                Une demande de démission a été soumise par
                <strong>{{ $employe->prenom }} {{ $employe->nom }}</strong>
                le {{ $demande->created_at->isoFormat('D MMMM YYYY à HH:mm') }} et est en attente de validation.
            </p>

            <div style="background:#f3e5f5; border:1px solid #7b1fa2; border-radius:6px; padding:16px; margin:20px 0;">
                <p style="margin:0; color:#4a148c; font-size:.9rem;">
                    <strong>📌 À traiter :</strong> Veuillez préparer le dossier de départ et les documents RH.
                </p>
            </div>

            {{-- ── Informations de la demande ────────────────────────────────── --}}
            <div style="background:#f8f9fa; border-left:4px solid #7b1fa2; padding:16px; margin:20px 0; border-radius:4px;">
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
                        <td style="padding:6px 0;"><strong>Ancienneté :</strong></td>
                        <td style="padding:6px 0;">
                            @php
                                $years = now()->diffInYears($demande->date_embauche);
                                $months = now()->diffInMonths($demande->date_embauche) % 12;
                                $yearsText = $years > 0 ? $years . ' an' . ($years > 1 ? 's' : '') : '';
                                $monthsText = $months > 0 ? $months . ' mois' : '';
                                echo trim("$yearsText $monthsText") ?: 'Moins d\'un mois';
                            @endphp
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0;"><strong>Date d'embauche :</strong></td>
                        <td style="padding:6px 0;">{{ $demande->date_embauche->isoFormat('D MMMM YYYY') }}</td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0;"><strong>Date départ souhaitée :</strong></td>
                        <td style="padding:6px 0;">{{ $demande->date_depart_souhaitee->isoFormat('D MMMM YYYY') }}</td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0;"><strong>Référence :</strong></td>
                        <td style="padding:6px 0;">{{ $demande->numero_reference }}</td>
                    </tr>
                </table>
            </div>

            <div style="background:#e3f2fd; border:1px solid #2196f3; border-radius:6px; padding:16px; margin:20px 0;">
                <p style="margin:0; color:#1565c0; font-weight:600;">
                    📋 Vous pouvez consulter le dossier complet dans le système pour archivage et traitement.
                </p>
            </div>

        </div>

        {{-- ── Footer ──────────────────────────────────────────────────────────── --}}
        <hr style="border:none; border-top:1px solid #ddd; margin:20px 0;">
        <div style="text-align:center; padding:0 30px 20px; font-size:0.85rem; color:#888;">
            <p style="margin:10px 0;">
                Cet e-mail a été généré automatiquement par le système de gestion des démissions COFIMA.
            </p>
        </div>

    </div>
@endsection
