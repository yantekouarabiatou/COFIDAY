<?php

namespace App\Services;

use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClientImportService
{
    private string $apiUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->apiUrl = config('services.cofigistre.url');
        $this->apiKey = config('services.cofigistre.key');
    }

    /**
     * Point d’entrée : récupère tous les clients depuis cofigistre.
     */
    public function importAll(array $filters = [], ?int $userId = null): array
    {
        // Si aucun utilisateur n’est fourni, on prend un utilisateur par défaut
        $userId = $userId ?? $this->getDefaultUserId();

        $page = 1;
        $results = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors'  => [],
        ];

        do {
            $response = Http::withHeaders([
                'X-API-KEY' => $this->apiKey,
                'Accept'    => 'application/json',
            ])->get("{$this->apiUrl}/api/v1/clients", array_merge($filters, ['page' => $page]));

            if (!$response->successful()) {
                // gestion d’erreur...
                return ['success' => false, 'message' => 'Erreur API'];
            }

            $data = $response->json();
            $clients = $data['data'] ?? [];

            foreach ($clients as $client) {
                try {
                    // ✅ On passe bien les 3 arguments
                    $this->importClient($client, $results, $userId);
                } catch (\Exception $e) {
                    Log::error("Erreur import client #{$client['id']}", ['error' => $e->getMessage()]);
                    $results['errors'][] = "Client #{$client['id']} : {$e->getMessage()}";
                }
            }

            $page++;
        } while (!empty($clients) && $page <= ($data['last_page'] ?? 1));

        return ['success' => true, 'results' => $results];
    }

    /**
     * Importe ou met à jour un client.
     */
    private function importClient(array $client, array &$results, int $userId): void
    {
        // Mapping des statuts
        $statut = match ($client['statut'] ?? 'actif') {
            'actif'   => 'actif',
            'inactif' => 'inactif',
            'en_cours' => 'actif',    // ou 'prospect' selon votre choix
            default   => 'actif',
        };

        // Préparation des données pour la table `clients`
        $data = [
            'nom'            => $client['nom'] ?? '',
            'adresse'        => $client['adresse'] ?? null,
            'siege_social'   => $client['siege_social'] ?? null,
            'statut'         => $statut,
            // Les champs suivants n'existent pas dans l'API, on les laisse vides
            'email'          => null,
            'telephone'      => null,
            'contact_principal' => null,
            'secteur_activite' => null,
            'numero_siret'   => null,
            'code_naf'       => null,
            'logo'           => null,
            'site_web'       => null,
            'notes'          => "Importé depuis Cofigistre (ID source: {$client['id']})",
            'source_id'      => (string) $client['id'], // stocke l'ID source
        ];

        // Vérifier si le client existe déjà via source_id
        $existing = Client::where('source_id', $data['source_id'])->first();

        if ($existing) {
            $existing->update($data);
            $results['updated']++;
        } else {
            Client::create($data);
            $results['created']++;
        }
    }

    /**
     * Mapping du statut source vers le statut local.
     * Adaptez selon les valeurs que vous avez.
     */
    private function mapStatut(string $status): string
    {
        return match ($status) {
            'active'   => 'actif',
            'inactive' => 'inactif',
            'pending'  => 'en_cours',
            default    => 'actif',
        };
    }

    /**
     * Retourne l’ID d’un utilisateur par défaut (par exemple le premier admin).
     */
    private function getDefaultUserId(): int
    {
        $user = User::first();
        return $user ? $user->id : 1;
    }
}
