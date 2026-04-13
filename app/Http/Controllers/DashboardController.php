<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\DemandeAttestation;
use App\Models\DemandeConge;
use App\Models\DemandeDemission;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        try {
            $user = auth()->user();

            // ── Totaux personnels ──────────────────────────────────────────────
            $totals = [
                // Congés
                'mes_conges_en_cours' => DemandeConge::where('user_id', $user->id)
                    ->where('date_debut', '<=', now())
                    ->where('date_fin', '>=', now())
                    ->where('statut', 'approuve')
                    ->count(),

                'conges_en_attente' => DemandeConge::where('user_id', $user->id)
                    ->where('statut', 'en_attente')
                    ->count(),

                // Attestations
                'attestations_en_attente' => DemandeAttestation::where('user_id', $user->id)
                    ->where('statut', 'en_attente')
                    ->count(),

                'attestations_approuvees_mois' => DemandeAttestation::where('user_id', $user->id)
                    ->where('statut', 'approuve')
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),

                'attestations_totales' => DemandeAttestation::where('user_id', $user->id)->count(),

                // Certificats (démissions acceptées avec certificat généré)
                'certificats_generes' => DemandeDemission::where('user_id', $user->id)
                    ->where('certificat_genere', true)
                    ->count(),

                'demission_en_attente' => DemandeDemission::where('user_id', $user->id)
                    ->where('statut', 'en_attente')
                    ->count(),
            ];

            // ── Statistiques hebdomadaires (7 derniers jours) ─────────────────
            $weekStart = now()->subDays(7);
            $weeklyStats = [
                'conges'       => DemandeConge::where('user_id', $user->id)
                    ->where('created_at', '>=', $weekStart)
                    ->count(),
                'attestations' => DemandeAttestation::where('user_id', $user->id)
                    ->where('created_at', '>=', $weekStart)
                    ->count(),
            ];

            // ── Statistiques du mois précédent (pour calcul d'évolution) ──────
            $lastMonthStart = now()->subMonth()->startOfMonth();
            $lastMonthEnd   = now()->subMonth()->endOfMonth();
            $lastMonthStats = [
                'conges'       => DemandeConge::where('user_id', $user->id)
                    ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
                    ->count(),
                'attestations' => DemandeAttestation::where('user_id', $user->id)
                    ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
                    ->count(),
            ];

            // ── Statistiques du mois en cours ─────────────────────────────────
            $monthlyStats = [
                'conges'       => DemandeConge::where('user_id', $user->id)
                    ->whereMonth('date_debut', now()->month)
                    ->whereYear('date_debut', now()->year)
                    ->count(),
                'attestations' => DemandeAttestation::where('user_id', $user->id)
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
                'certificats'  => DemandeDemission::where('user_id', $user->id)
                    ->where('certificat_genere', true)
                    ->whereMonth('date_generation_certificat', now()->month)
                    ->whereYear('date_generation_certificat', now()->year)
                    ->count(),
            ];

            // ── Pourcentages d'évolution ───────────────────────────────────────
            $percentages = [
                'conges'       => $this->calculatePercentage($monthlyStats['conges'],       $lastMonthStats['conges']),
                'attestations' => $this->calculatePercentage($monthlyStats['attestations'], $lastMonthStats['attestations']),
            ];

            // ── Attestations sur les 30 derniers jours ─────────────────────────
            $last30daysAttestations = collect();
            for ($i = 29; $i >= 0; $i--) {
                $date      = now()->subDays($i);
                $dateLabel = $date->format('d/m');

                $attestations = (int) DemandeAttestation::where('user_id', $user->id)
                    ->whereDate('created_at', $date)
                    ->count();

                $last30daysAttestations->put($dateLabel, ['attestations' => $attestations]);
            }

            // ── Congés par type (année en cours) ──────────────────────────────
            $mesCongesParType = DemandeConge::where('user_id', $user->id)
                ->whereYear('date_debut', now()->year)
                ->join('types_conges', 'demandes_conges.type_conge_id', '=', 'types_conges.id')
                ->select('types_conges.libelle as type_conge', DB::raw('count(*) as count'))
                ->groupBy('types_conges.id', 'types_conges.libelle')
                ->get();

            // ── Attestations par type (année en cours) ─────────────────────────
            $mesAttestationsParType = DemandeAttestation::where('user_id', $user->id)
                ->whereYear('created_at', now()->year)
                ->select('type', DB::raw('count(*) as count'))
                ->groupBy('type')
                ->get();

            // ── Congés à venir (prochains 30 jours) ───────────────────────────
            $mesCongesAVenir = DemandeConge::where('user_id', $user->id)
                ->where('date_debut', '>', now())
                ->where('date_debut', '<=', now()->addDays(30))
                ->where('statut', 'approuve')
                ->with('typeConge')
                ->orderBy('date_debut')
                ->get()
                ->map(fn($conge) => [
                    'type'  => $conge->typeConge->libelle ?? 'N/A',
                    'debut' => Carbon::parse($conge->date_debut)->format('d/m/Y'),
                    'fin'   => Carbon::parse($conge->date_fin)->format('d/m/Y'),
                    'jours' => $conge->nombre_jours,
                ]);

            // ── Demandes récentes (toutes natures) ────────────────────────────
            $demandes = collect();

            // Attestations récentes
            DemandeAttestation::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->each(function ($d) use (&$demandes) {
                    $demandes->push([
                        'date'   => Carbon::parse($d->created_at)->format('d/m/Y'),
                        'nature' => $d->libelleType,
                        'type'   => 'attestation',
                        'statut' => $d->statut,
                        'ref'    => $d->numero_reference ?? '—',
                    ]);
                });

            // Congés récents
            DemandeConge::where('user_id', $user->id)
                ->with('typeConge')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->each(function ($d) use (&$demandes) {
                    $demandes->push([
                        'date'   => Carbon::parse($d->created_at)->format('d/m/Y'),
                        'nature' => $d->typeConge->libelle ?? 'Congé',
                        'type'   => 'conge',
                        'statut' => $d->statut,
                        'ref'    => '—',
                    ]);
                });

            // Certificats/démissions récents
            DemandeDemission::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get()
                ->each(function ($d) use (&$demandes) {
                    $demandes->push([
                        'date'   => Carbon::parse($d->created_at)->format('d/m/Y'),
                        'nature' => 'Demande de démission',
                        'type'   => 'demission',
                        'statut' => $d->statut,
                        'ref'    => $d->numero_reference ?? '—',
                    ]);
                });

            // Trier par date décroissante, garder les 7 plus récentes
            $demandesRecentes = $demandes
                ->sortByDesc('date')
                ->values()
                ->take(7);

            return response()->json([
                'user' => [
                    'name'  => $user->prenom . ' ' . $user->nom,
                    'email' => $user->email,
                ],
                'totals'      => $totals,
                'weekly'      => $weeklyStats,
                'monthly'     => $monthlyStats,
                'percentages' => $percentages,
                'last30daysAttestations' => [
                    'dates'        => $last30daysAttestations->keys()->toArray(),
                    'attestations' => $last30daysAttestations->pluck('attestations')->toArray(),
                ],
                'mesCongesParType' => [
                    'types'  => $mesCongesParType->pluck('type_conge')->toArray(),
                    'counts' => $mesCongesParType->pluck('count')->toArray(),
                ],
                'mesAttestationsParType' => [
                    'types'  => $mesAttestationsParType->pluck('type')->map(fn($t) => match ($t) {
                        'attestation_simple'    => 'Simple',
                        'attestation_banque'    => 'Banque',
                        'attestation_ambassade' => 'Ambassade',
                        'attestation_autre'     => 'Autre',
                        default                 => ucfirst($t),
                    })->toArray(),
                    'counts' => $mesAttestationsParType->pluck('count')->toArray(),
                ],
                'mesCongesAVenir'   => $mesCongesAVenir,
                'demandesRecentes'  => $demandesRecentes,
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur dashboard data: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'error'   => true,
                'message' => 'Erreur lors du chargement des données: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculer le pourcentage d'évolution entre deux valeurs
     */
    private function calculatePercentage($current, $previous): string
    {
        if ($previous == 0) {
            return $current > 0 ? '+100' : '0';
        }

        $percentage = (($current - $previous) / $previous) * 100;
        $sign = $percentage >= 0 ? '+' : '';

        return $sign . number_format($percentage, 0);
    }

    /**
     * Mes congés détaillés
     */
    public function mesConges()
    {
        try {
            $user = auth()->user();

            $congesEnCours = DemandeConge::where('user_id', $user->id)
                ->where('date_debut', '<=', now())
                ->where('date_fin', '>=', now())
                ->where('statut', 'approuve')
                ->with('typeConge')
                ->get()
                ->map(fn($c) => [
                    'id'    => $c->id,
                    'type'  => $c->typeConge->libelle ?? 'N/A',
                    'debut' => Carbon::parse($c->date_debut)->format('d/m/Y'),
                    'fin'   => Carbon::parse($c->date_fin)->format('d/m/Y'),
                ]);

            $congesAVenir = DemandeConge::where('user_id', $user->id)
                ->where('date_debut', '>', now())
                ->where('date_debut', '<=', now()->addDays(90))
                ->where('statut', 'approuve')
                ->with('typeConge')
                ->orderBy('date_debut')
                ->get()
                ->map(fn($c) => [
                    'id'    => $c->id,
                    'type'  => $c->typeConge->libelle ?? 'N/A',
                    'debut' => Carbon::parse($c->date_debut)->format('d/m/Y'),
                    'fin'   => Carbon::parse($c->date_fin)->format('d/m/Y'),
                    'jours' => $c->nombre_jours,
                ]);

            $congesParType = DemandeConge::where('user_id', $user->id)
                ->whereYear('date_debut', now()->year)
                ->join('types_conges', 'demandes_conges.type_conge_id', '=', 'types_conges.id')
                ->select('types_conges.libelle as type_conge', DB::raw('count(*) as count'))
                ->groupBy('types_conges.id', 'types_conges.libelle')
                ->get();

            $totalJoursConges = DemandeConge::where('user_id', $user->id)
                ->whereYear('date_debut', now()->year)
                ->where('statut', 'approuve')
                ->sum('nombre_jours');

            return response()->json([
                'congesEnCours'    => $congesEnCours,
                'congesAVenir'     => $congesAVenir,
                'congesParType'    => [
                    'types'  => $congesParType->pluck('type_conge')->toArray(),
                    'counts' => $congesParType->pluck('count')->toArray(),
                ],
                'totalJoursConges' => $totalJoursConges,
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur mesConges: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors du chargement des congés'], 500);
        }
    }

    /**
     * Exporter mes statistiques
     */
    public function export(Request $request)
    {
        $user    = auth()->user();
        $type    = $request->get('type', 'excel');
        $periode = $request->get('periode', 'mois');

        return response()->json([
            'message' => 'Export en cours de développement',
            'type'    => $type,
            'periode' => $periode,
            'user_id' => $user->id,
        ]);
    }
}
