<?php

namespace Tests\Unit;

use App\Models\RegleConge;
use App\Models\User;
use App\Services\SoldeCongeCalculator;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * Exemples chiffrés sur plusieurs profils types. "Aujourd'hui" est figé au
 * 14/08/2026 pour des résultats déterministes.
 *
 * Règles couvertes :
 *  - accrual progressif (2 j./mois écoulé, jamais tout le quota d'un coup) ;
 *  - cycle d'acquisition ancré sur le mois d'embauche (anniversaire), pas
 *    sur l'année civile ;
 *  - majoration ancienneté (Code du travail, art. 158) basée UNIQUEMENT sur
 *    le service dans l'entreprise (date_embauche), jamais sur la reprise
 *    d'ancienneté d'autres entreprises.
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
        $this->assertSame(1, $calcul['mois_travailles']); // août seul, écoulé jusqu'à aujourd'hui (14/08)
        $this->assertEqualsWithDelta(2.0, $calcul['jours_acquis'], 0.01); // 24/12 × 1
        $this->assertFalse($calcul['plafond_applique']);
    }

    /**
     * Profil 2 : embauché le 10/04/2004 → 22 ans d'ancienneté, palier 20-25
     * ans (+2j). Cycle en cours démarré le 10/04/2026 (dernier anniversaire),
     * 4 mois écoulés depuis (avril→août inclus, jusqu'au 14/08).
     */
    public function test_profil_22_ans_anciennete(): void
    {
        $user = new User(['date_embauche' => '2004-04-10']);
        $regles = new RegleConge(['jours_par_mois' => 2]); // 24 j/an

        $calcul = SoldeCongeCalculator::calculerNouveauxDroitsAcquis($user, 2026, $regles);

        $this->assertSame(22, $calcul['annees_anciennete']);
        $this->assertSame(2, $calcul['majoration_anciennete']);
        $this->assertSame(4, $calcul['mois_travailles']);
        $this->assertEqualsWithDelta(8.67, $calcul['jours_acquis'], 0.01); // (24+2)/12 × 4
        $this->assertFalse($calcul['plafond_applique']);
    }

    /**
     * Profil 2 bis : embauché depuis seulement 3 ans chez COFIDAY, avec 18 ans
     * d'expérience reconnue ailleurs (reprise saisie par les RH). Code du
     * travail art. 158 : la majoration porte sur le service "dans la même
     * entreprise" → la reprise n'entre PAS dans le calcul de la majoration,
     * seule l'ancienneté "maison" (3 ans) compte, donc aucune majoration ici.
     */
    public function test_profil_reprise_anciennete_non_prise_en_compte_pour_majoration(): void
    {
        $user = new User([
            'date_embauche'           => '2023-06-01', // 3 ans chez COFIDAY
            'anciennete_reprise_ans'  => 18,
            'anciennete_reprise_mois' => 0,
        ]);
        $regles = new RegleConge(['jours_par_mois' => 2]); // 24 j/an

        $calcul = SoldeCongeCalculator::calculerNouveauxDroitsAcquis($user, 2026, $regles);

        $this->assertSame(3, $calcul['annees_anciennete']);
        $this->assertSame(0, $calcul['majoration_anciennete']);
        $this->assertSame(2, $calcul['mois_travailles']); // cycle démarré le 01/06/2026, 2 mois écoulés
        $this->assertEqualsWithDelta(4.0, $calcul['jours_acquis'], 0.01); // 24/12 × 2, pas de majoration
    }

    /** Profil 3 : embauché le 01/01/1995 → 31 ans d'ancienneté, palier ≥30 ans (+6j), plafond légal atteint. */
    public function test_profil_31_ans_anciennete_avec_plafond(): void
    {
        $user = new User(['date_embauche' => '1995-01-01']);
        $regles = new RegleConge(['jours_par_mois' => 2.5]); // 30 j/an de base

        $calcul = SoldeCongeCalculator::calculerNouveauxDroitsAcquis($user, 2026, $regles);

        $this->assertSame(31, $calcul['annees_anciennete']);
        $this->assertSame(6, $calcul['majoration_anciennete']);
        $this->assertSame(7, $calcul['mois_travailles']); // cycle démarré le 01/01/2026, 7 mois écoulés
        // 30 (base) + 6 (majoration) = 36 → plafonné à 30 j. ouvrables / 12 mois
        $this->assertEqualsWithDelta(17.5, $calcul['jours_acquis'], 0.01); // 30/12 × 7
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

    /**
     * Règle du 15 du mois pour une embauche dans l'année en cours :
     * présence au plus tard le 15 du mois = mois compté entier, sinon le
     * mois d'arrivée n'est pas compté (on démarre au mois suivant).
     */
    public function test_mois_travailles_regle_du_15(): void
    {
        // Embauché le 16/07 (jour > 15) : juillet non compté, on démarre en août → 1 mois (août)
        $this->assertSame(1, SoldeCongeCalculator::moisTravailles(Carbon::create(2026, 7, 16), 2026));

        // Embauché le 15/07 (limite incluse) : juillet compté entier → 2 mois (juillet-août)
        $this->assertSame(2, SoldeCongeCalculator::moisTravailles(Carbon::create(2026, 7, 15), 2026));
    }

    /**
     * Cycle ancré sur le mois d'embauche pour une embauche d'une année
     * antérieure : le compteur ne repart PAS au 1er janvier, il repart à
     * chaque anniversaire d'embauche (modulo 12 mois écoulés).
     */
    public function test_mois_travailles_cycle_ancre_sur_mois_embauche(): void
    {
        // Embauché le 03/11/2025 : au 14/08/2026, 9 mois pleins écoulés (nov→août)
        $this->assertSame(9, SoldeCongeCalculator::moisTravailles(Carbon::create(2025, 11, 3), 2026));

        // Embauché le 20/08/2020 : cycle actuel démarré le 20/08/2025, mais le
        // 14/08/2026 est avant l'anniversaire du 20/08 → dernier mois plein
        // pas encore atteint dans le cycle en cours (11 mois pleins écoulés).
        $this->assertSame(11, SoldeCongeCalculator::moisTravailles(Carbon::create(2020, 8, 20), 2026));
    }
}
