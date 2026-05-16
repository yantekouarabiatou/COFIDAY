@extends('emails.layouts.email')

@section('content')
<div class="card">

    {{-- En-tête --}}
    <div style="background: linear-gradient(135deg, #1e3a5f 0%, #2d5986 100%);
                border-radius: 8px 8px 0 0; padding: 28px 30px; text-align: center;">
        <h2 style="color:#fff; margin:0; font-size:1.3rem; font-weight:700;">
            📄 Certificat de Travail
        </h2>
        <p style="color:#b8d4f0; margin:8px 0 0; font-size:.9rem;">
            Réf. : <strong>{{ $demande->certificat_reference }}</strong>
        </p>
    </div>

    <div style="padding:30px;">

        <p style="font-size:1rem; color:#333; margin-bottom:20px;">
            Bonjour <strong>{{ $employe->prenom }} {{ $employe->nom }}</strong>,
        </p>

        <p style="color:#555; line-height:1.8;">
            Suite à l'approbation de votre démission par la Direction Générale,
            nous vous adressons ci-dessous votre <strong>certificat de travail</strong>.
        </p>

        {{-- ── Certificat de travail ─────────────────────────────────────── --}}
        <div style="background:#f8f9fa; border:1px solid #e0e0e0; border-left:4px solid #1e3a5f;
                    border-radius:6px; padding:28px; margin:24px 0; font-family: Arial, sans-serif;">

            <div style="text-align:center; margin-bottom:20px;">
                <img src="https://cofima.cc/wp-content/uploads/2020/09/logo-cofima-bon.jpg"
                     alt="COFIMA" style="max-width:120px; height:auto;">
            </div>

            <h3 style="text-align:center; text-decoration:underline; font-size:1.1rem;
                       text-transform:uppercase; color:#1e3a5f; margin-bottom:24px; letter-spacing:1px;">
                Certificat de Travail
            </h3>

            <p style="text-align:justify; line-height:2; color:#333;">
                Je soussigné, <strong>AVANDE Jean-Claude Alain</strong>, Expert-Comptable Diplômé inscrit à
                l'Ordre des Experts Comptables et Comptables Agréés du Bénin, Directeur du Cabinet COFIMA
                atteste que
                {{ $employe->sexe === 'F' ? 'Madame' : 'Monsieur' }}
                <strong>{{ strtoupper($employe->nom) }} {{ $employe->prenom }}</strong>
                a été {{ $employe->sexe === 'F' ? 'employée' : 'employé' }}
                en qualité de <strong>{{ $employe->poste->libelle ?? '[Poste]' }}</strong>
                au sein du Cabinet sous un Contrat de travail à Durée Indéterminée
                depuis le <strong>{{ optional($employe->date_embauche)->isoFormat('D MMMM YYYY') ?? '[date d\'embauche]' }}</strong>
                au <strong>{{ $demande->date_depart_effective->isoFormat('D MMMM YYYY') }}</strong>.
            </p>

            <p style="text-align:justify; line-height:2; color:#333;">
                {{ $employe->sexe === 'F' ? 'Madame' : 'Monsieur' }}
                <strong>{{ strtoupper($employe->nom) }} {{ $employe->prenom }}</strong>
                est libre de tout engagement vis-à-vis de COFIMA à compter du
                <strong>{{ $demande->date_depart_effective->addDay()->isoFormat('D MMMM YYYY') }}</strong>.
            </p>

            @if($demande->commentaire_dg)
            <p style="color:#555; font-style:italic; line-height:1.8; margin-top:10px;">
                {{ $demande->commentaire_dg }}
            </p>
            @endif

            <p style="margin-top:24px; color:#333;">
                Fait à Cotonou, le <strong>{{ $demande->date_validation->isoFormat('D MMMM YYYY') }}</strong>
            </p>

            <div style="margin-top:20px;">
                <p style="color:#333; margin-bottom:4px;"><strong>Pour COFIMA,</strong></p>
                <p style="color:#333; margin:0;">
                    <strong>Jean-Claude Alain AVANDE</strong><br>
                    Expert-Comptable Diplômé<br>
                    Associé-Gérant
                </p>
            </div>
        </div>

        {{-- Note --}}
        <div style="background:#fff3cd; border:1px solid #ffc107; border-radius:6px; padding:16px; margin:20px 0;">
            <p style="margin:0; color:#856404; font-size:.9rem;">
                <strong>⚠️ Important :</strong> Ce document ne comporte pas de cachet officiel.
                Merci de vous présenter auprès de la <strong>secrétaire</strong> pour retirer
                la <strong>version originale cachetée et signée</strong>.
            </p>
        </div>

        <p style="color:#888; font-size:.85rem; margin-top:28px; border-top:1px solid #eee; padding-top:16px;">
            Réf. certificat : <strong>{{ $demande->certificat_reference }}</strong><br>
            Généré le : {{ $demande->certificat_genere_le->isoFormat('D MMMM YYYY [à] HH:mm') }}
        </p>

    </div>
</div>
@endsection
