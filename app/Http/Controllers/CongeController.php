<?php

namespace App\Http\Controllers;

use App\Exports\CongesExport;
use App\Models\DemandeConge;
use App\Models\TypeConge;
use App\Models\SoldeConge;
use App\Models\HistoriqueConge;
use App\Models\RegleConge;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;
use App\Mail\LeaveRequestMail;
use App\Mail\LeaveApprovedMail;
use App\Mail\LeaveRejectedMail;
use App\Mail\LeavePreApprovedMail;
use App\Mail\RequestFinalValidationMail;
use Illuminate\Support\Facades\Storage;

class CongeController extends Controller
{
    // =========================================================================
    //  HELPERS : Gestion multi-années du solde
    // =========================================================================

    /**
     * Retourne tous les soldes disponibles d'un utilisateur,
     * des années ANTÉRIEURES à l'année courante + l'année courante,
     * triés du plus ancien au plus récent (FIFO).
     *
     * Seules les années dont jours_restants > 0 sont retournées.
     */
    private function getSoldesDisponibles(int $userId): \Illuminate\Support\Collection
    {
        return SoldeConge::where('user_id', $userId)
            ->where('jours_restants', '>', 0)
            ->orderBy('annee', 'asc') // plus ancienne d'abord
            ->get();
    }

    /**
     * Calcule le total de jours disponibles toutes années confondues.
     */
    private function getTotalJoursDisponibles(int $userId): float
    {
        return SoldeConge::where('user_id', $userId)
            ->where('jours_restants', '>', 0)
            ->sum('jours_restants');
    }

    /**
     * Déduit $joursADeduire des soldes en commençant par le plus ancien (FIFO).
     * Retourne un tableau décrivant ce qui a été déduit par année.
     *
     * @throws \Exception si le solde global est insuffisant
     */
    private function deduireSoldesMultiAnnees(int $userId, float $joursADeduire): array
    {
        $soldes = $this->getSoldesDisponibles($userId);

        $totalDisponible = $soldes->sum('jours_restants');

        if ($totalDisponible < $joursADeduire) {
            throw new \Exception(
                "Solde insuffisant. Total disponible : {$totalDisponible} jours (toutes années confondues). Demandé : {$joursADeduire} jours."
            );
        }

        $deductions = [];   // journal des déductions pour l'historique
        $resteADeduire = $joursADeduire;

        foreach ($soldes as $solde) {
            if ($resteADeduire <= 0) {
                break;
            }

            // Combien peut-on prendre sur ce solde ?
            $pris = min($solde->jours_restants, $resteADeduire);

            $solde->update([
                'jours_pris'     => $solde->jours_pris + $pris,
                'jours_restants' => $solde->jours_restants - $pris,
            ]);

            $deductions[] = [
                'annee'      => $solde->annee,
                'jours_pris' => $pris,
            ];

            $resteADeduire -= $pris;
        }

        return $deductions;
    }

    /**
     * Annule une déduction multi-années (lors d'un rollback ou annulation).
     * Restitue les jours déduits dans chaque solde d'origine.
     */
    private function restituerSoldesMultiAnnees(int $userId, array $deductions): void
    {
        foreach ($deductions as $deduction) {
            $solde = SoldeConge::where('user_id', $userId)
                ->where('annee', $deduction['annee'])
                ->first();

            if ($solde) {
                $solde->update([
                    'jours_pris'     => max(0, $solde->jours_pris - $deduction['jours_pris']),
                    'jours_restants' => $solde->jours_restants + $deduction['jours_pris'],
                ]);
            }
        }
    }

    /**
     * Construit un résumé lisible des déductions pour l'historique / les messages.
     */
    private function resumeDeductions(array $deductions): string
    {
        return collect($deductions)
            ->map(fn($d) => "{$d['jours_pris']} j. sur solde {$d['annee']}")
            ->implode(', ');
    }

    // =========================================================================
    //  HELPERS : Calcul des jours ouvrés
    // =========================================================================

    private function estJourOuvrable($date): bool
    {
        $date = Carbon::parse($date);

        if ($date->isWeekend()) {
            return false;
        }

        $regles = RegleConge::first();
        if ($regles && $regles->jours_feries) {
            $joursFeries = json_decode($regles->jours_feries, true);
            if (is_array($joursFeries)) {
                $dateStr = $date->format('m-d');
                foreach ($joursFeries as $jour) {
                    if (isset($jour['date']) && $jour['date'] === $dateStr) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    private function calculerJoursOuvres(Carbon $start, Carbon $end): int
    {
        $jours   = 0;
        $current = $start->copy();

        while ($current->lte($end)) {
            if ($this->estJourOuvrable($current)) {
                $jours++;
            }
            $current->addDay();
        }

        return $jours;
    }

    private function estPeriodeBloquee($date): bool
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
                $fin   = Carbon::parse($periode['fin']);

                if ($date->between($debut, $fin)) {
                    return true;
                }
            }
        }

        return false;
    }

    // =========================================================================
    //  INDEX
    // =========================================================================

    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin   = $user->hasRole('admin') || $user->hasRole('directeur-general');
        $isManager = $user->hasRole('manager');
        $isSimpleUser = !$isAdmin && !$isManager;

        $query = DemandeConge::with(['user', 'typeConge', 'validePar']);

        // 1. Restriction selon le rôle
        if ($isAdmin) {
            // Admin & Directeur Général : voient tout, aucune restriction
        } elseif ($isManager) {
            // Manager : voit uniquement les demandes des employés dont il est le manager
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('manager_id', $user->id);
            });
        } else {
            // Simple utilisateur : ne voit que ses propres demandes
            $query->where('user_id', $user->id);
        }

        // ── Filtres server-side ──────────────────────────────────────────────

        // Type de congé
        if ($request->filled('type_conge_id')) {
            $query->where('type_conge_id', $request->type_conge_id);
        }

        // Statut
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        // Collaborateur (admin ou manager uniquement)
        if ($request->filled('user_id')) {
            if ($isAdmin) {
                $query->where('user_id', $request->user_id);
            } elseif ($isManager) {
                // Le manager ne peut filtrer que parmi ses subordonnés
                $query->where('user_id', $request->user_id)
                    ->whereHas('user', function ($q) use ($user) {
                        $q->where('manager_id', $user->id);
                    });
            }
        }

        // Recherche texte libre (motif ou nom/prenom)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('motif', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('prenom', 'like', "%{$search}%")
                            ->orWhere('nom',   'like', "%{$search}%");
                    });
            });
        }

        // Date début (à partir de)
        if ($request->filled('date_debut')) {
            $query->whereDate('date_debut', '>=', $request->date_debut);
        }

        // Date fin (jusqu'à)
        if ($request->filled('date_fin')) {
            $query->whereDate('date_fin', '<=', $request->date_fin);
        }

        // ── Statistiques (sur la même base sans pagination) ──────────────────
        $statsQuery    = clone $query;
        $totalDemandes = (clone $statsQuery)->count();
        $enAttente     = (clone $statsQuery)->where('statut', 'en_attente')->count();
        $approuves     = (clone $statsQuery)->where('statut', 'approuve')->count();
        $refuses       = (clone $statsQuery)->where('statut', 'refuse')->count();

        $demandes    = $query->latest()->paginate(20)->withQueryString();
        $typesConges = TypeConge::where('actif', true)->get();

        // Liste des collaborateurs pour le select (admin/DG ou manager)
        $users = collect();
        if ($isAdmin) {
            $users = User::where('is_active', 1)
                ->orderBy('prenom')
                ->get(['id', 'prenom', 'nom']);
        } elseif ($isManager) {
            $users = User::where('is_active', 1)
                ->where('manager_id', $user->id)
                ->orderBy('prenom')
                ->get(['id', 'prenom', 'nom']);
        }

        return view('pages.conges.index', compact(
            'demandes',
            'typesConges',
            'totalDemandes',
            'enAttente',
            'approuves',
            'refuses',
            'users',
            'isAdmin'
        ));
    }

    // =========================================================================
    //  CREATE
    // =========================================================================

    // Dans CongeController.php, méthode create()

    public function create()
    {
        $user          = Auth::user();
        $anneeCourante = now()->year;

        $typesConges = TypeConge::where('actif', true)->get();

        // Solde de l'année courante (créé si inexistant)
        $solde = SoldeConge::where('user_id', $user->id)
            ->where('annee', $anneeCourante)
            ->first();

        if (!$solde) {
            $solde = $this->creerSoldeInitial($user->id, $anneeCourante);
        }

        $totalJoursDisponibles = $this->getTotalJoursDisponibles($user->id);
        $soldesParAnnee        = $this->getSoldesDisponibles($user->id);

        // Récupérer le supérieur hiérarchique (manager) de l'utilisateur
        $manager = $user->manager; // relation définie dans User

        return view('pages.conges.create', compact(
            'typesConges',
            'solde',
            'totalJoursDisponibles',
            'soldesParAnnee',
            'manager' // on passe le manager au lieu de $users
        ));
    }

    // =========================================================================
    //  STORE
    // =========================================================================

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $user          = Auth::user();
            $anneeCourante = now()->year;

            $validated = $request->validate([
                'type_conge_id'              => 'required|exists:types_conges,id',
                'date_debut'                 => 'required|date',
                'date_fin'                   => 'required|date|after_or_equal:date_debut',
                'motif'                      => 'required|string|max:1000',
                'superieur_hierarchique_id'  => 'required|exists:users,id',
                'nombre_jours'               => 'required|numeric|min:0.5',
                'fichier_justificatif'       => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx',
                'demande_attestation'        => 'nullable|boolean',
            ]);

            $user = Auth::user();

            // Vérifier que le supérieur sélectionné est bien le manager de l'utilisateur
            if ($request->superieur_hierarchique_id != $user->manager_id) {
                Alert::error('Erreur', 'Le supérieur hiérarchique ne correspond pas à votre manager.');
                return back()->withInput();
            }

            $dateDebut   = Carbon::parse($request->date_debut);
            $dateFin     = Carbon::parse($request->date_fin);
            $nombreJours = (float) $request->nombre_jours;

            $typeConge = TypeConge::findOrFail($request->type_conge_id);

            // Déclaration rétrospective (dates dans le passé) : justificatif obligatoire si type l'exige
            $estRetroactive = Carbon::parse($request->date_debut)->startOfDay()->lt(now()->startOfDay());
            if ($estRetroactive && $typeConge->justificatif_requis && ! $request->hasFile('fichier_justificatif')) {
                DB::rollBack();
                return back()
                    ->withErrors(['fichier_justificatif' => 'Le justificatif est obligatoire pour une déclaration rétrospective de « ' . $typeConge->libelle . ' ».'])
                    ->withInput();
            }

            // NOTE : nombre_jours_max est indicatif, pas bloquant.
            // Tant que le solde global (toutes années) couvre la demande, elle est acceptée.

            // ── Vérification & déduction du solde (congés annuels) ───────────
            $deductions = [];

            if ($typeConge->est_annuel) {
                $totalDisponible = $this->getTotalJoursDisponibles($user->id);

                if ($totalDisponible < $nombreJours) {
                    Alert::error(
                        'Solde insuffisant',
                        "Solde global disponible (toutes années) : {$totalDisponible} j. — Demandé : {$nombreJours} j."
                    );
                    return back()->withInput();
                }

                // Déduire FIFO (années les plus anciennes d'abord)
                $deductions = $this->deduireSoldesMultiAnnees($user->id, $nombreJours);
            }

            // ── Fichier justificatif ─────────────────────────────────────────────
            $cheminFichier = null;
            if ($request->hasFile('fichier_justificatif')) {
                $cheminFichier = $request->file('fichier_justificatif')
                    ->store('conges/justificatifs', 'public');
            }
            // ── Créer la demande ─────────────────────────────────────────────
            $demande = DemandeConge::create([
                'user_id'                   => $user->id,
                'superieur_hierarchique_id' => $request->superieur_hierarchique_id,
                'type_conge_id'             => $request->type_conge_id,
                'date_debut'                => $request->date_debut,
                'date_fin'                  => $request->date_fin,
                'nombre_jours'              => $nombreJours,
                'motif'                     => $request->motif,
                'statut'                    => 'en_attente',
                'fichier_justificatif'      => $cheminFichier,
                'demande_attestation'       => $request->boolean('demande_attestation'),
            ]);

            // Stocker le détail des déductions dans les métadonnées de la demande
            // (utile pour annulation / rollback)
            if (!empty($deductions)) {
                $demande->update([
                    'meta_deductions' => $deductions,
                ]);
            }

            // ── Historique ───────────────────────────────────────────────────
            $commentaireHistorique = 'Demande initiale soumise.';
            if (!empty($deductions)) {
                $commentaireHistorique .= ' Déduction : ' . $this->resumeDeductions($deductions);
            }

            HistoriqueConge::create([
                'demande_conge_id' => $demande->id,
                'action'           => 'demande_soumise',
                'effectue_par'     => $user->id,
                'commentaire'      => $commentaireHistorique,
            ]);

            // ── PDF + Mail ───────────────────────────────────────────────────
            $superieur = User::findOrFail($request->superieur_hierarchique_id);
            $pdf       = Pdf::loadView('pdfs.leave_request', ['leave' => $demande, 'superieur' => $superieur]);
            $pdfPath   = storage_path("app/temp/demande_{$demande->id}.pdf");

            if (!is_dir(dirname($pdfPath))) {
                mkdir(dirname($pdfPath), 0755, true);
            }

            $pdfContent = $pdf->output();
            file_put_contents($pdfPath, $pdfContent);

            // Vérifier que le PDF est complet
            if (!str_ends_with(trim($pdfContent), '%%EOF')) {
                throw new \Exception('PDF généré incomplet, veuillez réessayer.');
            }
            Mail::to($superieur->email)->send(new LeaveRequestMail($demande, $superieur, $pdfPath));

            DB::commit();

            Alert::success('Succès', 'Votre demande de congé a été soumise avec succès. En attente de validation.');
            return redirect()->route('conges.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Alert::error('Erreur', 'Une erreur est survenue : ' . $e->getMessage());
            return back()->withInput();
        }
    }

    // =========================================================================
    //  EDIT
    // =========================================================================

    // public function edit(DemandeConge $demande)
    // {
    //     if ($demande->statut !== 'en_attente') {
    //         Alert::warning('Information', 'Vous ne pouvez pas modifier une demande déjà traitée.');
    //         return redirect()->route('conges.show', $demande);
    //     }

    //     $typesConges = TypeConge::where('actif', true)->get();
    //     $users       = User::select('id', 'nom', 'prenom', 'email')
    //         ->orderBy('nom')->orderBy('prenom')->get();

    //     return view('pages.conges.edit', compact('demande', 'typesConges', 'users'));
    // }

    public function edit(DemandeConge $demande)
    {
        if ($demande->statut !== 'en_attente') {
            Alert::warning('Information', 'Vous ne pouvez pas modifier une demande déjà traitée.');
            return redirect()->route('conges.show', $demande);
        }

        $typesConges = TypeConge::where('actif', true)->get();
        $users       = User::select('id', 'nom', 'prenom', 'email')
            ->orderBy('nom')->orderBy('prenom')->get();

        // Nécessaire pour afficher le solde dans la vue
        $totalJoursDisponibles = $this->getTotalJoursDisponibles($demande->user_id);
        $soldesParAnnee        = $this->getSoldesDisponibles($demande->user_id);

        return view('pages.conges.edit', compact(
            'demande',
            'typesConges',
            'users',
            'totalJoursDisponibles',
            'soldesParAnnee'
        ));
    }

    // =========================================================================
    //  UPDATE
    // =========================================================================

    public function update(Request $request, DemandeConge $demande)
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();

            if ($demande->user_id !== $user->id) {
                abort(403, 'Accès non autorisé');
            }

            if ($demande->statut !== 'en_attente') {
                Alert::warning('Information', 'Vous ne pouvez pas modifier une demande déjà traitée.');
                return redirect()->route('conges.show', $demande);
            }

            $validated = $request->validate([
                'type_conge_id'             => 'required|exists:types_conges,id',
                'date_debut'                => 'required|date|after_or_equal:today',
                'date_fin'                  => 'required|date|after_or_equal:date_debut',
                'motif'                     => 'required|string|max:1000',
                'superieur_hierarchique_id' => 'required|exists:users,id',
                'nombre_jours'              => 'required|numeric|min:0.5',
                'fichier_justificatif'      => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx',
                'demande_attestation'       => 'nullable|boolean',
            ]);

            $nombreJours     = (float) $request->nombre_jours;
            $typeConge       = TypeConge::findOrFail($request->type_conge_id);
            $ancienTypeConge = TypeConge::find($demande->type_conge_id);

            // NOTE : nombre_jours_max est indicatif, pas bloquant.

            // ── Gestion du solde multi-années ─────────────────────────────────
            $deductions = [];

            if ($typeConge->est_annuel) {

                // 1. Restituer les jours de l'ancienne demande (si elle était annuelle)
                if ($ancienTypeConge && $ancienTypeConge->est_annuel) {
                    $anciennesDeductions = $demande->meta_deductions ?? [];

                    if (!empty($anciennesDeductions)) {
                        $this->restituerSoldesMultiAnnees($user->id, $anciennesDeductions);
                    } else {
                        // Fallback : restituer sur l'année de la demande originale
                        $anneeOrigine = Carbon::parse($demande->date_debut)->year;
                        $soldeOrigine = SoldeConge::where('user_id', $user->id)
                            ->where('annee', $anneeOrigine)
                            ->first();

                        if ($soldeOrigine) {
                            $soldeOrigine->update([
                                'jours_pris'     => max(0, $soldeOrigine->jours_pris - $demande->nombre_jours),
                                'jours_restants' => $soldeOrigine->jours_restants + $demande->nombre_jours,
                            ]);
                        }
                    }
                }

                // 2. Vérifier et déduire le nouveau nombre de jours (FIFO)
                $totalDisponible = $this->getTotalJoursDisponibles($user->id);

                if ($totalDisponible < $nombreJours) {
                    // Annuler la restitution avant de retourner
                    DB::rollBack();
                    Alert::error(
                        'Solde insuffisant',
                        "Solde global disponible (toutes années) : {$totalDisponible} j. — Demandé : {$nombreJours} j."
                    );
                    return back()->withInput();
                }

                $deductions = $this->deduireSoldesMultiAnnees($user->id, $nombreJours);
            }

            // ── Fichier justificatif ─────────────────────────────────────────
            $cheminFichier = $demande->fichier_justificatif; // conserver l'existant par défaut

            // Supprimer si coché
            if ($request->boolean('supprimer_justificatif') && $demande->fichier_justificatif) {
                Storage::disk('public')->delete($demande->fichier_justificatif);
                $cheminFichier = null;
            }

            // Nouveau fichier uploadé
            if ($request->hasFile('fichier_justificatif')) {
                // Supprimer l'ancien si existant
                if ($demande->fichier_justificatif) {
                    Storage::disk('public')->delete($demande->fichier_justificatif);
                }
                $cheminFichier = $request->file('fichier_justificatif')
                    ->store('conges/justificatifs', 'public');
            }

            // ── Mise à jour de la demande ─────────────────────────────────────
            $demande->update([
                'superieur_hierarchique_id' => $request->superieur_hierarchique_id,
                'type_conge_id'             => $request->type_conge_id,
                'date_debut'                => $request->date_debut,
                'date_fin'                  => $request->date_fin,
                'nombre_jours'              => $nombreJours,
                'motif'                     => $request->motif,
                'meta_deductions'           => !empty($deductions) ? $deductions : null,
                'fichier_justificatif'      => $cheminFichier,
                'demande_attestation'       => $request->boolean('demande_attestation'),
            ]);

            // ── Historique ────────────────────────────────────────────────────
            $commentaire = "Demande modifiée par l'employé.";
            if (!empty($deductions)) {
                $commentaire .= ' Déduction : ' . $this->resumeDeductions($deductions);
            }

            HistoriqueConge::create([
                'demande_conge_id' => $demande->id,
                'action'           => 'demande_modifiee',
                'effectue_par'     => $user->id,
                'commentaire'      => $commentaire,
            ]);

            // ── PDF + Mail ────────────────────────────────────────────────────
            $superieur = User::findOrFail($request->superieur_hierarchique_id);
            $pdf       = Pdf::loadView('pdfs.leave_request', ['leave' => $demande, 'superieur' => $superieur]);
            $pdfPath   = storage_path("app/temp/demande_{$demande->id}.pdf");

            if (!is_dir(dirname($pdfPath))) {
                mkdir(dirname($pdfPath), 0755, true);
            }

            $pdf->save($pdfPath);
            Mail::to($superieur->email)->send(new LeaveRequestMail($demande, $superieur, $pdfPath));

            DB::commit();

            Alert::success('Succès', 'La demande de congé a été modifiée avec succès.');
            return redirect()->route('conges.index');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur modification congé: ' . $e->getMessage());
            Alert::error('Erreur', 'Une erreur inattendue est survenue : ' . $e->getMessage());
            return back()->withInput();
        }
    }

    // =========================================================================
    //  ANNULER
    // =========================================================================

    public function annuler(Request $request, DemandeConge $demande)
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();

            if (!$user->hasRole('admin') && $demande->user_id !== $user->id) {
                abort(403, 'Accès non autorisé');
            }

            if ($demande->statut !== 'en_attente') {
                Alert::warning('Information', 'Seules les demandes en attente peuvent être annulées.');
                return back();
            }

            // Restituer les jours si c'était un congé annuel
            $typeConge = TypeConge::find($demande->type_conge_id);
            if ($typeConge && $typeConge->est_annuel) {
                $deductions = $demande->meta_deductions ?? [];

                if (!empty($deductions)) {
                    $this->restituerSoldesMultiAnnees($demande->user_id, $deductions);
                } else {
                    // Fallback : restituer sur l'année d'origine
                    $anneeOrigine = Carbon::parse($demande->date_debut)->year;
                    $solde        = SoldeConge::where('user_id', $demande->user_id)
                        ->where('annee', $anneeOrigine)
                        ->first();

                    if ($solde) {
                        $solde->update([
                            'jours_pris'     => max(0, $solde->jours_pris - $demande->nombre_jours),
                            'jours_restants' => $solde->jours_restants + $demande->nombre_jours,
                        ]);
                    }
                }
            }

            $demande->update(['statut' => 'annule']);

            HistoriqueConge::create([
                'demande_conge_id' => $demande->id,
                'action'           => 'demande_annulee',
                'effectue_par'     => $user->id,
                'commentaire'      => $request->commentaire ?? 'Demande annulée — jours restitués.',
            ]);

            DB::commit();

            Alert::success('Succès', 'La demande a été annulée et les jours ont été restitués.');
            return redirect()->route('conges.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Alert::error('Erreur', "Une erreur est survenue lors de l'annulation.");
            return back();
        }
    }

    // =========================================================================
    //  RETOUR ANTICIPÉ (admin / manager / DG)
    // =========================================================================

    public function enregistrerRetourAnticipe(Request $request, DemandeConge $demande)
    {
        $user = Auth::user();
        if (! $user->hasAnyRole(['admin', 'manager', 'directeur-general', 'rh'])) {
            abort(403);
        }

        if ($demande->statut !== 'approuve') {
            Alert::warning('Information', 'Seules les demandes approuvées peuvent faire l\'objet d\'un retour anticipé.');
            return back();
        }

        $request->validate([
            'date_retour_effectif' => [
                'required',
                'date',
                'after_or_equal:' . $demande->date_debut->toDateString(),
                'before_or_equal:' . $demande->date_fin->toDateString(),
            ],
            'motif_suspension' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $dateRetour = Carbon::parse($request->date_retour_effectif);
            $dateFin    = Carbon::parse($demande->date_fin);

            // Jours non consommés = du retour effectif jusqu'à la fin du congé
            $joursRestitues = 0;
            $typeConge = TypeConge::find($demande->type_conge_id);

            if ($typeConge && $typeConge->est_annuel) {
                $current = $dateRetour->copy();
                while ($current->lte($dateFin)) {
                    $dow = $current->dayOfWeek;
                    if ($dow !== 0 && $dow !== 6) { // exclure week-ends
                        $joursRestitues++;
                    }
                    $current->addDay();
                }

                if ($joursRestitues > 0) {
                    // Restituer sur l'année d'origine
                    $annee = $demande->date_debut->year;
                    $solde = SoldeConge::where('user_id', $demande->user_id)
                        ->where('annee', $annee)->first();

                    if ($solde) {
                        $solde->update([
                            'jours_pris'     => max(0, $solde->jours_pris - $joursRestitues),
                            'jours_restants' => $solde->jours_restants + $joursRestitues,
                        ]);
                    }
                }
            }

            $demande->update([
                'statut_suspension'  => 'repris',
                'date_suspension'    => $dateRetour,
                'jours_restitues'    => $joursRestitues,
                'motif_suspension'   => $request->motif_suspension,
            ]);

            HistoriqueConge::create([
                'demande_conge_id' => $demande->id,
                'action'           => 'retour_anticipe',
                'effectue_par'     => $user->id,
                'commentaire'      => "Retour anticipé le {$dateRetour->format('d/m/Y')} — {$joursRestitues} jour(s) restitué(s)." . ($request->motif_suspension ? ' Motif : ' . $request->motif_suspension : ''),
            ]);

            DB::commit();

            Alert::success('Succès', "{$joursRestitues} jour(s) restitué(s) au solde de l'employé.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur retour anticipé: ' . $e->getMessage());
            Alert::error('Erreur', 'Une erreur est survenue : ' . $e->getMessage());
        }

        return redirect()->route('conges.show', $demande);
    }

    // =========================================================================
    //  UPLOAD JUSTIFICATIF AU RETOUR (employé)
    // =========================================================================

    public function uploadJustificatifRetour(Request $request, DemandeConge $demande)
    {
        $user = Auth::user();

        if ($demande->user_id !== $user->id && ! $user->hasAnyRole(['admin', 'rh'])) {
            abort(403);
        }

        if (! in_array($demande->statut, ['approuve', 'pre_approuve'])) {
            Alert::warning('Information', 'Le justificatif ne peut être déposé que sur une demande approuvée.');
            return back();
        }

        $request->validate([
            'justificatif_retour' => 'required|file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx',
        ], [
            'justificatif_retour.required' => 'Veuillez sélectionner un fichier.',
            'justificatif_retour.mimes'    => 'Format accepté : PDF, Word, Image.',
        ]);

        $chemin = $request->file('justificatif_retour')
            ->store('conges/justificatifs-retour', 'public');

        $demande->update([
            'justificatif_retour'       => $chemin,
            'date_depot_justificatif'   => now(),
        ]);

        HistoriqueConge::create([
            'demande_conge_id' => $demande->id,
            'action'           => 'justificatif_depose',
            'effectue_par'     => $user->id,
            'commentaire'      => 'Justificatif médical déposé au retour.',
        ]);

        Alert::success('Succès', 'Votre justificatif a été enregistré.');
        return redirect()->route('conges.show', $demande);
    }

    // =========================================================================
    //  TRAITER (admin / manager)
    // =========================================================================

    public function traiter(Request $request, DemandeConge $demande)
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();

            if (!$user->hasRole('admin') && !$user->hasRole('manager') && !$user->hasRole('directeur-general')) {
                abort(403, 'Accès non autorisé');
            }

            $request->validate([
                'action'      => 'required|in:pre_approuve,refuse',
                'commentaire' => 'nullable|string|max:1000',
            ]);

            if ($demande->statut !== 'en_attente') {
                Alert::warning('Information', 'Cette demande a déjà été traitée.');
                return back();
            }

            $action = $request->action;

            $demande->update([
                'statut'          => $action,
                'valide_par'      => $user->id,
                'date_validation' => now(),
            ]);

            // ── Historique ────────────────────────────────────────────────────
            HistoriqueConge::create([
                'demande_conge_id' => $demande->id,
                'action'           => $action === 'pre_approuve' ? 'demande_pre_approuvee' : 'demande_refusee',
                'effectue_par'     => $user->id,
                'commentaire'      => $request->commentaire,
            ]);

            DB::commit();

            // ── Emails ────────────────────────────────────────────────────────
            $demandeur = $demande->user;

            if ($action === 'pre_approuve') {

                // Mail au demandeur
                Mail::to($demandeur->email)->send(new LeavePreApprovedMail($demande));

                // Mail au DG pour validation finale — emails récupérés depuis la config
                $dgEmails = config('cofima.email_dg');
                $dgEmails = is_array($dgEmails) ? $dgEmails : [$dgEmails];
                $dgEmails = array_filter($dgEmails);

                if (!empty($dgEmails)) {
                    $grandSuperieur = User::whereIn('email', $dgEmails)->first();

                    if ($grandSuperieur) {
                        $toDG  = $dgEmails[0];
                        $ccDG  = array_slice($dgEmails, 1);

                        // Secrétaire en CC du mail DG
                        $secEmails = config('cofima.email_secretaire');
                        $secEmails = is_array($secEmails) ? $secEmails : [$secEmails];
                        $secEmails = array_filter($secEmails, fn($e) => $e !== $demande->user->email && !in_array($e, $dgEmails));

                        $ccAll = array_values(array_unique(array_merge($ccDG, $secEmails)));

                        $mailDG = Mail::to($toDG);
                        if (!empty($ccAll)) {
                            $mailDG->cc($ccAll);
                        }
                        $mailDG->send(new RequestFinalValidationMail($demande, $user, $grandSuperieur, $request->commentaire));
                    }
                }
            } else {
                Mail::to($demandeur->email)->send(new LeaveRejectedMail($demande, $request->commentaire));
            }

            $message = $action === 'pre_approuve'
                ? 'La demande a été pré-approuvée. En attente de validation finale.'
                : 'La demande a été refusée.';

                Alert::success('Succès', $message);
                return redirect()->route('conges.index');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erreur traitement congé: ' . $e->getMessage());
                Alert::error('Erreur', 'Une erreur est survenue : ' . $e->getMessage());
                return back();
            }
    }

    // =========================================================================
    //  SOLDE (vue)
    // =========================================================================

    public function solde(User $user = null)
    {
        $currentUser = Auth::user();

        if (!$user) {
            $user = $currentUser;
        }

        $anneeCourante = now()->year;
        $premiereAnnee = 2022;
        $annees        = range($premiereAnnee, $anneeCourante);

        $soldesExistants = SoldeConge::where('user_id', $user->id)
            ->whereIn('annee', $annees)
            ->get()
            ->keyBy('annee');

        $soldeCourant = $soldesExistants->get($anneeCourante);

        if (!$soldeCourant) {
            $regles      = RegleConge::first();
            $joursAcquis = $regles ? $regles->jours_par_mois * 12 : 24;

            $soldeCourant = SoldeConge::create([
                'user_id'        => $user->id,
                'annee'          => $anneeCourante,
                'jours_acquis'   => $joursAcquis,
                'jours_pris'     => 0,
                'jours_restants' => $joursAcquis,
                'jours_reportes' => 0,
            ]);
            $soldesExistants->put($anneeCourante, $soldeCourant);
        }

        // Construire la collection complète avec années vides en fallback
        $soldes = collect();
        foreach ($annees as $annee) {
            if ($soldesExistants->has($annee)) {
                $soldes->push($soldesExistants->get($annee));
            } else {
                $soldeFactice                = new SoldeConge();
                $soldeFactice->annee         = $annee;
                $soldeFactice->jours_acquis  = 0;
                $soldeFactice->jours_pris    = 0;
                $soldeFactice->jours_restants = 0;
                $soldeFactice->jours_reportes = 0;
                $soldeFactice->user_id       = $user->id;
                $soldes->push($soldeFactice);
            }
        }

        $soldes = $soldes->sortByDesc('annee');

        // Total disponible toutes années confondues
        $totalJoursDisponibles = $this->getTotalJoursDisponibles($user->id);

        // Détail des soldes disponibles (années avec jours restants > 0), triés FIFO
        $soldesDisponibles = $this->getSoldesDisponibles($user->id);

        $demandesCongesPayes = DemandeConge::with('typeConge')
            ->where('user_id', $user->id)
            ->whereHas('typeConge', fn($q) => $q->where('est_paye', true))
            ->whereYear('created_at', $anneeCourante)
            ->orderBy('date_debut', 'desc')
            ->get();

        return view('pages.conges.solde', compact(
            'user',
            'soldes',
            'soldeCourant',
            'demandesCongesPayes',
            'totalJoursDisponibles',
            'soldesDisponibles'
        ));
    }

    // =========================================================================
    //  API : jours fériés
    // =========================================================================

    public function getFeries()
    {
        $regles      = RegleConge::first();
        $joursFeries = [];

        if ($regles && $regles->jours_feries) {
            $joursFeries = is_array($regles->jours_feries)
                ? $regles->jours_feries
                : json_decode($regles->jours_feries, true);

            if (!$joursFeries) {
                $joursFeries = [];
            }
        }

        return response()->json(['jours_feries' => $joursFeries]);
    }

    /**
     * API : retourne le total de jours disponibles (toutes années)
     * pour l'utilisateur connecté. Utilisé par la vue create/edit.
     */
    public function getSoldeTotal()
    {
        $userId = Auth::id();
        $total  = $this->getTotalJoursDisponibles($userId);

        $detail = $this->getSoldesDisponibles($userId)->map(fn($s) => [
            'annee'          => $s->annee,
            'jours_restants' => $s->jours_restants,
        ]);

        return response()->json([
            'total'  => $total,
            'detail' => $detail,
        ]);
    }

    // =========================================================================
    //  SHOW
    // =========================================================================

    public function show(DemandeConge $demande)
    {
        $user = Auth::user();

        if (!$user->hasRole('admin') && !$user->hasRole('manager') && $demande->user_id !== $user->id) {
            abort(403, 'Accès non autorisé');
        }

        $demande->loadMissing(['user', 'typeConge', 'validePar', 'historiques.effectuePar']);

        if (!$demande->typeConge) {
            $demande->typeConge = TypeConge::find($demande->type_conge_id) ?? tap(new TypeConge(), function ($t) {
                $t->libelle           = 'Type inconnu';
                $t->est_paye          = false;
                $t->nombre_jours_max  = null;
                $t->exists            = false;
            });
        }

        if (!$demande->user) {
            $demande->user = User::find($demande->user_id) ?? tap(new User(), function ($u) use ($demande) {
                $u->id     = $demande->user_id;
                $u->prenom = 'Utilisateur';
                $u->nom    = 'Supprimé';
                $u->email  = 'non.disponible@example.com';
                $u->exists = false;
            });
        }

        $statutColor = match ($demande->statut) {
            'en_attente' => 'warning',
            'approuve'   => 'success',
            'refuse'     => 'danger',
            'annule'     => 'secondary',
            default      => 'info',
        };

        return view('pages.conges.show', compact('demande', 'statutColor'));
    }

    // =========================================================================
    //  DASHBOARD
    // =========================================================================

    public function dashboard()
    {
        $user = Auth::user();

        if (!$user->hasRole('admin') && !$user->hasRole('manager') && !$user->hasRole('directeur-general')) {
            abort(403, 'Accès non autorisé');
        }

        $stats = [
            'total_demandes' => DemandeConge::count(),
            'en_attente'     => DemandeConge::where('statut', 'en_attente')->count(),
            'approuvees'     => DemandeConge::where('statut', 'approuve')->count(),
            'refusees'       => DemandeConge::where('statut', 'refuse')->count(),
            'annulees'       => DemandeConge::where('statut', 'annule')->count(),
        ];

        $demandesUrgentes = DemandeConge::with(['user', 'typeConge'])
            ->where('statut', 'en_attente')
            ->where('created_at', '<=', now()->subDays(3))
            ->orderBy('created_at', 'asc')->limit(10)->get();

        $congesEnCours = DemandeConge::with(['user', 'typeConge'])
            ->where('statut', 'approuve')
            ->where('date_debut', '<=', now())
            ->where('date_fin', '>=', now())
            ->orderBy('date_fin', 'asc')->get();

        $prochainsConges = DemandeConge::with(['user', 'typeConge'])
            ->where('statut', 'approuve')
            ->where('date_debut', '>', now())
            ->where('date_debut', '<=', now()->addDays(15))
            ->orderBy('date_debut', 'asc')->limit(10)->get();

        $soldesCritiques = SoldeConge::with('user')
            ->where('annee', now()->year)
            ->where('jours_restants', '<', 10)
            ->orderBy('jours_restants', 'asc')->limit(10)->get();

        $chartData = $this->getChartData(now()->year);
        $typeData  = $this->getTypeData();

        return view('pages.conges.dashboard', compact(
            'stats',
            'demandesUrgentes',
            'congesEnCours',
            'prochainsConges',
            'soldesCritiques',
            'chartData',
            'typeData'
        ));
    }

    // =========================================================================
    //  EXPORTS
    // =========================================================================

    public function exportExcel(Request $request)
    {
        $annee  = $request->get('annee', now()->year);
        $userId = $request->get('user_id');
        $type   = $request->get('type', 'all');

        $query = DemandeConge::with(['user', 'typeConge', 'validePar', 'historiques.effectuePar'])
            ->whereYear('created_at', $annee);

        if ($userId) {
            $query->where('user_id', $userId);
            $type = 'user';
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('date_debut', [$request->start_date, $request->end_date]);
            $type = 'period';
        }

        $conges   = $query->orderBy('date_debut', 'desc')->get();
        $fileName = 'conges_';

        switch ($type) {
            case 'user':
                $fileName .= strtolower(str_replace(' ', '_', $conges->first()->user->nom)) . '_';
                break;
            case 'period':
                $fileName .= $request->start_date . '_' . $request->end_date . '_';
                break;
            default:
                $fileName .= "{$annee}_";
        }

        $fileName .= now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new CongesExport($conges, $annee, $type), $fileName);
    }

    public function exportPdf(Request $request)
    {
        $annee  = $request->get('annee', now()->year);
        $conges = DemandeConge::with(['user', 'typeConge'])
            ->whereYear('created_at', $annee)
            ->orderBy('date_debut', 'desc')->get();

        $pdf = Pdf::loadView('exports.conges-pdf', compact('conges', 'annee'))
            ->setPaper('A4', 'landscape');

        return $pdf->download("conges_{$annee}.pdf");
    }

    public function exportCsv(Request $request)
    {
        $annee  = $request->get('annee', now()->year);
        $conges = DemandeConge::with(['user', 'typeConge'])
            ->whereYear('created_at', $annee)
            ->orderBy('date_debut', 'desc')->get();

        return Excel::download(
            new CongesExport($conges, $annee),
            "conges_{$annee}.csv",
            \Maatwebsite\Excel\Excel::CSV
        );
    }

    public function exportUserConges(User $user, Request $request)
    {
        $annee  = $request->get('annee', now()->year);
        $conges = DemandeConge::with(['user', 'typeConge', 'validePar', 'historiques.effectuePar'])
            ->where('user_id', $user->id)
            ->whereYear('created_at', $annee)
            ->orderBy('date_debut', 'desc')->get();

        $fileName = 'conges_' . strtolower(str_replace(' ', '_', $user->nom)) . "_{$annee}_" . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new CongesExport($conges, $annee, 'user'), $fileName);
    }

    // =========================================================================
    //  CALENDRIER
    // =========================================================================

    public function calendrier()
    {
        $user  = Auth::user();
        $query = DemandeConge::with(['user', 'typeConge'])
            ->whereIn('statut', ['approuve', 'en_attente'])
            ->whereNotNull('date_debut')
            ->whereNotNull('date_fin');

        if (!$user->hasRole('admin') && !$user->hasRole('manager')) {
            $query->where('user_id', $user->id);
        }

        $conges      = $query->get(['id', 'user_id', 'type_conge_id', 'date_debut', 'date_fin', 'statut', 'nombre_jours', 'motif']);
        $typesConges = TypeConge::where('actif', true)->get(['id', 'libelle', 'couleur', 'est_paye']);

        return view('pages.conges.calendrier', compact('conges', 'typesConges'));
    }

    // =========================================================================
    //  DESTROY
    // =========================================================================

    public function destroy(DemandeConge $conge)
    {
        if (!auth()->user()->hasRole('admin') && auth()->id() !== $conge->user_id) {
            abort(403);
        }

        $conge->delete();

        return redirect()->route('conges.index')->with('success', 'Demande supprimée avec succès');
    }

    // =========================================================================
    //  PRIVÉS : graphiques & helpers
    // =========================================================================

    private function getChartData(int $annee): array
    {
        $data = ['months' => [], 'en_attente' => [], 'approuvees' => [], 'refusees' => []];

        for ($i = 1; $i <= 12; $i++) {
            $start = Carbon::create($annee, $i, 1)->startOfMonth();
            $end   = Carbon::create($annee, $i, 1)->endOfMonth();

            $data['months'][]     = $start->locale('fr')->monthName;
            $data['en_attente'][] = DemandeConge::where('statut', 'en_attente')->whereBetween('created_at', [$start, $end])->count();
            $data['approuvees'][] = DemandeConge::where('statut', 'approuve')->whereBetween('created_at', [$start, $end])->count();
            $data['refusees'][]   = DemandeConge::where('statut', 'refuse')->whereBetween('created_at', [$start, $end])->count();
        }

        return $data;
    }

    private function getTypeData(): array
    {
        $types = TypeConge::where('actif', true)->get();
        $data  = ['labels' => [], 'data' => [], 'colors' => []];

        foreach ($types as $type) {
            $count = DemandeConge::where('type_conge_id', $type->id)
                ->where('statut', 'approuve')
                ->whereYear('created_at', now()->year)
                ->count();

            if ($count > 0) {
                $data['labels'][] = $type->libelle;
                $data['data'][]   = $count;
                $data['colors'][] = $type->couleur ?? $this->getDefaultColor($type->id);
            }
        }

        return $data;
    }

    private function getDefaultColor(int $typeId): string
    {
        $colors = [
            1 => '#3B82F6',
            2 => '#6B7280',
            3 => '#EF4444',
            4 => '#8B5CF6',
            5 => '#10B981',
        ];

        return $colors[$typeId] ?? '#6B7280';
    }

    private function creerSoldeInitial(int $userId, int $annee): SoldeConge
    {
        $regles      = RegleConge::first();
        $joursAcquis = $regles ? $regles->jours_par_mois * 12 : 24;

        return SoldeConge::create([
            'user_id'        => $userId,
            'annee'          => $annee,
            'jours_acquis'   => $joursAcquis,
            'jours_pris'     => 0,
            'jours_restants' => $joursAcquis,
        ]);
    }

    private function calculerJoursCalendaires(Carbon $dateDebut, Carbon $dateFin): float
    {
        return $dateDebut->diffInDays($dateFin) + 1;
    }

    public function validerFinale(Request $request, DemandeConge $demande)
    {
        $user = Auth::user();

        if (!$user->hasRole('directeur-general') && !$user->hasRole('rh') && !$user->hasRole('admin')) {
            abort(403, 'Accès non autorisé');
        }

        if ($demande->statut !== 'pre_approuve') {
            Alert::warning('Information', 'Cette demande n\'est pas en attente de validation finale.');
            return back();
        }

        $request->validate([
            'action'      => 'required|in:approuve,refuse',
            'commentaire' => 'nullable|string|max:1000',
        ]);

        $action = $request->action;

        // ── 1. Enregistrement en base (transaction isolée) ────────────────────
        try {
            DB::beginTransaction();

            $demande->update([
                'statut'                 => $action,
                'statut_final'           => $action,
                'valide_par_final'       => $user->id,
                'date_validation_finale' => now(),
                'commentaire_final'      => $request->commentaire,
            ]);

            HistoriqueConge::create([
                'demande_conge_id' => $demande->id,
                'action'           => $action === 'approuve' ? 'demande_approuvee_finale' : 'demande_refusee_finale',
                'effectue_par'     => $user->id,
                'commentaire'      => $request->commentaire,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur validation finale congé (DB): ' . $e->getMessage());
            Alert::error('Erreur', 'Impossible d\'enregistrer la décision : ' . $e->getMessage());
            return back();
        }

        // ── 2. Envoi des emails (après commit, séparé) ────────────────────────
        try {
            $demande->load('user');

            if ($action === 'approuve') {

                $deductions      = $demande->meta_deductions ?? [];
                $anneesPrelevees = !empty($deductions)
                    ? collect($deductions)->pluck('annee')->unique()->toArray()
                    : [Carbon::parse($demande->date_debut)->year];

                $soldes = SoldeConge::where('user_id', $demande->user_id)
                    ->orderBy('annee')
                    ->get()
                    ->filter(fn($s) => $s->jours_restants > 0 || in_array($s->annee, $anneesPrelevees))
                    ->values();

                $dateRepriseFormatee = Carbon::parse($demande->date_fin)
                    ->addWeekday()
                    ->isoFormat('dddd D MMMM YYYY');

                $numeroNote = str_pad($demande->id, 3, '0', STR_PAD_LEFT)
                    . '/COFIMA/SA/JCA/GAT/'
                    . now()->year;

                $secretaireEmails = config('cofima.email_secretaire');
                $secretaireEmails = is_array($secretaireEmails) ? $secretaireEmails : [$secretaireEmails];
                $secretaireEmails = array_filter($secretaireEmails, fn($e) => $e !== $demande->user->email);

                $rhEmail = config('cofima.email_rh');
                $ccList  = array_values($secretaireEmails);
                if ($rhEmail && $rhEmail !== $demande->user->email) {
                    $ccList[] = $rhEmail;
                }

                $mail = Mail::to($demande->user->email);
                if (!empty($ccList)) {
                    $mail->cc($ccList);
                }
                $mail->send(new LeaveApprovedMail(
                    $demande,
                    $soldes,
                    $anneesPrelevees,
                    $dateRepriseFormatee,
                    $numeroNote,
                    $request->commentaire
                ));
            } else {
                Mail::to($demande->user->email)->send(new LeaveRejectedMail($demande, $request->commentaire));
            }
        } catch (\Throwable $e) {
            Log::error('Erreur envoi email validation finale congé', [
                'demande_id' => $demande->id,
                'action'     => $action,
                'error'      => $e->getMessage(),
                'file'       => $e->getFile() . ':' . $e->getLine(),
                'trace'      => $e->getTraceAsString(),
            ]);
            Alert::warning('Attention', 'La décision a été enregistrée mais l\'envoi de l\'email a échoué.');
            return redirect()->route('conges.validation-finale.index');
        }

        Alert::success('Succès', $action === 'approuve'
            ? 'Congé approuvé définitivement. Notifications envoyées.'
            : 'Congé refusé. L\'employé a été notifié.');

        return redirect()->route('conges.validation-finale.index');
    }

    public function validationFinaleIndex()
    {
        $user = Auth::user();

        if (!$user->hasRole('directeur-general') && !$user->hasRole('rh') && !$user->hasRole('admin')) {
            abort(403, 'Accès non autorisé');
        }

        $demandes = DemandeConge::with(['user', 'typeConge', 'validePar'])
            ->where('statut', 'pre_approuve')
            ->latest()
            ->paginate(20);

        return view('pages.conges.validation-finale', compact('demandes'));
    }

    private function getLogoBase64(): string
    {
        $path = public_path('storage/photos/logo-cofima-bon.jpg');
        if (!file_exists($path)) {
            // Fallback si le logo n'est pas trouvé
            return '';
        }
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }

        public function showValidationFinale($id)
    {
        $demande = DemandeConge::findOrFail($id);
        // Vérifier que l'utilisateur connecté est bien le supérieur final
        // Afficher une vue avec un formulaire qui appelle la route POST
        return view('conges.validation-finale-show', compact('demande'));
    }

        public function preApprouver(Request $request, $id)
    {
        $demande = DemandeConge::findOrFail($id);
        // Mettre à jour le statut (par exemple 'pre_approuve')
        $demande->statut = 'pre_approuve';
        $demande->valide_par = auth()->id();
        $demande->save();

        // Rediriger avec un message
        return redirect()->route('conges.index')->with('success', 'Demande pré‑approuvée.');
    }
}
