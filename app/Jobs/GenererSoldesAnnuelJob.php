<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\SoldeConge;
use App\Models\RegleConge;
use App\Services\SoldeCongeCalculator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenererSoldesAnnuelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $annee;

    public function __construct($annee = null)
    {
        $this->annee = $annee ?? now()->year;
    }

    public function handle(): void
    {
        $regles = RegleConge::first();
        $users  = User::all();

        foreach ($users as $user) {
            // Vérifier si un solde existe déjà pour cette année
            $soldeExistant = SoldeConge::where('user_id', $user->id)
                ->where('annee', $this->annee)
                ->exists();

            if (!$soldeExistant) {
                // Nouveaux droits du cycle en cours (base + majoration ancienneté
                // + prorata mois d'arrivée + plafond légal). N'a aucun effet sur
                // les lignes soldes_conges déjà existantes des années antérieures.
                $calcul      = SoldeCongeCalculator::calculerNouveauxDroitsAcquis($user, $this->annee, $regles);
                $joursAcquis = $calcul['jours_acquis'];

                SoldeConge::create([
                    'user_id' => $user->id,
                    'annee' => $this->annee,
                    'jours_acquis' => $joursAcquis,
                    'jours_pris' => 0,
                    'jours_restants' => $joursAcquis, // Initialement égaux à jours_acquis
                ]);
            }
        }
    }
}
