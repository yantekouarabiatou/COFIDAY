<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Dossier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DossierSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('=== Début du DossierSeeder ===');

        // 1. Vérification des dépendances
        $clients = Client::all();
        if ($clients->isEmpty()) {
            $this->command->error('❌ Aucun client trouvé. Exécutez d\'abord ClientSeeder.');
            return;
        }

        $this->command->info("Clients trouvés : {$clients->count()}");

        $users = User::inRandomOrder()->limit(15)->get(); // On prend jusqu'à 15 pour plus de variété
        if ($users->count() < 3) {
            $this->command->error('❌ Pas assez d\'utilisateurs (minimum 3 recommandé). Exécutez UserSeeder.');
            return;
        }

        $this->command->info("Utilisateurs disponibles : {$users->count()}");

        // 2. Transaction + désactivation temporaire des FK
        DB::beginTransaction();

        try {
            $this->command->info('Désactivation temporaire des contraintes de clés étrangères...');
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            $this->command->info('Suppression des anciennes données...');
            DB::table('dossiers')->truncate();
            DB::table('collaborateur_dossier')->truncate();
            // Optionnel : nettoyer time_entries si tu veux repartir de zéro
            // DB::table('time_entries')->truncate();

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $typesDossier = ['audit', 'conseil', 'formation', 'expertise', 'autre'];
            $statuts = ['ouvert', 'en_cours', 'suspendu', 'cloture', 'archive'];

            $createdCount = 0;

            foreach ($clients as $client) {
                // 2 à 7 dossiers par client (plus réaliste)
                $nbDossiers = rand(2, 7);

                for ($i = 1; $i <= $nbDossiers; $i++) {
                    $type = $typesDossier[array_rand($typesDossier)];
                    $statut = $statuts[array_rand($statuts)];

                    // Dates cohérentes
                    $dateOuverture = Carbon::now()
                        ->subMonths(rand(1, 36))           // entre 1 mois et 3 ans
                        ->subDays(rand(0, 45));

                    $dureeMois = rand(4, 24);
                    $dateCloturePrevue = $dateOuverture->copy()->addMonths($dureeMois);

                    $dateClotureReelle = null;
                    if (in_array($statut, ['cloture', 'archive'])) {
                        $dateClotureReelle = $dateCloturePrevue->copy()
                            ->addDays(rand(-45, 45)); // ±1.5 mois
                    }

                    // Budget et frais
                    $budget = $this->getBudgetForType($type);
                    $frais = $budget ? round($budget * (rand(4, 18) / 100), 2) : null;

                    // Heures théoriques
                    $joursOuvrables = $this->calculateWorkingDays($dateOuverture, $dateCloturePrevue);
                    $heuresTheoriques = round($joursOuvrables * (rand(65, 85) / 10), 1); // 6.5h à 8.5h/jour en moyenne

                    $dossier = Dossier::create([
                        'client_id'                  => $client->id,
                        'nom'                        => $this->generateDossierName($type, $client->nom, $dateOuverture->year, $i),
                        'reference'                  => $this->generateUniqueReference($type, $dateOuverture, $client->id, $i),
                        'type_dossier'               => $type,
                        'description'                => $this->generateDescription($type, $client),
                        'date_ouverture'             => $dateOuverture,
                        'date_cloture_prevue'        => $dateCloturePrevue,
                        'date_cloture_reelle'        => $dateClotureReelle,
                        'statut'                     => $statut,
                        'budget'                     => $budget,
                        'frais_dossier'              => $frais,
                        'heure_theorique_sans_weekend' => $heuresTheoriques,
                        'heure_theorique_avec_weekend' => round($heuresTheoriques * 1.25, 1), // approx +25% avec weekends
                        'document'                   => rand(1, 100) > 65 ? 'dossiers/documents/exemple-' . rand(1, 5) . '.pdf' : null,
                        'notes'                      => $this->generateNotes($statut, $client->nom, $dateCloturePrevue),
                        'created_by'                 => $users->random()->id,
                        'created_at'                 => $dateOuverture,
                        'updated_at'                 => $statut === 'ouvert' ? $dateOuverture : now(),
                    ]);

                    $this->assignCollaborators($dossier, $users);

                    $createdCount++;
                }
            }

            DB::commit();

            $this->command->info("🎉 Succès ! $createdCount dossiers créés pour " . $clients->count() . " clients.");
            $this->command->info('Exemple de référence générée : ' . Dossier::inRandomOrder()->first()?->reference ?? 'aucun');
        } catch (\Exception $e) {
            DB::rollBack();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->command->error('❌ ÉCHEC création dossiers');
            $this->command->error('Message : ' . $e->getMessage());
            $this->command->error('Ligne : ' . $e->getLine() . ' | Fichier : ' . basename($e->getFile()));
        }
    }

    // ==============================================
    //      Fonctions utilitaires (simplifiées)
    // ==============================================

    private function getBudgetForType(string $type): ?float
    {
        if (rand(1, 100) <= 15) return null; // 15% sans budget

        return match ($type) {
            'audit'     => rand(12000, 95000),
            'conseil'   => rand(18000, 140000),
            'formation' => rand(6000, 48000),
            'expertise' => rand(25000, 180000),
            default     => rand(9000, 75000),
        };
    }

    private function calculateWorkingDays(Carbon $start, Carbon $end): int
    {
        $days = 0;
        $current = $start->copy();

        while ($current->lte($end)) {
            if (!$current->isWeekend()) $days++;
            $current->addDay();
        }

        return $days;
    }

    private function generateDossierName(string $type, string $clientName, int $year, int $index): string
    {
        $prefixes = [
            'audit' => 'Audit',
            'conseil' => 'Conseil',
            'formation' => 'Formation',
            'expertise' => 'Expertise',
            'autre' => 'Mission'
        ];

        $prefix = $prefixes[$type] ?? 'Projet';
        return "$prefix $clientName $year-" . str_pad($index, 2, '0', STR_PAD_LEFT);
    }

    private function generateUniqueReference(string $type, Carbon $date, int $clientId, int $index): string
    {
        $prefixes = ['audit' => 'AUD', 'conseil' => 'CON', 'formation' => 'FOR', 'expertise' => 'EXP', 'autre' => 'PRO'];
        $prefix = $prefixes[$type] ?? 'DOS';

        $clientCode = str_pad($clientId % 10000, 4, '0', STR_PAD_LEFT);
        $yearMonth = $date->format('ym');
        $seq = str_pad($index, 3, '0', STR_PAD_LEFT);

        return "$prefix-$clientCode-$yearMonth-$seq";
    }

    private function generateDescription(string $type, Client $client): string
    {
        $secteur = $client->secteur_activite ?? 'secteur non précisé';

        return match ($type) {
            'audit' => "Audit complet des processus et conformité de {$client->nom} dans le secteur $secteur.",
            'conseil' => "Accompagnement stratégique et optimisation organisationnelle pour {$client->nom} ($secteur).",
            'formation' => "Programme de formation ciblé pour les équipes de {$client->nom} dans le domaine $secteur.",
            'expertise' => "Expertise technique approfondie et évaluation spécialisée pour {$client->nom}.",
            default     => "Mission d'accompagnement personnalisée pour {$client->nom} dans le secteur $secteur."
        } . " Période : " . rand(6, 24) . " mois.";
    }

    private function generateNotes(string $statut, string $clientName, ?Carbon $endDate): string
    {
        if ($endDate) {
            $dateStr = $endDate->format('d/m/Y');
            return match ($statut) {
                'cloture' => "Clôturé le $dateStr - Rapport transmis à $clientName.",
                'archive' => "Archivé après clôture du $dateStr.",
                default => "En cours - Suivi mensuel avec $clientName."
            };
        }

        return "Dossier actif - Suivi régulier prévu avec $clientName.";
    }

    private function assignCollaborators(Dossier $dossier, $users): void
    {
        // Créateur = responsable principal
        $creator = $users->random();
        $dossier->addCollaborateur($creator->id, 'responsable');

        // 1 à 5 collaborateurs supplémentaires
        $nb = rand(1, 5);
        $others = $users->where('id', '!=', $creator->id)->random(min($nb, $users->count() - 1));

        $roles = ['collaborateur', 'expert', 'contrôleur', 'assistant', 'référent'];

        foreach ($others as $user) {
            $dossier->addCollaborateur($user->id, $roles[array_rand($roles)]);
        }
    }
}
