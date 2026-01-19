<?php

namespace App\Http\Controllers;

use App\Models\RegleConge;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class RegleCongeController extends Controller
{
    
    /**
     * Afficher les règles de congés
     */
    public function index()
    {
        $regles = RegleConge::getRegles();

        return view('pages.regles-conges.index', compact('regles'));
    }

    /**
     * Formulaire de création
     * (inutile en vrai, mais conservé pour REST)
     */
    public function create()
    {
        $regles = RegleConge::getRegles();

        return view('admin.regles-conges.create', compact('regles'));
    }

    public function store(Request $request)
    {
        return $this->saveOrUpdate($request);
    }

    /**
     * Afficher une règle (singleton)
     */
    public function show(RegleConge $regle)
    {
        return view('admin.regles-conges.show', compact('regle'));
    }


    public function edit()
    {
        $regles = RegleConge::getRegles();
        return view('pages.regles-conges.edit', compact('regles'));
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

    /**
     * Suppression (optionnelle – généralement inutile pour un singleton)
     */
    public function destroy(RegleConge $regle)
    {
        $regle->delete();

        Alert::success('Succès', 'Les règles de congés ont été supprimées.');
        return redirect()->route('admin.regles-conges.index');
    }

    /**
     * ======================
     * API – Jours acquis
     * ======================
     */
    // public function getJoursAcquis()
    // {
    //     $regles = RegleConge::getRegles();

    //     return response()->json([
    //         'jours_par_mois' => $regles->jours_par_mois,
    //         'jours_annuels'  => $regles->calculerJoursAcquisAnnuels(),
    //         'formatted' => [
    //             'mensuel' => $regles->getJoursParMoisFormatted(),
    //             'annuel'  => $regles->getJoursAcquisAnnuelsFormatted(),
    //         ]
    //     ]);
    // }

    /**
     * ======================
     * Méthode centrale
     * ======================
     */
    private function saveOrUpdate(Request $request, RegleConge $regle = null)
    {
        $validator = Validator::make($request->all(), [
            'jours_par_mois'       => 'required|numeric|min:0',
            'report_autorise'      => 'boolean',
            'limite_report'        => 'nullable|integer|min:0',
            'validation_multiple'  => 'boolean',
            'jours_feries'         => 'nullable|array',
            'periodes_bloquees'    => 'nullable|array',
            'preavis_minimum'      => 'required|integer|min:0',
            'delai_annulation'     => 'required|integer|min:0',
            'couleur_calendrier'   => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // Normalisation des booléens
        $data['report_autorise'] = $request->boolean('report_autorise');
        $data['validation_multiple'] = $request->boolean('validation_multiple');

        // Singleton
        $regle = $regle ?? RegleConge::getRegles();
        $regle->mettreAJourRegles($data);

        Alert::success('Succès', 'Les règles de congés ont été mises à jour.');

        return redirect()->route('admin.regles-conges.index');
    }
}
