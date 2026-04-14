<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Poste;
use App\Models\Conge;
use App\Models\Certificat;
use App\Models\Attestation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use RealRashid\SweetAlert\Facades\Alert;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;

class UserProfileController extends Controller
{
    /**
     * Afficher le formulaire d'édition du profil (pour l'utilisateur connecté)
     */
    public function editUser($id)
    {
        $user = Auth::user();

        // Charger les relations nécessaires pour les documents
        $user->load([
            'poste',
            'creator',
            'conges' => function($query) {
                $query->latest()->limit(5);
            },
            'certificats' => function($query) {
                $query->latest()->limit(5);
            },
            'attestations' => function($query) {
                $query->latest()->limit(5);
            }
        ]);

        $postes = Poste::orderBy('intitule')->get();
        $roles = Role::orderBy('name')->get();

        // Statistiques documentaires
        $statistiques = $this->calculerStatistiquesDocuments($user);

        return view('profile.edit', compact('user', 'postes', 'roles', 'statistiques'));
    }

    /**
     * Afficher le profil d'un autre utilisateur (pour admin)
     */
    public function showUser($id)
    {
        // Vérifier les permissions
        if (!Auth::user()->hasRole('admin|super-admin') && Auth::id() != $id) {
            abort(403, 'Accès non autorisé');
        }

        $user = User::with([
            'poste',
            'creator',
            'roles',
            'conges' => function($query) {
                $query->latest();
            },
            'certificats' => function($query) {
                $query->latest();
            },
            'attestations' => function($query) {
                $query->latest();
            }
        ])->findOrFail($id);

        $postes = Poste::orderBy('intitule')->get();
        $statistiques = $this->calculerStatistiquesDocuments($user);

        return view('profile.show', compact('user', 'postes', 'statistiques'));
    }

    /**
     * Calculer les statistiques liées aux congés, certificats et attestations
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

    /**
     * Changer le mot de passe
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => [
                'required',
                'current_password'
            ],
            'new_password' => [
                'required',
                'confirmed',
                'different:current_password',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
        ], [
            'current_password.required' => 'Le mot de passe actuel est requis.',
            'current_password.current_password' => 'Le mot de passe actuel est incorrect.',
            'new_password.required' => 'Le nouveau mot de passe est requis.',
            'new_password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'new_password.different' => 'Le nouveau mot de passe doit être différent de l\'actuel.',
            'new_password.min' => 'Le mot de passe doit contenir au moins :min caractères.',
            'new_password.mixed' => 'Le mot de passe doit contenir des majuscules et des minuscules.',
            'new_password.numbers' => 'Le mot de passe doit contenir au moins un chiffre.',
            'new_password.symbols' => 'Le mot de passe doit contenir au moins un caractère spécial.',
            'new_password.uncompromised' => 'Ce mot de passe a été compromis dans une fuite de données. Veuillez en choisir un autre.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Veuillez corriger les erreurs ci-dessous.');
        }

        try {
            $user = Auth::user();
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            Auth::logoutOtherDevices($request->current_password);

            activity()
                ->causedBy($user)
                ->log('Mot de passe modifié');

            Alert::success('Succès', 'Mot de passe changé avec succès!');
            return redirect()->route('user-profile.show', $user->id);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    /**
     * Mettre à jour les informations d'un utilisateur
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'telephone' => 'nullable|string|max:20',
            'poste_id' => 'nullable|exists:postes,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'username' => 'nullable|string|max:255|unique:users,username,' . $id,
            'email' => 'nullable|email|max:255|unique:users,email,' . $id,
            'role_name' => 'nullable|string|exists:roles,name',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Veuillez corriger les erreurs ci-dessous.');
        }

        try {
            $data = [
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'telephone' => $request->telephone,
                'poste_id' => $request->poste_id,
            ];

            if ($request->filled('username')) {
                $data['username'] = $request->username;
            }

            if ($request->filled('email')) {
                $data['email'] = $request->email;
            }

            if (Auth::user()->hasRole('admin|super-admin')) {
                $data['is_active'] = $request->has('is_active')
                    ? $request->is_active
                    : $user->is_active;
            }

            if ($request->hasFile('photo')) {
                if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                    Storage::disk('public')->delete($user->photo);
                }
                $data['photo'] = $request->file('photo')->store('photos/users', 'public');
            }

            $user->update($data);

            if (Auth::user()->hasRole('admin|super-admin') && $request->filled('role_name')) {
                $user->syncRoles([$request->role_name]);
            }

            Alert::success('Mis à jour', 'Utilisateur mis à jour avec succès!');
            return redirect()->route('user-profile.show', $user->id);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Une erreur est survenue: ' . $e->getMessage());
        }
    }

    /**
     * Désactiver un utilisateur
     */
    public function deactivate($id)
    {
        if (!Auth::user()->hasRole('admin|super-admin')) {
            abort(403, 'Accès non autorisé');
        }

        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            Alert::warning('Attention', 'Vous ne pouvez pas désactiver votre propre compte.');
            return redirect()->back();
        }

        $user->update(['is_active' => false]);

        Alert::success('Succès', 'Utilisateur désactivé avec succès.');
        return redirect()->back();
    }

    /**
     * Activer un utilisateur
     */
    public function activate($id)
    {
        if (!Auth::user()->hasRole('admin|super-admin')) {
            abort(403, 'Accès non autorisé');
        }

        $user = User::findOrFail($id);
        $user->update(['is_active' => true]);

        Alert::success('Succès', 'Utilisateur activé avec succès.');
        return redirect()->back();
    }

    /**
     * Télécharger la photo de profil
     */
    public function downloadPhoto($id)
    {
        $user = User::findOrFail($id);

        if (!$user->photo) {
            abort(404, 'Photo non trouvée.');
        }

        $path = storage_path('app/public/' . $user->photo);

        if (!file_exists($path)) {
            abort(404, 'Fichier non trouvé.');
        }

        return response()->download($path);
    }

    /**
     * Exporter les documents (congés, certificats, attestations) de l'utilisateur
     */
    public function exportDocuments($id, $format = 'pdf')
    {
        $user = User::findOrFail($id);

        if (!Auth::user()->hasRole('admin|super-admin') && Auth::id() != $id) {
            abort(403, 'Accès non autorisé');
        }

        // À implémenter selon vos besoins (ex: génération PDF/Excel)
        // Exemple : return PDF::loadView('exports.documents', compact('user'))->download();

        Alert::info('Info', 'Fonctionnalité d\'export en cours de développement.');
        return redirect()->back();
    }
}
