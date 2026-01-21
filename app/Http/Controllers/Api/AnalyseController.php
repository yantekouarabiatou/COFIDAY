<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AnalyseController extends Controller
{
   // Dans votre CongeController.php
private function estJourOuvrable($date)
{
    $date = Carbon::parse($date);
    
    // Vérifier si c'est un week-end
    if ($date->isWeekend()) {
        return false;
    }
    
    // Vérifier si c'est un jour férié
    $regles = RegleConge::first();
    if ($regles && $regles->jours_feries) {
        $joursFeries = json_decode($regles->jours_feries, true);
        if (is_array($joursFeries)) {
            $dateStr = $date->format('m-d'); // Format MM-JJ
            foreach ($joursFeries as $jour) {
                if (isset($jour['date']) && $jour['date'] === $dateStr) {
                    return false;
                }
            }
        }
    }
    
    return true;
}

private function estPeriodeBloquee($date)
{
    $regles = RegleConge::first();
    if (!$regles || !$regles->periodes_bloquees) {
        return false;
    }
    
    $periodesBloquees = json_decode($regles->periodes_bloquees, true);
    if (!is_array($periodesBloquees)) {
        return false;
    }
    
    $date = Carbon::parse($date);
    
    foreach ($periodesBloquees as $periode) {
        if (isset($periode['debut']) && isset($periode['fin'])) {
            $debut = Carbon::parse($periode['debut']);
            $fin = Carbon::parse($periode['fin']);
            
            if ($date->between($debut, $fin)) {
                return true;
            }
        }
    }
    
    return false;
}

private function verifierJoursAutorises($dateDebut, $dateFin)
{
    $joursNonOuvrables = [];
    $date = Carbon::parse($dateDebut);
    $fin = Carbon::parse($dateFin);
    
    while ($date->lte($fin)) {
        if (!$this->estJourOuvrable($date)) {
            $joursNonOuvrables[] = $date->format('d/m/Y');
        } elseif ($this->estPeriodeBloquee($date)) {
            $joursNonOuvrables[] = $date->format('d/m/Y') . ' (période bloquée)';
        }
        $date->addDay();
    }
    
    return $joursNonOuvrables;
}

// Modifiez la méthode store()
public function store(Request $request)
{
    DB::beginTransaction();

    try {
        $user = Auth::user();
        $anneeCourante = now()->year;

        // Validation
        $validated = $request->validate([
            'type_conge_id' => 'required|exists:types_conges,id',
            'date_debut' => 'required|date|after_or_equal:today',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'motif' => 'required|string|max:1000',
            'superieur_hierarchique_id' => 'required|exists:users,id',
        ]);

        $dateDebut = Carbon::parse($request->date_debut);
        $dateFin = Carbon::parse($request->date_fin);
        
        // Vérifier les jours non ouvrables
        $joursNonOuvrables = $this->verifierJoursAutorises($dateDebut, $dateFin);
        
        if (!empty($joursNonOuvrables)) {
            $message = "Les congés ne peuvent pas être pris sur les jours suivants :<br>";
            $message .= implode("<br>", array_unique($joursNonOuvrables));
            Alert::error('Erreur', $message);
            return back()->withInput();
        }

        // Calculer le nombre de jours ouvrés
        $nombreJours = $this->calculerJoursOuvres($dateDebut, $dateFin);

        // Le reste du code reste inchangé...
        $typeConge = TypeConge::findOrFail($request->type_conge_id);

        if ($typeConge->nombre_jours_max && $nombreJours > $typeConge->nombre_jours_max) {
            Alert::error('Erreur', "Ce type de congé ne peut pas dépasser {$typeConge->nombre_jours_max} jours.");
            return back()->withInput();
        }

        // Continuer avec le reste de votre méthode store...
        // ...
    } catch (\Exception $e) {
        DB::rollBack();
        Alert::error('Erreur', 'Une erreur est survenue lors de la soumission de la demande.');
        return back()->withInput();
    }
}

// Modifiez également la méthode update() de la même façon
public function update(Request $request, DemandeConge $demande)
{
    DB::beginTransaction();

    try {
        $user = Auth::user();
        $anneeCourante = now()->year;

        // Sécurité
        if ($demande->user_id !== $user->id) {
            abort(403, 'Accès non autorisé');
        }

        // Empêcher la modification si la demande n'est plus en attente
        if ($demande->statut !== 'en_attente') {
            Alert::warning('Information', 'Vous ne pouvez pas modifier une demande déjà traitée.');
            return redirect()->route('conges.show', $demande);
        }

        // Validation
        $validated = $request->validate([
            'type_conge_id' => 'required|exists:types_conges,id',
            'date_debut' => 'required|date|after_or_equal:today',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'motif' => 'required|string|max:1000',
            'superieur_hierarchique_id' => 'required|exists:users,id',
        ]);

        $dateDebut = Carbon::parse($request->date_debut);
        $dateFin = Carbon::parse($request->date_fin);
        
        // Vérifier les jours non ouvrables
        $joursNonOuvrables = $this->verifierJoursAutorises($dateDebut, $dateFin);
        
        if (!empty($joursNonOuvrables)) {
            $message = "Les congés ne peuvent pas être pris sur les jours suivants :<br>";
            $message .= implode("<br>", array_unique($joursNonOuvrables));
            Alert::error('Erreur', $message);
            return back()->withInput();
        }

        // Le reste du code reste inchangé...
        // ...
    } catch (\Exception $e) {
        DB::rollBack();
        Alert::error('Erreur', 'Une erreur est survenue lors de la modification de la demande.');
        return back()->withInput();
    }
}
}