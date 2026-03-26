<?php

namespace App\Http\Controllers;

use App\Models\SoldeConge;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SoldeCongeController extends Controller
{
    /**
     * Affiche la liste des soldes (DataTables).
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $soldes = SoldeConge::with('user')
                ->select('soldes_conges.*')
                // Sous-requête pour le total dispo par utilisateur
                ->selectSub(function ($query) {
                    $query->from('soldes_conges as sc')
                        ->selectRaw('SUM(sc.jours_restants)')
                        ->whereColumn('sc.user_id', 'soldes_conges.user_id');
                }, 'total_dispo');  // ← CHANGEMENT ICI : total_dispo au lieu de total_restant

            return DataTables::of($soldes)
                ->addColumn('user_name', function ($solde) {
                    return $solde->user
                        ? $solde->user->prenom . ' ' . $solde->user->nom
                        : 'N/A';
                })
                ->filterColumn('user_name', function ($query, $keyword) {
                    $keyword = mb_strtolower($keyword, 'UTF-8');
                    $query->whereHas('user', function ($q) use ($keyword) {
                        $q->whereRaw('LOWER(prenom) LIKE ?', ["%{$keyword}%"])
                          ->orWhereRaw('LOWER(nom) LIKE ?', ["%{$keyword}%"])
                          ->orWhereRaw("LOWER(CONCAT(prenom, ' ', nom)) LIKE ?", ["%{$keyword}%"]);
                    });
                })
                ->addColumn('total_dispo', function ($solde) {
                    // La valeur est déjà calculée via selectSub
                    return $solde->total_dispo ?? 0;
                })
                ->addColumn('action', function ($solde) {
                    return '
                    <a href="' . route('admin.soldes.show', $solde) . '"
                       class="btn btn-sm btn-info"
                       title="Voir">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="' . route('admin.soldes.edit', $solde) . '"
                       class="btn btn-sm btn-warning"
                       title="Modifier">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button class="btn btn-sm btn-danger btn-delete"
                            data-id="' . $solde->id . '"
                            title="Supprimer">
                        <i class="fas fa-trash"></i>
                    </button>
                ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('pages.soldes.index');
    }

    /**
     * Formulaire de création.
     */
    public function create()
    {
        $users = User::orderBy('nom')->get();
        $currentYear = now()->year;
        $years = range($currentYear - 5, $currentYear + 1);
        return view('pages.soldes.create', compact('users', 'years'));
    }

    /**
     * Enregistrement d'un nouveau solde.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'       => 'required|exists:users,id',
            'annee'         => 'required|integer|min:2000|max:' . (now()->year + 1),
            'jours_acquis'  => 'required|numeric|min:0|max:50',
            'jours_pris'    => 'required|numeric|min:0',
            'jours_reportes' => 'nullable|numeric|min:0',
        ]);

        // Forcer une valeur par défaut pour jours_reportes
        $validated['jours_reportes'] = $validated['jours_reportes'] ?? 0;

        // Vérifier que les jours pris ne dépassent pas le total disponible
        $totalDisponible = $validated['jours_acquis'] + $validated['jours_reportes'];
        if ($validated['jours_pris'] > $totalDisponible) {
            return back()->withErrors([
                'jours_pris' => "Les jours pris ($validated[jours_pris]) ne peuvent pas dépasser le total des jours acquis et reportés ($totalDisponible)."
            ])->withInput();
        }

        $validated['jours_restants'] = $totalDisponible - $validated['jours_pris'];

        SoldeConge::create($validated);

        return redirect()->route('admin.soldes.index')
            ->with('success', 'Solde créé avec succès.');
    }

    /**
     * Affichage d'un solde.
     */
    public function show(SoldeConge $solde)
    {
        $solde->load('user');
        return view('pages.soldes.show', compact('solde'));
    }

    /**
     * Formulaire d'édition.
     */
    public function edit(SoldeConge $solde)
    {
        $users = User::orderBy('nom')->get();
        $currentYear = now()->year;
        $years = range($currentYear - 5, $currentYear + 1);
        return view('pages.soldes.edit', compact('solde', 'users', 'years'));
    }

    /**
     * Mise à jour du solde.
     */
    public function update(Request $request, SoldeConge $solde)
    {
        $validated = $request->validate([
            'user_id'       => 'required|exists:users,id',
            'annee'         => 'required|integer|min:2000|max:' . (now()->year + 1),
            'jours_acquis'  => 'required|numeric|min:0|max:50',
            'jours_pris'    => 'required|numeric|min:0',
            'jours_reportes' => 'nullable|numeric|min:0',
        ]);

        $validated['jours_reportes'] = $validated['jours_reportes'] ?? 0;

        $totalDisponible = $validated['jours_acquis'] + $validated['jours_reportes'];
        if ($validated['jours_pris'] > $totalDisponible) {
            return back()->withErrors([
                'jours_pris' => "Les jours pris ($validated[jours_pris]) ne peuvent pas dépasser le total des jours acquis et reportés ($totalDisponible)."
            ])->withInput();
        }

        $validated['jours_restants'] = $totalDisponible - $validated['jours_pris'];

        $solde->update($validated);

        return redirect()->route('admin.soldes.index')
            ->with('success', 'Solde mis à jour.');
    }

    /**
     * Suppression (soft delete si le modèle utilise SoftDeletes).
     * Optionnel : vérifier l'intégrité référentielle.
     */
    public function destroy(SoldeConge $solde)
    {
        // Si vous souhaitez empêcher la suppression si le solde est utilisé
        // (par exemple dans des demandes de congé), décommentez ces lignes :
        /*
        if ($solde->demandes()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Ce solde est référencé par des demandes de congé et ne peut pas être supprimé.'
            ], 422);
        }
        */

        $solde->delete();

        return response()->json(['success' => true]);
    }
}
