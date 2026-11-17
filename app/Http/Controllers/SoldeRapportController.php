<?php

namespace App\Http\Controllers;

use App\Exports\SoldesRapportExport;
use App\Exports\SoldesRapportSheet;
use App\Models\SoldeConge;
use App\Models\User;
use App\Services\RapportSoldeGraphics;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Rapport graphique des soldes de congés (admin & directeur général uniquement).
 */
class SoldeRapportController extends Controller
{
    public function index(Request $request)
    {
        $annees = $this->anneesDisponibles();
        $annee  = $this->parseAnnee($request->input('annee'), $annees->first());
        $users  = User::orderBy('nom')->orderBy('prenom')->get(['id', 'nom', 'prenom']);

        $soldes = $this->soldesAnnee($annee);

        // Évolution annuelle globale (tous utilisateurs)
        $evolution = SoldeConge::selectRaw('annee,
                SUM(jours_acquis) as acquis,
                SUM(COALESCE(jours_reportes,0)) as reportes,
                SUM(jours_pris) as pris,
                SUM(jours_restants) as restants')
            ->groupBy('annee')
            ->orderBy('annee')
            ->get();

        // Historique par utilisateur (pour le graphique d'un utilisateur précis, côté JS)
        $historiques = SoldeConge::orderBy('annee')
            ->get(['user_id', 'annee', 'jours_acquis', 'jours_reportes', 'jours_pris', 'jours_restants'])
            ->groupBy('user_id')
            ->map(fn ($rows) => $rows->map(fn ($s) => [
                'annee'    => $s->annee,
                'acquis'   => (float) $s->jours_acquis,
                'reportes' => (float) ($s->jours_reportes ?? 0),
                'pris'     => (float) $s->jours_pris,
                'restants' => (float) $s->jours_restants,
            ])->values());

        $totaux = $this->totaux($soldes);

        $chartSoldes = $soldes->map(fn ($s) => [
            'user_id'  => $s->user_id,
            'nom'      => trim($s->user->prenom . ' ' . $s->user->nom),
            'acquis'   => (float) $s->jours_acquis,
            'reportes' => (float) ($s->jours_reportes ?? 0),
            'pris'     => (float) $s->jours_pris,
            'restants' => (float) $s->jours_restants,
        ])->values();

        return view('pages.soldes-rapport.index', compact(
            'annees', 'annee', 'users', 'soldes', 'evolution', 'historiques', 'totaux', 'chartSoldes'
        ));
    }

    /**
     * Export PDF / Excel.
     *  - avec user_id : historique de toutes les années de cet utilisateur
     *  - sans user_id : tous les utilisateurs pour l'année choisie
     */
    public function export(Request $request, string $format)
    {
        abort_unless(in_array($format, ['pdf', 'excel'], true), 404);

        $request->validate([
            'annee'   => ['nullable', 'regex:/^(toutes|\d{4})$/'],
            'user_id' => 'nullable|exists:users,id',
        ]);

        $annee = $this->parseAnnee($request->input('annee'), now()->year);
        $user  = $request->filled('user_id') ? User::findOrFail($request->user_id) : null;

        if ($user) {
            $soldes = SoldeConge::with('user')->where('user_id', $user->id)->orderBy('annee')->get();
            $titre  = 'Historique des soldes de congés - ' . $user->prenom . ' ' . $user->nom;
            $nom    = 'soldes_' . Str::slug($user->prenom . '_' . $user->nom, '_');
        } else {
            $soldes = $this->soldesAnnee($annee);
            $titre  = $annee === 'toutes'
                ? 'Soldes de congés de tous les utilisateurs - Toutes les années (cumul)'
                : "Soldes de congés de tous les utilisateurs - Année {$annee}";
            $nom    = "soldes_tous_{$annee}";
        }

        $totaux = $this->totaux($soldes);
        $mode   = $user ? 'user' : 'all';

        if ($format === 'excel') {
            return Excel::download(new SoldesRapportExport($this->feuillesExcel($soldes, $titre, $user, $annee)), "{$nom}.xlsx");
        }

        $logo      = RapportSoldeGraphics::logoBase64();
        $diagramme = RapportSoldeGraphics::diagramme($soldes->map(fn ($s) => [
            'label'    => $user ? (string) $s->annee : trim($s->user->prenom . ' ' . $s->user->nom),
            'pris'     => (float) $s->jours_pris,
            'restants' => (float) $s->jours_restants,
        ]));

        $pdf = Pdf::loadView('pages.soldes-rapport.pdf', compact(
            'soldes', 'titre', 'mode', 'totaux', 'user', 'annee', 'logo', 'diagramme'
        ))->setPaper('a4', 'landscape');

        return $pdf->download("{$nom}.pdf");
    }

    /**
     * Feuilles Excel :
     *  - un utilisateur : son historique par année
     *  - tous : synthèse de la sélection + détail de chaque utilisateur pour chaque année
     */
    private function feuillesExcel(Collection $soldes, string $titre, ?User $user, int|string $annee): array
    {
        $chiffres = fn ($s) => [
            'acquis'   => (float) $s->jours_acquis,
            'reportes' => (float) ($s->jours_reportes ?? 0),
            'pris'     => (float) $s->jours_pris,
            'restants' => (float) $s->jours_restants,
        ];
        $nomComplet = fn ($s) => trim($s->user->prenom . ' ' . $s->user->nom);

        if ($user) {
            return [new SoldesRapportSheet(
                'Historique', $titre, ['Année'],
                $soldes->map(fn ($s) => ['libelles' => [(string) $s->annee]] + $chiffres($s))
            )];
        }

        $synthese = new SoldesRapportSheet(
            $annee === 'toutes' ? 'Synthèse (cumul)' : "Synthèse {$annee}",
            $titre,
            $annee === 'toutes' ? ['Collaborateur', 'Période'] : ['Collaborateur'],
            $soldes->map(fn ($s) => [
                'libelles' => $annee === 'toutes' ? [$nomComplet($s), (string) $s->annee] : [$nomComplet($s)],
            ] + $chiffres($s))
        );

        $detail = SoldeConge::with('user:id,nom,prenom')
            ->whereHas('user')
            ->get()
            ->sortBy(fn ($s) => mb_strtolower($s->user->nom . ' ' . $s->user->prenom) . '|' . $s->annee)
            ->values();

        return [
            $synthese,
            new SoldesRapportSheet(
                'Détail par année',
                'Soldes de congés de tous les utilisateurs - détail par année',
                ['Collaborateur', 'Année'],
                $detail->map(fn ($s) => ['libelles' => [$nomComplet($s), (string) $s->annee]] + $chiffres($s))
            ),
        ];
    }

    private function anneesDisponibles(): Collection
    {
        $annees = SoldeConge::select('annee')->distinct()->orderByDesc('annee')->pluck('annee');

        return $annees->isEmpty() ? collect([now()->year]) : $annees;
    }

    /**
     * 'toutes' ou une année (int).
     */
    private function parseAnnee($valeur, $defaut): int|string
    {
        if ($valeur === 'toutes') {
            return 'toutes';
        }

        return (int) ($valeur ?: ($defaut ?? now()->year));
    }

    /**
     * Soldes d'une année, ou cumul par utilisateur sur toutes les années.
     */
    private function soldesAnnee(int|string $annee): Collection
    {
        if ($annee === 'toutes') {
            return SoldeConge::with('user:id,nom,prenom')
                ->whereHas('user')
                ->get()
                ->groupBy('user_id')
                ->map(function ($rows) {
                    $annees = $rows->pluck('annee')->sort()->values();

                    return (object) [
                        'user_id'        => $rows->first()->user_id,
                        'user'           => $rows->first()->user,
                        'annee'          => $annees->first() == $annees->last()
                            ? (string) $annees->first()
                            : $annees->first() . '–' . $annees->last(),
                        'jours_acquis'   => (float) $rows->sum('jours_acquis'),
                        'jours_reportes' => (float) $rows->sum(fn ($s) => $s->jours_reportes ?? 0),
                        'jours_pris'     => (float) $rows->sum('jours_pris'),
                        'jours_restants' => (float) $rows->sum('jours_restants'),
                    ];
                })
                ->sortBy(fn ($s) => mb_strtolower($s->user->nom . ' ' . $s->user->prenom))
                ->values();
        }

        return SoldeConge::with('user:id,nom,prenom')
            ->where('annee', $annee)
            ->whereHas('user')
            ->get()
            ->sortBy(fn ($s) => mb_strtolower($s->user->nom . ' ' . $s->user->prenom))
            ->values();
    }

    private function totaux(Collection $soldes): array
    {
        return [
            'acquis'   => (float) $soldes->sum('jours_acquis'),
            'reportes' => (float) $soldes->sum(fn ($s) => $s->jours_reportes ?? 0),
            'pris'     => (float) $soldes->sum('jours_pris'),
            'restants' => (float) $soldes->sum('jours_restants'),
        ];
    }
}
