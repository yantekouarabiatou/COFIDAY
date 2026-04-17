<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TimeEntry;
use App\Models\DemandeConge;
use App\Models\SoldeConge;
use App\Models\DemandeAttestation;
use App\Models\DemandeDemission;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function index()
    {
        // Vérifier que l'utilisateur est admin
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Accès non autorisé');
        }

        return view('pages.statistics.statglobale');
    }
    public function globalStats(Request $request)
    {
        if (!auth()->user()->hasRole('admin')) return response()->json(['message' => 'Accès non autorisé'], 403);

        $periode = $request->get('periode', 'mois');
        $dateDebut = $request->get('date_debut');
        $dateFin = $request->get('date_fin');
        $userId = $request->get('user_id');

        [$startDate, $endDate] = $this->getDateRange($periode, $dateDebut, $dateFin);

        $stats = [
            'totaux' => $this->getTotauxGlobaux($startDate, $endDate, $userId),
            'repartition_attestations' => $this->getRepartitionAttestations($startDate, $endDate, $userId),
            'evolution_demandes' => $this->getEvolutionDemandes($startDate, $endDate, $userId),
            'dernieres_demandes' => $this->getDernieresDemandes($startDate, $endDate, $userId),
        ];

        return response()->json(['stats' => $stats, 'periode' => ['debut' => $startDate->toDateString(), 'fin' => $endDate->toDateString()]]);
    }

    private function getTotauxGlobaux($startDate, $endDate, $userId = null)
    {
        // Heures
        $timeQuery = TimeEntry::whereBetween('created_at', [$startDate, $endDate]);
        if ($userId) $timeQuery->where('user_id', $userId);
        $totalHeures = round($timeQuery->sum('heures_reelles'), 2);
        $employesActifs = $userId ? 1 : User::whereHas('timeEntries', fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))->count();

        // Congés
        $congesQuery = DemandeConge::where(function ($q) use ($startDate, $endDate) { /* ... */
        });
        if ($userId) $congesQuery->where('user_id', $userId);
        $totalConges = $congesQuery->count();
        $congesEnCours = DemandeConge::where('date_debut', '<=', now())->where('date_fin', '>=', now())->where('statut', 'approuve')->when($userId, fn($q) => $q->where('user_id', $userId))->count();

        // Attestations
        $attestQuery = DemandeAttestation::whereBetween('created_at', [$startDate, $endDate]);
        if ($userId) $attestQuery->where('user_id', $userId);
        $totalAttestations = $attestQuery->count();
        $attestationsApprouvees = (clone $attestQuery)->where('statut', 'approuve')->count();

        // Certificats (démissions)
        $certifQuery = DemandeDemission::whereBetween('created_at', [$startDate, $endDate]);
        if ($userId) $certifQuery->where('user_id', $userId);
        $totalCertificats = $certifQuery->count();
        $certificatsAcceptes = (clone $certifQuery)->where('statut', 'acceptee')->count();

        return [
            'total_employes' => $userId ? 1 : User::count(),
            'employes_actifs' => $employesActifs,
            'total_heures' => $totalHeures,
            'moyenne_heures_employe' => $userId ? null : round($totalHeures / max(User::count(), 1), 2),
            'total_conges' => $totalConges,
            'conges_en_cours' => $congesEnCours,
            'total_attestations' => $totalAttestations,
            'attestations_approuvees' => $attestationsApprouvees,
            'total_certificats' => $totalCertificats,
            'certificats_acceptes' => $certificatsAcceptes,
        ];
    }

    private function getRepartitionAttestations($startDate, $endDate, $userId = null)
    {
        $query = DemandeAttestation::select('type', DB::raw('count(*) as total'))
            ->whereBetween('created_at', [$startDate, $endDate]);
        if ($userId) $query->where('user_id', $userId);
        $data = $query->groupBy('type')->get();

        $labels = $data->pluck('type')->map(fn($t) => match ($t) {
            'attestation_simple' => 'Attestation simple',
            'attestation_banque' => 'Usage bancaire',
            'attestation_ambassade' => 'Ambassade / Visa',
            'attestation_autre' => 'Format spécifique',
            default => $t,
        })->toArray();

        return ['labels' => $labels, 'counts' => $data->pluck('total')->toArray()];
    }

    private function getEvolutionDemandes($startDate, $endDate, $userId = null)
    {
        $groupBy = 'DATE(created_at)';
        $format = 'd/m';
        if ($startDate->diffInDays($endDate) > 60) {
            $groupBy = 'DATE_FORMAT(created_at, "%Y-%m")';
            $format = 'M Y';
        }

        $attestData = DemandeAttestation::selectRaw("$groupBy as periode, count(*) as total")
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->groupBy('periode')->orderBy('periode')->get();

        $certifData = DemandeDemission::selectRaw("$groupBy as periode, count(*) as total")
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->groupBy('periode')->orderBy('periode')->get();

        $periodes = $attestData->pluck('periode')->merge($certifData->pluck('periode'))->unique()->sort()->values();
        $attest = [];
        $certif = [];
        foreach ($periodes as $p) {
            $attest[] = $attestData->firstWhere('periode', $p)->total ?? 0;
            $certif[] = $certifData->firstWhere('periode', $p)->total ?? 0;
        }

        $labels = $periodes->map(fn($p) => $format === 'd/m' ? Carbon::parse($p)->format('d/m') : Carbon::parse($p . '-01')->format('M Y'))->toArray();

        return ['labels' => $labels, 'attestations' => $attest, 'certificats' => $certif];
    }

    private function getDernieresDemandes($startDate, $endDate, $userId = null)
    {
        $attestations = DemandeAttestation::with('user')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->orderByDesc('created_at')->limit(5)->get()
            ->map(fn($a) => [
                'type_label' => $a->libelle_type,
                'employe' => $a->user->prenom . ' ' . $a->user->nom,
                'date' => $a->created_at->format('d/m/Y'),
                'statut_badge' => $a->statut_badge,
                'reference' => $a->numero_reference ?? '—',
            ]);

        $certificats = DemandeDemission::with('user')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->orderByDesc('created_at')->limit(5)->get()
            ->map(fn($c) => [
                'type_label' => 'Certificat de travail',
                'employe' => $c->user->prenom . ' ' . $c->user->nom,
                'date' => $c->created_at->format('d/m/Y'),
                'statut_badge' => $c->statut_badge,
                'reference' => $c->numero_certificat ?? $c->numero_reference ?? '—',
            ]);

        return $attestations->merge($certificats)->sortByDesc('date')->take(10)->values();
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
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'nom_complet' => $user->prenom . ' ' . $user->nom,
                    'email' => $user->email,
                ];
            });

        return response()->json($employes);
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
