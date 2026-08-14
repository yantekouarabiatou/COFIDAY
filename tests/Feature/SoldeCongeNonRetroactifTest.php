<?php

namespace Tests\Feature;

use App\Models\RegleConge;
use App\Models\SoldeConge;
use App\Models\User;
use App\Services\SoldeCongeCalculator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Livrable 4/5 : vérifie que l'ajout des nouveaux droits de l'année en cours
 * n'altère jamais les soldes déjà acquis/affichés des années antérieures.
 */
class SoldeCongeNonRetroactifTest extends TestCase
{
    use RefreshDatabase;

    public function test_creation_du_solde_annee_courante_ne_modifie_pas_lhistorique(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 14));

        $user = User::factory()->create([
            'poste_id'      => null,
            'date_embauche' => '2004-04-10', // 22 ans d'ancienneté
        ]);

        // (a) Solde déjà acquis/validé des années antérieures, avant toute intervention.
        $soldePasse2024 = SoldeConge::create([
            'user_id'        => $user->id,
            'annee'          => 2024,
            'jours_acquis'   => 24,
            'jours_pris'     => 10,
            'jours_restants' => 14,
            'jours_reportes' => 0,
        ]);
        $soldePasse2025 = SoldeConge::create([
            'user_id'        => $user->id,
            'annee'          => 2025,
            'jours_acquis'   => 24,
            'jours_pris'     => 0,
            'jours_restants' => 24,
            'jours_reportes' => 0,
        ]);

        $avant2024 = $soldePasse2024->only(['jours_acquis', 'jours_pris', 'jours_restants']);
        $avant2025 = $soldePasse2025->only(['jours_acquis', 'jours_pris', 'jours_restants']);

        // (b) Nouveaux jours acquis calculés pour le cycle en cours (2026), avec majoration ancienneté.
        $regles = RegleConge::first() ?? new RegleConge(['jours_par_mois' => 2]);
        $calcul = SoldeCongeCalculator::calculerNouveauxDroitsAcquis($user->fresh(), 2026, $regles);
        $this->assertEqualsWithDelta(26.0, $calcul['jours_acquis'], 0.01); // (24 base + 2 majoration)

        // Point d'insertion réel du delta : création de la ligne 2026 (comme creerSoldeInitial()).
        $soldeCourant = SoldeConge::create([
            'user_id'        => $user->id,
            'annee'          => 2026,
            'jours_acquis'   => $calcul['jours_acquis'],
            'jours_pris'     => 0,
            'jours_restants' => $calcul['jours_acquis'],
            'jours_reportes' => 0,
        ]);

        // (c) Solde disponible après : les lignes 2024/2025 doivent être BYTE-FOR-BYTE identiques à avant.
        $apres2024 = $soldePasse2024->fresh()->only(['jours_acquis', 'jours_pris', 'jours_restants']);
        $apres2025 = $soldePasse2025->fresh()->only(['jours_acquis', 'jours_pris', 'jours_restants']);

        $this->assertSame($avant2024, $apres2024, 'Le solde 2024 déjà acquis a été altéré rétroactivement.');
        $this->assertSame($avant2025, $apres2025, 'Le solde 2025 déjà acquis a été altéré rétroactivement.');

        // Le seul changement est l'ajout de la nouvelle ligne 2026 (le delta).
        $this->assertSame(26.0, (float) $soldeCourant->jours_acquis);
        $this->assertSame(3, SoldeConge::where('user_id', $user->id)->count());
    }
}
