<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Dossier;
use App\Models\TimeEntry;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnalyseController extends Controller
{
    /**
     * Récupérer les données des personnels pour les graphiques
     */
    public function getPersonnelData(Request $request)
    {
        $date = $request->get('date', now()->toDateString());

        // Récupérer les personnels avec leurs heures
        $personnels = User::with(['timeEntries' => function ($query) use ($date) {
            $query->whereDate('created_at', $date);
        }])->get();

        $data = [
            'personnels' => $personnels->map(function ($user) {
                $heures = $user->timeEntries->sum('heures_reelles');
                return [
                    'id' => $user->id,
                    'nom_complet' => $user->prenom . ' ' . $user->nom,
                    'nom_abrege' => $user->prenom . ' ' . substr($user->nom, 0, 1) . '.',
                    'heures' => $heures,
                ];
            }),
            'statistiques' => [
                'charge_normale' => $personnels->filter(fn($p) => $p->timeEntries->sum('heures_reelles') <= 6)->count(),
                'charge_moyenne' => $personnels->filter(fn($p) => $p->timeEntries->sum('heures_reelles') > 6 && $p->timeEntries->sum('heures_reelles') <= 8)->count(),
                'surcharge' => $personnels->filter(fn($p) => $p->timeEntries->sum('heures_reelles') > 8)->count(),
            ],
            'total_heures' => $personnels->sum(function($p) {
                return $p->timeEntries->sum('heures_reelles');
            }),
            'total_personnels' => $personnels->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Récupérer les données d'évolution sur 7 jours
     */
    public function getEvolutionData(Request $request)
    {
        $endDate = $request->get('date', now()->toDateString());
        $startDate = Carbon::parse($endDate)->subDays(6)->toDateString();

        $evolution = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::parse($endDate)->subDays($i);
            $dateStr = $date->toDateString();

            // Récupérer les heures pour cette date
            $totalHeures = TimeEntry::whereDate('created_at', $dateStr)
                ->sum('heures_reelles');

            $evolution[] = [
                'date' => $dateStr,
                'label' => $date->locale('fr')->translatedFormat('D'),
                'heures' => $totalHeures,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $evolution
        ]);
    }

    /**
     * Récupérer les données d'un dossier spécifique
     */
    public function getDossierData($dossierId)
    {
        $dossier = Dossier::with(['timeEntries.user', 'collaborateurs'])->find($dossierId);

        if (!$dossier) {
            return response()->json([
                'success' => false,
                'message' => 'Dossier non trouvé'
            ], 404);
        }

        // Vérifier l'accès
        if (!Auth::user()->hasRole(['admin', 'super-admin']) &&
            !$dossier->userCanAccess(Auth::id())) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        $data = [
            'dossier' => $dossier,
            'total_heures' => $dossier->timeEntries->sum('heures_reelles'),
            'collaborateurs_count' => $dossier->collaborateurs->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
