<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AnalyseController;
use App\Http\Controllers\Api\MissionImportController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/analyse/personnels', [AnalyseController::class, 'getPersonnelData']);
    Route::get('/analyse/evolution', [AnalyseController::class, 'getEvolutionData']);
    Route::get('/analyse/dossier/{dossier}', [AnalyseController::class, 'getDossierData']);

    Route::post('/missions/import', [MissionImportController::class, 'import'])
         ->name('missions.import');
});
