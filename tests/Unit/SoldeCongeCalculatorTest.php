<?php

namespace Tests\Unit;

use App\Models\RegleConge;
use App\Models\User;
use App\Services\SoldeCongeCalculator;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * Exemples chiffrés (livrable 4) sur 3 profils types :
 *  - arrivée en cours d'année, sans ancienneté
 *  - 22 ans d'ancienneté, présent toute l'année
 *  - 31 ans d'ancienneté, avec plafond légal de 30 j. atteint
 *
 * "Aujourd'hui" est figé au 14/08/2026 pour des résultats déterministes.
 */
class SoldeCongeCalculatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2026, 8, 14));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /** Profil 1 : arrivée le 01/08/2026 (jour <= 15 → mois d'août compté), aucune ancienneté. */
    public function test_profil_nouvel_arrivant_sans_anciennete(): void
    {
        $user = new User(['date_embauche' => '2026-08-01']);
        $regles = new RegleConge(['jours_par_mois' => 2]); // 24 j/an

        $calcul = SoldeCongeCalculator::calculerNouveauxDroitsAcquis($user, 2026, $regles);

        $this->assertSame(0, $calcul['annees_anciennete']);
        $this->assertSame(0, $calcul['majoration_anciennete']);
        $this->assertSame(5, $calcul['mois_travailles']); // août à décembre
        $this->assertEqualsWithDelta(10.0, $calcul['jours_acquis'], 0.01); // 24/12 × 5
        $this->assertFalse($calcul['plafond_applique']);
    }

    /** Profil 2 : embauché le 10/04/2004 → 22 ans (et 4 mois) d'ancienneté, palier 20-25 ans (+2j). */
    public function test_profil_22_ans_anciennete(): void
    {
        $user = new User(['date_embauche' => '2004-04-10']);
        $regles = new RegleConge(['jours_par_mois' => 2]); // 24 j/an

        $calcul = SoldeCongeCalculator::calculerNouveauxDroitsAcquis($user, 2026, $regles);

        $this->assertSame(22, $calcul['annees_anciennete']);
        $this->assertSame(2, $calcul['majoration_anciennete']);
        $this->assertSame(12, $calcul['mois_travailles']); // déjà présent toute l'année
        $this->assertEqualsWithDelta(26.0, $calcul['jours_acquis'], 0.01); // (24+2)/12 × 12
        $this->assertFalse($calcul['plafond_applique']);
    }

    /**
     * Profil 2 bis : embauché depuis seulement 3 ans chez COFIDAY, mais avec
     * 18 ans d'expérience reconnue ailleurs (reprise saisie par les RH) →
     * ancienneté totale 21 ans, franchit le palier 20-25 ans (+2j), alors que
     * l'ancienneté "maison" seule (3 ans) n'aurait donné aucune majoration.
     */
    public function test_profil_reprise_anciennete_autres_entreprises(): void
    {
        $user = new User([
            'date_embauche'           => '2023-06-01', // 3 ans chez COFIDAY
            'anciennete_reprise_ans'  => 18,
            'anciennete_reprise_mois' => 0,
        ]);
        $regles = new RegleConge(['jours_par_mois' => 2]); // 24 j/an

        $calcul = SoldeCongeCalculator::calculerNouveauxDroitsAcquis($user, 2026, $regles);

        $this->assertSame(21, $calcul['annees_anciennete']);
        $this->assertSame(2, $calcul['majoration_anciennete']);
        $this->assertEqualsWithDelta(26.0, $calcul['jours_acquis'], 0.01); // (24+2)/12 × 12
    }

    /** Profil 3 : embauché le 01/01/1995 → 31 ans d'ancienneté, palier ≥30 ans (+6j), plafond légal atteint. */
    public function test_profil_31_ans_anciennete_avec_plafond(): void
    {
        $user = new User(['date_embauche' => '1995-01-01']);
        $regles = new RegleConge(['jours_par_mois' => 2.5]); // 30 j/an de base

        $calcul = SoldeCongeCalculator::calculerNouveauxDroitsAcquis($user, 2026, $regles);

        $this->assertSame(31, $calcul['annees_anciennete']);
        $this->assertSame(6, $calcul['majoration_anciennete']);
        $this->assertSame(12, $calcul['mois_travailles']);
        // 30 (base) + 6 (majoration) = 36 → plafonné à 30 j. ouvrables / 12 mois
        $this->assertEqualsWithDelta(30.0, $calcul['jours_acquis'], 0.01);
        $this->assertTrue($calcul['plafond_applique']);
    }

    /** Règle "palier unique" (défaut) : seul le palier le plus élevé atteint s'applique, jamais cumulé. */
    public function test_majoration_palier_unique_non_cumulatif(): void
    {
        $this->assertSame(0, SoldeCongeCalculator::majorationAnciennete(19));
        $this->assertSame(2, SoldeCongeCalculator::majorationAnciennete(20));
        $this->assertSame(2, SoldeCongeCalculator::majorationAnciennete(24));
        $this->assertSame(4, SoldeCongeCalculator::majorationAnciennete(25));
        $this->assertSame(4, SoldeCongeCalculator::majorationAnciennete(29));
        $this->assertSame(6, SoldeCongeCalculator::majorationAnciennete(30));
        $this->assertSame(6, SoldeCongeCalculator::majorationAnciennete(40));
    }

    /** Règle du 15 du mois : embauche après le 15 → le mois d'entrée n'est pas compté. */
    public function test_mois_travailles_regle_du_15(): void
    {
        // Embauché le 16/08 : août non compté, on démarre en septembre → 4 mois (sept-déc)
        $this->assertSame(4, SoldeCongeCalculator::moisTravailles(Carbon::create(2026, 8, 16), 2026));

        // Embauché le 15/08 (limite incluse) : août compté entier → 5 mois (août-déc)
        $this->assertSame(5, SoldeCongeCalculator::moisTravailles(Carbon::create(2026, 8, 15), 2026));
    }
}
