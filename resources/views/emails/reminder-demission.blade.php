<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rappel — Démissions en attente</title>
    <style>
        body { background-color: #f4f4f7; margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; }
        .email-container { width: 100%; padding: 20px 0; }
        .logo-container  { text-align: center; margin-bottom: 20px; }
        .logo            { max-width: 140px; height: auto; }
        .header {
            background: linear-gradient(135deg, #7c2d12 0%, #dc2626 100%);
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
            background: #fff5f5; border-left: 4px solid #dc2626;
            border-radius: 5px; padding: 12px 16px; margin-bottom: 10px;
            font-size: 14px; color: #333;
        }
        .demande-row .nom    { font-weight: bold; font-size: 15px; color: #7c2d12; }
        .demande-row .detail { color: #555; margin-top: 3px; }
        .demande-row .ref {
            display: inline-block; background: #dc2626; color: white;
            border-radius: 12px; padding: 2px 10px; font-size: 11px;
            font-weight: bold; margin-left: 8px;
        }
        .btn-action {
            display: block; width: fit-content; margin: 24px auto 0;
            background: #7c2d12; color: white; text-decoration: none;
            padding: 13px 32px; border-radius: 6px; font-size: 15px;
            font-weight: bold; text-align: center;
        }
        .alert-box {
            background: #fff8e1; border-left: 4px solid #f59e0b;
            border-radius: 5px; padding: 13px 16px; margin-top: 20px;
            font-size: 14px; color: #555;
        }
        .urgence-box {
            background: #fff5f5; border: 1px solid #fca5a5;
            border-radius: 5px; padding: 13px 16px; margin-top: 12px;
            font-size: 14px; color: #7c2d12;
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
        🔴 Rappel — Démission(s) en attente de traitement
        <div class="sub">{{ $demandes->count() }} démission(s) nécessitent votre attention</div>
    </div>

    <div class="card">
        <div class="content">

            <p>
                Bonjour <strong>{{ $destinataire->prenom }} {{ $destinataire->nom }}</strong>,
            </p>

            <p>
                Vous avez <span class="badge-count">{{ $demandes->count() }}</span>
                lettre(s) de démission en attente de traitement.
            </p>

            @foreach($demandes as $demande)
            <div class="demande-row">
                <div class="nom">
                    {{ $demande->user->prenom ?? '' }} {{ $demande->user->nom ?? '' }}
                    @if($demande->numero_reference)
                        <span class="ref">{{ $demande->numero_reference }}</span>
                    @endif
                </div>
                <div class="detail">
                    @if($demande->date_depart_souhaitee)
                        <strong>Départ souhaité :</strong>
                        {{ \Carbon\Carbon::parse($demande->date_depart_souhaitee)->isoFormat('D MMMM YYYY') }}<br>
                    @endif
                    @if($demande->date_embauche)
                        <strong>Date d'embauche :</strong>
                        {{ \Carbon\Carbon::parse($demande->date_embauche)->isoFormat('D MMMM YYYY') }}<br>
                    @endif
                    <strong>Soumise :</strong> {{ $demande->created_at->diffForHumans() }}
                    ({{ $demande->created_at->isoFormat('D MMM YYYY') }})
                </div>
            </div>
            @endforeach

            <div class="urgence-box">
                🚨 <strong>Attention :</strong> Une démission non traitée peut avoir des conséquences
                sur la paie et l'organisation de la transition du collaborateur.
            </div>

            <div class="alert-box">
                ⚠️ Merci de traiter ces dossiers rapidement afin de générer les certificats de travail.
            </div>

            <a href="{{ url('/demissions') }}" class="btn-action">
                Accéder aux démissions en attente →
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
