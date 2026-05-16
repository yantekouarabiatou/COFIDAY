<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rappel — Demandes d'attestation en attente</title>
    <style>
        body { background-color: #f4f4f7; margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; }
        .email-container { width: 100%; padding: 20px 0; }
        .logo-container  { text-align: center; margin-bottom: 20px; }
        .logo            { max-width: 140px; height: auto; }
        .header {
            background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
            color: white; padding: 24px; text-align: center;
            font-size: 20px; font-weight: bold; border-radius: 8px 8px 0 0;
        }
        .header .sub { font-size: 13px; font-weight: normal; opacity: 0.85; margin-top: 5px; }
        .card {
            background: #ffffff; max-width: 620px; margin: auto;
            border-radius: 0 0 8px 8px; padding: 30px;
            border: 1px solid #e0e0e0;
        }
        .content p { font-size: 15px; color: #444; line-height: 1.7; }
        .badge-count {
            display: inline-block; background: #e53e3e; color: white;
            font-size: 22px; font-weight: bold; border-radius: 50%;
            width: 42px; height: 42px; line-height: 42px; text-align: center;
            margin-right: 10px; vertical-align: middle;
        }
        .demande-row {
            background: #eff6ff; border-left: 4px solid #2563eb;
            border-radius: 5px; padding: 12px 16px; margin-bottom: 10px;
            font-size: 14px; color: #333;
        }
        .demande-row .nom    { font-weight: bold; font-size: 15px; color: #1e3a5f; }
        .demande-row .detail { color: #555; margin-top: 3px; }
        .demande-row .type-badge {
            display: inline-block; background: #2563eb; color: white;
            border-radius: 12px; padding: 2px 10px; font-size: 11px;
            font-weight: bold; margin-left: 8px;
        }
        .btn-action {
            display: block; width: fit-content; margin: 24px auto 0;
            background: #1e3a5f; color: white; text-decoration: none;
            padding: 13px 32px; border-radius: 6px; font-size: 15px;
            font-weight: bold; text-align: center;
        }
        .alert-box {
            background: #fff8e1; border-left: 4px solid #f59e0b;
            border-radius: 5px; padding: 13px 16px; margin-top: 20px;
            font-size: 14px; color: #555;
        }
        .footer { text-align: center; color: #999; margin-top: 24px; font-size: 12px; }
    </style>
</head>
<body>
<div class="email-container">

    <div class="logo-container">
        <img src="https://cofima.cc/wp-content/uploads/2020/09/logo-cofima-bon.jpg" alt="Logo COFIMA" class="logo">
    </div>

    <div class="header">
        📄 Rappel — Demandes d'attestation à valider
        <div class="sub">{{ $demandes->count() }} demande(s) attendent votre validation</div>
    </div>

    <div class="card">
        <div class="content">

            <p>
                Bonjour <strong>{{ $destinataire->prenom }} {{ $destinataire->nom }}</strong>,
            </p>

            <p>
                Vous avez <span class="badge-count">{{ $demandes->count() }}</span>
                demande(s) d'attestation de travail en attente de votre <strong>validation</strong>.
            </p>

            @foreach($demandes as $demande)
            <div class="demande-row">
                <div class="nom">
                    {{ $demande->user->prenom ?? '' }} {{ $demande->user->nom ?? '' }}
                    <span class="type-badge">{{ $demande->libelleType }}</span>
                </div>
                <div class="detail">
                    <strong>Motif :</strong> {{ \Str::limit($demande->motif, 80) }}<br>
                    @if($demande->destinataire)
                        <strong>Destinataire :</strong> {{ $demande->destinataire }}<br>
                    @endif
                    <strong>Soumise :</strong> {{ $demande->created_at->diffForHumans() }}
                    ({{ $demande->created_at->isoFormat('D MMM YYYY') }})
                </div>
            </div>
            @endforeach

            <div class="alert-box">
                ⚠️ Ces attestations n'ont pas encore été traitées. Un collaborateur attend votre décision.
            </div>

            <a href="{{ url('/attestations/validation') }}" class="btn-action">
                Accéder aux attestations en attente →
            </a>

            <p style="margin-top: 28px; color: #888; font-size: 13px;">
                Cordialement,<br>
                <strong>COFIDAY — Système de gestion RH COFIMA</strong>
            </p>
        </div>
    </div>

    <div class="footer">
        © {{ date('Y') }} COFIMA BENIN — Tous droits réservés.<br>
        Cet email est généré automatiquement, merci de ne pas y répondre.
    </div>
</div>
</body>
</html>
