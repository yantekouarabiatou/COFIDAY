<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plainte;
use App\Models\Assignation;
use App\Models\Poste;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Mail\UserCreatedMail;
use App\Mail\UserUpdateMail;
use App\Models\User as ModelsUser;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        // Lecture : Liste et Détails
        $this->middleware('permission:voir les utilisateurs')
            ->only(['index', 'show']);

        // Création : Formulaire et Enregistrement
        $this->middleware('permission:créer des utilisateurs')
            ->only(['create', 'store']);

        // Modification : Formulaire et Mise à jour
        // Note : On peut aussi inclure 'assigner des rôles' ici si c'est géré dans l'update
        $this->middleware('permission:modifier les utilisateurs')
            ->only(['edit', 'update']);

        // Cas spécifique : Si vous avez une méthode dédiée au changement de rôle
        $this->middleware('permission:assigner des rôles')
            ->only(['updateRole']); // à adapter selon le nom de votre méthode

        // Suppression
        $this->middleware('permission:supprimer des utilisateurs')
            ->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with('poste')->latest()->get();
        $postes = Poste::orderBy('intitule')->get(); // Tous les postes pour le select
        return view('pages.users.index', compact('users', 'postes'));
    }

    public function show($id)
    {
        // Vérifier les permissions
        if (!Auth::user()->hasRole('admin|super-admin') && Auth::id() != $id) {
            abort(403, 'Accès non autorisé');
        }

        $user = User::with([
            'poste',
            'creator',
            'roles', // Charger les rôles Spatie
            'dailyEntries' => function ($query) {
                $query->latest('jour')->limit(10);
            },
            'timeEntries' => function ($query) {
                $query->with('dossier')->latest()->limit(10);
            },
            'conges' => function ($query) {
                $query->latest()->limit(5);
            }
        ])->findOrFail($id);

        // Récupérer la liste des postes
        $postes = Poste::orderBy('intitule')->get();

        // Calculer les statistiques
        $statistiques = $this->calculerStatistiquesTemps($user);

        return view('profile.show', compact('user', 'postes', 'statistiques'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $postes = Poste::orderBy('intitule')->get(); // Tous les postes pour le select
        $roles = Role::orderBy('name')->get(); // Tous les postes pour le select
        return view('pages.users.create', compact('postes', 'roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'poste_id' => 'required|exists:postes,id',
            'telephone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'required|in:0,1',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'nom.required' => 'Le nom est obligatoire',
            'prenom.required' => 'Le prénom est obligatoire',
            'username.required' => "Le nom d'utilisateur est obligatoire",
            'username.unique' => "Ce nom d'utilisateur existe déjà",
            'email.required' => "L'email est obligatoire",
            'email.email' => "L'email n'est pas valide",
            'email.unique' => 'Cette adresse email existe déjà',
            'poste_id.required' => 'Le poste est obligatoire',
            'poste_id.exists' => "Ce poste n'existe pas",
            'role_id.required' => 'Le role est obligatoire',
            'role_id.exists' => "Ce role n'existe pas",
            'password.required' => 'Le mot de passe est obligatoire',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères',
            'password.confirmed' => 'Les mots de passe ne correspondent pas',
            'photo.image' => 'Le fichier doit être une image',
            'photo.mimes' => 'La photo doit être au format JPG, JPEG ou PNG',
            'photo.max' => 'La photo ne doit pas dépasser 2 Mo',
        ]);

        try {
            // Gestion de la photo
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $photoName = 'user_' . time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                $photoPath = $photo->storeAs('photos/users', $photoName, 'public');
            }

            // Création utilisateur
            $user = User::create([
                'nom' => $validated['nom'],
                'prenom' => $validated['prenom'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'poste_id' => $validated['poste_id'],
                'telephone' => $validated['telephone'] ?? null,
                'password' => Hash::make($validated['password']),
                'role_id' => $validated['role_id'],
                'is_active' => $validated['is_active'],
                'photo' => $photoPath,
                'created_by' => auth()->id(),
            ]);

            // 2. ASSIGNATION SPATIE (C'est ici que la magie opère)
            // =========================================================
            // On récupère l'objet Rôle depuis son ID
            $role = Role::findById($validated['role_id']);

            // On l'assigne à l'utilisateur (remplit la table model_has_roles)
            $user->assignRole($role);

            // après User::create(...)
            Mail::to($user->email)->send(new UserCreatedMail($user));

            Alert::success('Succès', 'Utilisateur créé avec succès !')->persistent('OK');
            return redirect()->back();
        } catch (Exception $e) {

            if (isset($photoPath) && Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }

            Alert::error('Erreur', 'Création échouée : ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    // public function show(string $id)
    // {
    //     //
    // }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $postes = Poste::all();
        $roles = Role::all();
        return view('pages.users.edit', compact('user', 'postes', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        // Validation des données
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'poste_id' => 'required|exists:postes,id',
            'telephone' => 'nullable|string|max:20',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'required|in:0,1',
            'password' => 'nullable|string|min:8|confirmed',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'nom.required' => 'Le nom est obligatoire',
            'prenom.required' => 'Le prénom est obligatoire',
            'username.required' => "Le nom d'utilisateur est obligatoire",
            'username.unique' => "Ce nom d'utilisateur existe déjà",
            'email.required' => "L'email est obligatoire",
            'email.email' => "L'email n'est pas valide",
            'email.unique' => 'Cette adresse email existe déjà',
            'poste_id.required' => 'Le poste est obligatoire',
            'poste_id.exists' => "Ce poste n'existe pas",
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères',
            'password.confirmed' => 'Les mots de passe ne correspondent pas',
            'photo.image' => 'Le fichier doit être une image',
            'photo.mimes' => 'La photo doit être au format JPG, JPEG ou PNG',
            'photo.max' => 'La photo ne doit pas dépasser 2 Mo',
        ]);
        try {
            $oldPhotoPath = $user->photo;
            $newPhotoPath = null;

            // Gestion upload
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $photoName = 'user_' . time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                $newPhotoPath = $photo->storeAs('photos/users', $photoName, 'public');

                // Suppression ancienne photo
                if ($oldPhotoPath && Storage::disk('public')->exists($oldPhotoPath)) {
                    Storage::disk('public')->delete($oldPhotoPath);
                }
            }

            $updateData = [
                'nom' => $validated['nom'],
                'prenom' => $validated['prenom'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'poste_id' => $validated['poste_id'],
                'telephone' => $validated['telephone'] ?? null,
                'role_id' => $validated['role_id'],
                'is_active' => $validated['is_active'],
            ];

            if ($newPhotoPath) {
                $updateData['photo'] = $newPhotoPath;
            }

            if (!empty($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $user->update($updateData);

            // =========================================================
            // 2. SYNCHRONISATION SPATIE
            // =========================================================
            // On récupère le nouveau rôle choisi
            $role = Role::findById($validated['role_id']);

            // On remplace tous les anciens rôles par celui-ci
            $user->syncRoles($role);

            // Envoyer un mail à l'utilisateur
            Mail::to($user->email)->send(new UserUpdateMail($user, auth()->user()->nom . ' ' . auth()->user()->prenom));
            Alert::success('Succès', "L'utilisateur a été mis à jour avec succès.");
            return redirect()->route('users.index');
        } catch (\Exception $e) {

            if (isset($newPhotoPath) && Storage::disk('public')->exists($newPhotoPath)) {
                Storage::disk('public')->delete($newPhotoPath);
            }

            Alert::error('Erreur', 'Erreur lors de la modification : ' . $e->getMessage());
            return redirect()->back()
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */

    public function destroy(User $user)
    {
        try {
            // 1. Vérifier que l'utilisateur ne se supprime pas lui-même
            if (auth()->id() === $user->id) {
                return redirect()->back()
                    ->with('error', '❌ Vous ne pouvez pas supprimer votre propre compte !');
            }

            // 2. Vérifier les permissions (optionnel - si vous avez un système de rôles)
            // if (auth()->user()->role !== 'admin') {
            //     return redirect()->back()
            //         ->with('error', '❌ Vous n\'avez pas les permissions pour supprimer un utilisateur !');
            // }

            // 3. Sauvegarder les informations pour le message
            $userName = $user->nom . ' ' . $user->prenom;
            $photoPath = $user->photo;

            // 4. Gérer les relations avant suppression (si nécessaire)
            // Exemple : réassigner les enregistrements créés par cet utilisateur
            // User::where('created_by', $user->id)->update(['created_by' => null]);
            // CadeauInvitation::where('user_id', $user->id)->update(['user_id' => null]);

            // 5. Supprimer la photo de profil si elle existe
            if ($photoPath && Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }

            // 6. Supprimer l'utilisateur de la base de données
            $user->delete();

            // 7. Message de succès
            Alert::success('Succès', "✅ L'utilisateur {$userName} a été supprimé avec succès !");
            return redirect()->route('users.index');
        } catch (Exception $e) {
            // Log de l'erreur
            Log::error('Erreur suppression utilisateur: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            // Message d'erreur
            // Retour avec message d'erreur
            Alert::error('Erreur', '❌ Erreur lors de la suppression : ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function toggleStatus(Request $request, User $user)
    {
        $user->is_active = !$user->is_active;
        $user->save();

        return response()->json([
            'success' => true,
            'status' => $user->is_active ? 'Actif' : 'Inactif'
        ]);
    }

    /**
     * Calculer les statistiques de temps pour un utilisateur
     */
    private function calculerStatistiquesTemps($user)
    {
        $now = Carbon::now();
        $debutMois = $now->copy()->startOfMonth();
        $finMois = $now->copy()->endOfMonth();

        // Statistiques globales
        $totalDailyEntries = $user->dailyEntries()->count();
        $totalTimeEntries = $user->timeEntries()->count();
        $totalConges = $user->conges()->count();

        // Heures du mois en cours
        $heuresMoisEnCours = $user->dailyEntries()
            ->whereBetween('jour', [$debutMois, $finMois])
            ->sum('heures_reelles');

        // Heures théoriques du mois
        $heuresTheoriquesMois = $user->dailyEntries()
            ->whereBetween('jour', [$debutMois, $finMois])
            ->sum('heures_theoriques');

        // Écart heures (réelles - théoriques)
        $ecartHeures = $heuresMoisEnCours - $heuresTheoriquesMois;

        // Taux de réalisation
        $tauxRealisation = $heuresTheoriquesMois > 0
            ? round(($heuresMoisEnCours / $heuresTheoriquesMois) * 100, 1)
            : 0;

        // Jours de congés pris cette année (calculé depuis les dates)
        $debutAnnee = $now->copy()->startOfYear();
        $congesApprouves = $user->conges()
            ->whereBetween('date_debut', [$debutAnnee, $now])
            ->get();

        // Calculer le total des jours de congé
        $congesPris = $congesApprouves->sum(function ($conge) {
            if ($conge->date_debut && $conge->date_fin) {
                return $conge->date_debut->diffInDays($conge->date_fin) + 1;
            }
            return 0;
        });

        // Congés en attente
        $congesEnAttente = $user->conges()
            ->count();

        // Dernière entrée de temps
        $derniereEntree = $user->dailyEntries()
            ->latest('jour')
            ->first();

        // Journées validées ce mois
        $journeesValidees = $user->dailyEntries()
            ->whereBetween('jour', [$debutMois, $finMois])
            ->count();

        // Journées en attente
        $journeesEnAttente = $user->dailyEntries()
            ->count();

        return [
            'total_daily_entries' => $totalDailyEntries,
            'total_time_entries' => $totalTimeEntries,
            'total_conges' => $totalConges,
            'heures_mois_en_cours' => round($heuresMoisEnCours, 2),
            'heures_theoriques_mois' => round($heuresTheoriquesMois, 2),
            'ecart_heures' => round($ecartHeures, 2),
            'taux_realisation' => $tauxRealisation,
            'conges_pris' => $congesPris,
            'conges_en_attente' => $congesEnAttente,
            'derniere_entree' => $derniereEntree,
            'journees_validees' => $journeesValidees,
            'journees_en_attente' => $journeesEnAttente,
        ];
    }
}
