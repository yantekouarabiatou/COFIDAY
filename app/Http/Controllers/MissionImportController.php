<?php

// app/Http/Controllers/MissionImportController.php

namespace App\Http\Controllers;

use App\Services\MissionImportService;
use Illuminate\Http\Request;

class MissionImportController extends Controller
{
    public function __construct(private MissionImportService $service) {}

    public function import(Request $request)
    {
        $filters = $request->only(['status', 'search', 'per_page']);

        $result = $this->service->importAll($filters);

        if (!$result['success']) {
            return back()->with('error', 'Erreur lors de la synchronisation avec Cofplan.');
        }

        $r = $result['results'];

        $message = "Synchronisation terminée : {$r['created']} créé(s), {$r['updated']} mis à jour, {$r['skipped']} ignoré(s).";

        if (!empty($r['errors'])) {
            $message .= " ({$r['errors'][0]})";
        }

        return back()->with('success', $message);
    }
}