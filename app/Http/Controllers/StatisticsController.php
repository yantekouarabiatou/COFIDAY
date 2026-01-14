<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TimeEntry;
use App\Models\Dossier;
use App\Models\DailyEntry;
use App\Models\Client;
use App\Models\DemandeConge;
use App\Models\SoldeConge;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    /**
     * Afficher la page des statistiques globales (Admin uniquement)
     */
    public function index()
    {
        // Vérifier que l'utilisateur est admin
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Accès non autorisé');
        }

        return view('pages.statistics.statglobale');
    }

    /**
     * Récupérer les statistiques globales avec filtres
     */
    public function globalStats(Request $request)
    {
        // Vérifier que l'utilisateur est admin
        if (!auth()->user()->hasRole('admin')) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }

        // Récupérer les filtres
        $periode = $request->get('periode', 'mois'); // jour, semaine, mois, annee, personnalise
        $dateDebut = $request->get('date_debut');
        $dateFin = $request->get('date_fin');
        $userId = $request->get('user_id'); // Filtre par employé spécifique

        // Définir les dates selon la période
        [$startDate, $endDate] = $this->getDateRange($periode, $dateDebut, $dateFin);

        // Statistiques globales
        $stats = [
            'totaux' => $this->getTotauxGlobaux($startDate, $endDate, $userId),
            'classement_employes' => $this->getClassementEmployes($startDate, $endDate),
            'classement_conges' => $this->getClassementConges($startDate, $endDate),
            'evolution_heures' => $this->getEvolutionHeures($startDate, $endDate, $userId),
            'repartition_dossiers' => $this->getRepartitionDossiers($startDate, $endDate, $userId),
            'statistiques_conges' => $this->getStatistiquesConges($startDate, $endDate, $userId),
            'performance_mensuelle' => $this->getPerformanceMensuelle($startDate, $endDate, $userId),
            'taux_validation' => $this->getTauxValidation($startDate, $endDate, $userId),
            'heures_par_jour_semaine' => $this->getHeuresParJourSemaine($startDate, $endDate, $userId),
            'soldes_conges' => $this->getSoldesConges($userId),
            'activites_par_heure' => $this->getActivitesParHeure($startDate, $endDate, $userId),
        ];

        return response()->json([
            'stats' => $stats,
            'periode' => [
                'type' => $periode,
                'debut' => $startDate->format('Y-m-d'),
                'fin' => $endDate->format('Y-m-d'),
            ],
            'filtre_user' => $userId ? User::find($userId)->prenom . ' ' . User::find($userId)->nom : null,
        ]);
    }

    /**
     * Obtenir la plage de dates selon la période
     */
    private function getDateRange($periode, $dateDebut = null, $dateFin = null)
    {
        switch ($periode) {
            case 'jour':
                $startDate = now()->startOfDay();
                $endDate = now()->endOfDay();
                break;
            case 'semaine':
                $startDate = now()->startOfWeek();
                $endDate = now()->endOfWeek();
                break;
            case 'mois':
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
                break;
            case 'annee':
                $startDate = now()->startOfYear();
                $endDate = now()->endOfYear();
                break;
            case 'personnalise':
                $startDate = $dateDebut ? Carbon::parse($dateDebut)->startOfDay() : now()->startOfMonth();
                $endDate = $dateFin ? Carbon::parse($dateFin)->endOfDay() : now()->endOfMonth();
                break;
            default:
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
        }

        return [$startDate, $endDate];
    }

    /**
     * Totaux globaux (avec nouvelles tables congés)
     */
    private function getTotauxGlobaux($startDate, $endDate, $userId = null)
    {
        // Statistiques des heures travaillées
        $timeEntriesQuery = TimeEntry::whereBetween('created_at', [$startDate, $endDate]);

        if ($userId) {
            $timeEntriesQuery->where('user_id', $userId);
        }

        $totalHeures = round($timeEntriesQuery->sum('heures_reelles'), 2);
        $employesActifs = $userId ? 1 : User::whereHas('timeEntries', function($q) use ($startDate, $endDate) {
            $q->whereBetween('created_at', [$startDate, $endDate]);
        })->count();

        // Statistiques des congés
        $congesQuery = DemandeConge::whereBetween('date_debut', [$startDate, $endDate])
            ->orWhereBetween('date_fin', [$startDate, $endDate])
            ->orWhere(function($q) use ($startDate, $endDate) {
                $q->where('date_debut', '<=', $startDate)
                   ->where('date_fin', '>=', $endDate);
            });

        if ($userId) {
            $congesQuery->where('user_id', $userId);
        }

        $totalConges = $congesQuery->count();
        $congesEnCours = DemandeConge::where('date_debut', '<=', now())
            ->where('date_fin', '>=', now())
            ->where('statut', 'approuve')
            ->when($userId, function($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->count();

        return [
            'total_employes' => $userId ? 1 : User::count(),
            'employes_actifs' => $employesActifs,
            'total_heures' => $totalHeures,
            'total_dossiers' => $userId
                ? Dossier::whereHas('timeEntries', function($q) use ($userId, $startDate, $endDate) {
                    $q->where('user_id', $userId)
                      ->whereBetween('created_at', [$startDate, $endDate]);
                })->count()
                : Dossier::whereBetween('created_at', [$startDate, $endDate])->count(),
            'dossiers_actifs' => Dossier::whereIn('statut', ['ouvert', 'en_cours'])->count(),
            'total_conges' => $totalConges,
            'conges_en_cours' => $congesEnCours,
            'total_clients' => Client::count(),
            'moyenne_heures_employe' => $userId ? null : round(
                $totalHeures / max(User::count(), 1),
                2
            ),
            'moyenne_heures_actif' => $employesActifs > 0 ? round($totalHeures / $employesActifs, 2) : 0,
        ];
    }

    /**
     * Classement des employés par heures travaillées
     */
    private function getClassementEmployes($startDate, $endDate)
    {
        return User::select('users.id', 'users.prenom', 'users.nom', 'users.email')
            ->leftJoin('time_entries', 'users.id', '=', 'time_entries.user_id')
            ->whereBetween('time_entries.created_at', [$startDate, $endDate])
            ->groupBy('users.id', 'users.prenom', 'users.nom', 'users.email')
            ->selectRaw('SUM(time_entries.heures_reelles) as total_heures')
            ->selectRaw('COUNT(DISTINCT time_entries.dossier_id) as nombre_dossiers')
            ->selectRaw('COUNT(DISTINCT DATE(time_entries.created_at)) as jours_travailles')
            ->orderByDesc('total_heures')
            ->limit(20)
            ->get()
            ->map(function($user, $index) {
                $moyenneJour = $user->jours_travailles > 0
                    ? round(($user->total_heures ?? 0) / $user->jours_travailles, 2)
                    : 0;

                return [
                    'rang' => $index + 1,
                    'id' => $user->id,
                    'nom_complet' => $user->prenom . ' ' . $user->nom,
                    'email' => $user->email,
                    'total_heures' => round($user->total_heures ?? 0, 2),
                    'nombre_dossiers' => $user->nombre_dossiers ?? 0,
                    'jours_travailles' => $user->jours_travailles ?? 0,
                    'moyenne_jour' => $moyenneJour,
                ];
            });
    }

    /**
     * Classement des employés par nombre de congés
     */
    private function getClassementConges($startDate, $endDate)
    {
        return User::select('users.id', 'users.prenom', 'users.nom', 'users.email')
            ->leftJoin('demandes_conges', 'users.id', '=', 'demandes_conges.user_id')
            ->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('demandes_conges.date_debut', [$startDate, $endDate])
                  ->orWhereBetween('demandes_conges.date_fin', [$startDate, $endDate]);
            })
            ->groupBy('users.id', 'users.prenom', 'users.nom', 'users.email')
            ->selectRaw('COUNT(demandes_conges.id) as nombre_conges')
            ->selectRaw('SUM(demandes_conges.nombre_jours) as total_jours_conges')
            ->selectRaw('SUM(CASE WHEN demandes_conges.statut = "approuve" THEN demandes_conges.nombre_jours ELSE 0 END) as jours_approuves')
            ->orderByDesc('nombre_conges')
            ->limit(20)
            ->get()
            ->map(function($user, $index) {
                return [
                    'rang' => $index + 1,
                    'id' => $user->id,
                    'nom_complet' => $user->prenom . ' ' . $user->nom,
                    'email' => $user->email,
                    'nombre_conges' => $user->nombre_conges ?? 0,
                    'total_jours' => $user->total_jours_conges ?? 0,
                    'jours_approuves' => $user->jours_approuves ?? 0,
                ];
            });
    }

    /**
     * Évolution des heures dans la période
     */
    private function getEvolutionHeures($startDate, $endDate, $userId = null)
    {
        $diffInDays = $startDate->diffInDays($endDate);
        $groupBy = 'DATE(time_entries.created_at)';
        $format = '%Y-%m-%d';

        // Adapter le groupement selon la durée
        if ($diffInDays > 90) {
            $groupBy = 'DATE_FORMAT(time_entries.created_at, "%Y-%m")';
            $format = '%Y-%m';
        } elseif ($diffInDays > 365) {
            $groupBy = 'YEAR(time_entries.created_at)';
            $format = '%Y';
        }

        $query = TimeEntry::selectRaw("$groupBy as periode")
            ->selectRaw('SUM(heures_reelles) as total_heures')
            ->selectRaw('COUNT(DISTINCT user_id) as nombre_employes')
            ->selectRaw('COUNT(DISTINCT dossier_id) as nombre_dossiers')
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $data = $query->groupBy('periode')
            ->orderBy('periode')
            ->get();

        return [
            'labels' => $data->pluck('periode')->map(function($date) use ($diffInDays) {
                if ($diffInDays > 90) {
                    return Carbon::parse($date . '-01')->format('M Y');
                }
                return Carbon::parse($date)->format('d/m');
            })->toArray(),
            'heures' => $data->pluck('total_heures')->map(fn($h) => round($h, 2))->toArray(),
            'employes' => $data->pluck('nombre_employes')->toArray(),
            'dossiers' => $data->pluck('nombre_dossiers')->toArray(),
        ];
    }

    /**
     * Répartition des heures par dossier
     */
    private function getRepartitionDossiers($startDate, $endDate, $userId = null)
    {
        $query = Dossier::select('dossiers.id', 'dossiers.nom', 'dossiers.reference', 'dossiers.type_dossier')
            ->join('time_entries', 'dossiers.id', '=', 'time_entries.dossier_id')
            ->whereBetween('time_entries.created_at', [$startDate, $endDate]);

        if ($userId) {
            $query->where('time_entries.user_id', $userId);
        }

        $data = $query->groupBy('dossiers.id', 'dossiers.nom', 'dossiers.reference', 'dossiers.type_dossier')
            ->selectRaw('SUM(time_entries.heures_reelles) as total_heures')
            ->selectRaw('COUNT(DISTINCT time_entries.user_id) as nombre_intervenants')
            ->orderByDesc('total_heures')
            ->limit(10)
            ->get();

        return [
            'dossiers' => $data->pluck('nom')->toArray(),
            'heures' => $data->pluck('total_heures')->map(fn($h) => round($h, 2))->toArray(),
            'intervenants' => $data->pluck('nombre_intervenants')->toArray(),
            'types' => $data->pluck('type_dossier')->toArray(),
        ];
    }

    /**
     * Statistiques des congés par type
     */
    private function getStatistiquesConges($startDate, $endDate, $userId = null)
    {
        $query = DemandeConge::join('types_conges', 'demandes_conges.type_conge_id', '=', 'types_conges.id')
            ->whereBetween('date_debut', [$startDate, $endDate]);

        if ($userId) {
            $query->where('demandes_conges.user_id', $userId);
        }

        $congesParType = $query->select('types_conges.libelle', DB::raw('count(*) as count'))
            ->groupBy('types_conges.libelle')
            ->get();

        $congesParStatut = DemandeConge::whereBetween('date_debut', [$startDate, $endDate])
            ->when($userId, function($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->select('statut', DB::raw('count(*) as count'))
            ->groupBy('statut')
            ->get();

        return [
            'types' => [
                'labels' => $congesParType->pluck('libelle')->toArray(),
                'counts' => $congesParType->pluck('count')->toArray(),
            ],
            'statuts' => [
                'labels' => $congesParStatut->pluck('statut')->map(fn($s) => ucfirst($s))->toArray(),
                'counts' => $congesParStatut->pluck('count')->toArray(),
            ],
        ];
    }

    /**
     * Performance mensuelle (comparaison mois par mois)
     */
    private function getPerformanceMensuelle($startDate, $endDate, $userId = null)
    {
        $query = TimeEntry::selectRaw('MONTH(created_at) as mois')
            ->selectRaw('YEAR(created_at) as annee')
            ->selectRaw('SUM(heures_reelles) as total_heures')
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $data = $query->groupBy('annee', 'mois')
            ->orderBy('annee')
            ->orderBy('mois')
            ->get();

        // Ajouter les données des congés mensuels
        $congesMensuels = DemandeConge::selectRaw('MONTH(date_debut) as mois')
            ->selectRaw('YEAR(date_debut) as annee')
            ->selectRaw('SUM(nombre_jours) as total_jours')
            ->whereBetween('date_debut', [$startDate, $endDate])
            ->where('statut', 'approuve')
            ->when($userId, function($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->groupBy('annee', 'mois')
            ->orderBy('annee')
            ->orderBy('mois')
            ->get();

        // Fusionner les données par mois
        $allMonths = collect();
        for ($i = 0; $i < 12; $i++) {
            $date = Carbon::create()->month($i + 1);
            $allMonths->push([
                'mois' => $date->month,
                'annee' => $date->year,
                'label' => $date->format('M Y'),
                'heures' => 0,
                'jours_conges' => 0
            ]);
        }

        // Remplir les données réelles
        foreach ($data as $item) {
            $key = $item->annee . '-' . str_pad($item->mois, 2, '0', STR_PAD_LEFT);
            $monthData = $allMonths->firstWhere('mois', $item->mois);
            if ($monthData) {
                $monthData['heures'] = round($item->total_heures, 2);
            }
        }

        foreach ($congesMensuels as $item) {
            $monthData = $allMonths->firstWhere('mois', $item->mois);
            if ($monthData) {
                $monthData['jours_conges'] = round($item->total_jours, 1);
            }
        }

        return [
            'labels' => $allMonths->pluck('label')->toArray(),
            'heures' => $allMonths->pluck('heures')->toArray(),
            'jours_conges' => $allMonths->pluck('jours_conges')->toArray(),
        ];
    }

    /**
     * Taux de validation des saisies
     */
    private function getTauxValidation($startDate, $endDate, $userId = null)
    {
        $query = DailyEntry::whereBetween('jour', [$startDate, $endDate]);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $stats = $query->select('statut', DB::raw('count(*) as count'))
            ->groupBy('statut')
            ->get();

        $total = $stats->sum('count');

        return [
            'statuts' => $stats->pluck('statut')->map(fn($s) => ucfirst($s))->toArray(),
            'counts' => $stats->pluck('count')->toArray(),
            'pourcentages' => $stats->map(function($item) use ($total) {
                return $total > 0 ? round(($item->count / $total) * 100, 1) : 0;
            })->toArray(),
        ];
    }

    /**
     * Heures par jour de la semaine
     */
    private function getHeuresParJourSemaine($startDate, $endDate, $userId = null)
    {
        $query = TimeEntry::selectRaw('DAYOFWEEK(created_at) as jour')
            ->selectRaw('SUM(heures_reelles) as total_heures')
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $data = $query->groupBy('jour')
            ->orderBy('jour')
            ->get();

        $jours = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];

        // Réorganiser pour commencer par Lundi
        $heuresParJour = array_fill(0, 7, 0);
        foreach ($data as $item) {
            $heuresParJour[$item->jour - 1] = round($item->total_heures, 2);
        }

        // Décaler pour commencer par lundi
        $lundi = array_slice($heuresParJour, 1, 6);
        $dimanche = array_slice($heuresParJour, 0, 1);
        $heuresParJour = array_merge($lundi, $dimanche);

        $joursOrdre = array_merge(array_slice($jours, 1, 6), array_slice($jours, 0, 1));

        return [
            'jours' => $joursOrdre,
            'heures' => $heuresParJour,
        ];
    }

    /**
     * Soldes de congés des employés
     */
    private function getSoldesConges($userId = null)
    {
        $query = SoldeConge::join('users', 'soldes_conges.user_id', '=', 'users.id')
            ->where('soldes_conges.annee', now()->year);

        if ($userId) {
            $query->where('soldes_conges.user_id', $userId);
        }

        $data = $query->select(
                'users.id',
                'users.prenom',
                'users.nom',
                'soldes_conges.jours_acquis',
                'soldes_conges.jours_pris',
                'soldes_conges.jours_restants',
                'soldes_conges.jours_reportes'
            )
            ->orderByDesc('soldes_conges.jours_restants')
            ->limit(10)
            ->get();

        return [
            'employes' => $data->map(function($item) {
                return [
                    'nom_complet' => $item->prenom . ' ' . $item->nom,
                    'jours_acquis' => $item->jours_acquis,
                    'jours_pris' => $item->jours_pris,
                    'jours_restants' => $item->jours_restants,
                    'jours_reportes' => $item->jours_reportes,
                    'pourcentage_pris' => $item->jours_acquis > 0
                        ? round(($item->jours_pris / $item->jours_acquis) * 100, 1)
                        : 0,
                ];
            })->toArray(),
        ];
    }

    /**
     * Activités par heure de la journée
     */
    private function getActivitesParHeure($startDate, $endDate, $userId = null)
    {
        $query = TimeEntry::selectRaw('HOUR(heure_debut) as heure')
            ->selectRaw('COUNT(*) as nombre_activites')
            ->selectRaw('SUM(heures_reelles) as total_heures')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('heure_debut');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $data = $query->groupBy('heure')
            ->orderBy('heure')
            ->get();

        // Compléter toutes les heures de la journée
        $heures = [];
        for ($i = 7; $i <= 20; $i++) { // De 7h à 20h
            $heureData = $data->firstWhere('heure', $i);
            $heures[] = [
                'heure' => sprintf('%02d:00', $i),
                'nombre_activites' => $heureData ? $heureData->nombre_activites : 0,
                'total_heures' => $heureData ? round($heureData->total_heures, 2) : 0,
            ];
        }

        return $heures;
    }

    /**
     * Liste des employés pour le filtre
     */
    public function getEmployes()
    {
        if (!auth()->user()->hasRole('admin')) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }

        $employes = User::select('id', 'prenom', 'nom', 'email')
            ->orderBy('prenom')
            ->orderBy('nom')
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'nom_complet' => $user->prenom . ' ' . $user->nom,
                    'email' => $user->email,
                ];
            });

        return response()->json($employes);
    }

    /**
     * Statistiques détaillées d'un employé
     */
    public function employeDetails($userId)
    {
        if (!auth()->user()->hasRole('admin')) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }

        $user = User::findOrFail($userId);
        $currentYear = now()->year;

        // Heures travaillées
        $heuresTotales = round(TimeEntry::where('user_id', $userId)->sum('heures_reelles'), 2);
        $heuresMois = round(TimeEntry::where('user_id', $userId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('heures_reelles'), 2);
        $heuresAnnee = round(TimeEntry::where('user_id', $userId)
            ->whereYear('created_at', now()->year)
            ->sum('heures_reelles'), 2);

        // Dossiers
        $nombreDossiers = Dossier::whereHas('timeEntries', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->count();

        // Congés
        $demandesConges = DemandeConge::where('user_id', $userId)
            ->whereYear('date_debut', $currentYear)
            ->get();

        $nombreCongesAnnee = $demandesConges->count();
        $joursCongesAnnee = $demandesConges->where('statut', 'approuve')->sum('nombre_jours');
        $congesEnAttente = $demandesConges->where('statut', 'en_attente')->count();

        // Solde de congés
        $soldeConge = SoldeConge::where('user_id', $userId)
            ->where('annee', $currentYear)
            ->first();

        $stats = [
            'user' => [
                'id' => $user->id,
                'nom_complet' => $user->prenom . ' ' . $user->nom,
                'email' => $user->email,
                'poste' => $user->poste->intitule ?? 'Non défini',
                'type_contrat' => $user->type_contrat ?? 'Non défini',
            ],
            'heures' => [
                'total' => $heuresTotales,
                'mois' => $heuresMois,
                'annee' => $heuresAnnee,
                'moyenne_mensuelle' => round($heuresAnnee / max(now()->month, 1), 2),
            ],
            'dossiers' => [
                'total' => $nombreDossiers,
                'actifs' => Dossier::whereHas('timeEntries', function($q) use ($userId) {
                    $q->where('user_id', $userId);
                })->whereIn('statut', ['ouvert', 'en_cours'])->count(),
            ],
            'conges' => [
                'total_demandes' => $nombreCongesAnnee,
                'jours_pris' => $joursCongesAnnee,
                'en_attente' => $congesEnAttente,
                'jours_acquis' => $soldeConge ? $soldeConge->jours_acquis : 0,
                'jours_restants' => $soldeConge ? $soldeConge->jours_restants : 0,
                'jours_reportes' => $soldeConge ? $soldeConge->jours_reportes : 0,
            ],
            'validation' => [
                'feuilles_validees' => DailyEntry::where('user_id', $userId)
                    ->where('statut', 'validé')
                    ->count(),
                'feuilles_en_attente' => DailyEntry::where('user_id', $userId)
                    ->where('statut', 'soumis')
                    ->count(),
            ],
        ];

        return response()->json($stats);
    }

    /**
     * Exporter les statistiques
     */
    public function export(Request $request)
    {
        if (!auth()->user()->hasRole('admin')) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }

        $type = $request->get('type', 'excel'); // excel, pdf, csv
        $periode = $request->get('periode', 'mois');

        // TODO: Implémenter l'export avec Laravel Excel ou DomPDF

        return response()->json([
            'message' => 'Export en cours de développement',
            'type' => $type,
            'periode' => $periode,
        ]);
    }
}
