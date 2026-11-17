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
 *  - accrual progressif (2 j./mois ENTIÈREMENT ÉCOULÉ, jamais le mois en
 *    cours tant qu'il n'est pas terminé, jamais tout le quota d'un coup) ;
 *  - cycle d'acquisition ancré sur l'année civile (1er janvier → 31
 *    décembre) pour tous les salariés, quelle que soit leur date d'embauche ;
 *  - majoration ancienneté (Code du travail, art. 158) basée sur l'ancienneté
 *    TOTALE : service dans l'entreprise (date_embauche) + reprise
 *    d'ancienneté d'autres entreprises saisie par les RH.
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

    /** Profil 1 : arrivée le 01/08/2026, aucune ancienneté. Août n'est pas encore terminé → 0 mois acquis. */
    public function test_profil_nouvel_arrivant_sans_anciennete(): void
    {
        $user = new User(['date_embauche' => '2026-08-01']);
        $regles = new RegleConge(['jours_par_mois' => 2]); // 24 j/an

        $calcul = SoldeCongeCalculator::calculerNouveauxDroitsAcquis($user, 2026, $regles);

        $this->assertSame(0, $calcul['annees_anciennete']);
        $this->assertSame(0, $calcul['majoration_anciennete']);
        $this->assertSame(0, $calcul['mois_travailles']); // août en cours, pas encore terminé
        $this->assertEqualsWithDelta(0.0, $calcul['jours_acquis'], 0.01);
        $this->assertFalse($calcul['plafond_applique']);
    }

    /**
     * Profil 2 : embauché le 10/04/2004 → 22 ans d'ancienneté, palier 20-25
     * ans (+2j). Cycle ancré sur l'année civile : présent depuis avant 2026,
     * donc compté de janvier à juillet inclus (7 mois ENTIÈREMENT écoulés au
     * 14/08 — août est en cours, pas encore terminé, donc pas encore compté).
     */
    public function test_profil_22_ans_anciennete(): void
    {
        $user = new User(['date_embauche' => '2004-04-10']);
        $regles = new RegleConge(['jours_par_mois' => 2]); // 24 j/an

        $calcul = SoldeCongeCalculator::calculerNouveauxDroitsAcquis($user, 2026, $regles);

        $this->assertSame(22, $calcul['annees_anciennete']);
        $this->assertSame(2, $calcul['majoration_anciennete']);
        $this->assertSame(7, $calcul['mois_travailles']);
        $this->assertEqualsWithDelta(15.17, $calcul['jours_acquis'], 0.01); // (24+2)/12 × 7
        $this->assertFalse($calcul['plafond_applique']);
    }

    /**
     * Profil 2 bis : embauché depuis seulement 3 ans chez COFIDAY, avec 18 ans
     * d'expérience reconnue ailleurs (reprise saisie par les RH). L'ancienneté
     * TOTALE (maison + reprise = 3 + 18 = 21 ans) compte désormais pour la
     * majoration → palier 20 ans franchi (+2j).
     */
    public function test_profil_reprise_anciennete_comptee_pour_majoration(): void
    {
        $user = new User([
            'date_embauche'           => '2023-06-01', // 3 ans chez COFIDAY
            'anciennete_reprise_ans'  => 18,
            'anciennete_reprise_mois' => 0,
        ]);
        $regles = new RegleConge(['jours_par_mois' => 2]); // 24 j/an

        $calcul = SoldeCongeCalculator::calculerNouveauxDroitsAcquis($user, 2026, $regles);

        $this->assertSame(21, $calcul['annees_anciennete']); // 3 (maison) + 18 (reprise)
        $this->assertSame(2, $calcul['majoration_anciennete']); // palier 20 ans franchi
        $this->assertSame(7, $calcul['mois_travailles']); // embauché avant 2026 → janvier à juillet inclus (août en cours exclu)
        $this->assertEqualsWithDelta(15.17, $calcul['jours_acquis'], 0.01); // (24+2)/12 × 7
    }

    /** Profil 3 : embauché le 01/01/1995 → 31 ans d'ancienneté, palier ≥30 ans (+6j), plafond légal atteint. */
    public function test_profil_31_ans_anciennete_avec_plafond(): void
    {
        $user = new User(['date_embauche' => '1995-01-01']);
        $regles = new RegleConge(['jours_par_mois' => 2.5]); // 30 j/an de base

        $calcul = SoldeCongeCalculator::calculerNouveauxDroitsAcquis($user, 2026, $regles);

        $this->assertSame(31, $calcul['annees_anciennete']);
        $this->assertSame(6, $calcul['majoration_anciennete']);
        $this->assertSame(7, $calcul['mois_travailles']); // embauché avant 2026 → janvier à juillet inclus (août en cours exclu)
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
     * présence au plus tard le 15 du mois = mois compté UNE FOIS TERMINÉ,
     * sinon le mois d'arrivée n'est pas compté (on démarre au mois suivant).
     * "Aujourd'hui" = 14/08/2026 → juillet est terminé, août est en cours.
     */
    public function test_mois_travailles_regle_du_15(): void
    {
        // Embauché le 16/07 (jour > 15) : juillet non compté, on démarre en août,
        // qui n'est pas encore terminé → 0 mois
        $this->assertSame(0, SoldeCongeCalculator::moisTravailles(Carbon::create(2026, 7, 16), 2026));

        // Embauché le 15/07 (limite incluse) : juillet compté (terminé) → 1 mois
        // (août est en cours, pas encore compté)
        $this->assertSame(1, SoldeCongeCalculator::moisTravailles(Carbon::create(2026, 7, 15), 2026));
    }

    /**
     * Cycle ancré sur l'année civile, pas sur le mois d'embauche : pour un
     * salarié déjà présent avant l'année $annee, le compteur repart à 1 en
     * janvier de $annee, quel que soit le mois/jour d'embauche d'origine.
     * Seuls les mois ENTIÈREMENT terminés comptent (août en cours exclu).
     */
    public function test_mois_travailles_cycle_ancre_sur_annee_civile(): void
    {
        // Embauché le 03/11/2025 : en 2026, compté depuis janvier → 7 mois
        // entièrement écoulés au 14/08 (janvier à juillet)
        $this->assertSame(7, SoldeCongeCalculator::moisTravailles(Carbon::create(2025, 11, 3), 2026));

        // Embauché le 20/08/2020 (ancienneté différente) : même résultat, le
        // mois/jour d'embauche n'influence plus le cycle une fois l'année
        // d'embauche passée → 7 mois au 14/08/2026.
        $this->assertSame(7, SoldeCongeCalculator::moisTravailles(Carbon::create(2020, 8, 20), 2026));
    }

    /**
     * Réinitialisation au 1er janvier : une année déjà terminée compte pour
     * 12 mois pleins ; une année future compte pour 0.
     */
    public function test_mois_travailles_annee_terminee_et_annee_future(): void
    {
        // Année 2025 déjà terminée (on est le 14/08/2026) : 12 mois pleins
        $this->assertSame(12, SoldeCongeCalculator::moisTravailles(Carbon::create(2020, 1, 1), 2025));

        // Année 2027 future : 0 mois
        $this->assertSame(0, SoldeCongeCalculator::moisTravailles(Carbon::create(2020, 1, 1), 2027));
    }
}
