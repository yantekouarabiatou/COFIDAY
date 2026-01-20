<?php

namespace App\Http\Controllers;

use App\Models\LogActivite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogActivitesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Liste des logs d'activité (avec filtres et recherche)
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = LogActivite::query()
            ->select([
                'id',
                'user_id',
                'action',
                'loggable_type',
                'loggable_id',
                'description',
                'ip_address',
                'created_at',
            ])
            ->with('user:id,prenom,nom') // Chargement minimal
            ->latest('created_at');

        // Restriction stricte selon les rôles
        if (!$user->hasAnyRole(['super-admin', 'admin'])) {
            $query->where('user_id', $user->id);
        }

        // Filtres simples (très utiles pour les admins)
        if ($user->hasAnyRole(['super-admin', 'admin'])) {
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->filled('action')) {
                $query->where('action', $request->action);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($sub) use ($search) {
                          $sub->where('prenom', 'like', "%{$search}%")
                              ->orWhere('nom', 'like', "%{$search}%");
                      });
                });
            }
        }

        // Pagination + conservation des filtres dans l'URL
        $logs = $query->paginate(50)->appends($request->query());

        return view('pages.logs.index', compact('logs'));
    }

    /**
     * Affichage détaillé d'un log d'activité
     */
    public function show(LogActivite $log)
    {
        $user = Auth::user();

        // Politique d'accès stricte
        if (!$user->hasAnyRole(['super-admin', 'admin']) && $log->user_id !== $user->id) {
            abort(403, 'Accès refusé.');
        }

        // Chargement intelligent + sécurisé des relations
        $log->loadMissing([
            'user:id,prenom,nom,email,photo', // Plus d'infos utiles pour l'affichage
            'loggable' => function ($query) {
                $query->withTrashed(); // Important pour voir les ressources supprimées
            }
        ]);

        // Optionnel : enrichir avec des données supplémentaires si besoin
        if ($log->loggable && method_exists($log->loggable, 'getReferenceAttribute')) {
            $log->reference = $log->loggable->reference;
        }

        return view('pages.logs.show', compact('log'));
    }

    /**
     * (Optionnel) Marquer tous les logs comme lus (si tu ajoutes un système de "lu")
     * Peut être utile plus tard
     */
    // public function markAllAsRead()
    // {
    //     Auth::user()->logs()->update(['read_at' => now()]);
    //     return back()->with('success', 'Tous les logs marqués comme lus.');
    // }
}
