@extends('emails.layouts.email')

@section('title', 'Rappel : validez vos feuilles de temps')

@push('styles')
    <style>
        /* Couleurs spécifiques à cet email */
        :root {
            --theme-color: #0d6efd;      /* Bleu */
            --theme-bg: #e7f1ff;
            --theme-border: #0d6efd;
            --theme-note: #f0f7ff;
        }
        .header {
            background: var(--theme-color);
            color: white;
            padding: 22px;
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            border-radius: 8px 8px 0 0;
            max-width: 600px;
            margin: 0 auto;
        }
        .header .sub {
            font-size: 14px;
            font-weight: normal;
            opacity: 0.9;
            margin-top: 4px;
        }
        .content h2 {
            color: #333;
            margin-top: 0;
        }
        .info-list {
            padding-left: 0;
            margin-top: 15px;
        }
        .info-list li {
            list-style: none;
            background: var(--theme-bg);
            padding: 10px 14px;
            margin-bottom: 8px;
            border-radius: 5px;
            font-size: 14px;
            color: #333;
        }
        .note {
            background: var(--theme-note);
            padding: 14px 16px;
            border-left: 4px solid var(--theme-border);
            border-radius: 5px;
            margin-top: 20px;
            font-size: 14px;
            color: #444;
        }
        .button {
            display: inline-block;
            background-color: var(--theme-color);
            color: white !important;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin-top: 20px;
            font-weight: bold;
        }
        .button:hover {
            background-color: #0b5ed7;
        }
    </style>
@endpush

@section('content')
    {{-- Header --}}
    <div class="header">
        Rappel : validez vos feuilles de temps
        <div class="sub">Mois {{ $mois }} — Clôture imminente</div>
    </div>

    {{-- Contenu --}}
    <div class="card">
        <div class="content">
            <h2>Bonjour {{ $prenom ?? $nom }},</h2>

            <p>Le mois <strong>{{ $mois }}</strong> se termine bientôt.</p>

            <p>
                Pensez à <strong>soumettre et valider toutes vos feuilles de temps</strong>
                pour la période en cours. Une fois la clôture effectuée, vous ne pourrez plus
                apporter de modifications.
            </p>

            {{-- Informations complémentaires (optionnel) --}}
            <ul class="info-list">
                <li>
                    <strong>Période concernée :</strong>
                    {{ $mois }} (du {{ $debut_periode }} au {{ $fin_periode }})
                </li>
                <li>
                    <strong>Statut actuel :</strong>
                    {{ $statut ?? 'En attente de validation' }}
                </li>
            </ul>

            <div class="note">
                ⏰ Dès que vos feuilles sont validées, vous recevrez une confirmation.
            </div>

            {{-- Bouton d'action --}}
            <p style="text-align: center;">
                <a href="{{ $url }}" class="button">Voir mes feuilles de temps</a>
            </p>

            <p style="margin-top:20px;">
                Merci de votre diligence,<br>
                <strong>COFIDAY</strong>
            </p>
        </div>
    </div>
@endsection
