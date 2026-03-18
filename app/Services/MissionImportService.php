<?php

// app/Services/MissionImportService.php

namespace App\Services;

use App\Models\Dossier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Client;

class MissionImportService
{
    private string $apiUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->apiUrl = config('services.cofplan.url');
        $this->apiKey = config('services.cofplan.key');
    }

    /**
     * Point d'entrée principal : récupère et importe toutes les missions
     */
    public function importAll(array $filters = []): array
    {
        $response = Http::withHeaders([
            'X-API-KEY' => $this->apiKey,
            'Accept'    => 'application/json',
        ])->get("{$this->apiUrl}/missions", $filters);

        if (!$response->successful()) {
            Log::error('Erreur API Cofplan', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des missions.',
                'status'  => $response->status(),
            ];
        }

        $data      = $response->json();
        $missions  = $data['data'] ?? [];

        $results = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors'  => [],
        ];

        foreach ($missions as $mission) {
            try {
                $this->importMission($mission, $results);
            } catch (\Exception $e) {
                Log::error("Erreur import mission #{$mission['id']}", [
                    'error' => $e->getMessage(),
                ]);
                $results['errors'][] = "Mission #{$mission['id']} : {$e->getMessage()}";
            }
        }

        return ['success' => true, 'results' => $results];
    }

    /**
     * Importe ou met à jour une mission unique
     */
    private function importMission(array $mission, array &$results): void
    {
        // Mapping statut source → statut dossier
        $statut = $this->mapStatut($mission['status']);

        // Calcul des heures théoriques
        $heuresSansWeekend = null;
        $heuresAvecWeekend = null;

        if (!empty($mission['start_date']) && !empty($mission['end_date'])) {
            $debut = Carbon::parse($mission['start_date']);
            $fin   = Carbon::parse($mission['end_date']);

            $heuresSansWeekend = $this->calculerHeuresSansWeekend($debut, $fin);
            $heuresAvecWeekend = $this->calculerHeuresAvecWeekend($debut, $fin);
        }

        // Données à insérer / mettre à jour
        $data = [
            'nom'                          => $mission['label'],
            'description'                  => $mission['description'] ?? null,
            'type_dossier'                 => 'audit', // valeur par défaut
            'date_ouverture'               => $mission['start_date'] ?? now()->toDateString(),
            'date_cloture_prevue'          => $mission['end_date'] ?? null,
            'statut'                       => $statut,
            'heure_theorique_sans_weekend' => $heuresSansWeekend,
            'heure_theorique_avec_weekend' => $heuresAvecWeekend,
            'notes'                        => "Importé depuis Cofplan (code: {$mission['code']})",
            'client_id'                    => $this->getDefaultClientId(), // On associe toutes les missions au client "Cofplan" par défaut
        ];

        // Chercher par référence (code de la mission source)
        $dossier = Dossier::where('reference', $mission['code'])->first();

        if ($dossier) {
            // Mise à jour
            $dossier->update($data);
            $results['updated']++;
        } else {
            // Création
            $data['reference'] = $mission['code'];
            $dossier = Dossier::create($data);
            $results['created']++;
        }

        // Synchroniser les collaborateurs
        $this->syncCollaborateurs($dossier, $mission['assigned_users'] ?? []);
    }

    private function getDefaultClientId(): int
    {
        $client = Client::where('nom', 'Cofplan')->first();

        return $client->id;
    }

    /**
     * Synchronise les collaborateurs du dossier
     * On ne garde que ceux qui existent dans la base locale
     */
    private function syncCollaborateurs(Dossier $dossier, array $assignedUsers): void
    {
        foreach ($assignedUsers as $assignedUser) {
            // Vérifier si le user existe localement par username ou email
            $localUser = User::where('username', $assignedUser['username'])
                ->orWhere('email', $assignedUser['email'])
                ->first();

            // Si le user n'existe pas localement, on skip
            if (!$localUser) {
                continue;
            }

            // Mapper le type_assign vers un rôle local
            $role = $this->mapRole($assignedUser['type_assign']);

            // Vérifier s'il est déjà collaborateur
            $existingPivot = $dossier->allCollaborateurs()
                ->where('user_id', $localUser->id)
                ->first();

            if ($existingPivot) {
                // Réactiver si inactif + mettre à jour le rôle
                $dossier->allCollaborateurs()->updateExistingPivot($localUser->id, [
                    'role'      => $role,
                    'is_active' => true,
                    'added_at'  => now(),
                ]);
            } else {
                // Ajouter comme nouveau collaborateur
                $dossier->collaborateurs()->attach($localUser->id, [
                    'role'      => $role,
                    'is_active' => true,
                    'added_at'  => now(),
                ]);
            }
        }
    }

    /**
     * Calcul des heures théoriques SANS weekend (jours ouvrables × 8h)
     */
    private function calculerHeuresSansWeekend(Carbon $debut, Carbon $fin): float
    {
        $joursOuvrables = 0;
        $current = $debut->copy();

        while ($current->lte($fin)) {
            // 1 = Lundi ... 5 = Vendredi
            if ($current->isWeekday()) {
                $joursOuvrables++;
            }
            $current->addDay();
        }

        return round($joursOuvrables * 8, 2);
    }

    /**
     * Calcul des heures théoriques AVEC weekend (tous les jours × 8h)
     */
    private function calculerHeuresAvecWeekend(Carbon $debut, Carbon $fin): float
    {
        $totalJours = $debut->diffInDays($fin) + 1;

        return round($totalJours * 8, 2);
    }

    /**
     * Mapping statut mission source → statut dossier local
     */
    private function mapStatut(string $status): string
    {
        return match ($status) {
            'draft'     => 'ouvert',
            'planned'   => 'ouvert',
            'assigned'  => 'en_cours',
            'done'      => 'cloture',
            'abandoned' => 'archive',
            default     => 'ouvert',
        };
    }

    /**
     * Mapping type_assign → rôle collaborateur local
     */
    private function mapRole(string $typeAssign): string
    {
        return match ($typeAssign) {
            'chef_mission' => 'responsable',
            'associe'      => 'collaborateur',
            'consultant'   => 'collaborateur',
            'team'         => 'collaborateur',
            'stagiaire'    => 'stagiaire',
            default        => 'collaborateur',
        };
    }
}