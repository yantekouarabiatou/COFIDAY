<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AnalyseController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/analyse/personnels', [AnalyseController::class, 'getPersonnelData']);
    Route::get('/analyse/evolution', [AnalyseController::class, 'getEvolutionData']);
    Route::get('/analyse/dossier/{dossier}', [AnalyseController::class, 'getDossierData']);
});
