<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ClientImportService;
use Illuminate\Http\Request;

class ClientImportController extends Controller
{
    // ClientImportController.php
    public function import(Request $request, ClientImportService $service)
    {
        $userId = auth()->id(); // ID de l'utilisateur authentifié

        $result = $service->importAll($request->only(['status', 'search']), $userId);

        if (!$result['success']) {
            return redirect()->back()->with('error', 'Erreur lors de l’import : ' . ($result['message'] ?? ''));
        }

        return redirect()->back()->with(
            'success',
            'Import terminé. Créés : ' . $result['results']['created'] .
                ', Mis à jour : ' . $result['results']['updated']
        );
    }
}
