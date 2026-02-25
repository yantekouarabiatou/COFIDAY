<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\DailyEntry;
use App\Models\Dossier;
use App\Models\TimeEntry;
use App\Models\DemandeConge;
use App\Models\LogActivite;
use App\Models\SoldeConge;
use App\Models\User;
use App\Observers\UniversalModelObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
         Schema::defaultStringLength(191);

        // Configuration des SweetAlert
        $this->configureSweetAlert();

        // Configuration des Gates
        $this->configureGates();

        // Enregistrement des observateurs
        $this->registerObservers();

        // Directives Blade
        Blade::directive('adjustBrightness', function ($expression) {
            return "<?php echo adjustBrightness{$expression}; ?>";
        });

        // Log de démarrage
        Log::info('AppServiceProvider booté', ['time' => now()]);
    }

    private function configureSweetAlert()
    {
        if (session('success')) {
            alert()->success('Succès !', session('success'));
        }

        if (session('error')) {
            alert()->error('Erreur !', session('error'));
        }

        if (session('warning')) {
            alert()->warning('Attention !', session('warning'));
        }

        if (session('info')) {
            alert()->info('Information', session('info'));
        }
    }

    private function configureGates()
    {
        Gate::before(function ($user, $ability) {
            $rolesToutPuissants = ['super-admin', 'admin'];

            // Vérification via Spatie
            if ($user->hasAnyRole($rolesToutPuissants)) {
                Log::info('Gate bypass - Spatie role', [
                    'user_id' => $user->id,
                    'ability' => $ability
                ]);
                return true;
            }

            // Vérification via role_id (votre système)
            if ($user->role && in_array($user->role->name, $rolesToutPuissants)) {
                Log::info('Gate bypass - Custom role', [
                    'user_id' => $user->id,
                    'role' => $user->role->name,
                    'ability' => $ability
                ]);
                return true;
            }
        });
    }

    private function registerObservers()
    {
        try {
            // Observateur universel pour TOUS les modèles importants
            $models = [
                LogActivite::class,  // Logs d'activité
                TimeEntry::class,     // Saisies de temps
                DailyEntry::class,    // Feuilles de temps
                Dossier::class,       // Dossiers
                Client::class,        // Clients
                DemandeConge::class,  // Demandes de congés
                SoldeConge::class,    // Soldes de congés
                \App\Models\User::class,          // Utilisateurs
            ];

            foreach ($models as $modelClass) {
                $modelClass::observe(UniversalModelObserver::class);
                Log::info("UniversalModelObserver enregistré pour {$modelClass}");
            }

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'enregistrement des observateurs', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
