@extends('emails.layouts.email')
@section('content')
<div class="card">
    <div style="background:linear-gradient(135deg,#e67e22 0%,#f39c12 100%);border-radius:8px 8px 0 0;padding:24px 30px;text-align:center;">
        <h2 style="color:#fff;margin:0;font-size:1.1rem;font-weight:700;">
            ✍️ Attestation format spécifique à préparer
        </h2>
    </div>
    <div style="padding:28px;">
        <p style="color:#333;">Bonjour,</p>
        <p style="color:#555;line-height:1.8;">
            Une demande d'<strong>attestation de travail — format spécifique</strong> vient d'être
            approuvée par la Direction Générale pour l'employé(e) suivant(e) :
        </p>
        <table style="width:100%;border-collapse:collapse;margin:16px 0;">
            <tr style="background:#f8f9fc;">
                <td style="padding:10px 14px;font-weight:600;width:160px;border:1px solid #e3e6f0;">Employé(e)</td>
                <td style="padding:10px 14px;border:1px solid #e3e6f0;">{{ $employe->prenom }} {{ $employe->nom }}</td>
            </tr>
            <tr>
                <td style="padding:10px 14px;font-weight:600;border:1px solid #e3e6f0;">Poste</td>
                <td style="padding:10px 14px;border:1px solid #e3e6f0;">{{ $employe->poste->libelle ?? 'Non défini' }}</td>
            </tr>
            <tr style="background:#f8f9fc;">
                <td style="padding:10px 14px;font-weight:600;border:1px solid #e3e6f0;">Email</td>
                <td style="padding:10px 14px;border:1px solid #e3e6f0;">{{ $employe->email }}</td>
            </tr>
            <tr>
                <td style="padding:10px 14px;font-weight:600;border:1px solid #e3e6f0;">Réf.</td>
                <td style="padding:10px 14px;border:1px solid #e3e6f0;">{{ $demande->numero_reference }}</td>
            </tr>
        </table>
        <div style="background:#fef9e7;border:1px solid #f39c12;border-radius:6px;padding:16px;margin:16px 0;">
            <p style="margin:0 0 6px;font-weight:700;color:#856404;">Besoin exprimé par l'employé(e) :</p>
            <p style="margin:0;color:#555;white-space:pre-wrap;line-height:1.7;">{{ $demande->motif }}</p>
        </div>
        <p style="color:#555;line-height:1.8;">
            Merci de préparer ce document manuellement et de contacter l'employé(e) pour la remise.
        </p>
    </div>
</div>
@endsection
