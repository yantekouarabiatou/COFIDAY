<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\DailyEntry;
use App\Models\Dossier;
use App\Models\TimeEntry;
use App\Observers\TimeEntryObserver;
use App\Observers\UniversalModelObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {

        // Pour les messages flash SweetAlert
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
        Gate::before(function ($user, $ability) {

            // Liste des rôles qui ont le pouvoir absolu (God Mode)
            $rolesToutPuissants = ['super-admin', 'admin'];

            // 1. Vérification via Spatie (Méthode standard)
            if ($user->hasAnyRole($rolesToutPuissants)) {
                return true;
            }

            // 2. Vérification via votre relation role_id (Sécurité supplémentaire pour votre config)
            if ($user->role && in_array($user->role->name, $rolesToutPuissants)) {
                return true;
            }
        });
        // Enregistrez l'observateur pour les modèles spécifiques


        Blade::directive('adjustBrightness', function ($expression) {
            return "<?php echo adjustBrightness{$expression}; ?>";
        });
    }
}
