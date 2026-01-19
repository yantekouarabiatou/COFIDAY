<?php

namespace App\Http\Controllers;

use App\Models\DailyEntry;
use App\Models\User;
use App\Models\Dossier;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DailyEntriesExport;
use App\Models\CompanySetting;
use App\Models\TimeEntry;
use App\Rules\BelongsToDailyEntry;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class DailyEntryController extends Controller
{
    /**
     * Afficher la liste des feuilles de temps
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = DailyEntry::with(['user', 'timeEntries.dossier.client']);

        // Si l'utilisateur n'est pas admin, filtrer uniquement ses enregistrements
        if (!$user->hasRole('admin')) {
            $query->where('user_id', $user->id);
        }

        // Filtres supplémentaires
        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->has('user') && $user->hasRole('admin')) {
            // Seuls les admins peuvent filtrer par utilisateur
            $query->where('user_id', $request->user);
        }

        if ($request->has('date')) {
            $query->whereDate('jour', $request->date);
        }

        // Pour les responsables : feuilles à valider
        if ($request->has('pending') && $user->hasRole('Directeur Général')) {
            $query->where('statut', 'soumis')
                ->where('user_id', '!=', $user->id);
        }

        $dailyEntries = $query->latest()->paginate(20);

        // Calcul des statistiques globales avec les mêmes restrictions de rôle
        if ($user->hasRole('admin')) {
            $totalHours = DailyEntry::sum('heures_reelles');
            $submittedCount = DailyEntry::where('statut', 'soumis')->count();
            $validatedCount = DailyEntry::where('statut', 'validé')->count();
            $rejectedCount = DailyEntry::where('statut', 'refusé')->count();
        } else {
            // Statistiques uniquement pour l'utilisateur connecté
            $totalHours = DailyEntry::where('user_id', $user->id)->sum('heures_reelles');
            $submittedCount = DailyEntry::where('user_id', $user->id)->where('statut', 'soumis')->count();
            $validatedCount = DailyEntry::where('user_id', $user->id)->where('statut', 'validé')->count();
            $rejectedCount = DailyEntry::where('user_id', $user->id)->where('statut', 'refusé')->count();
        }

        return view('pages.daily-entries.index', compact(
            'dailyEntries',
            'totalHours',
            'submittedCount',
            'validatedCount',
            'rejectedCount'
        ));
    }

    public function create()
    {
        $currentUser = Auth::user();

        // Vérifier s'il existe déjà une entrée pour aujourd'hui
        $todayEntry = DailyEntry::where('user_id', $currentUser->id)
            ->whereDate('jour', now()->toDateString())
            ->first();

        if ($todayEntry) {
            return redirect()
                ->route('daily-entries.edit', $todayEntry)
                ->with('info', 'Vous avez déjà une feuille de temps pour aujourd\'hui.');
        }

        // Récupérer les dossiers accessibles par l'utilisateur
        if ($currentUser->hasRole(['admin', 'super-admin', 'manager', 'directeur-general'])) {
            // Admins voient tous les dossiers actifs
            $dossiers = Dossier::with('client')
                ->whereIn('statut', ['ouvert', 'en_cours'])
                ->orderBy('nom')
                ->get();
        } else {
            // Collaborateurs ne voient que les dossiers qui leur sont associés
            // Utiliser whereHas avec une closure plus précise
            $dossiers = Dossier::with(['client', 'collaborateurs' => function ($query) use ($currentUser) {
                $query->where('users.id', $currentUser->id);
            }])
                ->whereIn('statut', ['ouvert', 'en_cours'])
                ->where(function ($query) use ($currentUser) {
                    $query->where('created_by', $currentUser->id)
                        ->orWhereHas('collaborateurs', function ($q) use ($currentUser) {
                            // Utiliser l'accès au pivot
                            $q->where('users.id', $currentUser->id)
                                ->where('collaborateur_dossier.is_active', 1);
                        });
                })
                ->orderBy('nom')
                ->get();
        }

        // Récupérer les clients actifs
        $clients = Client::whereIn('statut', ['actif', 'prospect'])
            ->orderBy('nom')
            ->get();

        // Pour la sélection des collaborateurs
        $users = User::where('is_active', 'actif')
            ->orderBy('prenom')
            ->get();

        return view('pages.daily-entries.create', compact(
            'currentUser',
            'dossiers',
            'clients',
            'users'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'jour' => 'required|date',
            'heures_theoriques' => 'required|numeric|min:0|max:24',
            'commentaire' => 'nullable|string',
            'time_entries' => 'required|array|min:1',
            'time_entries.*.dossier_id' => 'required|exists:dossiers,id',
            'time_entries.*.heure_debut' => 'required|date_format:H:i',
            'time_entries.*.heure_fin' => 'required|date_format:H:i|after:time_entries.*.heure_debut',
            'time_entries.*.heures_reelles' => 'required|numeric|min:0.25',
            'time_entries.*.travaux' => 'nullable|string|max:500',
            'time_entries.*.rendu' => 'nullable|string|max:500',

        ]);

        // VÉRIFICATION : Récupérer ou créer la DailyEntry
        $dailyEntry = DailyEntry::firstOrCreate(
            [
                'user_id' => $validated['user_id'],
                'jour' => $validated['jour']
            ],
            [
                'heures_theoriques' => $validated['heures_theoriques'],
                'commentaire' => $validated['commentaire'],
                'statut' => 'soumis',
            ]
        );

        // Si l'entrée existe déjà, on met à jour les autres champs
        if ($dailyEntry->wasRecentlyCreated === false) {
            $dailyEntry->update([
                'heures_theoriques' => $validated['heures_theoriques'],
                'commentaire' => $validated['commentaire'],
                'statut' => 'soumis', // On remet en "soumis" si modification
            ]);

            // Message d'alerte informatif
            session()->flash('info', 'Une feuille de temps existante pour cette date a été mise à jour.');
        }

        // Calcul du total des heures
        $totalHeures = collect($validated['time_entries'])
            ->sum('heures_reelles');

        // Mettre à jour le total des heures réelles
        $dailyEntry->update(['heures_reelles' => $totalHeures]);

        // Supprimer les anciennes time entries avant d'ajouter les nouvelles
        $dailyEntry->timeEntries()->delete();

        // Créer les nouvelles time entries
        foreach ($validated['time_entries'] as $entry) {
            $dailyEntry->timeEntries()->create([
                'user_id' => $dailyEntry->user_id,
                'dossier_id' => $entry['dossier_id'],
                'heure_debut' => $entry['heure_debut'],
                'heure_fin' => $entry['heure_fin'],
                'heures_reelles' => $entry['heures_reelles'],
                'travaux' => $entry['travaux'] ?? null,
                'rendu' => $entry['rendu'] ?? null,
            ]);
        }

        return redirect()->route('daily-entries.show', $dailyEntry)
            ->with('success', 'Feuille de temps enregistrée avec succès.');
    }

    /**
     * Afficher une feuille de temps
     */
    public function show(DailyEntry $dailyEntry)
    {
        $dailyEntry->load(['user', 'timeEntries.dossier.client']);

        return view('pages.daily-entries.show', compact('dailyEntry'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(DailyEntry $dailyEntry)
    {
        // Vérifier que l'utilisateur peut éditer cette entrée
        if (!$dailyEntry->userCanAccess(auth()->id())) {
            abort(403, 'Accès non autorisé.');
        }

        $dailyEntry->load('timeEntries');

        $currentUser = Auth::user();

        // Même logique pour filtrer les dossiers
        if ($currentUser->hasRole(['admin', 'super-admin', 'rh', 'manager', 'directeur-general'])) {
            $dossiers = Dossier::with('client')
                ->whereIn('statut', ['ouvert', 'en_cours'])
                ->orderBy('nom')
                ->get();
        } else {
            $dossiers = Dossier::with('client')
                ->whereIn('statut', ['ouvert', 'en_cours'])
                ->where(function ($query) use ($currentUser) {
                    $query->where('created_by', $currentUser->id)
                        ->orWhereHas('collaborateurs', function ($q) use ($currentUser) {
                            $q->where('user_id', $currentUser->id)
                                ->where('is_active', true);
                        });
                })
                ->orderBy('nom')
                ->get();
        }

        $clients = Client::whereIn('statut', ['actif', 'prospect'])
            ->orderBy('nom')
            ->get();

        $users = User::where('is_active', 'actif')
            ->orderBy('prenom')
            ->get();

        return view('pages.daily-entries.edit', compact(
            'dailyEntry',
            'currentUser',
            'dossiers',
            'clients',
            'users'
        ));
    }

    /**
     * Mettre à jour une feuille de temps
     */
    public function update(Request $request, DailyEntry $dailyEntry)
    {
        // Validation de base - sans statut required
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'jour' => 'required|date',
            'heures_theoriques' => 'required|numeric|min:0|max:24',
            'commentaire' => 'nullable|string',
            'time_entries' => 'required|array|min:1',
        ]);

        // Validation détaillée pour les time_entries
        foreach ($request->time_entries as $index => $entry) {
            $validator = Validator::make($entry, [
                'id' => ['nullable', 'exists:time_entries,id'],
                'dossier_id' => 'required|exists:dossiers,id',
                'heure_debut' => 'required|date_format:H:i',
                'heure_fin' => 'required|date_format:H:i',
                'heures_reelles' => 'required|numeric|min:0.25',
                'travaux' => 'nullable|string|max:500',
                'rendu' => 'nullable|string|max:500',
            ]);

            // Validation supplémentaire : heure_fin doit être après heure_debut
            if (
                isset($entry['heure_debut'], $entry['heure_fin']) &&
                strtotime($entry['heure_fin']) <= strtotime($entry['heure_debut'])
            ) {
                $validator->errors()->add('heure_fin', 'L\'heure de fin doit être après l\'heure de début.');
            }

            if ($validator->fails()) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors($validator->errors());
            }

            // Vérifier que l'TimeEntry appartient bien au DailyEntry si ID existe
            if (!empty($entry['id'])) {
                $timeEntry = TimeEntry::find($entry['id']);
                if (!$timeEntry || $timeEntry->daily_entry_id != $dailyEntry->id) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['error' => 'Activité invalide.']);
                }
            }
        }

        // VÉRIFICATION : Si la date OU l'utilisateur a changé, vérifier qu'il n'existe pas déjà une entrée pour cette combinaison
        $existingJour = Carbon::parse($dailyEntry->jour)->toDateString();
        $requestedJour = Carbon::parse($request->jour)->toDateString();

        $hasDateOrUserChanged = ($existingJour !== $requestedJour) ||
            ($dailyEntry->user_id != $request->user_id);

        if ($hasDateOrUserChanged) {
            $existingEntry = DailyEntry::where('user_id', $request->user_id)
                ->whereDate('jour', $request->jour)
                ->where('id', '!=', $dailyEntry->id)
                ->first();

            if ($existingEntry) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Une feuille de temps existe déjà pour cet utilisateur et cette date.
                        Veuillez choisir une autre date ou un autre utilisateur.');
            }
        }

        // Calcul du total des heures réelles
        $totalHeures = collect($request->time_entries)->sum('heures_reelles');

        // Préparer les données de mise à jour
        $updateData = [
            'user_id' => $validated['user_id'],
            'jour' => $validated['jour'],
            'heures_theoriques' => $validated['heures_theoriques'],
            'heures_reelles' => $totalHeures,
            'commentaire' => $validated['commentaire'] ?? null,
        ];

        // Mettre à jour uniquement si le statut est fourni (pour les responsables)
        if ($request->has('statut') && auth()->user()->can('change-status', $dailyEntry)) {
            $updateData['statut'] = $request->statut;

            // Si validation ou refus, enregistrer qui et quand
            if (in_array($request->statut, ['validé', 'refusé'])) {
                $updateData['valide_par'] = auth()->id();
                $updateData['valide_le'] = now();

                if ($request->statut === 'refusé' && $request->has('motif_refus')) {
                    $updateData['motif_refus'] = $request->motif_refus;
                }
            }
        } else {
            // Pour les utilisateurs normaux, remettre en "soumis" s'ils modifient
            $updateData['statut'] = 'soumis';
            $updateData['valide_par'] = null;
            $updateData['valide_le'] = null;
            $updateData['motif_refus'] = null;
        }

        // Mise à jour de la feuille principale
        $dailyEntry->update($updateData);

        $existingIds = [];

        foreach ($request->time_entries as $entry) {
            $data = [
                'user_id' => $dailyEntry->user_id,
                'dossier_id' => $entry['dossier_id'],
                'heure_debut' => $entry['heure_debut'],
                'heure_fin' => $entry['heure_fin'],
                'heures_reelles' => $entry['heures_reelles'],
                'travaux' => $entry['travaux'] ?? null,
                'rendu' => $entry['rendu'] ?? null,
            ];

            if (isset($entry['id']) && !empty($entry['id'])) {
                // Mise à jour d'une entrée existante
                $timeEntry = $dailyEntry->timeEntries()->find($entry['id']);
                if ($timeEntry) {
                    $timeEntry->update($data);
                    $existingIds[] = $timeEntry->id;
                }
            } else {
                // Création d'une nouvelle activité
                $newEntry = $dailyEntry->timeEntries()->create($data);
                $existingIds[] = $newEntry->id;
            }
        }

        // Suppression des activités qui ont été retirées dans le formulaire
        $dailyEntry->timeEntries()
            ->whereNotIn('id', $existingIds)
            ->delete();

        // Message d'alerte selon qui fait la modification
        $message = 'Feuille de temps mise à jour avec succès.';
        $alertType = 'success';

        if (auth()->user()->hasRole(['responsable', 'Directeur Général'])) {
            if ($request->has('statut')) {
                switch ($request->statut) {
                    case 'validé':
                        $message = 'Feuille de temps validée avec succès.';
                        break;
                    case 'refusé':
                        $message = 'Feuille de temps refusée avec succès.';
                        break;
                    case 'soumis':
                        $message = 'Feuille de temps modifiée et remise en attente de validation.';
                        break;
                }
            }
        } else {
            // Utilisateur normal
            $message = 'Feuille de temps modifiée et soumise pour validation.';
        }

        return redirect()
            ->route('daily-entries.show', $dailyEntry)
            ->with($alertType, $message);
    }
    /**
     * Supprimer une feuille de temps
     */
    public function destroy(DailyEntry $dailyEntry)
    {
        // Supprimer d'abord les entrées de temps associées
        $dailyEntry->timeEntries()->delete();

        // Puis supprimer la feuille de temps
        $dailyEntry->delete();

        return redirect()->route('daily-entries.index')
            ->with('success', 'Feuille de temps supprimée avec succès.');
    }

    /**
     * Valider une feuille de temps (pour les responsables)
     */
    public function validateEntry(DailyEntry $dailyEntry)
    {
        $dailyEntry->update([
            'statut' => 'validé',
            'valide_par' => Auth::id(),
            'valide_le' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Feuille de temps validée avec succès.');
    }

    /**
     * Refuser une feuille de temps (pour les responsables)
     */
    public function rejectEntry(DailyEntry $dailyEntry, Request $request)
    {
        $request->validate([
            'motif_refus' => 'required|string|max:500',
        ]);

        $dailyEntry->update([
            'statut' => 'refusé',
            'valide_par' => Auth::id(),
            'valide_le' => now(),
            'motif_refus' => $request->motif_refus,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Feuille de temps refusée avec succès.'
        ]);
    }


    /**
     * AJAX: Créer un dossier rapidement
     */
    public function createDossierQuick(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'client_id' => 'required|exists:clients,id',
            'type_dossier' => 'nullable|in:audit,conseil,formation,expertise,autre',
            'statut' => 'nullable|in:ouvert,en_cours,suspendu',
            'description' => 'nullable|string',
        ]);

        // Générer une référence automatique
        $reference = 'DOS-' . strtoupper(substr($validated['nom'], 0, 3)) . '-' . date('Ymd-His');

        $dossier = Dossier::create([
            'nom' => $validated['nom'],
            'reference' => $reference,
            'client_id' => $validated['client_id'],
            'type_dossier' => $validated['type_dossier'] ?? 'autre',
            'statut' => $validated['statut'] ?? 'ouvert',
            'description' => $validated['description'],
            'date_ouverture' => now(),
        ]);

        // Charger les relations pour la réponse
        $dossier->load('client');

        return response()->json([
            'success' => true,
            'dossier' => $dossier,
            'client' => $dossier->client,
        ]);
    }

    public function export(Request $request)
    {
        $request->validate([
            'date_debut' => 'required|date',
            'date_fin'   => 'required|date|after_or_equal:date_debut',
            'format'     => 'required|in:excel,pdf,csv',
        ]);

        $dateDebut = Carbon::parse($request->date_debut);
        $dateFin   = Carbon::parse($request->date_fin);
        $format    = $request->format;

        // Récupère les données avec les relations nécessaires
        $entries = DailyEntry::with(['user', 'user.poste', 'timeEntries.dossier'])
            ->whereBetween('jour', [$dateDebut, $dateFin])
            ->orderBy('jour', 'desc')
            ->orderBy('user_id')
            ->get();

        $filename = 'feuilles-temps_' . $dateDebut->format('Y-m-d') . '_au_' . $dateFin->format('Y-m-d');

        if ($format === 'excel' || $format === 'csv') {
            return Excel::download(
                new DailyEntriesExport($entries, $dateDebut, $dateFin),
                $filename . ($format === 'csv' ? '.csv' : '.xlsx'),
                $format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX
            );
        }

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('pages.daily-entries.export.pdf', compact('entries', 'dateDebut', 'dateFin'))
                ->setPaper('a4', 'landscape');

            return $pdf->download($filename . '.pdf');
        }
    }

    public function pdf(DailyEntry $dailyEntry)
    {
        // Chargement des relations
        $dailyEntry->load(['user.poste', 'timeEntries.dossier']);

        // Sécurité : si la feuille n'existe pas ou jour est null → erreur 404 propre
        if (!$dailyEntry->exists || is_null($dailyEntry->jour)) {
            abort(404, 'Feuille de temps non trouvée ou date invalide.');
        }

        // Logo
        $logoPath = public_path('images/logo.png');
        $logoBase64 = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        // Paramètres entreprise
        $companySetting = CompanySetting::first();

        // Nom du fichier sécurisé
        $dateFile = Carbon::parse($dailyEntry->jour)->format('Y-m-d');
        $filename = "feuille-temps-{$dateFile}.pdf";

        $pdf = Pdf::loadView('pages.daily-entries.export.pdf1', [
            'entry' => $dailyEntry,
            'logoBase64' => $logoBase64,
            'companySetting' => $companySetting,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream($filename);
    }
}
