@extends('emails.layouts.email')

@section('content')
<div class="card">

    <div style="background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%);
                border-radius: 8px 8px 0 0; padding: 28px 30px; text-align: center;">
        <h2 style="color: #fff; margin: 0; font-size: 1.3rem; font-weight: 700;">
            🚪 Nouvelle lettre de démission
        </h2>
        <p style="color: #ffc9c9; margin: 8px 0 0; font-size: .9rem;">
            Action requise — Validation Direction Générale
        </p>
    </div>

    <div style="padding: 30px;">

        <p style="color: #333; line-height: 1.8;">
            Bonjour,<br><br>
            Une nouvelle lettre de démission a été soumise et est en attente de votre validation.
        </p>

        {{-- Fiche récapitulatif --}}
        <div style="background: #f8f9fa; border: 1px solid #dee2e6;
                    border-radius: 6px; padding: 20px; margin: 20px 0;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; color: #6c757d; width: 160px; font-size: .9rem;">Employé(e)</td>
                    <td style="padding: 8px 0; color: #333; font-weight: bold;">
                        {{ $employe->prenom }} {{ $employe->nom }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #6c757d; font-size: .9rem;">Poste</td>
                    <td style="padding: 8px 0; color: #333;">{{ $employe->poste->libelle ?? '—' }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #6c757d; font-size: .9rem;">Email</td>
                    <td style="padding: 8px 0; color: #333;">{{ $employe->email }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #6c757d; font-size: .9rem;">Départ souhaité</td>
                    <td style="padding: 8px 0; color: #dc3545; font-weight: bold;">
                        {{ $demande->date_depart_souhaitee->isoFormat('D MMMM YYYY') }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #6c757d; font-size: .9rem;">Soumise le</td>
                    <td style="padding: 8px 0; color: #333;">
                        {{ $demande->created_at->isoFormat('D MMMM YYYY [à] HH:mm') }}
                    </td>
                </tr>
            </table>
        </div>

        {{-- Extrait de la lettre --}}
        <div style="background: #fff3cd; border: 1px solid #ffc107;
                    border-radius: 6px; padding: 16px; margin: 16px 0;">
            <p style="margin: 0 0 8px; color: #856404; font-weight: bold; font-size: .9rem;">
                Extrait de la lettre :
            </p>
            <p style="margin: 0; color: #333; font-size: .9rem; line-height: 1.7; font-style: italic;">
                "{{ Str::limit($demande->lettre, 300) }}"
            </p>
        </div>

        <p style="color: #555; line-height: 1.8;">
            Connectez-vous à l'application pour lire la lettre complète,
            fixer la date de départ effective et valider ou refuser la démission.
            <strong>Le certificat de travail sera généré automatiquement à l'approbation.</strong>
        </p>

        <p style="color: #888; font-size: .85rem; margin-top: 28px;
                  border-top: 1px solid #eee; padding-top: 16px;">
            Ceci est une notification automatique — COFIMA RH
        </p>

    </div>
</div>
@endsection
