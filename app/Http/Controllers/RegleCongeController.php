<?php

namespace App\Http\Controllers;

use App\Models\RegleConge;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class RegleCongeController extends Controller
{
    public function edit()
    {
        $regles = RegleConge::getRegles();
        return view('admin.regles-conges.edit', compact('regles'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'jours_par_mois' => 'required|numeric|min:0|max:5',
            'limite_report' => 'nullable|integer|min:0',
            'preavis_minimum' => 'required|integer|min:0',
            'delai_annulation' => 'required|integer|min:0',
            'couleur_calendrier' => 'required|string',
        ]);

        $regles = RegleConge::getRegles();

        // Décoder les tableaux JSON s'ils existent
        $data = $request->all();

        if ($request->has('jours_feries')) {
            $data['jours_feries'] = json_encode($request->jours_feries);
        }

        if ($request->has('periodes_bloquees')) {
            $data['periodes_bloquees'] = json_encode($request->periodes_bloquees);
        }

        $regles->update($data);

        Alert::success('Succès', 'Les règles de congés ont été mises à jour.');
        return back();
    }

    public function getJoursAcquis()
    {
        $regles = RegleConge::getRegles();
        return response()->json([
            'jours_par_mois' => $regles->jours_par_mois,
            'jours_annuels' => $regles->calculerJoursAcquisAnnuels(),
            'report_autorise' => $regles->report_autorise,
            'limite_report' => $regles->limite_report,
        ]);
    }
}
