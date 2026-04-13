{{-- resources/views/emails/attestation/refusee.blade.php --}}
@extends('emails.layouts.email')
@section('content')
<div class="card">
    <div style="background:linear-gradient(135deg,#c0392b 0%,#e74c3c 100%);border-radius:8px 8px 0 0;padding:24px 30px;text-align:center;">
        <h2 style="color:#fff;margin:0;font-size:1.2rem;font-weight:700;">❌ Demande non aboutie</h2>
    </div>
    <div style="padding:28px;">
        <p style="color:#333;">Bonjour <strong>{{ $employe->prenom }} {{ $employe->nom }}</strong>,</p>
        <p style="color:#555;line-height:1.8;">
            Nous vous informons que votre demande de <strong>{{ $demande->libelleType }}</strong>,
            enregistrée le <strong>{{ $demande->created_at->isoFormat('D MMMM YYYY') }}</strong>,
            n'a pas pu être traitée favorablement.
        </p>
        @if($motifRefus)
        <div style="background:#fdf2f2;border:1px solid #f5c6cb;border-left:4px solid #dc3545;border-radius:6px;padding:16px;margin:20px 0;">
            <p style="margin:0;color:#721c24;"><strong>Motif :</strong> {{ $motifRefus }}</p>
        </div>
        @endif
        <p style="color:#555;line-height:1.8;">N'hésitez pas à contacter les Ressources Humaines pour plus d'informations.</p>
    </div>
</div>
@endsection
