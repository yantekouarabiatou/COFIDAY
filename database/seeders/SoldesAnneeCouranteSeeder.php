<?php

namespace Database\Seeders;

use App\Models\RegleConge;
use App\Models\SoldeConge;
use App\Models\User;
use App\Services\SoldeCongeCalculator;
use Illuminate\Database\Seeder;

/**
 * Seeder ADDITIF UNIQUEMENT — sûr à exécuter sur une base déjà en production
 * ("en ligne") : il ne fait jamais d'UPDATE, seulement des INSERT.
 *
 * Crée le solde de l'année en cours (nouvelle logique : majoration
 * ancienneté + prorata mois d'arrivée + plafond légal) uniquement pour les
 * salariés qui n'ont PAS ENCORE de ligne soldes_conges pour cette année.
 *
 * Ne touche JAMAIS une ligne soldes_conges déjà existante, quelle que soit
 * l'année (ni l'historique 2022-2025, ni un solde 2026 déjà créé/validé).
 * Rejouable à volonté : sans effet sur les salariés déjà traités.
 */
class SoldesAnneeCouranteSeeder extends Seeder
{
    public function run(): void
    {
        $annee  = now()->year;
        $regles = RegleConge::first();

        $idsAvecSoldeExistant = SoldeConge::where('annee', $annee)->pluck('user_id');

        $utilisateursSansSolde = User::whereNotIn('id', $idsAvecSoldeExistant)->get();

        if ($utilisateursSansSolde->isEmpty()) {
            $this->command->info("Tous les salariés ont déjà un solde {$annee}. Rien à créer, aucune donnée touchée.");
            return;
        }

        $this->command->info("Création du solde {$annee} pour {$utilisateursSansSolde->count()} salarié(s) sans données existantes à écraser…");

        foreach ($utilisateursSansSolde as $user) {
            $calcul = SoldeCongeCalculator::calculerNouveauxDroitsAcquis($user, $annee, $regles);

            SoldeConge::create([
                'user_id'        => $user->id,
                'annee'          => $annee,
                'jours_acquis'   => $calcul['jours_acquis'],
                'jours_pris'     => 0,
                'jours_restants' => $calcul['jours_acquis'],
                'jours_reportes' => 0,
            ]);

            $this->command->line(
                "  ✔  {$user->email} ({$annee})  {$calcul['jours_acquis']} j. acquis créés "
                . "(ancienneté {$calcul['annees_anciennete']} an(s), +{$calcul['majoration_anciennete']} j. majoration, "
                . "{$calcul['mois_travailles']}/12 mois travaillés)."
            );
        }

        $this->command->info('Terminé. Aucune ligne soldes_conges existante n\'a été modifiée.');
    }
}
