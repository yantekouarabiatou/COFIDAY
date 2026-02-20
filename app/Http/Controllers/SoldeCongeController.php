<?php

namespace App\Http\Controllers;

use App\Models\SoldeConge;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class SoldeCongeController extends Controller
{
    /**
     * Affiche la liste des soldes (DataTables).
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $soldes = SoldeConge::with('user')->select('soldes_conges.*');

            return DataTables::of($soldes)
                ->addColumn('user_name', function ($solde) {
                    return $solde->user ? $solde->user->prenom . ' ' . $solde->user->nom : 'N/A';
                })
                ->addColumn('action', function ($solde) {
                    return '
                        <a href="' . route('admin.soldes.show', $solde) . '" class="btn btn-sm btn-info" title="Voir"><i class="fas fa-eye"></i></a>
                        <a href="' . route('admin.soldes.edit', $solde) . '" class="btn btn-sm btn-warning" title="Modifier"><i class="fas fa-edit"></i></a>
                        <button class="btn btn-sm btn-danger btn-delete" data-id="' . $solde->id . '" title="Supprimer"><i class="fas fa-trash"></i></button>
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
        $validator = Validator::make($request->all(), [
            'user_id'       => 'required|exists:users,id',
            'annee'         => 'required|integer|min:2000|max:' . (now()->year + 1),
            'jours_acquis'  => 'required|numeric|min:0|max:50',
            'jours_pris'    => 'required|numeric|min:0',
            'jours_reportes'=> 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        // Calcul du restant : (acquis + reportés) - pris
        $data['jours_reportes'] = $data['jours_reportes'] ?? 0;
        $data['jours_restants'] = ($data['jours_acquis'] + $data['jours_reportes']) - $data['jours_pris'];

        SoldeConge::create($data);

        return redirect()->route('admin.soldes.index')
            ->with('success', 'Solde créé avec succès.');
    }

    /**
     * Affichage d'un solde (optionnel).
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
        $validator = Validator::make($request->all(), [
            'user_id'       => 'required|exists:users,id',
            'annee'         => 'required|integer|min:2000|max:' . (now()->year + 1),
            'jours_acquis'  => 'required|numeric|min:0|max:50',
            'jours_pris'    => 'required|numeric|min:0',
            'jours_reportes'=> 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        $data['jours_reportes'] = $data['jours_reportes'] ?? 0;
        $data['jours_restants'] = ($data['jours_acquis'] + $data['jours_reportes']) - $data['jours_pris'];

        $solde->update($data);

        return redirect()->route('admin.soldes.index')
            ->with('success', 'Solde mis à jour.');
    }

    /**
     * Suppression (soft delete si le modèle le supporte, sinon hard).
     */
    public function destroy(SoldeConge $solde)
    {
        $solde->delete();
        return response()->json(['success' => true]);
    }
}
