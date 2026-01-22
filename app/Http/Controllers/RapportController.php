<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\DailyEntry;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RapportController extends Controller
{
    public function mensuel(Request $request)
    {
        // Vérifier les permissions de l'utilisateur connecté
        $user = Auth::user();

        // Seuls les admin, super-admin, rh, manager et directeur-général peuvent voir tous les rapports
        $canViewAll = $user->hasRole(['admin', 'super-admin', 'manager', 'directeur-general', 'responsable-conformite']);

        // Récupérer les paramètres depuis la requête
        $userId = $request->get('user_id');
        $dateFilter = $request->get('date_filter');

        // Déterminer l'année et le mois
        if ($dateFilter) {
            list($year, $month) = explode('-', $dateFilter);
        } else {
            $now = Carbon::now();
            $year = $now->year;
            $month = $now->month;
        }

        $date = Carbon::create($year, $month, 1);
        $start = $date->copy()->startOfMonth();
        $end = $date->copy()->endOfMonth();

        // Récupérer la liste des utilisateurs selon les permissions
        if ($canViewAll) {
            $users = User::orderBy('nom')->get();
        } else {
            // L'utilisateur ne peut voir que lui-même
            $users = User::where('id', $user->id)->orderBy('nom')->get();
        }

        // Initialiser $selectedUser à null par défaut
        $selectedUser = null;

        // Vérifier si l'utilisateur peut sélectionner un autre utilisateur
        if ($userId) {
            // Si l'utilisateur a essayé de sélectionner quelqu'un d'autre mais n'a pas les permissions
            if ($userId != $user->id && !$canViewAll) {
                // Rediriger vers son propre rapport
                return redirect()->route('rapports.mensuel', [
                    'user_id' => $user->id,
                    'date_filter' => $dateFilter
                ]);
            }

            $selectedUser = User::findOrFail($userId);

            // S'assurer que l'utilisateur a le droit de voir ce rapport
            if ($selectedUser->id != $user->id && !$canViewAll) {
                abort(403, 'Vous n\'avez pas la permission de voir les rapports des autres utilisateurs.');
            }

            $dailyEntries = DailyEntry::with('timeEntries.dossier.client')
                ->where('user_id', $userId)
                ->whereBetween('jour', [$start, $end])
                ->orderBy('jour')
                ->get();

$title = "Rapport mensuel du personnel - {$selectedUser->nom} - " . ucfirst($date->locale('fr')->translatedFormat('F Y'));        } else {
            // Si pas d'utilisateur sélectionné et l'utilisateur ne peut voir que ses propres données
            if (!$canViewAll) {
                // Afficher automatiquement son propre rapport
                $selectedUser = $user;
                $dailyEntries = DailyEntry::with('timeEntries.dossier.client')
                    ->where('user_id', $user->id)
                    ->whereBetween('jour', [$start, $end])
                    ->orderBy('jour')
                    ->get();

                $title = "Rapport mensuel - {$selectedUser->nom} - {$date->translatedFormat('F Y')}";
            } else {
                // Pour un rapport global, regrouper par utilisateur (uniquement pour ceux qui ont les droits)
                $dailyEntries = DailyEntry::with('user', 'timeEntries.dossier.client')
                    ->whereBetween('jour', [$start, $end])
                    ->orderBy('user_id')
                    ->orderBy('jour')
                    ->get()
                    ->groupBy('user_id');

                $title = "Rapport mensuel global - {$date->translatedFormat('F Y')}";
            }
        }

        // Pour la vue, passer l'information sur ce que l'utilisateur peut voir
        $canViewAll = $canViewAll; // Passer à la vue

        return view('pages.rapports.mensuel', compact(
            'dailyEntries',
            'users',
            'year',
            'month',
            'title',
            'selectedUser',
            'canViewAll'
        ));
    }
}
