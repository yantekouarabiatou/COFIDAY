<?php

namespace App\Http\Controllers;

use App\Models\DemandeConge;
use App\Models\TypeConge;
use App\Models\SoldeConge;
use App\Models\HistoriqueConge;
use App\Models\RegleConge;
use App\Models\User;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Mail\LeaveRequestMail;
use App\Mail\LeaveApprovedMail;
use App\Mail\LeaveRejectedMail;


class CongeController extends Controller
{
    /**
     * Afficher la liste des demandes de congés
     */
    public function index()
    {
        $user = Auth::user();
        $anneeCourante = now()->year;

        // Charger les relations nécessaires
        $query = DemandeConge::with(['user', 'typeConge', 'validePar']);

        // Filtre pour les non-admins
        if (!$user->hasRole('admin') && !$user->hasRole('manager')) {
            $query->where('user_id', $user->id);
        }

        // Récupérer les types de congés pour les filtres
        $typesConges = TypeConge::where('actif', true)->get();

        // Décider de paginer ou non
        $isAdmin = $user->hasRole('admin') || $user->hasRole('manager');

        if ($isAdmin) {
            // Pagination pour les admins (beaucoup de données)
            $demandes = $query->latest()->paginate(20);

            // Statistiques
            $totalDemandes = $query->count();
            $enAttente = $query->clone()->where('statut', 'en_attente')->count();
            $approuves = $query->clone()->where('statut', 'approuve')->count();
            $refuses = $query->clone()->where('statut', 'refuse')->count();

            $usePagination = true;
        } else {
            // Pas de pagination pour les employés (peu de données)
            $demandes = $query->latest()->get();

            // Statistiques
            $totalDemandes = $demandes->count();
            $enAttente = $demandes->where('statut', 'en_attente')->count();
            $approuves = $demandes->where('statut', 'approuve')->count();
            $refuses = $demandes->where('statut', 'refuse')->count();

            $usePagination = false;
        }

        return view('pages.conges.index', compact(
            'demandes',
            'typesConges',
            'totalDemandes',
            'enAttente',
            'approuves',
            'refuses',
            'usePagination'
        ));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        $user = Auth::user();
        $anneeCourante = now()->year;

        // Récupérer les types de congés actifs
        $typesConges = TypeConge::where('actif', true)->get();

        // Vérifier le solde de congés
        $solde = SoldeConge::where('user_id', $user->id)
            ->where('annee', $anneeCourante)
            ->first();

        if (!$solde) {
            // Créer un solde si inexistant
            $solde = $this->creerSoldeInitial($user->id, $anneeCourante);
        }

        // Récupérer tous les utilisateurs (seulement pour les admins)
        $users = collect(); // Collection vide par défaut

        if ($user->hasRole('admin')) {
            $users = User::select('id', 'nom', 'prenom', 'email')
                ->orderBy('nom')
                ->orderBy('prenom')
                ->get();
        }

        return view('pages.conges.create', compact('typesConges', 'solde', 'users'));
    }
    /**
     * Stocker une nouvelle demande
     */

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();
            $anneeCourante = now()->year;

            // -----------------------------
            // 1. Validation
            // -----------------------------
            $validated = $request->validate([
                'type_conge_id' => 'required|exists:types_conges,id',
                'date_debut' => 'required|date|after_or_equal:today',
                'date_fin' => 'required|date|after_or_equal:date_debut',
                'motif' => 'nullable|string|max:1000',
            ]);

            // -----------------------------
            // 2. Calcul des jours ouvrés
            // -----------------------------
            $dateDebut = Carbon::parse($request->date_debut);
            $dateFin = Carbon::parse($request->date_fin);
            $nombreJours = $this->calculerJoursOuvres($dateDebut, $dateFin);

            // -----------------------------
            // 3. Vérification du type de congé et du solde
            // -----------------------------
            $typeConge = TypeConge::findOrFail($request->type_conge_id);

            if ($typeConge->nombre_jours_max && $nombreJours > $typeConge->nombre_jours_max) {
                Alert::error('Erreur', "Ce type de congé ne peut pas dépasser {$typeConge->nombre_jours_max} jours.");
                return back()->withInput();
            }

            if ($typeConge->est_paye) {
                $solde = SoldeConge::where('user_id', $user->id)
                    ->where('annee', $anneeCourante)
                    ->firstOrFail();

                if ($solde->jours_restants < $nombreJours) {
                    Alert::error('Erreur', "Solde insuffisant. Il vous reste {$solde->jours_restants} jours sur {$solde->jours_acquis} acquis.");
                    return back()->withInput();
                }
            }

            // -----------------------------
            // 4. Création de la demande
            // -----------------------------
            $demande = DemandeConge::create([
                'user_id' => $user->id,
                'type_conge_id' => $request->type_conge_id,
                'date_debut' => $request->date_debut,
                'date_fin' => $request->date_fin,
                'nombre_jours' => $nombreJours,
                'motif' => $request->motif,
                'statut' => 'en_attente',
            ]);

            // Historique
            HistoriqueConge::create([
                'demande_conge_id' => $demande->id,
                'action' => 'demande_soumise',
                'effectue_par' => $user->id,
                'commentaire' => 'Demande initiale soumise',
            ]);

            // -----------------------------
            // 5. Génération du PDF
            // -----------------------------
            $pdf = Pdf::loadView('pdfs.leave_request', [
                'leave' => $demande,
            ]);

            $pdfPath = storage_path("app/temp/demande_{$demande->id}.pdf");

            if (!is_dir(dirname($pdfPath))) {
                mkdir(dirname($pdfPath), 0755, true);
            }

            $pdf->save($pdfPath);

            // -----------------------------
            // 6. Envoi du mail
            // -----------------------------
            $destinataire = 'adisiroko@gmail.com'; // Remplace par l'email réel du manager

            Mail::to($destinataire)->send(new LeaveRequestMail($demande, $pdfPath));

            DB::commit();

            Alert::success('Succès', 'Votre demande de congé a été soumise et un PDF a été envoyé au manager.');
            return redirect()->route('conges.index');

        } catch (\Exception $e) {
            DB::rollBack();
            Alert::error('Erreur', "Une erreur est survenue : {$e->getMessage()}");
            return back()->withInput();
        }
    }


    /**
     * Afficher une demande spécifique
     */
    public function show(DemandeConge $demande)
    {
        $user = Auth::user();

        // Vérifier les permissions
        if (!$user->hasRole('admin') && !$user->hasRole('manager') && $demande->user_id !== $user->id) {
            abort(403, 'Accès non autorisé');
        }

        // Charger TOUTES les relations manquantes avec fallback
        $demande->loadMissing(['user', 'typeConge', 'validePar', 'historiques.effectuePar']);

        // S'assurer que typeConge existe (avec fallback si nécessaire)
        if (!$demande->typeConge) {
            $demande->typeConge = \App\Models\TypeConge::find($demande->type_conge_id);

            // Si le type de congé n'existe pas, créer un objet factice
            if (!$demande->typeConge) {
                $demande->typeConge = new \App\Models\TypeConge();
                $demande->typeConge->libelle = 'Type inconnu';
                $demande->typeConge->est_paye = false;
                $demande->typeConge->nombre_jours_max = null;
                $demande->typeConge->exists = false;
            }
        }

        // S'assurer que l'utilisateur existe (avec fallback si nécessaire)
        if (!$demande->user) {
            $demande->user = \App\Models\User::find($demande->user_id);

            if (!$demande->user) {
                $demande->user = new \App\Models\User();
                $demande->user->id = $demande->user_id;
                $demande->user->prenom = 'Utilisateur';
                $demande->user->nom = 'Supprimé';
                $demande->user->email = 'non.disponible@example.com';
                $demande->user->photo = null;
                $demande->user->exists = false;
            }
        }

        // Déterminer la couleur selon le statut
        $statutColor = match ($demande->statut) {
            'en_attente' => 'warning',
            'approuve' => 'success',
            'refuse' => 'danger',
            'annule' => 'secondary',
            default => 'info',
        };

        return view('pages.conges.show', compact('demande', 'statutColor'));
    }

    /**
     * Afficher le formulaire de modification
     */
    public function edit(DemandeConge $demande)
    {
        $user = Auth::user();

        // Sécurité
        //if ($demande->user_id !== $user->id) {
        //abort(403, 'Accès non autorisé');
        //}

        // Empêcher la modification si la demande n'est plus en attente
        if ($demande->statut !== 'en_attente') {
            Alert::warning('Information', 'Vous ne pouvez pas modifier une demande déjà traitée.');
            return redirect()->route('conges.show', $demande);
        }

        $typesConges = TypeConge::where('actif', true)->get();

        return view('pages.conges.edit', compact('demande', 'typesConges'));
    }

    /**
     * Mettre à jour une demande
     */
    public function update(Request $request, DemandeConge $demande)
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();

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
                'motif' => 'nullable|string|max:1000',
            ]);

            // Calculer le nombre de jours
            $dateDebut = Carbon::parse($request->date_debut);
            $dateFin = Carbon::parse($request->date_fin);
            $nombreJours = $this->calculerJoursOuvres($dateDebut, $dateFin);

            // Mettre à jour
            $demande->update([
                'type_conge_id' => $request->type_conge_id,
                'date_debut' => $request->date_debut,
                'date_fin' => $request->date_fin,
                'nombre_jours' => $nombreJours,
                'motif' => $request->motif,
            ]);

            // Historique
            HistoriqueConge::create([
                'demande_conge_id' => $demande->id,
                'action' => 'demande_modifiee',
                'effectue_par' => $user->id,
                'commentaire' => 'Demande modifiée par l\'employé',
            ]);

            DB::commit();

            Alert::success('Succès', 'La demande de congé a été modifiée avec succès.');
            return redirect()->route('conges.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Alert::error('Erreur', 'Une erreur est survenue lors de la modification.');
            return back()->withInput();
        }
    }

    /**
     * Annuler une demande
     */
    public function annuler(Request $request, DemandeConge $demande)
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();

            // Sécurité
            if (!$user->hasRole('admin') && $demande->user_id !== $user->id) {
                abort(403, 'Accès non autorisé');
            }

            // Vérifier que la demande peut être annulée
            if ($demande->statut !== 'en_attente') {
                Alert::warning('Information', 'Seules les demandes en attente peuvent être annulées.');
                return back();
            }

            // Mettre à jour le statut
            $demande->update([
                'statut' => 'annule'
            ]);

            // Historique
            HistoriqueConge::create([
                'demande_conge_id' => $demande->id,
                'action' => 'demande_annulee',
                'effectue_par' => $user->id,
                'commentaire' => $request->commentaire ?? 'Demande annulée',
            ]);

            DB::commit();

            Alert::success('Succès', 'La demande a été annulée avec succès.');
            return redirect()->route('conges.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Alert::error('Erreur', 'Une erreur est survenue lors de l\'annulation.');
            return back();
        }
    }

    /**
     * Traitement des demandes (pour admin/manager)
     */
    public function traiter(Request $request, DemandeConge $demande)
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();

            // Vérifier les permissions
            if (!$user->hasRole('admin') && !$user->hasRole('manager')) {
                abort(403, 'Accès non autorisé');
            }

            $validated = $request->validate([
                'action' => 'required|in:approuve,refuse',
                'commentaire' => 'nullable|string|max:1000',
            ]);

            $action = $request->action;

            // Vérifier si la demande est encore en attente
            if ($demande->statut !== 'en_attente') {
                Alert::warning('Information', 'Cette demande a déjà été traitée.');
                return back();
            }

            // Mettre à jour la demande
            $demande->update([
                'statut' => $action,
                'valide_par' => $user->id,
                'date_validation' => now(),
            ]);

            // Si approuvé et congé payé, mettre à jour le solde
            if ($action === 'approuve' && $demande->typeConge->est_paye) {

                $solde = SoldeConge::where('user_id', $demande->user_id)
                    ->where('annee', now()->year)
                    ->firstOrFail();

                $solde->update([
                    'jours_pris' => $solde->jours_pris + $demande->nombre_jours,
                    'jours_restants' => $solde->jours_acquis - ($solde->jours_pris + $demande->nombre_jours),
                ]);

                if ($solde->jours_restants < 0) {
                    throw new \Exception('Le solde ne peut pas être négatif');
                }
            }

            // Historique
            HistoriqueConge::create([
                'demande_conge_id' => $demande->id,
                'action' => $action === 'approuve'
                    ? 'demande_approuvee'
                    : 'demande_refusee',
                'effectue_par' => $user->id,
                'commentaire' => $request->commentaire,
            ]);

            // ✅ COMMIT AVANT ENVOI DES MAILS
            DB::commit();

            /*
            |----------------------------------------------------
            | 📧 Envoi des emails
            |----------------------------------------------------
            */

            $demandeur = $demande->user; // l’employé qui a demandé le congé

            if ($action === 'approuve') {

                Mail::to($demandeur->email)
                    ->send(new LeaveApprovedMail($demande));

            } else {

                Mail::to($demandeur->email)
                    ->send(new LeaveRejectedMail(
                        $demande,
                        $request->commentaire
                    ));
            }

            $message = $action === 'approuve'
                ? 'La demande a été approuvée avec succès.'
                : 'La demande a été refusée avec succès.';

            Alert::success('Succès', $message);
            return redirect()->route('conges.index');

        } catch (\Exception $e) {

            DB::rollBack();

            // 👇 DEBUG TEMPORAIRE
            dd(
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            );

            Alert::error('Erreur', 'Une erreur est survenue lors du traitement.');
            return back();
        }

    }

    /**
     * Afficher le tableau de bord des congés (admin)
     */
    public function dashboard()
    {
        $user = Auth::user();

        if (!$user->hasRole('admin') && !$user->hasRole('manager')) {
            abort(403, 'Accès non autorisé');
        }

        // Statistiques
        $stats = [
            'total_demandes' => DemandeConge::count(),
            'en_attente' => DemandeConge::where('statut', 'en_attente')->count(),
            'approuvees' => DemandeConge::where('statut', 'approuve')->count(),
            'refusees' => DemandeConge::where('statut', 'refuse')->count(),
        ];

        // Demandes récentes
        $demandesRecent = DemandeConge::with(['user', 'typeConge'])
            ->where('statut', 'en_attente')
            ->latest()
            ->limit(10)
            ->get();

        // Congés en cours (approuvés et dates actuelles)
        $congesEnCours = DemandeConge::with(['user', 'typeConge'])
            ->where('statut', 'approuve')
            ->where('date_debut', '<=', now())
            ->where('date_fin', '>=', now())
            ->get();

        return view('pages.conges.dashboard', compact('stats', 'demandesRecent', 'congesEnCours'));
    }

    /**
     * Afficher le solde de congés d'un utilisateur
     */
    public function solde(User $user = null)
    {
        $currentUser = Auth::user();

        // Si pas de user spécifié, prendre l'utilisateur connecté
        if (!$user) {
            $user = $currentUser;
        }

        // Vérifier les permissions
        if ($user->id !== $currentUser->id && !$currentUser->hasRole('admin')) {
            abort(403, 'Accès non autorisé');
        }

        $anneeCourante = now()->year;

        // Récupérer les soldes des 3 dernières années
        $soldes = SoldeConge::where('user_id', $user->id)
            ->whereIn('annee', [$anneeCourante - 2, $anneeCourante - 1, $anneeCourante])
            ->orderBy('annee', 'desc')
            ->get();

        return view('pages.conges.solde', compact('user', 'soldes'));
    }

    /**
     * Calculer les jours ouvrés entre deux dates
     */
    /**
     * Calculer les jours ouvrés entre deux dates (exclut weekends)
     */
    private function calculerJoursOuvres(Carbon $dateDebut, Carbon $dateFin): float
    {
        $jours = 0;
        $date = $dateDebut->copy();

        while ($date->lte($dateFin)) {
            // Exclure les weekends
            if (!$date->isWeekend()) {
                $jours += 1;
            }
            $date->addDay();
        }

        return $jours;
    }

    /**
     * Calculer les jours calendaires (inclut weekends)
     */
    private function calculerJoursCalendaires(Carbon $dateDebut, Carbon $dateFin): float
    {
        return $dateDebut->diffInDays($dateFin) + 1; // +1 pour inclure le jour de début
    }

    /**
     * Créer un solde initial pour un utilisateur
     */
    private function creerSoldeInitial(int $userId, int $annee): SoldeConge
    {
        $regles = RegleConge::first();
        $joursAcquis = $regles ? $regles->jours_par_mois * 12 : 25; // 2.5 jours/mois par défaut

        return SoldeConge::create([
            'user_id' => $userId,
            'annee' => $annee,
            'jours_acquis' => $joursAcquis,
            'jours_pris' => 0,
            'jours_restants' => $joursAcquis,
        ]);
    }

    /**
     * Afficher le calendrier des congés
     */
    public function calendrier()
    {
        $user = Auth::user();

        // Récupérer tous les congés approuvés (et éventuellement en attente)
        $query = DemandeConge::with(['user', 'typeConge'])
            ->whereIn('statut', ['approuve', 'en_attente'])
            ->whereNotNull('date_debut')
            ->whereNotNull('date_fin');

        // Si l'utilisateur n'est pas admin, ne voir que ses congés
        if (!$user->hasRole('admin') && !$user->hasRole('manager')) {
            $query->where('user_id', $user->id);
        }

        $conges = $query->get(['id', 'user_id', 'type_conge_id', 'date_debut', 'date_fin', 'statut', 'nombre_jours', 'motif']);

        // Récupérer les types de congés pour les filtres et légendes
        $typesConges = TypeConge::where('actif', true)->get(['id', 'libelle', 'couleur', 'est_paye']);

        return view('pages.conges.calendrier', compact('conges', 'typesConges'));
    }

    public function destroy(DemandeConge $conge)
    {
        // Sécurité
        if (
            !auth()->user()->hasRole('admin') &&
            auth()->id() !== $conge->user_id
        ) {
            abort(403);
        }

        $conge->delete();

        return redirect()
            ->route('conges.index')
            ->with('success', 'Demande supprimée avec succès');
    }
}
