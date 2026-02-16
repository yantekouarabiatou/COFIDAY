<?php

namespace App\Http\Controllers;

use App\Models\Dossier;
use App\Models\User;
use App\Models\TimeEntry;
use App\Models\DailyEntry;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PersonnelMissionExport;

class MissionAnalyseController extends Controller
{
    /**
     * Page d'analyse des personnels par mission
     */
    // Dans MissionAnalyseController.php
    public function index(Request $request)
    {
        // Récupérer les dossiers accessibles par l'utilisateur courant
        $dossiers = Dossier::accessibleBy(auth()->id())
            ->enCours()
            ->with('client')
            ->get();

        // Récupérer les personnels (filtrer selon les besoins)
        $personnels = User::where('is_active', 'actif')
            ->with(['poste', 'timeEntries' => function ($q) {
                $q->whereDate('created_at', now());
            }])
            ->get();

        // Si on vient d'une soumission avec des données
        if ($request->has('dossier_id')) {
            // Vérifier que l'utilisateur a accès à ce dossier
            $dossier = Dossier::find($request->dossier_id);
            if (!$dossier || !$dossier->userCanAccess(auth()->id())) {
                return redirect()->back()->with('error', 'Accès non autorisé à ce dossier.');
            }
            return $this->filtrerPersonnels($request);
        }

        return view('pages.missions.analyse', compact('dossiers', 'personnels'));
    }

    /**
     * Filtrer les personnels par mission
     */
    public function filtrerPersonnels(Request $request)
    {
        $request->validate([
            'dossier_id' => 'required|exists:dossiers,id',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'export_format' => 'nullable|in:pdf,excel,csv,json'
        ]);

        $dossier = Dossier::with(['client', 'timeEntries.user'])->find($request->dossier_id);

        // Récupérer les collaborateurs du dossier
        $collaborateursDossier = $dossier->collaborateurs;

        // Si l'utilisateur n'est pas admin, vérifier qu'il est bien collaborateur
        if (!auth()->user()->hasRole(['admin', 'super-admin'])) {
            if (!$collaborateursDossier->contains('id', auth()->id())) {
                return redirect()->back()->with('error', 'Vous n\'êtes pas collaborateur sur ce dossier.');
            }
        }
        // Récupérer les time entries du dossier
        $query = TimeEntry::with(['user', 'user.poste', 'dailyEntry'])
            ->where('dossier_id', $request->dossier_id);

        // Filtrer par période
        if ($request->date_debut) {
            $query->whereHas('dailyEntry', function ($q) use ($request) {
                $q->where('jour', '>=', $request->date_debut);
            });
        }

        if ($request->date_fin) {
            $query->whereHas('dailyEntry', function ($q) use ($request) {
                $q->where('jour', '<=', $request->date_fin);
            });
        }

        $timeEntries = $query->get();

        // Regrouper par utilisateur
        $personnelsParUser = [];
        $totalHeures = 0;

        foreach ($timeEntries as $entry) {
            $userId = $entry->user_id;

            if (!isset($personnelsParUser[$userId])) {
                $personnelsParUser[$userId] = [
                    'user' => $entry->user,
                    'total_heures' => 0,
                    'entries' => [],
                    'autres_missions' => []
                ];
            }

            $personnelsParUser[$userId]['total_heures'] += $entry->heures_reelles;
            $personnelsParUser[$userId]['entries'][] = $entry;
            $totalHeures += $entry->heures_reelles;
        }

        // Vérifier les autres activités pour chaque utilisateur
        foreach ($personnelsParUser as $userId => &$data) {
            $autresMissions = TimeEntry::with(['dossier', 'dailyEntry'])
                ->where('user_id', $userId)
                ->where('dossier_id', '!=', $request->dossier_id)
                ->when($request->date_debut, function ($q) use ($request) {
                    $q->whereHas('dailyEntry', function ($sub) use ($request) {
                        $sub->where('jour', '>=', $request->date_debut);
                    });
                })
                ->when($request->date_fin, function ($q) use ($request) {
                    $q->whereHas('dailyEntry', function ($sub) use ($request) {
                        $sub->where('jour', '<=', $request->date_fin);
                    });
                })
                ->get()
                ->groupBy('dossier_id');

            $data['autres_missions'] = $autresMissions;
            $data['charge_totale'] = $this->calculerChargeTotale($userId, $request->date_debut, $request->date_fin);
        }

        // Calcul du surplus de temps
        $heureTheorique = $dossier->heure_theorique_sans_weekend ?? $dossier->heure_theorique_avec_weekend ?? 0;
        $surplus = $heureTheorique > 0 ? $totalHeures - $heureTheorique : 0;

        // Statistiques
        $stats = [
            'total_personnels' => count($personnelsParUser),
            'total_heures' => $totalHeures,
            'heure_theorique' => $heureTheorique,
            'surplus' => $surplus,
            'surplus_pourcentage' => $heureTheorique > 0 ? round(($surplus / $heureTheorique) * 100, 2) : 0,
            'moyenne_par_personnel' => count($personnelsParUser) > 0 ?
                round($totalHeures / count($personnelsParUser), 2) : 0
        ];

        // Export si demandé
        if ($request->export_format) {
            return $this->exporter($personnelsParUser, $dossier, $stats, $request->export_format);
        }

        return view('pages.missions.resultats', compact(
            'dossier',
            'personnelsParUser',
            'stats',
            'timeEntries',
            'request',
            'collaborateursDossier'
        ));
    }

    /**
     * Calculer la charge totale d'un utilisateur
     */
    private function calculerChargeTotale($userId, $dateDebut = null, $dateFin = null)
    {
        $query = DailyEntry::where('user_id', $userId);

        if ($dateDebut) {
            $query->where('jour', '>=', $dateDebut);
        }

        if ($dateFin) {
            $query->where('jour', '<=', $dateFin);
        }

        $heuresReelles = $query->sum('heures_reelles');
        $heuresTheoriques = $query->sum('heures_theoriques');

        return [
            'heures_reelles' => $heuresReelles,
            'heures_theoriques' => $heuresTheoriques,
            'ecart' => $heuresReelles - $heuresTheoriques,
            'taux_realisation' => $heuresTheoriques > 0 ?
                round(($heuresReelles / $heuresTheoriques) * 100, 2) : 0
        ];
    }

    /**
     * Exporter les résultats
     */
    private function exporter($personnelsParUser, $dossier, $stats, $format)
    {
        $data = [
            'dossier' => $dossier,
            'personnels' => $personnelsParUser,
            'stats' => $stats,
            'date_export' => now()->format('d/m/Y H:i')
        ];

        $filename = 'personnels-mission-' . $dossier->reference . '-' . now()->format('Y-m-d');

        switch ($format) {
            case 'pdf':
                $pdf = Pdf::loadView('exports.missions.pdf', $data);
                return $pdf->download($filename . '.pdf');

            case 'excel':
                return Excel::download(new PersonnelMissionExport($data), $filename . '.xlsx');

            case 'csv':
                return Excel::download(new PersonnelMissionExport($data), $filename . '.csv');

            case 'json':
                return response()->json($data)
                    ->header('Content-Type', 'application/json')
                    ->header('Content-Disposition', 'attachment; filename="' . $filename . '.json"');
        }
    }
    /**
     * Vue détaillée d'un utilisateur avec toutes ses activités
     */
    /**
     * Vue détaillée d'un utilisateur avec toutes ses activités
     */
    public function vueUtilisateur($userId, $dossierId = null)
    {
        $user = User::with(['poste', 'dailyEntries.timeEntries.dossier'])->findOrFail($userId);

        $query = TimeEntry::with(['dossier', 'dailyEntry'])
            ->where('user_id', $userId);

        if ($dossierId) {
            $query->where('dossier_id', $dossierId);
        }

        $timeEntries = $query->orderBy('created_at', 'desc')->get();

        // Regrouper par mission
        $missions = $timeEntries->groupBy('dossier_id')->map(function ($entries, $dossierId) {
            return [
                'dossier' => $entries->first()->dossier,
                'total_heures' => $entries->sum('heures_reelles'),
                'entries' => $entries,
                'derniere_activite' => $entries->max('created_at')
            ];
        });

        // Charge de travail sur 30 jours
        $dateDebut = now()->subDays(30);
        $charge = $this->calculerChargeTotale($userId, $dateDebut, now());

        return view('pages.missions.utilisateur', compact(
            'user',
            'missions',
            'charge',
            'timeEntries'  // Ajout de cette variable
        ));
    }
}
