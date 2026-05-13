@extends('emails.layouts.email')

@section('content')
<div class="card">

    {{-- Header --}}
    <div style="background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%);
                border-radius: 8px 8px 0 0; padding: 28px 30px; text-align: center;">
        <h2 style="color:#fff; margin:0; font-size:1.2rem; font-weight:700;">
            Votre demande d'attestation n'a pas abouti
        </h2>
        <p style="color:#f9b4b4; margin:8px 0 0; font-size:.9rem;">
            Décision de la Direction Générale
        </p>
    </div>

    <div style="padding: 30px;">

        <p style="font-size:1rem; color:#333; margin-bottom:20px;">
            Bonjour <strong>{{ $employe->prenom }} {{ $employe->nom }}</strong>,
        </p>

        <p style="color:#555; line-height:1.8;">
            Nous vous informons que votre demande de
            <strong>{{ $demande->libelle_type }}</strong>,
            soumise le <strong>{{ $demande->created_at->isoFormat('D MMMM YYYY') }}</strong>,
            n'a pas pu être traitée favorablement par la Direction Générale.
        </p>

        @if($motifRefus)
        <div style="background:#fdf2f2; border:1px solid #f5c6cb;
                    border-left:4px solid #dc3545; border-radius:6px;
                    padding:16px; margin:20px 0;">
            <p style="margin:0; color:#721c24;">
                <strong>Motif du refus :</strong> {{ $motifRefus }}
            </p>
        </div>
        @endif

        <p style="color:#555; line-height:1.8;">
            Pour toute question ou pour soumettre une nouvelle demande, veuillez
            contacter le service des Ressources Humaines.
        </p>

        <div style="background:#f8f9fa; border-radius:6px; padding:16px; margin-top:20px;
                    border:1px solid #dee2e6; font-size:.88rem; color:#666;">
            <strong>Référence de la demande :</strong> #{{ str_pad($demande->id, 5, '0', STR_PAD_LEFT) }}<br>
            <strong>Type :</strong> {{ $demande->libelle_type }}
        </div>

    </div>
</div>
@endsection
