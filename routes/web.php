<?php

use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\ProfileController;
use App\Mail\LeaveRejectedMail;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\LogActivitesController;
use App\Http\Controllers\PlaintesController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PosteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CompanySettingController;
use App\Http\Controllers\DailyEntryController;
use App\Http\Controllers\DossierController;
use App\Http\Controllers\CongeController;
use App\Http\Controllers\MissionAnalyseController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RapportController;
use App\Http\Controllers\RegleCongeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SoldeCongeController;
use App\Http\Controllers\StatisticsController;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\UserProfileController;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\MissionImportController;
use App\Http\Controllers\Api\ClientImportController;
use App\Http\Controllers\AttestationController;
use App\Http\Controllers\DemissionController;

Route::get('/', function () {
    return view('auth.login');
})->middleware('guest');

Route::get('/otp', [AuthenticatedSessionController::class, 'showOtpForm'])
    ->name('otp.form');

Route::post('/otp/resend', [AuthenticatedSessionController::class, 'resendOtp'])
    ->name('otp.resend');

Route::post('/otp/verify', [AuthenticatedSessionController::class, 'verifyOtp'])
    ->name('otp.verify');


Route::middleware(['auth', 'otp.verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'otp.verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');
    Route::get('/dashboard/user-stats/{userId}', [DashboardController::class, 'userStats'])->name('dashboard.user-stats');
    Route::post('/dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');
    Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggleStatus');

    Route::get('/test-leave-mail', [CongeController::class, 'store'])->name('test.leave.mail');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::delete('/notifications', [NotificationController::class, 'clearAll'])->name('notifications.clear-all');
    Route::get('/notifications/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/mark-multiple-read', [NotificationController::class, 'markMultipleAsRead'])->name('notifications.mark-multiple-read');
    // Activités / logs
    Route::get('/logs', [LogActivitesController::class, 'index'])->name('logs.index');
    Route::get('/logs/{log}', [LogActivitesController::class, 'show'])->name('logs.show'); // ← Nouvelle route
    Route::get('/activities', [LogActivitesController::class, 'index'])->name('activities');
    Route::resource('users', UserController::class);
    Route::resource('postes', PosteController::class);
    // Cadeau Invitations

    Route::get('/conges/validation-finale', [CongeController::class, 'validationFinaleIndex'])
        ->name('conges.validation-finale.index');

    Route::post('/conges/{demande}/valider-finale', [CongeController::class, 'validerFinale'])
        ->name('conges.valider-finale');
    Route::get('/conges/{demande}/validation-finale', [CongeController::class, 'showValidationFinale'])
    ->name('conges.validation-finale.show');
    Route::get('/conges/{demande}/pre-approbation', [CongeController::class, 'preApprouver'])
    ->name('conges.pre-approbation.show');
    Route::get('/error_404', function () {
        return view('errors.errors-404');
    });

    Route::get('/error_403', function () {
        return view('errors.errors-403');
    });

    Route::get('/error_419', function () {
        return view('errors.index');
    });

    Route::get('/error_500', function () {
        return view('errors.errors-500');
    });

    Route::get('/error_503', function () {
        return view('errors.errors-503');
    });
    Route::middleware('auth')->prefix('notifications')->name('notifications.')->group(function () {

        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::delete('/', [NotificationController::class, 'destroyAll'])->name('destroy-all');

        // Bonus realtime
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
        Route::get('/recent', [NotificationController::class, 'recent'])->name('recent');
    });

    Route::prefix('admin')->middleware(['auth'])->group(function () {

        Route::get('/permissions', [PermissionController::class, 'index'])
            ->name('admin.roles.permissions.index');

        Route::get('/roles/{role}/permissions', [PermissionController::class, 'show'])
            ->name('admin.roles.permissions.show');

        // Change POST → PUT (ou PATCH)
        Route::put('/roles/{role}/permissions', [PermissionController::class, 'updateRolePermissions'])
            ->name('admin.roles.permissions.update');
    });
    Route::get('/dashboard/data', [App\Http\Controllers\DashboardController::class, 'data'])->name('dashboard.data')->middleware('auth');
    Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {

        Route::get('/roles', [RolePermissionController::class, 'index'])
            ->name('roles.index');

        Route::get('/roles/create', [RolePermissionController::class, 'create'])
            ->name('roles.create');

        Route::post('/roles', [RolePermissionController::class, 'store'])
            ->name('roles.store');

        Route::get('/roles/{role}/edit', [RolePermissionController::class, 'edit'])
            ->name('roles.edit');

        Route::put('/roles/{role}', [RolePermissionController::class, 'update'])
            ->name('roles.update');

        Route::delete('/roles/{role}', [RolePermissionController::class, 'destroy'])
            ->name('roles.destroy');

        Route::get('/statistics/globale', [StatisticsController::class, 'index'])
            ->name('stats.globale');

        Route::get('/statistics/data', [StatisticsController::class, 'globalStats'])
            ->name('stats.data');

        Route::get('/statistics/employes', [StatisticsController::class, 'getEmployes'])
            ->name('stats.employes');

        Route::get('/statistics/employes/{user}', [StatisticsController::class, 'employeDetails'])
            ->name('stats.employe.details');
    });
    Route::get('roles-permissions/{role}', [PermissionController::class, 'show'])
        ->name('admin.roles-permissions.show');

    Route::put('/admin/roles-permissions/{role}', [RolePermissionController::class, 'update'])
        ->name('admin.roles-permissions.update');

    Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
        Route::resource('roles', RoleController::class)->except(['show']);
        Route::resource('roles', RoleController::class);
    });

    Route::get('/statistics/export', [StatisticsController::class, 'export'])->name('statistics.export');
    Route::post('/stats/annual/update', [StatisticsController::class, 'updateCharts'])->name('stats.annual.update');

    Route::middleware('auth')
        ->prefix('profile')
        ->name('user-profile.')
        ->group(function () {

            Route::get('/', [UserProfileController::class, 'index'])->name('index');
            Route::get('/{id}', [UserProfileController::class, 'showUser'])->name('show');
            Route::get('/{id}/edit', [UserProfileController::class, 'editUser'])->name('edit');
            Route::put('/{id}', [UserProfileController::class, 'updateUser'])->name('update');
            Route::put('/{id}/deactivate', [UserProfileController::class, 'deactivate'])->name('deactivate');
            Route::put('/{id}/activate', [UserProfileController::class, 'activate'])->name('activate');
            Route::get('/{id}/download-photo', [UserProfileController::class, 'downloadPhoto'])->name('download-photo');
            Route::post('/change-password', [UserProfileController::class, 'changePassword'])->name('change-password');
        });

    Route::get('/rapports/mensuel', [RapportController::class, 'mensuel'])
        ->name('rapports.mensuel');


    Route::get('/user-profile/export-temps/{id}/{format}', [UserProfileController::class, 'exportTemps'])->name('user-profile.export-temps');

    Route::prefix('settings')->group(function () {
        // Affiche les paramètres
        Route::get('/', [CompanySettingController::class, 'show'])->name('settings.show');

        // Affiche le formulaire d'édition
        Route::get('/edit', [CompanySettingController::class, 'edit'])->name('settings.edit');
        Route::get('guide/visualiser', [CompanySettingController::class, 'viewGuide'])->name('settings.guide.view');
        Route::get('guide/telecharger', [CompanySettingController::class, 'downloadGuide'])->name('settings.guide.download');
        // Traite la mise à jour (nécessite l'ID ou une logique de singleton)
        // Ici, on passe l'ID 1 qui sera géré par la méthode update
        Route::put('/{setting}', [CompanySettingController::class, 'update'])->name('settings.update');
    })->middleware('auth'); // Appliquez les middlewares nécessaires

    Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

        // ================= RÈGLES DE CONGÉS =================

        Route::get('regles-conges', [RegleCongeController::class, 'index'])
            ->name('regles-conges.index');

        Route::get('regles-conges/create', [RegleCongeController::class, 'create'])
            ->name('regles-conges.create');

        Route::post('regles-conges', [RegleCongeController::class, 'store'])
            ->name('regles-conges.store');

        Route::get('regles-conges/{regle}', [RegleCongeController::class, 'show'])
            ->name('regles-conges.show');

        Route::get('regles-conges/{regle}/edit', [RegleCongeController::class, 'edit'])
            ->name('regles-conges.edit');

        Route::put('regles-conges/{regle}', [RegleCongeController::class, 'update'])
            ->name('regles-conges.update');

        Route::delete('regles-conges/{regle}', [RegleCongeController::class, 'destroy'])
            ->name('regles-conges.destroy');

        // ================= API =================

        Route::get('api/regles-conges/jours-acquis', [RegleCongeController::class, 'getJoursAcquis'])
            ->name('regles-conges.jours-acquis');
    });

    Route::middleware(['auth'])->group(function () {
        // Routes pour les employés
        Route::get('/conges/solde', [CongeController::class, 'solde'])->name('conges.solde');
        Route::get('/conges/calendrier', [CongeController::class, 'calendrier'])->name('conges.calendrier');

        // Route pour annuler une demande
        Route::post('/conges/{demande}/annuler', [CongeController::class, 'annuler'])->name('conges.annuler');

        // Routes pour admin/manager
        Route::middleware(['role:admin|manager'])->group(function () {
            Route::get('/conges/dashboard', [CongeController::class, 'dashboard'])->name('conges.dashboard');
            Route::post('/conges/{demande}/traiter', [CongeController::class, 'traiter'])->name('conges.traiter');
            Route::get('/conges/solde/{user}', [CongeController::class, 'solde'])->name('conges.solde.user');
        });
    });

    Route::resource('conges', CongeController::class)
        ->parameters(['conges' => 'demande']);

    Route::prefix('export')->group(function () {
        Route::get('/excel', [CongeController::class, 'exportExcel'])->name('conges.export.excel');
        Route::get('/pdf', [CongeController::class, 'exportPdf'])->name('conges.export.pdf');
        Route::get('/csv', [CongeController::class, 'exportCsv'])->name('conges.export.csv');
    });

    Route::middleware(['auth', 'role:admin'])->group(function () {
        Route::get('/user-profile/{id}/export-documents/{format}', [UserProfileController::class, 'exportDocuments'])
            ->name('user-profile.export-documents');
        Route::post('/conges/solde/{user}/ajuster', [CongeController::class, 'ajusterSolde'])->name('conges.ajuster-solde');
    });

    // ── Attestations de travail ──────────────────────────────────────────────────
    Route::prefix('attestations')->name('attestations.')->group(function () {
        Route::get('/',                                [AttestationController::class, 'index'])->name('index');
        Route::get('/creer',                           [AttestationController::class, 'create'])->name('create');
        Route::post('/',                               [AttestationController::class, 'store'])->name('store');
        Route::get('/{attestation}',                   [AttestationController::class, 'show'])->name('show');
        Route::delete('/{attestation}/annuler',        [AttestationController::class, 'annuler'])->name('annuler');

        Route::middleware(['role:directeur-general|rh|admin'])->group(function () {
            Route::get('/validation/liste',            [AttestationController::class, 'validationIndex'])->name('validation.index');
            Route::post('/{attestation}/traiter',      [AttestationController::class, 'traiter'])->name('traiter');
        });
    });

    // ── Démissions & Certificats de travail ─────────────────────────────────────
    Route::prefix('demissions')->name('demissions.')->group(function () {
        Route::get('/',                                [DemissionController::class, 'index'])->name('index');
        Route::get('/soumettre',                       [DemissionController::class, 'create'])->name('create');
        Route::post('/',                               [DemissionController::class, 'store'])->name('store');
        Route::get('/{demission}',                     [DemissionController::class, 'show'])->name('show');

        Route::middleware(['role:directeur-general|rh|admin'])->group(function () {
            Route::get('/validation/liste',            [DemissionController::class, 'validationIndex'])->name('validation.index');
            Route::post('/{demission}/traiter',        [DemissionController::class, 'traiter'])->name('traiter');
        });
    });

    Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('soldes', SoldeCongeController::class);
    });


    Route::post('/clients/import', [ClientImportController::class, 'import'])
        ->name('clients.import')
        ->middleware('auth'); // si besoin
    Route::get('/conges/get-feries', [CongeController::class, 'getFeries'])
        ->name('conges.get-feries')
        ->middleware('auth');
    // Route API à créer dans routes/api.php
    Route::get('/personnel-details', function (Request $request) {
        $personnel = User::with(['poste', 'timeEntries' => function ($q) use ($request) {
            $q->where('dossier_id', $request->dossier_id);
        }])->find($request->personnel_id);

        return response()->json([
            'html' => view('partials.personnel-details', compact('personnel'))->render()
        ]);
    });
});

// ================= STATISTIQUES GLOBALES (ADMIN) =================
Route::middleware(['auth', 'otp.verified'])->prefix('admin')->name('admin.')->group(function () {
    // Page principale
    Route::get('/statistics', [StatisticsController::class, 'index'])->name('stats.index');

    // API pour les données (KPI, graphiques, tableau)
    Route::get('/statistics/data', [StatisticsController::class, 'globalStats'])->name('stats.data');

    // Liste des employés pour le filtre Select2
    Route::get('/statistics/employes', [StatisticsController::class, 'getEmployes'])->name('stats.employes');

    // Export (optionnel)
    Route::get('/statistics/export', [StatisticsController::class, 'export'])->name('stats.export');
});
require __DIR__ . '/auth.php';
