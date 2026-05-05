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
            'roles',
            'manager.roles',
            'conges',            // Charger les congés
            'certificats',       // Charger les certificats
            'attestations',      // Charger les attestations
        ])->findOrFail($id);

        // Récupérer la liste des postes
        $postes = Poste::orderBy('intitule')->get();

        // Calculer les statistiques documentaires
        $statistiques = $this->calculerStatistiquesDocuments($user);

        return view('profile.show', compact('user', 'postes', 'statistiques'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $postes = Poste::orderBy('intitule')->get();
        $roles = Role::orderBy('name')->get();
        $managers = User::whereDoesntHave('roles', function($query) {
                        $query->where('name', 'collaborateur');
                    })
                    ->where('id', '!=', auth()->id()) // Exclure l'utilisateur connecté
                    ->orderBy('nom')
                    ->get();
        return view('pages.users.create', compact('postes', 'roles', 'managers'));
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
            'sexe' => 'nullable|in:M,F',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'required|in:0,1',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'manager_id' => 'nullable|exists:users,id',
            'date_embauche' => 'nullable|date|before_or_equal:today',
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
            'sexe.in' => 'Le sexe doit être "M" ou "F"',
            'photo.image' => 'Le fichier doit être une image',
            'photo.mimes' => 'La photo doit être au format JPG, JPEG ou PNG',
            'photo.max' => 'La photo ne doit pas dépasser 2 Mo',
            'manager_id.exists' => "Le manager sélectionné n'existe pas",
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
                'sexe' => $validated['sexe'] ?? null,
                'password' => Hash::make($validated['password']),
                'is_active' => $validated['is_active'],
                'photo' => $photoPath,
                'created_by' => auth()->id(),
                'manager_id' => $validated['manager_id'] ?? null,
                'date_embauche' => $validated['date_embauche'] ?? null,

            ]);

            // 2. ASSIGNATION SPATIE
            $role = Role::findById($validated['role_id']);
            $roleName = $role->name;
            $user->assignRole($role);

            // Envoi de l'email de bienvenue
            Mail::to($user->email)->send(new UserCreatedMail($user, $roleName));

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
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $postes = Poste::all();
        $roles = Role::all();
        $roleActuel = $user->roles->first();
        $managers = User::whereDoesntHave('roles', function($query) {
                        $query->where('name', 'collaborateur');
                    })
                    ->where('id', '!=', $user->id) // Exclure l'utilisateur lui-même
                    ->orderBy('nom')
                    ->get();

        return view('pages.users.edit', compact('user', 'postes', 'roles', 'roleActuel', 'managers'));
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
            'sexe' => 'nullable|in:M,F',
            'is_active' => 'required|in:0,1',
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'manager_id' => 'nullable|exists:users,id',
            'date_embauche' => 'nullable|date|before_or_equal:today',
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
            'sexe.in' => 'Le sexe doit être "M" ou "F"',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères',
            'password.confirmed' => 'Les mots de passe ne correspondent pas',
            'role_id.required' => 'Le role est obligatoire',
            'role_id.exists' => "Ce role n'existe pas",
            'photo.image' => 'Le fichier doit être une image',
            'photo.mimes' => 'La photo doit être au format JPG, JPEG ou PNG',
            'photo.max' => 'La photo ne doit pas dépasser 2 Mo',
            'manager_id.exists' => "Le manager sélectionné n'existe pas",
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
                'sexe' => $validated['sexe'] ?? null,
                'is_active' => $validated['is_active'],
                'manager_id' => $validated['manager_id'] ?? null,
                'date_embauche' => $validated['date_embauche'] ?? null,

            ];

            if ($newPhotoPath) {
                $updateData['photo'] = $newPhotoPath;
            }

            if (!empty($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $user->update($updateData);

            // Synchronisation du rôle Spatie
            $role = Role::findById($validated['role_id']);
            $roleName = $role->name;
            $user->syncRoles($role);

            // Envoi de l'email de mise à jour
            Mail::to($user->email)->send(new UserUpdateMail($user, auth()->user()->nom . ' ' . auth()->user()->prenom, $roleName));

            Alert::success('Succès', "L'utilisateur a été mis à jour avec succès.");
            return redirect()->route('users.index');
        } catch (\Exception $e) {
            if (isset($newPhotoPath) && Storage::disk('public')->exists($newPhotoPath)) {
                Storage::disk('public')->delete($newPhotoPath);
            }

            Alert::error('Erreur', 'Erreur lors de la modification : ' . $e->getMessage());
            return redirect()->back()->withInput();
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
                Alert::error('Erreur', '❌ Vous ne pouvez pas supprimer votre propre compte !');
                return redirect()->back();
            }

            // 2. Sauvegarder les informations pour le message
            $userName = $user->nom . ' ' . $user->prenom;
            $photoPath = $user->photo;

            // 3. Supprimer la photo de profil si elle existe
            if ($photoPath && Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }

            // 4. Supprimer l'utilisateur de la base de données
            $user->delete();

            // 5. Message de succès
            Alert::success('Succès', "✅ L'utilisateur {$userName} a été supprimé avec succès !");
            return redirect()->route('users.index');
        } catch (Exception $e) {
            Log::error('Erreur suppression utilisateur: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

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
     * Calculer les statistiques documentaires (congés, certificats, attestations)
     */
    private function calculerStatistiquesDocuments($user)
    {
        $now = Carbon::now();
        $debutAnnee = $now->copy()->startOfYear();

        // Congés pris cette année (approuvés)
        $congesApprouves = $user->conges()
            ->where('statut', 'approuvé')
            ->whereBetween('date_debut', [$debutAnnee, $now])
            ->get();

        $congesPris = $congesApprouves->sum(function($conge) {
            if ($conge->date_debut && $conge->date_fin) {
                return $conge->date_debut->diffInDays($conge->date_fin) + 1;
            }
            return 0;
        });

        // Congés en attente
        $congesEnAttente = $user->conges()
            ->where('statut', 'en_attente')
            ->count();

        // Nombre total de certificats
        $certificatsCount = $user->certificats()->count();

        // Nombre total d'attestations
        $attestationsCount = $user->attestations()->count();

        // Dernier certificat
        $dernierCertificat = $user->certificats()->latest()->first();

        // Dernière attestation
        $derniereAttestation = $user->attestations()->latest()->first();

        return [
            'conges_pris'          => $congesPris,
            'conges_en_attente'    => $congesEnAttente,
            'certificats_count'    => $certificatsCount,
            'attestations_count'   => $attestationsCount,
            'dernier_certificat'   => $dernierCertificat,
            'derniere_attestation' => $derniereAttestation,
        ];
    }
}