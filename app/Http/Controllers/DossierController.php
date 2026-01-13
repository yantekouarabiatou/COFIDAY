<?php

namespace App\Http\Controllers;

use App\Models\Dossier;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\DataTables\DossiersDataTable;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
class DossierController extends Controller
{
    public function index(DossiersDataTable $dataTable)
    {
        // Utilise les scopes du modèle → tout en SQL, rapide et sans erreur
        $totalDossiers      = Dossier::count();

        $dossiersEnCours    = Dossier::enCours()->count();           // ouvert + en_cours

        $dossiersEnRetard   = Dossier::enRetard()->count();         // ton scope parfait !

        $dossiersClotures   = Dossier::cloture()->count();          // statut = 'cloture'

        // Si tu veux aussi les archivés séparément (optionnel)
        // $dossiersArchives = Dossier::where('statut', 'archive')->count();

        return $dataTable->render('pages.dossiers.index', compact(
            'totalDossiers',
            'dossiersEnCours',
            'dossiersEnRetard',
            'dossiersClotures'
        ));
    }
    public function create()
    {
        $clients = Client::whereIn('statut', ['actif', 'prospect'])->get();
        return view('pages.dossiers.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'nom' => 'required|string|max:255',
            'reference' => 'nullable|string|max:50|unique:dossiers,reference',
            'type_dossier' => 'required|in:audit,conseil,formation,expertise,autre',
            'statut' => 'required|in:ouvert,en_cours,suspendu,cloture,archive',
            'description' => 'nullable|string',
            'date_ouverture' => 'required|date',
            'date_cloture_prevue' => 'nullable|date|after_or_equal:date_ouverture',
            'date_cloture_reelle' => 'nullable|date|after_or_equal:date_ouverture',
            'budget' => 'nullable|numeric|min:0',
            'frais_dossier' => 'nullable|numeric|min:0',

            'heure_theorique_sans_weekend' => 'nullable|numeric|min:0',
            'heure_theorique_avec_weekend' => 'nullable|numeric|min:0',

            'document' => 'nullable|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
            'notes' => 'nullable|string',
        ]);

        // Gestion du document
        if ($request->hasFile('document')) {
            $validated['document'] = $request->file('document')
                ->store('dossiers/documents', 'public');
        }

        $dossier = Dossier::create($validated);

        // 3. SweetAlert succès
        Alert::success('Succès', 'Dossier créé avec succès.');
        return redirect()->route('dossiers.show', $dossier);
    }


    public function show(Dossier $dossier)
    {
        return view('pages.dossiers.show', compact('dossier'));
    }

    public function edit(Dossier $dossier)
    {
        $clients = Client::whereIn('statut', ['actif', 'prospect'])->get();
        return view('pages.dossiers.edit', compact('dossier', 'clients'));
    }

    public function update(Request $request, Dossier $dossier)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'client_id' => 'required|exists:clients,id',
                'nom' => 'required|string|max:255',
                'reference' => 'required|string|max:50|unique:dossiers,reference,' . $dossier->id,
                'type_dossier' => 'required|in:audit,conseil,formation,expertise,autre',
                'statut' => 'required|in:ouvert,en_cours,suspendu,cloture,archive',
                'description' => 'nullable|string',
                'date_ouverture' => 'required|date',
                'date_cloture_prevue' => 'nullable|date|after_or_equal:date_ouverture',
                'date_cloture_reelle' => 'nullable|date|after_or_equal:date_ouverture',
                'budget' => 'nullable|numeric|min:0',
                'frais_dossier' => 'nullable|numeric|min:0',
                'heure_theorique_sans_weekend' => 'nullable|numeric|min:0',
                'heure_theorique_avec_weekend' => 'nullable|numeric|min:0',
                'document' => 'nullable|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
                'notes' => 'nullable|string',
            ]);

            // Si les heures ne sont pas fournies manuellement, les calculer automatiquement
            if (
                !$request->filled('heure_theorique_sans_weekend') &&
                !$request->filled('heure_theorique_avec_weekend') &&
                $request->filled('date_ouverture') &&
                $request->filled('date_cloture_prevue')
            ) {

                $dateDebut = Carbon::parse($validated['date_ouverture']);
                $dateFin = Carbon::parse($validated['date_cloture_prevue']);

                if ($dateFin->gte($dateDebut)) {
                    // Calcul des jours totaux
                    $joursTotaux = $dateDebut->diffInDays($dateFin) + 1;

                    // Calcul des jours ouvrables (lundi à vendredi)
                    $joursOuvrables = 0;
                    $currentDate = $dateDebut->copy();

                    while ($currentDate->lte($dateFin)) {
                        if (!$currentDate->isWeekend()) {
                            $joursOuvrables++;
                        }
                        $currentDate->addDay();
                    }

                    // Calcul des heures (8h par jour)
                    $validated['heure_theorique_avec_weekend'] = $joursTotaux * 8;
                    $validated['heure_theorique_sans_weekend'] = $joursOuvrables * 8;
                }
            }

            // Gestion de la suppression du document
            if ($request->has('remove_document') && $dossier->document) {
                Storage::disk('public')->delete($dossier->document);
                $validated['document'] = null;
                Log::info('Document supprimé pour le dossier', ['dossier_id' => $dossier->id]);
            }

            // Gestion du nouveau document
            if ($request->hasFile('document')) {
                // Supprimer l'ancien document s'il existe
                if ($dossier->document) {
                    Storage::disk('public')->delete($dossier->document);
                    Log::info('Ancien document supprimé', ['dossier_id' => $dossier->id]);
                }

                $fileName = $dossier->reference . '_' . time() . '.' . $request->file('document')->getClientOriginalExtension();
                $validated['document'] = $request->file('document')->storeAs('dossiers/documents', $fileName, 'public');

                Log::info('Nouveau document uploadé', [
                    'dossier_id' => $dossier->id,
                    'file_name' => $fileName
                ]);
            } else {
                // Conserver le document existant si aucun nouveau n'est fourni
                $validated['document'] = $dossier->document;
            }

            // Mettre à jour le dossier
            $dossier->update($validated);

            DB::commit();

            return redirect()->route('dossiers.show', $dossier)
                ->with('success', 'Dossier mis à jour avec succès.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour du dossier', [
                'dossier_id' => $dossier->id,
                'error' => $e->getMessage()
            ]);

            return back()->withInput()
                ->with('error', 'Une erreur est survenue lors de la mise à jour du dossier.');
        }
    }

    public function destroy(Dossier $dossier)
    {
        // Supprimer le document associé s'il existe
        if ($dossier->document) {
            Storage::disk('public')->delete($dossier->document);
        }

        $dossier->delete();

        return redirect()->route('dossiers.index')
            ->with('success', 'Dossier supprimé avec succès.');
    }
}
