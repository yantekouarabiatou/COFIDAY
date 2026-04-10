<?php

namespace App\Http\Controllers;

use App\Models\DailyEntry;
use App\Models\WeeklyValidation;
use App\Models\User;
use App\Models\Dossier;
use App\Models\Client;
use App\Models\CompanySetting;
use App\Models\TimeEntry;
use App\Exports\DailyEntriesExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class DailyEntryController extends Controller
{

    /**
     * Afficher la liste des feuilles de temps
     */ public function index(Request $request)
    {
        $user  = auth()->user();
        $query = DailyEntry::with(['user', 'timeEntries.dossier.client']);

        // Filtre mois en cours par défaut
        $mois = $request->filled('mois')
            ? Carbon::parse($request->mois . '-01')
            : Carbon::now()->startOfMonth();

        $query->whereBetween('jour', [
            $mois->copy()->startOfMonth(),
            $mois->copy()->endOfMonth()
        ]);

        if (!$user->hasRole('admin')) {
            // L'utilisateur voit ses propres entrées
            // + celles des collaborateurs du même poste
            $memePosteIds = User::where('poste_id', $user->poste_id)
                ->where('id', '!=', $user->id)
                ->where('is_active', 1)
                ->pluck('id');

            $query->where(function ($q) use ($user, $memePosteIds) {
                $q->where('user_id', $user->id)
                    ->orWhereIn('user_id', $memePosteIds);
            });
        }


        // ── Filtres server-side ──────────────────────────────────────────────

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('user') && $user->hasRole(['admin', 'responsable', 'directeur-general'])) {
            $query->where('user_id', $request->user);
        }

        if ($request->filled('date')) {
            $query->whereDate('jour', $request->date);
        }

        if ($request->filled('pending') && $user->hasRole('directeur-general')) {
            $query->where('statut', 'soumis')
                ->where('user_id', '!=', $user->id);
        }

        // ── Statistiques calculées sur la même query filtrée ────────────────
        $statsQuery     = clone $query;
        $totalHours     = (clone $statsQuery)->sum('heures_reelles');
        $submittedCount = (clone $statsQuery)->where('statut', 'soumis')->count();
        $validatedCount = (clone $statsQuery)->where('statut', 'validé')->count();
        $rejectedCount  = (clone $statsQuery)->where('statut', 'refusé')->count();

        $dailyEntries = $query->latest()->paginate(20)->withQueryString();

        // Liste des collaborateurs pour le select (rôles autorisés uniquement)
        $users = collect();
        if ($user->hasRole(['admin', 'manager', 'directeur-general'])) {
            $users = User::where('is_active', 1)
                ->orderBy('prenom')
                ->get(['id', 'prenom', 'nom']);
        }

        return view('pages.daily-entries.index', compact(
            'dailyEntries',
            'totalHours',
            'submittedCount',
            'validatedCount',
            'rejectedCount',
            'users'
        ));
    }

    // ════════════════════════════════════════════════════════════════════════
    // CREATE / STORE
    // ════════════════════════════════════════════════════════════════════════

    public function create()
    {
        $currentUser = Auth::user();

        // Si une feuille existe déjà pour aujourd'hui → rediriger vers edit
        $todayEntry = DailyEntry::where('user_id', $currentUser->id)
            ->whereDate('jour', now()->toDateString())
            ->where('est_manquant', false)
            ->first();

        if ($todayEntry) {
            return redirect()
                ->route('daily-entries.edit', $todayEntry)
                ->with('info', 'Vous avez déjà une feuille de temps pour aujourd\'hui.');
        }

        $dossiers = $this->getDossiersForUser($currentUser);
        $clients  = Client::whereIn('statut', ['actif', 'prospect'])->orderBy('nom')->get();

        return view('pages.daily-entries.create', compact('currentUser', 'dossiers', 'clients'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'                           => 'required|exists:users,id',
            'jour'                              => 'required|date',
            'heures_theoriques'                 => 'required|numeric|min:0|max:24',
            'commentaire'                       => 'nullable|string',
            'time_entries'                      => 'required|array|min:1',
            'time_entries.*.dossier_id'         => 'required|exists:dossiers,id',
            'time_entries.*.heure_debut'        => 'required|date_format:H:i',
            'time_entries.*.heure_fin'          => 'required|date_format:H:i',
            'time_entries.*.heures_reelles'     => 'required|numeric|min:0.25',
            'time_entries.*.travaux'            => 'nullable|string|max:500',
            'time_entries.*.rendu'              => 'nullable|string|max:500',
        ]);

        $validator->after(function ($validator) use ($request) {
            foreach ($request->input('time_entries', []) as $index => $entry) {
                if (!empty($entry['heure_debut']) && !empty($entry['heure_fin'])
                    && strtotime($entry['heure_fin']) <= strtotime($entry['heure_debut'])) {
                    $validator->errors()->add("time_entries.{$index}.heure_fin", 'L\'heure de fin doit être après l\'heure de début.');
                }
            }
        });

        $validated = $validator->validate();

        $jour = Carbon::parse($validated['jour']);

        $dailyEntry = DailyEntry::firstOrCreate(
            ['user_id' => $validated['user_id'], 'jour' => $validated['jour']],
            [
                'heures_theoriques' => $validated['heures_theoriques'],
                'commentaire'       => $validated['commentaire'],
                'statut'            => 'soumis',
                'est_manquant'      => false,
                'semaine'           => $jour->isoWeek(),
                'annee_semaine'     => $jour->isoWeekYear(),
            ]
        );

        if (!$dailyEntry->wasRecentlyCreated) {
            $dailyEntry->update([
                'heures_theoriques' => $validated['heures_theoriques'],
                'commentaire'       => $validated['commentaire'],
                'statut'            => 'soumis',
                'est_manquant'      => false,
            ]);
            session()->flash('info', 'Feuille existante mise à jour.');
        }

        $totalHeures = collect($validated['time_entries'])->sum('heures_reelles');
        $dailyEntry->update(['heures_reelles' => $totalHeures]);
        $dailyEntry->timeEntries()->delete();

        foreach ($validated['time_entries'] as $entry) {
            $dailyEntry->timeEntries()->create([
                'user_id'      => $dailyEntry->user_id,
                'dossier_id'   => $entry['dossier_id'],
                'heure_debut'  => $entry['heure_debut'],
                'heure_fin'    => $entry['heure_fin'],
                'heures_reelles' => $entry['heures_reelles'],
                'travaux'      => $entry['travaux'] ?? null,
                'rendu'        => $entry['rendu'] ?? null,
            ]);
        }

        Alert::success('Succès', 'Feuille de temps enregistrée avec succès.');
        return redirect()->route('daily-entries.show', $dailyEntry)
            ->with('success', 'Feuille de temps enregistrée avec succès.');
    }

    // ════════════════════════════════════════════════════════════════════════
    // SHOW / EDIT / UPDATE / DESTROY
    // ════════════════════════════════════════════════════════════════════════

    public function show(DailyEntry $dailyEntry)
    {
        $dailyEntry->load(['user.poste', 'timeEntries.dossier.client']);
        return view('pages.daily-entries.show', compact('dailyEntry'));
    }

    public function edit(DailyEntry $dailyEntry)
    {
        $this->authorizeEdit($dailyEntry);

        $dailyEntry->load('timeEntries');
        $currentUser = Auth::user();
        $dossiers    = $this->getDossiersForUser($currentUser);
        $clients     = Client::whereIn('statut', ['actif', 'prospect'])->orderBy('nom')->get();

        return view('pages.daily-entries.edit', compact('dailyEntry', 'currentUser', 'dossiers', 'clients'));
    }

    public function update(Request $request, DailyEntry $dailyEntry)
    {
        $this->authorizeEdit($dailyEntry);

        $rules = [
            'user_id'           => 'required|exists:users,id',
            'jour'              => 'required|date',
            'heures_theoriques' => 'required|numeric|min:0|max:24',
            'commentaire'       => 'nullable|string',
            'time_entries'      => 'required|array|min:1',
            'statut'            => 'nullable|in:soumis,validé,refusé',
            'motif_refus'       => 'nullable|string|max:500',
        ];

        if ($request->input('statut') === 'refusé') {
            $rules['motif_refus'] = 'required|string|max:500';
        }

        $validated = $request->validate($rules);

        foreach ($request->time_entries as $index => $entry) {
            $v = Validator::make($entry, [
                'id'             => ['nullable', 'exists:time_entries,id'],
                'dossier_id'     => 'required|exists:dossiers,id',
                'heure_debut'    => 'required|date_format:H:i',
                'heure_fin'      => 'required|date_format:H:i',
                'heures_reelles' => 'required|numeric|min:0.25',
                'travaux'        => 'nullable|string|max:500',
                'rendu'          => 'nullable|string|max:500',
            ]);

            if (
                isset($entry['heure_debut'], $entry['heure_fin'])
                && strtotime($entry['heure_fin']) <= strtotime($entry['heure_debut'])
            ) {
                $v->errors()->add("time_entries.{$index}.heure_fin", 'L\'heure de fin doit être après l\'heure de début.');
            }

            if ($v->fails()) {
                return redirect()->back()->withInput()->withErrors($v->errors());
            }

            if (!empty($entry['id'])) {
                $te = TimeEntry::find($entry['id']);
                if (!$te || $te->daily_entry_id != $dailyEntry->id) {
                    return redirect()->back()->withInput()->withErrors(['error' => 'Activité invalide.']);
                }
            }
        }

        // Vérif doublon si date/user a changé
        $hasChanged = Carbon::parse($dailyEntry->jour)->toDateString() !== Carbon::parse($request->jour)->toDateString()
            || $dailyEntry->user_id != $request->user_id;

        if ($hasChanged) {
            $duplicate = DailyEntry::where('user_id', $request->user_id)
                ->whereDate('jour', $request->jour)
                ->where('id', '!=', $dailyEntry->id)
                ->first();

            if ($duplicate) {
                return redirect()->back()->withInput()
                    ->with('error', 'Une feuille existe déjà pour cet utilisateur et cette date.');
            }
        }

        $totalHeures = collect($request->time_entries)->sum('heures_reelles');
        $jour        = Carbon::parse($request->jour);

        $updateData = [
            'user_id'          => $validated['user_id'],
            'jour'             => $validated['jour'],
            'semaine'          => $jour->isoWeek(),
            'annee_semaine'    => $jour->isoWeekYear(),
            'heures_theoriques' => $validated['heures_theoriques'],
            'heures_reelles'   => $totalHeures,
            'commentaire'      => $validated['commentaire'] ?? null,
            'est_manquant'     => false,
        ];

        // Gestion statut selon rôle
        $authUser = auth()->user();
        if ($request->has('statut') && $this->canValidate($dailyEntry)) {
            $updateData['statut'] = $request->statut;
            if (in_array($request->statut, ['validé', 'refusé'])) {
                $updateData['valide_par'] = auth()->id();
                $updateData['valide_le']  = now();
                if ($request->statut === 'refusé' && $request->has('motif_refus')) {
                    $updateData['motif_refus'] = $request->motif_refus;
                }
            }
        } else {
            $updateData['statut']      = 'soumis';
            $updateData['valide_par']  = null;
            $updateData['valide_le']   = null;
            $updateData['motif_refus'] = null;
        }

        $dailyEntry->update($updateData);

        $existingIds = [];
        foreach ($request->time_entries as $entry) {
            $data = [
                'user_id'        => $dailyEntry->user_id,
                'dossier_id'     => $entry['dossier_id'],
                'heure_debut'    => $entry['heure_debut'],
                'heure_fin'      => $entry['heure_fin'],
                'heures_reelles' => $entry['heures_reelles'],
                'travaux'        => $entry['travaux'] ?? null,
                'rendu'          => $entry['rendu'] ?? null,
            ];

            if (!empty($entry['id'])) {
                $te = $dailyEntry->timeEntries()->find($entry['id']);
                if ($te) {
                    $te->update($data);
                    $existingIds[] = $te->id;
                }
            } else {
                $new = $dailyEntry->timeEntries()->create($data);
                $existingIds[] = $new->id;
            }
        }

        $dailyEntry->timeEntries()->whereNotIn('id', $existingIds)->delete();

        Alert::success('Succès', 'Feuille de temps mise à jour avec succès.');
        return redirect()->route('daily-entries.show', $dailyEntry)
            ->with('success', 'Feuille de temps mise à jour.');
    }

    public function destroy(DailyEntry $dailyEntry)
    {
        $dailyEntry->timeEntries()->delete();
        $dailyEntry->delete();
        Alert::success('Succès', 'Feuille de temps supprimée avec succès.');
        return redirect()->route('daily-entries.index')
            ->with('success', 'Feuille supprimée.');
    }



    // ════════════════════════════════════════════════════════════════════════
    // VALIDATION HEBDOMADAIRE GROUPÉE
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Valider toutes les feuilles soumises d'un collaborateur pour une semaine.
     */
    public function validateWeek(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'semaine' => 'required|integer|min:1|max:53',
            'annee'   => 'required|integer',
        ]);

        $manager = auth()->user();
        $collab  = User::findOrFail($request->user_id);

        // Seul le manager direct peut valider
        if (!$manager->isManagerOf($collab->id) && !$manager->hasRole(['admin', 'super-admin'])) {
            return response()->json(['success' => false, 'message' => 'Vous n\'êtes pas le supérieur de cet utilisateur.'], 403);
        }

        $count = DailyEntry::where('user_id', $request->user_id)
            ->where('semaine', $request->semaine)
            ->where('annee_semaine', $request->annee)
            ->where('statut', 'soumis')
            ->update(['statut' => 'validé', 'valide_par' => $manager->id, 'valide_le' => now()]);

        // Enregistrer la validation hebdomadaire
        WeeklyValidation::updateOrCreate(
            ['user_id' => $request->user_id, 'semaine' => $request->semaine, 'annee' => $request->annee],
            ['validated_by' => $manager->id, 'statut' => 'validé', 'validated_at' => now()]
        );

        return response()->json(['success' => true, 'message' => "{$count} feuille(s) validée(s) pour la semaine {$request->semaine}."]);
    }

    /**
     * Refuser toutes les feuilles d'un collaborateur pour une semaine.
     */
    public function rejectWeek(Request $request)
    {
        $request->validate([
            'user_id'     => 'required|exists:users,id',
            'semaine'     => 'required|integer|min:1|max:53',
            'annee'       => 'required|integer',
            'motif_refus' => 'required|string|max:500',
        ]);

        $manager = auth()->user();
        $collab  = User::findOrFail($request->user_id);

        if (!$manager->isManagerOf($collab->id) && !$manager->hasRole(['admin', 'super-admin'])) {
            return response()->json(['success' => false, 'message' => 'Permission refusée.'], 403);
        }

        $count = DailyEntry::where('user_id', $request->user_id)
            ->where('semaine', $request->semaine)
            ->where('annee_semaine', $request->annee)
            ->whereIn('statut', ['soumis', 'refusé'])
            ->update([
                'statut'      => 'refusé',
                'valide_par'  => $manager->id,
                'valide_le'   => now(),
                'motif_refus' => $request->motif_refus,
            ]);

        WeeklyValidation::updateOrCreate(
            ['user_id' => $request->user_id, 'semaine' => $request->semaine, 'annee' => $request->annee],
            ['validated_by' => $manager->id, 'statut' => 'refusé', 'motif_refus' => $request->motif_refus, 'validated_at' => now()]
        );

        return response()->json(['success' => true, 'message' => "{$count} feuille(s) refusée(s)."]);
    }

    // ════════════════════════════════════════════════════════════════════════
    // CRÉATION RAPIDE DE DOSSIER (AJAX)
    // ════════════════════════════════════════════════════════════════════════

    public function createDossierQuick(Request $request)
    {
        $validated = $request->validate([
            'nom'         => 'required|string|max:255',
            'client_id'   => 'nullable|exists:clients,id',
            'type_dossier' => 'nullable|in:audit,conseil,formation,expertise,autre',
            'statut'      => 'nullable|in:ouvert,en_cours,suspendu',
            'description' => 'nullable|string',
        ]);

        $reference = 'DOS-' . strtoupper(substr($validated['nom'], 0, 3)) . '-' . date('Ymd-His');

        $dossier = Dossier::create([
            'nom'          => $validated['nom'],
            'reference'    => $reference,
            'client_id'    => $validated['client_id'] ?? null,
            'type_dossier' => $validated['type_dossier'] ?? 'autre',
            'statut'       => $validated['statut'] ?? 'ouvert',
            'description'  => $validated['description'] ?? null,
            'date_ouverture' => now(),
        ]);

        $dossier->load('client');

        return response()->json([
            'success' => true,
            'dossier' => $dossier,
            'client'  => $dossier->client,
        ]);
    }

    // ════════════════════════════════════════════════════════════════════════
    // EXPORT / PDF
    // ════════════════════════════════════════════════════════════════════════

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

        $entries = DailyEntry::with(['user', 'user.poste', 'timeEntries.dossier'])
            ->whereBetween('jour', [$dateDebut, $dateFin])
            ->orderBy('jour', 'desc')
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
        $dailyEntry->load(['user.poste', 'timeEntries.dossier']);

        if (!$dailyEntry->exists || is_null($dailyEntry->jour)) {
            abort(404, 'Feuille introuvable.');
        }

        $logoPath   = public_path('images/logo.png');
        $logoBase64 = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        $companySetting = CompanySetting::first();
        $dateFile       = Carbon::parse($dailyEntry->jour)->format('Y-m-d');

        $pdf = Pdf::loadView('pages.daily-entries.export.pdf1', [
            'entry'          => $dailyEntry,
            'logoBase64'     => $logoBase64,
            'companySetting' => $companySetting,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("feuille-temps-{$dateFile}.pdf");
    }

    // ════════════════════════════════════════════════════════════════════════
    // HELPERS PRIVÉS
    // ════════════════════════════════════════════════════════════════════════

    private function resolveSemaine(Request $request): array
    {
        if ($request->filled('semaine') && $request->filled('annee')) {
            return [(int) $request->semaine, (int) $request->annee];
        }
        return [now()->isoWeek(), now()->isoWeekYear()];
    }

    private function getDossiersForUser(User $user)
    {
        if ($user->hasRole(['admin', 'super-admin', 'manager', 'directeur-general'])) {
            return Dossier::with('client')->whereIn('statut', ['ouvert', 'en_cours'])->orderBy('nom')->get();
        }

        return Dossier::with(['client', 'collaborateurs'])
            ->whereIn('statut', ['ouvert', 'en_cours'])
            ->where(
                fn($q) =>
                $q->where('created_by', $user->id)
                    ->orWhereHas(
                        'collaborateurs',
                        fn($s) =>
                        $s->where('users.id', $user->id)->where('collaborateur_dossier.is_active', 1)
                    )
            )
            ->orderBy('nom')
            ->get();
    }

    private function authorizeEdit(DailyEntry $entry): void
    {
        $user = auth()->user();
        if ($entry->user_id !== $user->id && !$this->canValidate($entry)) {
            abort(403, 'Accès non autorisé.');
        }
    }

    private function canValidate(DailyEntry $entry): bool
    {
        $user = auth()->user();
        if ($user->hasRole(['admin', 'super-admin'])) return true;
        return $user->isManagerOf($entry->user_id);
    }

    private function ensureCanManage(DailyEntry $entry): void
    {
        if (!$this->canValidate($entry)) {
            abort(403, 'Vous n\'êtes pas le supérieur hiérarchique de cet utilisateur.');
        }
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
        // Autoriser le refus même si déjà refusé (pour changer le motif)
        // ou bloquer si déjà validé
        if ($dailyEntry->statut === 'validé') {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de refuser une feuille déjà validée.'
            ], 422);
        }

        $request->validate([
            'motif_refus' => 'required|string|max:500',
        ]);

        $dailyEntry->update([
            'statut'      => 'refusé',
            'valide_par'  => Auth::id(),
            'valide_le'   => now(),
            'motif_refus' => $request->motif_refus,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Feuille de temps refusée avec succès.'
        ]);
    }


    public function bulkValidate(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:daily_entries,id']);

        $count = DailyEntry::whereIn('id', $request->ids)
            ->where('user_id', '!=', auth()->id())
            ->whereIn('statut', ['soumis'])
            ->update(['statut' => 'validé', 'valide_par' => auth()->id(), 'valide_le' => now()]);

        return response()->json(['success' => true, 'message' => "{$count} feuille(s) validée(s)."]);
    }

    public function bulkReject(Request $request)
    {
        $request->validate([
            'ids'         => 'required|array',
            'ids.*'       => 'exists:daily_entries,id',
            'motif_refus' => 'required|string|max:500',
        ]);

        $count = DailyEntry::whereIn('id', $request->ids)
            ->where('user_id', '!=', auth()->id())
            ->whereIn('statut', ['soumis', 'refusé'])
            ->update(['statut' => 'refusé', 'valide_par' => auth()->id(), 'valide_le' => now(), 'motif_refus' => $request->motif_refus]);

        return response()->json(['success' => true, 'message' => "{$count} feuille(s) refusée(s)."]);
    }

    public function validateAll(Request $request)
    {
        $request->validate(['mois' => 'required|date_format:Y-m']);

        $mois  = Carbon::parse($request->mois . '-01');
        $count = DailyEntry::where('statut', 'soumis')
            ->where('user_id', '!=', auth()->id())
            ->whereBetween('jour', [$mois->copy()->startOfMonth(), $mois->copy()->endOfMonth()])
            ->update(['statut' => 'validé', 'valide_par' => auth()->id(), 'valide_le' => now()]);

        return response()->json(['success' => true, 'message' => "{$count} feuille(s) validée(s) pour {$request->mois}."]);
    }
}
