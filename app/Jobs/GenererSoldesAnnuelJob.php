<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\SoldeConge;
use App\Models\RegleConge;
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
        $joursAcquis = $regles ? $regles->jours_par_mois * 12 : 25; // ex: 2.5 × 12 = 30 jours

        $users = User::all();

        foreach ($users as $user) {
            // Vérifier si un solde existe déjà pour cette année
            $soldeExistant = SoldeConge::where('user_id', $user->id)
                ->where('annee', $this->annee)
                ->exists();

            if (!$soldeExistant) {
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
