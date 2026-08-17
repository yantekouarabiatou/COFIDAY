<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Renseigne une date_embauche de test pour les utilisateurs qui n'en ont pas
 * encore, répartie aléatoirement sur les 5 dernières années. Sert à tester
 * l'accrual progressif du solde de congés (SoldeCongeCalculator::moisTravailles)
 * sur des anciennetés variées (arrivée en cours d'année, année complète, etc.).
 *
 * Ne touche jamais un utilisateur qui a déjà une date_embauche renseignée.
 */
class DateEmbaucheSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::whereNull('date_embauche')->get();

        if ($users->isEmpty()) {
            $this->command->info('Aucun utilisateur sans date_embauche à seeder.');
            return;
        }

        foreach ($users as $user) {
            $anneesAnciennete = random_int(0, 5);
            $mois             = random_int(1, 12);
            $jour             = random_int(1, 28);

            $dateEmbauche = Carbon::now()
                ->subYears($anneesAnciennete)
                ->setMonth($mois)
                ->setDay($jour);

            // Une date d'embauche future n'a pas de sens (ex. anciennete=0,
            // mois tiré après le mois courant).
            if ($dateEmbauche->isFuture()) {
                $dateEmbauche->subYear();
            }

            $user->update(['date_embauche' => $dateEmbauche->toDateString()]);

            $this->command->line("  ✔  {$user->email}  date_embauche = {$dateEmbauche->toDateString()}");
        }

        $this->command->info($users->count() . " date(s) d'embauche de test générée(s).");
    }
}
