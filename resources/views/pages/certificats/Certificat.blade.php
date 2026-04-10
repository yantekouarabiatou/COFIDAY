@extends('emails.layout')

@section('content')
<div class="card">

    {{-- ── Header ─────────────────────────────────────────────────────────── --}}
    <div style="background: linear-gradient(135deg, #1e3a5f 0%, #2d5986 100%);
                border-radius: 8px 8px 0 0; padding: 28px 30px; text-align: center;">
        <h2 style="color:#fff; margin:0; font-size:1.3rem; font-weight:700; letter-spacing:.5px;">
            Certificat de travail
        </h2>
        <p style="color:#b8d4f0; margin:8px 0 0; font-size:.9rem;">
            Réf. : <strong>{{ $demission->numero_certificat }}</strong>
        </p>
    </div>

    <div style="padding: 30px;">

        <p style="font-size:1rem; color:#333; margin-bottom:20px;">
            Bonjour <strong>{{ $employe->prenom }} {{ $employe->nom }}</strong>,
        </p>

        <p style="color:#555; line-height:1.8;">
            La Direction Générale du Cabinet COFIMA a <span style="color:#28a745; font-weight:700;">accepté</span>
            votre lettre de démission. Votre date de départ est fixée au
            <strong>{{ $dateDepartConfirmee->isoFormat('D MMMM YYYY') }}</strong>.
        </p>

        @if($demission->commentaire_dg)
        <div style="background:#e8f4f8; border-radius:6px; padding:14px; margin-bottom:20px;">
            <p style="margin:0; color:#1e3a5f; font-size:.9rem;">
                <strong>💬 Message de la Direction :</strong><br>{{ $demission->commentaire_dg }}
            </p>
        </div>
        @endif

        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- CERTIFICAT DE TRAVAIL                                           --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}
        <div style="border: 2px solid #1e3a5f; border-radius: 8px; padding: 32px; margin: 24px 0;
                    background: #ffffff; font-family: Arial, Helvetica, sans-serif;">

            {{-- Logo --}}
            <div style="text-align:center; margin-bottom:24px;">
                <img src="https://cofima.cc/wp-content/uploads/2020/09/logo-cofima-bon.jpg"
                     alt="COFIMA" style="max-width:130px; height:auto;">
            </div>

            {{-- Titre --}}
            <h3 style="text-align:center; text-decoration:underline; text-transform:uppercase;
                        font-size:1.1rem; color:#1e3a5f; letter-spacing:2px; margin-bottom:28px;">
                Certificat de Travail
            </h3>

            {{-- Corps --}}
            <p style="text-align:justify; line-height:2; color:#222; font-size:.95rem; margin-bottom:16px;">
                Je soussigné, <strong>AVANDE Jean-Claude</strong>, Expert-Comptable Diplômé inscrit à
                l'Ordre des Experts Comptables et Comptables Agréés du Bénin, Directeur du Cabinet COFIMA
                atteste que
                @if(strtolower($employe->sexe ?? '') === 'f')
                    <strong>Madame {{ strtoupper($employe->nom) }} {{ $employe->prenom }}</strong>
                    a été employée
                @else
                    <strong>Monsieur {{ strtoupper($employe->nom) }} {{ $employe->prenom }}</strong>
                    a été employé
                @endif
                en qualité de <strong>{{ $employe->poste->libelle ?? '[Poste]' }}</strong>
                au sein du Cabinet sous un Contrat de travail à Durée Indéterminée depuis le
                <strong>{{ optional($employe->date_embauche)->isoFormat('D MMMM YYYY') ?? '[date d\'embauche]' }}</strong>
                au <strong> {{ $dateDepartConfirmee->isoFormat('D MMMM YYYY') }} </strong> .
            </p>

            <p style="text-align:justify; line-height:2; color:#222; font-size:.95rem; margin-bottom:24px;">
                {{ strtolower($employe->sexe ?? '') === 'f' ? 'Madame' : 'Monsieur' }}
                <strong>{{ strtoupper($employe->nom) }} {{ $employe->prenom }}</strong>
                est libre de tout engagement vis-à-vis de COFIMA à compter du
                <strong>{{ $dateDepartConfirmee->addDay()->isoFormat('D MMMM YYYY') }}</strong>.
            </p>

            <p style="color:#222; font-size:.95rem; margin-bottom:6px;">
                Fait à Cotonou, le <strong>{{ $demission->date_validation->isoFormat('D MMMM YYYY') }}</strong>
            </p>

            <div style="margin-top:24px;">
                <p style="color:#222; margin-bottom:4px;"><strong>Pour COFIMA,</strong></p>
                <p style="color:#222; line-height:1.8; margin:0;">
                    <strong>Jean-Claude AVANDE</strong><br>
                    Expert-Comptable Diplômé<br>
                    Associé-Gérant
                </p>
            </div>

            {{-- Référence --}}
            <p style="margin-top:24px; font-size:.8rem; color:#999; border-top:1px solid #eee; padding-top:10px;">
                Réf. : {{ $demission->numero_certificat }}
            </p>
        </div>
        {{-- ════════════════════════════════════════════════════════════════ --}}

        <p style="color:#888; font-size:.82rem; margin-top:24px; border-top:1px solid #eee; padding-top:14px;">
            © {{ date('Y') }} COFIMA BENIN — Ce document est confidentiel.
        </p>

    </div>
</div>
@endsection
