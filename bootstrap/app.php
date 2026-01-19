<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Session\TokenMismatchException;
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // C'est ici qu'on ajoute les alias pour Spatie
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'dossier.access' => \App\Http\Middleware\CheckDossierAccess::class,

        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Gestion des erreurs 404
        $exceptions->render(function (NotFoundHttpException $e, $request) {
            return response()->view('errors.errors-404', [], 404);
        });

        // Gestion des erreurs 403
        $exceptions->render(function (HttpException $e, $request) {
            if ($e->getStatusCode() === 403) {
                return response()->view('errors.errors-403', [], 403);
            }
        });

        // Gestion des erreurs CSRF (419)
        $exceptions->render(function (TokenMismatchException $e, $request) {
            return response()->view('errors.errors-419', [], 419);
        });

        // Gestion des erreurs d'authentification
        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Non authentifié'], 401);
            }
            return redirect()->route('login')->with('error', 'Veuillez vous connecter');
        });

        // Gestion des erreurs 500 (seulement en production)
        $exceptions->render(function (Throwable $e, $request) {
            if (app()->environment('production') && !($e instanceof NotFoundHttpException)) {
                return response()->view('errors.errors-500', [], 500);
            }
        });
    })->create();
