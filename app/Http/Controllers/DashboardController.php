<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TimeEntry;
use App\Models\Dossier;
use App\Models\DailyEntry;
use App\Models\DemandeConge;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Afficher le tableau de bord
     */
    public function index()
    {
        return view('dashboard');
    }

    /**
     * Récupérer les données pour le dashboard de l'utilisateur connecté
     */
    public function data()
    {
        $user = auth()->user();

        // Totaux personnels
        $totals = [
            'mes_dossiers' => $this->getUserDossiers($user->id)->count(),
            'dossiers_actifs' => $this->getUserDossiers($user->id)
                                      ->whereIn('statut', ['ouvert', 'en_cours'])
                                      ->count(),
            'heures_mois' => TimeEntry::where('user_id', $user->id)
                                      ->whereMonth('created_at', now()->month)
                                      ->whereYear('created_at', now()->year)
                                      ->sum('heures_reelles'),
            'mes_conges_en_cours' => DemandeConge::where('date_debut', '<=', now())
                                          ->where('date_fin', '>=', now())
                                          ->where('user_id', $user->id)
                                          ->count(),
            'heures_totales' => TimeEntry::where('user_id', $user->id)->sum('heures_reelles'),
        ];

        // Statistiques hebdomadaires (7 derniers jours) - Personnel
        $weekStart = now()->subDays(7);
        $weeklyStats = [
            'heures' => TimeEntry::where('user_id', $user->id)
                                 ->where('created_at', '>=', $weekStart)
                                 ->sum('heures_reelles'),
            'dossiers_travailles' => TimeEntry::where('user_id', $user->id)
                                              ->where('created_at', '>=', $weekStart)
                                              ->distinct('dossier_id')
                                              ->count('dossier_id'),
        ];

        // Statistiques du mois précédent (pour comparaison) - Personnel
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();
        $lastMonthStats = [
            'heures' => TimeEntry::where('user_id', $user->id)
                                 ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
                                 ->sum('heures_reelles'),
            'dossiers_travailles' => TimeEntry::where('user_id', $user->id)
                                              ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
                                              ->distinct('dossier_id')
                                              ->count('dossier_id'),
        ];

        // Statistiques mensuelles (mois en cours) - Personnel
        $monthlyStats = [
            'heures' => TimeEntry::where('user_id', $user->id)
                                 ->whereMonth('created_at', now()->month)
                                 ->whereYear('created_at', now()->year)
                                 ->sum('heures_reelles'),
            'dossiers_travailles' => TimeEntry::where('user_id', $user->id)
                                              ->whereMonth('created_at', now()->month)
                                              ->whereYear('created_at', now()->year)
                                              ->distinct('dossier_id')
                                              ->count('dossier_id'),
            'conges' => DemandeConge::where('user_id', $user->id)
                             ->whereMonth('date_debut', now()->month)
                             ->whereYear('date_debut', now()->year)
                             ->count(),
        ];

        // Calculer les pourcentages d'évolution
        $percentages = [
            'heures' => $this->calculatePercentage($monthlyStats['heures'], $lastMonthStats['heures']),
            'dossiers' => $this->calculatePercentage($monthlyStats['dossiers_travailles'], $lastMonthStats['dossiers_travailles']),
        ];

        // Mes heures sur les 30 derniers jours
        $last30days = collect();
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateLabel = $date->format('d/m');
            
            $heures = TimeEntry::where('user_id', $user->id)
                               ->whereDate('created_at', $date)
                               ->sum('heures_reelles') ?? 0;
            
            $last30days->put($dateLabel, [
                'heures' => round($heures, 2),
            ]);
        }

        // Mes dossiers les plus actifs (par heures) - Mois en cours
        $mesDossiersActifs = Dossier::select('dossiers.id', 'dossiers.nom', 'dossiers.reference')
            ->join('time_entries', 'dossiers.id', '=', 'time_entries.dossier_id')
            ->where('time_entries.user_id', $user->id)
            ->whereMonth('time_entries.created_at', now()->month)
            ->whereYear('time_entries.created_at', now()->year)
            ->groupBy('dossiers.id', 'dossiers.nom', 'dossiers.reference')
            ->selectRaw('SUM(time_entries.heures_reelles) as total_heures')
            ->orderByDesc('total_heures')
            ->limit(5)
            ->get();

        // Répartition de mes heures par dossier (mois en cours)
        $mesHeuresParDossier = Dossier::select('dossiers.nom', 'dossiers.reference')
            ->join('time_entries', 'dossiers.id', '=', 'time_entries.dossier_id')
            ->where('time_entries.user_id', $user->id)
            ->whereMonth('time_entries.created_at', now()->month)
            ->whereYear('time_entries.created_at', now()->year)
            ->groupBy('dossiers.id', 'dossiers.nom', 'dossiers.reference')
            ->selectRaw('SUM(time_entries.heures_reelles) as total_heures')
            ->orderByDesc('total_heures')
            ->limit(10)
            ->get();

        // Mes congés par type (année en cours)
        $mesCongesParType = DemandeConge::where('user_id', $user->id)
            ->whereYear('date_debut', now()->year)
            ->select('type_conge', DB::raw('count(*) as count'))
            ->groupBy('type_conge')
            ->get();

        // Mes daily entries récentes (7 derniers jours)
        $mesDailyEntries = DailyEntry::where('user_id', $user->id)
            ->where('jour', '>=', now()->subDays(7))
            ->orderBy('jour', 'desc')
            ->get()
            ->map(function($entry) {
                return [
                    'jour' => Carbon::parse($entry->jour)->format('d/m/Y'),
                    'heures_reelles' => round($entry->heures_reelles, 2),
                    'heures_theoriques' => round($entry->heures_theoriques, 2),
                    'statut' => $entry->statut,
                    'is_weekend' => $entry->is_weekend,
                    'is_holiday' => $entry->is_holiday,
                ];
            });

        // Mes congés à venir (prochains 30 jours)
        $mesCongesAVenir = DemandeConge::where('user_id', $user->id)
            ->where('date_debut', '>', now())
            ->where('date_debut', '<=', now()->addDays(30))
            ->orderBy('date_debut')
            ->get()
            ->map(function($conge) {
                return [
                    'type' => $conge->type_conge,
                    'debut' => Carbon::parse($conge->date_debut)->format('d/m/Y'),
                    'fin' => Carbon::parse($conge->date_fin)->format('d/m/Y'),
                    'jours' => Carbon::parse($conge->date_debut)->diffInDays(Carbon::parse($conge->date_fin)) + 1,
                ];
            });

        return response()->json([
            'user' => [
                'name' => $user->prenom . ' ' . $user->nom,
                'email' => $user->email,
            ],
            'totals' => $totals,
            'weekly' => $weeklyStats,
            'monthly' => $monthlyStats,
            'percentages' => $percentages,
            'last30days' => [
                'dates' => $last30days->keys()->toArray(),
                'heures' => $last30days->pluck('heures')->toArray(),
            ],
            'mesDossiersActifs' => [
                'names' => $mesDossiersActifs->pluck('nom')->toArray(),
                'heures' => $mesDossiersActifs->pluck('total_heures')->map(fn($h) => round($h, 2))->toArray(),
            ],
            'mesHeuresParDossier' => [
                'dossiers' => $mesHeuresParDossier->pluck('nom')->toArray(),
                'heures' => $mesHeuresParDossier->pluck('total_heures')->map(fn($h) => round($h, 2))->toArray(),
            ],
            'mesCongesParType' => [
                'types' => $mesCongesParType->pluck('type_conge')->toArray(),
                'counts' => $mesCongesParType->pluck('count')->toArray(),
            ],
            'mesDailyEntries' => $mesDailyEntries,
            'mesCongesAVenir' => $mesCongesAVenir,
        ]);
    }

    /**
     * Récupérer les dossiers de l'utilisateur
     */
    private function getUserDossiers($userId)
    {
        return Dossier::whereHas('timeEntries', function($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    /**
     * Calculer le pourcentage d'évolution
     */
    private function calculatePercentage($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? '+100' : '0';
        }
        
        $percentage = (($current - $previous) / $previous) * 100;
        $sign = $percentage >= 0 ? '+' : '';
        
        return $sign . number_format($percentage, 0);
    }

    /**
     * Mes statistiques détaillées
     */
    public function myStats()
    {
        $user = auth()->user();
        
        // Heures ce mois
        $heuresMois = TimeEntry::where('user_id', $user->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('heures_reelles');

        // Heures totales
        $heuresTotales = TimeEntry::where('user_id', $user->id)->sum('heures_reelles');

        // Congés ce mois
        $congesMois = DemandeConge::where('user_id', $user->id)
            ->where(function($q) {
                $q->whereMonth('date_debut', now()->month)
                  ->orWhereMonth('date_fin', now()->month);
            })
            ->whereYear('date_debut', now()->year)
            ->count();

        // Mes dossiers actifs
        $dossiersActifs = $this->getUserDossiers($user->id)
            ->whereIn('statut', ['ouvert', 'en_cours'])
            ->count();

        // Heures par jour (7 derniers jours)
        $heuresJournalieres = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $heures = TimeEntry::where('user_id', $user->id)
                ->whereDate('created_at', $date)
                ->sum('heures_reelles') ?? 0;
            
            $heuresJournalieres->put($date->format('d/m'), round($heures, 2));
        }

        // Moyenne d'heures par jour travaillé
        $joursTravailles = DailyEntry::where('user_id', $user->id)
            ->whereMonth('jour', now()->month)
            ->whereYear('jour', now()->year)
            ->where('heures_reelles', '>', 0)
            ->count();

        $moyenneHeuresJour = $joursTravailles > 0 ? $heuresMois / $joursTravailles : 0;

        return response()->json([
            'user' => [
                'name' => $user->prenom . ' ' . $user->nom,
                'email' => $user->email,
            ],
            'stats' => [
                'heures_mois' => round($heuresMois, 2),
                'heures_totales' => round($heuresTotales, 2),
                'conges_mois' => $congesMois,
                'dossiers_actifs' => $dossiersActifs,
                'moyenne_heures_jour' => round($moyenneHeuresJour, 2),
                'jours_travailles' => $joursTravailles,
            ],
            'heuresJournalieres' => [
                'dates' => $heuresJournalieres->keys()->toArray(),
                'heures' => $heuresJournalieres->values()->toArray(),
            ]
        ]);
    }

    /**
     * Statistiques par dossier (accessible uniquement si l'utilisateur a travaillé dessus)
     */
    public function dossierStats($dossierId)
    {
        $user = auth()->user();
        $dossier = Dossier::with('client')->findOrFail($dossierId);
        
        // Vérifier que l'utilisateur a travaillé sur ce dossier
        $hasWorked = TimeEntry::where('dossier_id', $dossierId)
                              ->where('user_id', $user->id)
                              ->exists();
        
        if (!$hasWorked) {
            return response()->json(['message' => 'Vous n\'avez pas accès à ce dossier'], 403);
        }
        
        // Mes heures sur ce dossier
        $mesHeures = TimeEntry::where('dossier_id', $dossierId)
                              ->where('user_id', $user->id)
                              ->sum('heures_reelles');

        // Mes heures ce mois sur ce dossier
        $mesHeuresMois = TimeEntry::where('dossier_id', $dossierId)
            ->where('user_id', $user->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('heures_reelles');

        // Mes dernières interventions
        $mesDernieresInterventions = TimeEntry::where('dossier_id', $dossierId)
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($entry) {
                return [
                    'date' => Carbon::parse($entry->created_at)->format('d/m/Y'),
                    'heures' => round($entry->heures_reelles, 2),
                    'debut' => $entry->heure_debut,
                    'fin' => $entry->heure_fin,
                    'travaux' => $entry->travaux,
                ];
            });

        return response()->json([
            'dossier' => [
                'nom' => $dossier->nom,
                'reference' => $dossier->reference,
                'client' => $dossier->client->nom ?? 'N/A',
                'statut' => $dossier->statut,
            ],
            'mes_stats' => [
                'mes_heures_totales' => round($mesHeures, 2),
                'mes_heures_mois' => round($mesHeuresMois, 2),
                'budget' => $dossier->budget,
            ],
            'mesDernieresInterventions' => $mesDernieresInterventions,
        ]);
    }

    /**
     * Mes congés
     */
    public function mesConges()
    {
        $user = auth()->user();

        // Mes congés en cours
        $congesEnCours = DemandeConge::where('user_id', $user->id)
            ->where('date_debut', '<=', now())
            ->where('date_fin', '>=', now())
            ->get()
            ->map(function($conge) {
                return [
                    'id' => $conge->id,
                    'type' => $conge->type_conge,
                    'debut' => Carbon::parse($conge->date_debut)->format('d/m/Y'),
                    'fin' => Carbon::parse($conge->date_fin)->format('d/m/Y'),
                ];
            });

        // Mes congés à venir (prochains 90 jours)
        $congesAVenir = DemandeConge::where('user_id', $user->id)
            ->where('date_debut', '>', now())
            ->where('date_debut', '<=', now()->addDays(90))
            ->orderBy('date_debut')
            ->get()
            ->map(function($conge) {
                return [
                    'id' => $conge->id,
                    'type' => $conge->type_conge,
                    'debut' => Carbon::parse($conge->date_debut)->format('d/m/Y'),
                    'fin' => Carbon::parse($conge->date_fin)->format('d/m/Y'),
                    'jours' => Carbon::parse($conge->date_debut)->diffInDays(Carbon::parse($conge->date_fin)) + 1,
                ];
            });

        // Mes congés par type (année en cours)
        $congesParType = DemandeConge::where('user_id', $user->id)
            ->whereYear('date_debut', now()->year)
            ->select('type_conge', DB::raw('count(*) as count'))
            ->groupBy('type_conge')
            ->get();

        // Total de jours de congés cette année
        $totalJoursConges = DemandeConge::where('user_id', $user->id)
            ->whereYear('date_debut', now()->year)
            ->get()
            ->sum(function($conge) {
                return Carbon::parse($conge->date_debut)->diffInDays(Carbon::parse($conge->date_fin)) + 1;
            });

        return response()->json([
            'congesEnCours' => $congesEnCours,
            'congesAVenir' => $congesAVenir,
            'congesParType' => [
                'types' => $congesParType->pluck('type_conge')->toArray(),
                'counts' => $congesParType->pluck('count')->toArray(),
            ],
            'totalJoursConges' => $totalJoursConges,
        ]);
    }

    /**
     * Exporter mes statistiques
     */
    public function export(Request $request)
    {
        $user = auth()->user();
        $type = $request->get('type', 'excel'); // excel ou pdf
        $periode = $request->get('periode', 'mois'); // jour, semaine, mois, annee
        
        // Logique d'exportation à implémenter
        // Utiliser Laravel Excel ou DomPDF
        
        return response()->json([
            'message' => 'Export en cours de développement',
            'type' => $type,
            'periode' => $periode,
            'user_id' => $user->id,
        ]);
    }
}