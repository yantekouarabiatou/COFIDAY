<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\DemandeConge;
use App\Models\TypeConge;
use App\Models\SoldeConge;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DemandesCongesSeeder extends Seeder
{
    private array $motifs = [
        'Vacances en famille',
        "Voyage à l'étranger",
        'Raisons personnelles',
        'Événement familial',
        'Repos et détente',
        'Projets personnels',
        'Formation personnelle',
        'Raisons médicales',
        'Déplacement professionnel',
        'Célébration spéciale',
    ];

    private array $durees = [
        'Congés payés'      => [1, 15],
        'Congés sans solde' => [1, 30],
        'Congés maladie'    => [1, 10],
        'Congés maternité'  => [90, 98],
        'Congés paternité'  => [11, 25],
    ];

    // Workflow : en_attente → pre_approuve → approuve|refuse
    //            en_attente → refuse (niveau 1, direct)
    //            annule (par l'employé)
    private array $probabilites = [
        'en_attente'   => 0.15,
        'pre_approuve' => 0.15,
        'approuve'     => 0.45,
        'refuse'       => 0.15,
        'annule'       => 0.10,
    ];

    public function run(): void
    {
        if (app()->environment('local')) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('historiques_conges')->truncate();
            DB::table('demandes_conges')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            // Remettre les soldes à zéro : on les reconstruit depuis les demandes
            DB::table('soldes_conges')->update([
                'jours_pris'     => 0,
                'jours_restants' => DB::raw('jours_acquis'),
                'updated_at'     => now(),
            ]);
        }

        $users       = User::all();
        $typesConges = TypeConge::all();

        // Niveau 1 : managers
        $managers = User::role('manager')->get();
        if ($managers->isEmpty()) {
            $managers = User::limit(2)->get();
        }

        // Niveau 2 : DG, RH ou admin (validation finale)
        $validateursFinals = User::role(['directeur-general', 'rh', 'admin'])->get();
        if ($validateursFinals->isEmpty()) {
            $validateursFinals = User::limit(1)->get();
        }

        $totalDemandes = 0;

        foreach ($users as $user) {
            $nombreDemandes = rand(1, 5);
            for ($i = 0; $i < $nombreDemandes; $i++) {
                if ($this->creerDemande($user, $typesConges, $managers, $validateursFinals)) {
                    $totalDemandes++;
                }
            }
        }

        $this->command->info("✅ {$totalDemandes} demandes de congés créées avec succès !");
    }

    private function creerDemande(User $user, $typesConges, $managers, $validateursFinals): bool
    {
        $typeConge   = $typesConges->random();
        $statut      = $this->getStatutAleatoire();
        $annee       = $this->choisirAnnee($user);
        $dateDebut   = $this->genererDateAleatoire($annee);
        $dateFin     = $this->genererDateFin($dateDebut, $typeConge);
        $nombreJours = $this->calculerJoursOuvres($dateDebut, $dateFin);

        if ($nombreJours <= 0) {
            return false;
        }

        $manager         = $managers->random();
        $validateurFinal = $validateursFinals->random();
        $createdAt       = $dateDebut->copy()->subDays(rand(5, 30));

        // Champs communs à toutes les demandes
        $data = [
            'user_id'                   => $user->id,
            'type_conge_id'             => $typeConge->id,
            'superieur_hierarchique_id' => $manager->id,
            'date_debut'                => $dateDebut,
            'date_fin'                  => $dateFin,
            'nombre_jours'              => $nombreJours,
            'motif'                     => $this->motifs[array_rand($this->motifs)],
            'statut'                    => $statut,
            'meta_deductions'           => null,
            // Niveau 1
            'valide_par'                => null,
            'date_validation'           => null,
            // Niveau 2
            'valide_par_final'          => null,
            'date_validation_finale'    => null,
            'statut_final'              => null,
            'commentaire_final'         => null,
            'created_at'                => $createdAt,
            'updated_at'                => now(),
        ];

        // Variables pour l'historique
        $datePreApprobation  = null;
        $dateValidationFinal = null;
        $preApprouvePar      = null;
        $validateurFinalId   = null;
        $refusNiveau         = null;
        $deductions          = [];

        switch ($statut) {

            // Soumise, manager pas encore intervenu
            case 'en_attente':
                break;

            // Manager a approuvé, attend DG/RH
            case 'pre_approuve':
                $datePreApprobation      = $createdAt->copy()->addDays(rand(1, 3));
                $preApprouvePar          = $manager->id;
                $data['valide_par']      = $manager->id;
                $data['date_validation'] = $datePreApprobation;
                break;

            // Pré-approbation manager + validation finale DG/RH
            // FIFO : la déduction n'a lieu qu'ici, comme dans validerFinale()
            case 'approuve':
                $datePreApprobation             = $createdAt->copy()->addDays(rand(1, 3));
                $dateValidationFinal            = $datePreApprobation->copy()->addDays(rand(1, 3));
                $preApprouvePar                 = $manager->id;
                $validateurFinalId              = $validateurFinal->id;
                $data['valide_par']             = $manager->id;
                $data['date_validation']        = $datePreApprobation;
                $data['valide_par_final']       = $validateurFinal->id;
                $data['date_validation_finale'] = $dateValidationFinal;
                $data['statut_final']           = 'approuve';

                $estAnnuel = $typeConge->est_annuel ?? $typeConge->est_paye;
                if ($estAnnuel) {
                    $result = $this->deduireSoldesFIFO($user->id, $nombreJours);
                    if ($result === null) {
                        // Solde insuffisant : basculer sur congé non payé
                        $nonPaye = TypeConge::where('est_paye', false)->first();
                        if (!$nonPaye) return false;
                        $data['type_conge_id'] = $nonPaye->id;
                    } else {
                        $deductions              = $result;
                        $data['meta_deductions'] = !empty($deductions) ? json_encode($deductions) : null;
                    }
                }
                break;

            // Refus : niveau 1 (manager) OU niveau 2 (DG/RH) après pré-approbation
            // Pas de déduction (les jours n'ont jamais été prélevés)
            case 'refuse':
                if (rand(0, 1)) {
                    // Refus direct niveau 1
                    $refusNiveau             = 'manager';
                    $datePreApprobation      = $createdAt->copy()->addDays(rand(1, 3));
                    $data['valide_par']      = $manager->id;
                    $data['date_validation'] = $datePreApprobation;
                } else {
                    // Pré-approbation puis refus niveau 2
                    $refusNiveau                    = 'final';
                    $datePreApprobation             = $createdAt->copy()->addDays(rand(1, 3));
                    $dateValidationFinal            = $datePreApprobation->copy()->addDays(rand(1, 3));
                    $preApprouvePar                 = $manager->id;
                    $validateurFinalId              = $validateurFinal->id;
                    $data['valide_par']             = $manager->id;
                    $data['date_validation']        = $datePreApprobation;
                    $data['valide_par_final']       = $validateurFinal->id;
                    $data['date_validation_finale'] = $dateValidationFinal;
                    $data['statut_final']           = 'refuse';
                }
                break;

            // Annulée par l'employé, pas de déduction
            case 'annule':
                break;
        }

        $demande = DemandeConge::create($data);

        $this->creerHistoriqueDemande(
            $demande, $createdAt,
            $preApprouvePar, $datePreApprobation,
            $validateurFinalId, $dateValidationFinal,
            $refusNiveau, $deductions
        );

        return true;
    }

    // FIFO : déduction uniquement à la validation finale (approuve)
    private function deduireSoldesFIFO(int $userId, float $joursADeduire): ?array
    {
        $soldes = DB::table('soldes_conges')
            ->where('user_id', $userId)
            ->where('jours_restants', '>', 0)
            ->orderBy('annee', 'asc')
            ->get();

        if ($soldes->sum('jours_restants') < $joursADeduire) {
            return null;
        }

        $deductions    = [];
        $resteADeduire = $joursADeduire;

        foreach ($soldes as $solde) {
            if ($resteADeduire <= 0) break;

            $pris = min($solde->jours_restants, $resteADeduire);

            DB::table('soldes_conges')
                ->where('user_id', $userId)
                ->where('annee', $solde->annee)
                ->update([
                    'jours_pris'     => $solde->jours_pris + $pris,
                    'jours_restants' => $solde->jours_restants - $pris,
                    'updated_at'     => now(),
                ]);

            $deductions[]  = ['annee' => $solde->annee, 'jours_pris' => $pris];
            $resteADeduire -= $pris;
        }

        return $deductions;
    }

    // Historique qui reflète exactement les actions du contrôleur
    private function creerHistoriqueDemande(
        DemandeConge $demande,
        Carbon $createdAt,
        ?int $preApprouvePar,
        ?Carbon $datePreApprobation,
        ?int $validateurFinalId,
        ?Carbon $dateValidationFinal,
        ?string $refusNiveau,
        array $deductions
    ): void {
        $historiques = [];

        // 1. Soumission (toujours présente)
        $historiques[] = [
            'demande_conge_id' => $demande->id,
            'action'           => 'demande_soumise',
            'effectue_par'     => $demande->user_id,
            'commentaire'      => 'Demande initiale soumise.',
            'created_at'       => $createdAt,
            'updated_at'       => $createdAt,
        ];

        // 2. Annulation employé
        if ($demande->statut === 'annule') {
            $annulAt = $createdAt->copy()->addDays(rand(1, 4));
            $historiques[] = [
                'demande_conge_id' => $demande->id,
                'action'           => 'demande_annulee',
                'effectue_par'     => $demande->user_id,
                'commentaire'      => "Demande annulée par l'employé.",
                'created_at'       => $annulAt,
                'updated_at'       => $annulAt,
            ];
            DB::table('historiques_conges')->insert($historiques);
            return;
        }

        // 3. Refus niveau 1 (manager, direct, sans pré-approbation)
        if ($demande->statut === 'refuse' && $refusNiveau === 'manager') {
            $historiques[] = [
                'demande_conge_id' => $demande->id,
                'action'           => 'demande_refusee',
                'effectue_par'     => $demande->valide_par,
                'commentaire'      => 'Refusée par le supérieur hiérarchique.',
                'created_at'       => $datePreApprobation,
                'updated_at'       => $datePreApprobation,
            ];
            DB::table('historiques_conges')->insert($historiques);
            return;
        }

        // 4. Pré-approbation niveau 1 (manager)
        //    Présente pour : pre_approuve, approuve, refuse(niveau 2)
        if ($preApprouvePar && $datePreApprobation) {
            $historiques[] = [
                'demande_conge_id' => $demande->id,
                'action'           => 'demande_pre_approuvee',
                'effectue_par'     => $preApprouvePar,
                'commentaire'      => "Pré-approuvée par le manager. En attente de validation finale (DG/RH).",
                'created_at'       => $datePreApprobation,
                'updated_at'       => $datePreApprobation,
            ];
        }

        // 5. Validation finale niveau 2 (DG / RH / admin)
        if ($validateurFinalId && $dateValidationFinal) {

            if ($demande->statut === 'approuve') {
                $commentaire = 'Approuvée définitivement.';
                if (!empty($deductions)) {
                    $detail      = collect($deductions)
                        ->map(fn($d) => "{$d['jours_pris']} j. sur {$d['annee']}")
                        ->implode(', ');
                    $commentaire .= " Déduction FIFO : {$detail}.";
                }
                $historiques[] = [
                    'demande_conge_id' => $demande->id,
                    'action'           => 'demande_approuvee_finale',
                    'effectue_par'     => $validateurFinalId,
                    'commentaire'      => $commentaire,
                    'created_at'       => $dateValidationFinal,
                    'updated_at'       => $dateValidationFinal,
                ];

            } elseif ($demande->statut === 'refuse' && $refusNiveau === 'final') {
                $historiques[] = [
                    'demande_conge_id' => $demande->id,
                    'action'           => 'demande_refusee_finale',
                    'effectue_par'     => $validateurFinalId,
                    'commentaire'      => 'Refusée par le DG/RH après pré-approbation du manager.',
                    'created_at'       => $dateValidationFinal,
                    'updated_at'       => $dateValidationFinal,
                ];
            }
        }

        DB::table('historiques_conges')->insert($historiques);
    }

    // Choisit une année avec solde > 0, favorise les plus anciennes (FIFO)
    private function choisirAnnee(User $user): int
    {
        $annees = DB::table('soldes_conges')
            ->where('user_id', $user->id)
            ->where('jours_restants', '>', 0)
            ->orderBy('annee', 'asc')
            ->pluck('annee')
            ->toArray();

        if (empty($annees)) return now()->year;

        if (count($annees) > 1 && rand(1, 10) <= 7) {
            $anciens = array_slice($annees, 0, count($annees) - 1);
            return $anciens[array_rand($anciens)];
        }

        return $annees[array_rand($annees)];
    }

    private function genererDateAleatoire(int $annee): Carbon
    {
        $moisMax = $annee < now()->year ? 12 : min(now()->month, 11);
        $date    = Carbon::create($annee, rand(1, max(1, $moisMax)), rand(1, 28));
        if ($date->isWeekend()) $date->next(Carbon::MONDAY);
        return $date;
    }

    private function genererDateFin(Carbon $dateDebut, TypeConge $typeConge): Carbon
    {
        $range = $this->durees[$typeConge->libelle] ?? [1, 10];
        $minJ  = $range[0];
        $maxJ  = ($typeConge->nombre_jours_max && $typeConge->nombre_jours_max < $range[1])
            ? $typeConge->nombre_jours_max
            : $range[1];
        return $dateDebut->copy()->addDays(rand($minJ, max($minJ, $maxJ)));
    }

    private function calculerJoursOuvres(Carbon $debut, Carbon $fin): float
    {
        $jours = 0;
        $c     = $debut->copy();
        while ($c->lte($fin)) {
            if (!$c->isWeekend()) $jours++;
            $c->addDay();
        }
        return $jours;
    }

    private function getStatutAleatoire(): string
    {
        $rand = mt_rand() / mt_getrandmax();
        $cum  = 0;
        foreach ($this->probabilites as $statut => $prob) {
            $cum += $prob;
            if ($rand <= $cum) return $statut;
        }
        return 'en_attente';
    }
}
