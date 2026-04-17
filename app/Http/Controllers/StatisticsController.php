<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\DemandeConge;
use App\Models\DemandeAttestation;
use App\Models\DemandeDemission;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StatisticsController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Accès non autorisé');
        }
        return view('pages.statistics.statglobale');
    }

    public function globalStats(Request $request)
    {
        try {
            if (!auth()->user()->hasRole('admin')) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

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

            return response()->json([
                'stats' => $stats,
                'periode' => [
                    'debut' => $startDate->toDateString(),
                    'fin' => $endDate->toDateString()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur stats globales : ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Erreur interne du serveur'], 500);
        }
    }

    private function getTotauxGlobaux($startDate, $endDate, $userId = null)
    {
        // Employés actifs sur la période (ayant au moins une demande)
        $employesActifsQuery = User::whereHas('attestations', fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->orWhereHas('certificats', fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->orWhereHas('conges', fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]));
        if ($userId) {
            $employesActifsQuery->where('id', $userId);
        }
        $employesActifs = $employesActifsQuery->distinct()->count('users.id');

        // Congés
        $congesQuery = DemandeConge::whereBetween('created_at', [$startDate, $endDate]);
        if ($userId) $congesQuery->where('user_id', $userId);
        $totalConges = $congesQuery->count();

        $congesEnCours = DemandeConge::where('date_debut', '<=', now())
            ->where('date_fin', '>=', now())
            ->where('statut', 'approuve')
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->count();

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

        $labels = $data->pluck('type')->map(fn($t) => $this->getAttestationTypeLabel($t))->toArray();

        return ['labels' => $labels, 'counts' => $data->pluck('total')->toArray()];
    }

    private function getAttestationTypeLabel($type)
    {
        return match ($type) {
            'attestation_simple' => 'Attestation simple',
            'attestation_banque' => 'Usage bancaire',
            'attestation_ambassade' => 'Ambassade / Visa',
            'attestation_autre' => 'Format spécifique',
            default => $type,
        };
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
                'type_label' => $this->getAttestationTypeLabel($a->type),
                'employe' => $a->user->prenom . ' ' . $a->user->nom,
                'date' => $a->created_at->format('d/m/Y'),
                'statut_badge' => $this->getStatusBadge($a->statut),
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
                'statut_badge' => $this->getStatusBadge($c->statut),
                'reference' => $c->numero_certificat ?? $c->numero_reference ?? '—',
            ]);

        return $attestations->merge($certificats)->sortByDesc('date')->take(10)->values();
    }

    private function getStatusBadge($statut)
    {
        return match ($statut) {
            'approuve'   => '<span class="badge-approuve"><i class="fas fa-check-circle"></i>Approuvé</span>',
            'en_attente' => '<span class="badge-en_attente"><i class="fas fa-clock"></i>En attente</span>',
            'refuse'     => '<span class="badge-refuse"><i class="fas fa-times-circle"></i>Refusé</span>',
            'acceptee'   => '<span class="badge-acceptee"><i class="fas fa-check"></i>Accepté</span>',
            default      => '<span class="badge-default">' . e($statut) . '</span>',
        };
    }

    public function getEmployes()
    {
        try {
            if (!auth()->user()->hasRole('admin')) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            $employes = User::select('id', 'prenom', 'nom', 'email')
                ->orderBy('prenom')
                ->orderBy('nom')
                ->get()
                ->map(fn($user) => [
                    'id' => $user->id,
                    'nom_complet' => $user->prenom . ' ' . $user->nom,
                    'email' => $user->email,
                ]);

            return response()->json($employes);
        } catch (\Exception $e) {
            Log::error('Erreur getEmployes : ' . $e->getMessage());
            return response()->json(['message' => 'Erreur interne'], 500);
        }
    }

    public function export(Request $request)
    {
        if (!auth()->user()->hasRole('admin')) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }
        return response()->json(['message' => 'Export en développement'], 200);
    }

    private function getDateRange($periode, $dateDebut = null, $dateFin = null)
    {
        if ($periode === 'personnalise' && $dateDebut && $dateFin) {
            return [Carbon::parse($dateDebut)->startOfDay(), Carbon::parse($dateFin)->endOfDay()];
        }

        $now = Carbon::now();
        switch ($periode) {
            case 'jour':    return [$now->copy()->startOfDay(), $now->copy()->endOfDay()];
            case 'semaine': return [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()];
            case 'annee':   return [$now->copy()->startOfYear(), $now->copy()->endOfYear()];
            case 'mois':
            default:        return [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()];
        }
    }
}
