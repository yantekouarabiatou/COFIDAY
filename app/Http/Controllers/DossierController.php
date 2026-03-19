<?php

namespace App\Http\Controllers;

use App\Models\Dossier;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\DataTables\DossiersDataTable;
use App\Models\User;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Mail\CollaborateurAssigneMail;
use Illuminate\Support\Facades\Mail;

class DossierController extends Controller
{
    public function index(DossiersDataTable $dataTable)
    {
        // Récupérer les dossiers accessibles
        $user = auth()->user();

        if ($user->hasRole(['admin', 'super-admin', 'rh', 'manager', 'directeur-general'])) {
            $totalDossiers = Dossier::count();
            $dossiersEnCours = Dossier::enCours()->count();
            $dossiersEnRetard = Dossier::enRetard()->count();
            $dossiersClotures = Dossier::cloture()->count();
        } else {
            $accessibleDossiers = $user->accessibleDossiers();

            $totalDossiers = $accessibleDossiers->count();
            $dossiersEnCours = $accessibleDossiers->enCours()->count();
            $dossiersEnRetard = $accessibleDossiers->enRetard()->count();
            $dossiersClotures = $accessibleDossiers->cloture()->count();
        }

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
        $collaborateurs = User::where('id', '!=', auth()->id())->get();

        return view('pages.dossiers.create', compact('clients', 'collaborateurs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
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
            'collaborateurs' => 'nullable|array',
            'collaborateurs.*' => 'exists:users,id',
        ]);

        if (empty($validated['client_id'])) {
            $clientDefaut = Client::where('nom', 'Coftime')->first();

            if (!$clientDefaut) {
                return back()->withInput()->with('error', 'Client Coftime introuvable en base.');
            }

            $validated['client_id'] = $clientDefaut->id;
        }

        DB::beginTransaction();

        try {
            // Gestion du document
            if ($request->hasFile('document')) {
                $validated['document'] = $request->file('document')
                    ->store('dossiers/documents', 'public');
            }

            // Ajouter le créateur
            $validated['created_by'] = auth()->id();

            $dossier = Dossier::create($validated);

            // Ajouter les collaborateurs
            $createurId = auth()->id();
            $dossier->addCollaborateur($createurId);

            $createur = auth()->user();
            if ($createur && $createur->email) {
                try {
                    Mail::to($createur->email)
                        ->send(new CollaborateurAssigneMail($dossier, $createur));
                } catch (\Exception $e) {
                    \Log::error("Échec envoi mail créateur: " . $e->getMessage());
                }
            }

            // Ajouter les collaborateurs sélectionnés
            if ($request->has('collaborateurs')) {
                foreach ($request->collaborateurs as $collaborateurId) {

                    if ($collaborateurId == $createurId) continue; // sécurité backend uniquement

                    $dossier->addCollaborateur($collaborateurId);

                    $collaborateur = User::find($collaborateurId);
                    if ($collaborateur && $collaborateur->email) {
                        try {
                            Mail::to($collaborateur->email)
                                ->send(new CollaborateurAssigneMail($dossier, $collaborateur));
                        } catch (\Exception $e) {
                            \Log::error("Échec envoi mail collaborateur {$collaborateurId}: " . $e->getMessage());
                        }
                    }
                }
            }

            DB::commit();

            Alert::success('Succès', 'Dossier créé avec succès.');
            return redirect()->route('dossiers.show', $dossier);
        } catch (\Exception $e) {
            DB::rollBack();
            Alert::error('Erreur', 'Une erreur est survenue lors de la création du dossier.');
            return back()->withInput();
        }
    }

    public function show(Dossier $dossier)
    {
        // Vérifier l'accès
        if (!$dossier->userCanAccess(auth()->id())) {
            abort(403, 'Vous n\'avez pas accès à ce dossier.');
        }
        $collaborateurs = User::where('id', '!=', auth()->id())->get();
        return view('pages.dossiers.show', compact('dossier', 'collaborateurs'));
    }

    public function edit(Dossier $dossier)
    {
        // Vérifier l'accès
        if (!$dossier->userCanAccess(auth()->id())) {
            abort(403, 'Vous n\'avez pas accès à ce dossier.');
        }

        $clients = Client::whereIn('statut', ['actif', 'prospect'])->get();
        $collaborateurs = User::where('id', '!=', auth()->id())->get();

        return view('pages.dossiers.edit', compact('dossier', 'clients', 'collaborateurs'));
    }

    public function update(Request $request, Dossier $dossier)
    {
        // Vérifier l'accès
        if (!$dossier->userCanAccess(auth()->id())) {
            abort(403, 'Vous n\'avez pas accès à ce dossier.');
        }

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
            'collaborateurs' => 'nullable|array',
            'collaborateurs.*' => 'exists:users,id',
        ]);

        DB::beginTransaction();

        try {

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
            }
            // Gestion des collaborateurs
            if ($request->has('collaborateurs')) {
                $currentCollaborateurs = $dossier->collaborateurs->pluck('id')->toArray();
                $newCollaborateurs = $request->collaborateurs;

                // Supprimer les collaborateurs retirés
                $toRemove = array_diff($currentCollaborateurs, $newCollaborateurs);
                foreach ($toRemove as $userId) {
                    $dossier->removeCollaborateur($userId);
                }

                // Ajouter les nouveaux collaborateurs
                $toAdd = array_diff($newCollaborateurs, $currentCollaborateurs);
                foreach ($toAdd as $userId) {
                    $dossier->addCollaborateur($userId);
                }
            } else {
                // Si aucun collaborateur n'est sélectionné, supprimer tous sauf le créateur
                foreach ($dossier->collaborateurs as $collaborateur) {
                    if ($collaborateur->id != $dossier->created_by) {
                        $dossier->removeCollaborateur($collaborateur->id);
                    }
                }
            }

            DB::commit();
            Alert::success('Succès', 'Dossier mis à jour avec succès.');
            return redirect()->route('dossiers.show', $dossier)
                ->with('success', 'Dossier mis à jour avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            Alert::error('Erreur', "Une erreur est survenue lors de la mise à jour du dossier.");
            return back()->withInput()
                ->with('error', 'Une erreur est survenue lors de la mise à jour du dossier.');
        }
    }

    // Ajouter une méthode pour gérer les collaborateurs
    public function gestionCollaborateurs(Request $request, Dossier $dossier)
    {
        // Vérifier l'accès
        if (!$dossier->userCanAccess(auth()->id())) {
            abort(403, 'Vous n\'avez pas accès à ce dossier.');
        }

        $request->validate([
            'collaborateur_id' => 'required|exists:users,id',
            'action' => 'required|in:add,remove,update_role',
            'role' => 'nullable|string|max:50',
        ]);

        try {
            switch ($request->action) {
                case 'add':
                    $dossier->addCollaborateur($request->collaborateur_id, $request->role ?? 'collaborateur');
                    
                    $collaborateur = User::find($request->collaborateur_id);
                    if ($collaborateur && $collaborateur->email) {
                        try {
                            Mail::to($collaborateur->email)
                                ->send(new CollaborateurAssigneMail($dossier, $collaborateur));
                        } catch (\Exception $e) {
                            \Log::error("Échec envoi mail collaborateur: " . $e->getMessage());
                            // On ne bloque pas l'ajout si le mail échoue
                        }
                    }
                    $message = 'Collaborateur ajouté avec succès.';
                    break;

                case 'remove':
                    $dossier->removeCollaborateur($request->collaborateur_id);
                    $message = 'Collaborateur retiré avec succès.';
                    break;

                case 'update_role':
                    $dossier->updateCollaborateurRole($request->collaborateur_id, $request->role);
                    $message = 'Rôle du collaborateur mis à jour.';
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'collaborateurs' => $dossier->collaborateurs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue.'
            ], 500);
        }
    }

    public function destroy(Dossier $dossier)
    {
        if ($dossier->timeEntries()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer ce dossier car il possède des entrées de temps associées.'
            ], 422);
        }
        if ($dossier->document) {
            Storage::disk('public')->delete($dossier->document);
        }

        $dossier->delete();

        return response()->json([
            'success' => true,
            'message' => 'Dossier supprimé avec succès.'
        ]);
    }
}
