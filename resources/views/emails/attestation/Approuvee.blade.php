@extends('emails.layouts.email')

@section('content')
<div class="card">

    {{-- ── Header ─────────────────────────────────────────────────────────── --}}
    <div style="background: linear-gradient(135deg, #1e3a5f 0%, #2d5986 100%);
                border-radius: 8px 8px 0 0; padding: 28px 30px; text-align: center;">
        @if($enAttentePrepRH)
            <h2 style="color:#fff; margin:0; font-size:1.2rem; font-weight:700;">
                 Votre demande est en cours de préparation
            </h2>
        @else
            <h2 style="color:#fff; margin:0; font-size:1.2rem; font-weight:700;">
                Votre attestation de travail est disponible
            </h2>
            <p style="color:#b8d4f0; margin:8px 0 0; font-size:.9rem;">
                Réf. : <strong>{{ $demande->numero_reference }}</strong>
            </p>
        @endif
    </div>

    <div style="padding: 30px;">

        <p style="font-size:1rem; color:#333; margin-bottom:20px;">
            Bonjour <strong>{{ $employe->prenom }} {{ $employe->nom }}</strong>,
        </p>

        @if($enAttentePrepRH)
        {{-- ── Type "autre" : RH va préparer manuellement ──────────────────── --}}
        <p style="color:#555; line-height:1.8;">
            Votre demande d'<strong>attestation de travail — format spécifique</strong> a été
            <span style="color:#28a745; font-weight:700;">approuvée</span>
            par la Direction Générale le {{ $demande->date_validation->isoFormat('D MMMM YYYY') }}.
        </p>

        <div style="background:#fff3cd; border:1px solid #ffc107; border-radius:6px; padding:16px; margin:20px 0;">
            <p style="margin:0; color:#856404; font-size:.9rem;">
                <strong>ℹ️ Prochaine étape :</strong>
                Le service RH va préparer votre attestation selon vos indications.
                Vous serez contacté(e) dès qu'elle sera prête pour la retirer auprès de la secrétaire.
            </p>
        </div>

        @else
        {{-- ── Attestation générée ─────────────────────────────────────────── --}}
        <p style="color:#555; line-height:1.8;">
            Votre demande de <strong>{{ $demande->libelleType }}</strong> a été
            <span style="color:#28a745; font-weight:700;">approuvée</span>
            par la Direction Générale.
        </p>

        <div style="background:#eef6ff; border:1px solid #b6d4ff; border-radius:6px; padding:16px; margin:20px 0;">
            <p style="margin:0; color:#1d4ed8; font-size:.95rem; line-height:1.7;">
                📎 Vous trouverez en pièce jointe votre attestation de travail au format PDF,
                contenant l'en-tête administrative de COFIMA et le contenu officiel du document.
            </p>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════ --}}
        {{-- DOCUMENT ATTESTATION                                           --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}
        <div style="border: 2px solid #1e3a5f; border-radius: 8px; padding: 32px; margin: 24px 0; background: #fff;">

            <div style="text-align:center; margin-bottom:24px;">
                <img src="https://cofima.cc/wp-content/uploads/2020/09/logo-cofima-bon.jpg"
                     alt="COFIMA" style="max-width:130px; height:auto;">
            </div>

            <h3 style="text-align:center; text-decoration:underline; text-transform:uppercase;
                        font-size:1.05rem; color:#1e3a5f; letter-spacing:2px; margin-bottom:28px;">
                Attestation de Travail
            </h3>

            {{-- Corps adapté selon le type --}}
            @if($demande->type === 'attestation_simple')

                <p style="text-align:justify; line-height:2; color:#222; font-size:.93rem;">
                    Je soussigné, <strong>AVANDE Jean-Claude</strong>, Expert-Comptable Diplômé inscrit à
                    l'Ordre des Experts Comptables et Comptables Agréés du Bénin sous le numéro 006-EC,
                    Associé-Gérant du Cabinet COFIMA atteste que
                    @if(strtolower($employe->sexe ?? '') === 'f')
                        <strong>Madame {{ strtoupper($employe->nom) }} {{ $employe->prenom }}</strong>
                        est employée
                    @else
                        <strong>Monsieur {{ strtoupper($employe->nom) }} {{ $employe->prenom }}</strong>
                        est employé
                    @endif
                    au sein du Cabinet COFIMA depuis le
                    <strong>{{ optional($employe->date_embauche)->isoFormat('D MMMM YYYY') ?? '[date d\'embauche]' }}</strong>
                    en qualité de <strong>{{ $employe->poste->libelle ?? '[Poste]' }}</strong>.
                </p>
                <p style="text-align:justify; line-height:2; color:#222; font-size:.93rem;">
                    En foi de quoi, la présente attestation est établie pour servir et valoir ce que de droit.
                </p>

            @elseif($demande->type === 'attestation_banque')

                <p style="text-align:justify; line-height:2; color:#222; font-size:.93rem;">
                    Je soussigné, <strong>AVANDE Jean-Claude</strong>, Expert-Comptable Diplômé inscrit à
                    l'Ordre des Experts Comptables et Comptables Agréés du Bénin sous le numéro 006-EC,
                    Associé-Gérant du Cabinet COFIMA atteste que
                    @if(strtolower($employe->sexe ?? '') === 'f')
                        <strong>Madame {{ strtoupper($employe->nom) }} {{ $employe->prenom }}</strong>
                        est employée
                    @else
                        <strong>Monsieur {{ strtoupper($employe->nom) }} {{ $employe->prenom }}</strong>
                        est employé
                    @endif
                    au sein du Cabinet COFIMA depuis le
                    <strong>{{ optional($employe->date_embauche)->isoFormat('D MMMM YYYY') ?? '[date d\'embauche]' }}</strong>
                    en qualité de <strong>{{ $employe->poste->libelle ?? '[Poste]' }}</strong>
                    @if($demande->inclure_salaire && $demande->salaire_net)
                        , avec une rémunération nette mensuelle de
                        <strong>{{ number_format($demande->salaire_net, 0, ',', ' ') }} FCFA</strong>
                    @endif
                    .
                </p>
                @if($demande->destinataire)
                <p style="text-align:justify; line-height:2; color:#222; font-size:.93rem;">
                    La présente attestation est établie à la demande de l'intéressé(e) pour être produite
                    auprès de <strong>{{ $demande->destinataire }}</strong> et pour servir et valoir ce que de droit.
                </p>
                @endif

            @elseif($demande->type === 'attestation_ambassade')

                <p style="text-align:justify; line-height:2; color:#222; font-size:.93rem;">
                    Je soussigné, <strong>AVANDE Jean-Claude</strong>, Expert-Comptable Diplômé inscrit à
                    l'Ordre des Experts Comptables et Comptables Agréés du Bénin sous le numéro 006-EC,
                    Associé-Gérant du Cabinet COFIMA atteste que
                    @if(strtolower($employe->sexe ?? '') === 'f')
                        <strong>Madame {{ strtoupper($employe->nom) }} {{ $employe->prenom }}</strong>
                        est employée
                    @else
                        <strong>Monsieur {{ strtoupper($employe->nom) }} {{ $employe->prenom }}</strong>
                        est employé
                    @endif
                    au sein du Cabinet COFIMA depuis le
                    <strong>{{ optional($employe->date_embauche)->isoFormat('D MMMM YYYY') ?? '[date d\'embauche]' }}</strong>
                    en qualité de <strong>{{ $employe->poste->libelle ?? '[Poste]' }}</strong>,
                    sous contrat à durée indéterminée.
                </p>
                @if($demande->destinataire)
                <p style="text-align:justify; line-height:2; color:#222; font-size:.93rem;">
                    La présente attestation est délivrée à la demande de l'intéressé(e) pour être produite
                    auprès de <strong>{{ $demande->destinataire }}</strong>
                    dans le cadre d'une démarche consulaire, et pour servir et valoir ce que de droit.
                </p>
                @endif

            @endif

            <p style="color:#222; font-size:.93rem; margin-top:16px; margin-bottom:4px;">
                Fait à Cotonou, le <strong>{{ $demande->date_validation->isoFormat('D MMMM YYYY') }}</strong>
            </p>

            <div style="margin-top:24px;">
                <p style="color:#222; margin-bottom:4px;"><strong>Pour COFIMA,</strong></p>
                <p style="color:#222; line-height:1.8; margin:0;">
                    <strong>Jean-Claude AVANDE</strong><br>
                    Expert-Comptable Diplômé<br>
                    Associé-Gérant
                </p>
            </div>

            <p style="margin-top:20px; font-size:.78rem; color:#aaa; border-top:1px solid #eee; padding-top:8px;">
                Réf. : {{ $demande->numero_reference }}
            </p>
        </div>
        {{-- ════════════════════════════════════════════════════════════════ --}}

        {{-- Avertissement --}}
        <div style="background:#fff3cd; border:1px solid #ffc107; border-radius:6px; padding:16px; margin:20px 0;">
            <p style="margin:0; color:#856404; font-size:.9rem;">
                <strong>⚠️ Important :</strong>
                Ce document est une version préliminaire <strong>sans cachet officiel</strong>.
                Présentez-vous auprès de la <strong>secrétaire</strong> pour retirer la version originale
                cachetée et signée.
            </p>
        </div>

        @if($demande->commentaire_dg)
        <div style="background:#e8f4f8; border-radius:6px; padding:14px;">
            <p style="margin:0; color:#1e3a5f; font-size:.9rem;">
                <strong>💬 Commentaire :</strong> {{ $demande->commentaire_dg }}
            </p>
        </div>
        @endif

        @endif {{-- fin @if(!enAttentePrepRH) --}}

        <p style="color:#888; font-size:.82rem; margin-top:24px; border-top:1px solid #eee; padding-top:14px;">
            © {{ date('Y') }} COFIMA BENIN — Document confidentiel.
        </p>
    </div>
</div>
@endsection
