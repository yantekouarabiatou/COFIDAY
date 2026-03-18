<?php

// app/Http/Controllers/Api/MissionImportController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MissionImportService;
use Illuminate\Http\Request;

class MissionImportController extends Controller
{
    public function __construct(private MissionImportService $service) {}

    /**
     * Importer toutes les missions
     */
    public function import(Request $request)
    {
        // Filtres optionnels transmis à l'API source
        $filters = $request->only(['status', 'search', 'per_page']);

        $result = $this->service->importAll($filters);

        if (!$result['success']) {
            return response()->json($result, 502);
        }

        return response()->json([
            'success' => true,
            'message' => 'Import terminé.',
            'results' => $result['results'],
        ]);
    }
}