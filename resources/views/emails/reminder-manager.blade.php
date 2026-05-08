<!DOCTYPE html>
<html>
<head>
    <title>Rappel demandes en attente</title>
</head>
<body>
    <h2>Bonjour {{ $manager->prenom }} {{ $manager->nom }},</h2>
    <p>Vous avez <strong>{{ $demandes->count() }} demande(s) de congé</strong> en attente de votre pré‑approbation.</p>

    <ul>
        @foreach($demandes as $demande)
            <li>
                <strong>{{ $demande->user->prenom }} {{ $demande->user->nom }}</strong> –
                {{ $demande->typeConge->libelle ?? 'Congé' }} –
                du {{ $demande->date_debut }} au {{ $demande->date_fin }}
                ({{ $demande->nombre_jours }} jours)
            </li>
        @endforeach
    </ul>

    <p>
        <a href="{{ route('conges.index', ['statut' => 'en_attente']) }}">
            ➡️ Accéder à la liste des demandes
        </a>
    </p>

    <p>Merci de traiter ces demandes dans les meilleurs délais.</p>
</body>
</html>