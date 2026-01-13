<?php

namespace Database\Seeders;

use App\Models\Dossier;
use App\Models\Client;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DossierSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::all();

        if ($clients->isEmpty()) {
            $this->command->error('❌ Aucun client trouvé. Exécutez d\'abord ClientSeeder.');
            return;
        }

        $typesDossier = ['audit', 'conseil', 'formation', 'expertise', 'autre'];
        $statuts = ['ouvert', 'en_cours', 'suspendu', 'cloture', 'archive'];

        $dossiers = [];

        foreach ($clients as $client) {
            // Créer entre 1 et 4 dossiers par client
            $nombreDossiers = rand(1, 4);

            for ($i = 1; $i <= $nombreDossiers; $i++) {
                $type = $typesDossier[array_rand($typesDossier)];
                $statut = $statuts[array_rand($statuts)];

                $dateOuverture = Carbon::now()->subMonths(rand(0, 12))->subDays(rand(0, 30));
                $dateCloturePrevue = $statut == 'cloture' || $statut == 'archive'
                    ? $dateOuverture->copy()->addMonths(rand(1, 6))
                    : ($statut == 'en_cours' ? $dateOuverture->copy()->addMonths(rand(3, 12)) : null);

                $dateClotureReelle = $statut == 'cloture' || $statut == 'archive'
                    ? $dateCloturePrevue->copy()->addDays(rand(-15, 15))
                    : null;

                $budget = rand(0, 100) > 30 ? rand(5000, 50000) : null;
                $frais = $budget ? $budget * (rand(5, 20) / 100) : null;

                $dossiers[] = [
                    'client_id' => $client->id,
                    'nom' => $this->generateDossierName($type, $client->nom, $i),
                    'reference' => $this->generateReference($type, $dateOuverture),
                    'type_dossier' => $type,
                    'description' => $this->generateDescription($type, $client->nom),
                    'date_ouverture' => $dateOuverture,
                    'date_cloture_prevue' => $dateCloturePrevue,
                    'date_cloture_reelle' => $dateClotureReelle,
                    'statut' => $statut,
                    'budget' => $budget,
                    'frais_dossier' => $frais,
                    'document' => rand(0, 100) > 60 ? 'dossiers/documents/sample.pdf' : null,
                    'notes' => rand(0, 100) > 50 ? $this->generateNotes($statut) : null,
                    'created_at' => $dateOuverture,
                    'updated_at' => now(),
                ];
            }
        }

        foreach ($dossiers as $dossier) {
            Dossier::create($dossier);
        }

        $this->command->info('📂 ' . count($dossiers) . ' dossiers créés avec succès !');
        $this->command->info('   - Ouverts/En cours: ' . count(array_filter($dossiers, fn($d) => in_array($d['statut'], ['ouvert', 'en_cours']))));
        $this->command->info('   - Clôturés: ' . count(array_filter($dossiers, fn($d) => $d['statut'] == 'cloture')));
        $this->command->info('   - Archivés: ' . count(array_filter($dossiers, fn($d) => $d['statut'] == 'archive')));
        $this->command->info('   - Suspendus: ' . count(array_filter($dossiers, fn($d) => $d['statut'] == 'suspendu')));
    }

    private function generateDossierName(string $type, string $client, int $index): string
    {
        $types = [
            'audit' => ['Audit financier', 'Audit interne', 'Audit réglementaire', 'Audit de conformité'],
            'conseil' => ['Mission de conseil', 'Accompagnement stratégique', 'Consulting organisationnel'],
            'formation' => ['Formation équipe', 'Session de formation', 'Atelier pratique'],
            'expertise' => ['Expertise technique', 'Évaluation spécialisée', 'Rapport d\'expertise'],
            'autre' => ['Mission spéciale', 'Projet particulier', 'Accompagnement personnalisé'],
        ];

        $year = date('Y');
        return $types[$type][array_rand($types[$type])] . ' ' . $client . ' ' . $year . '-' . str_pad($index, 2, '0', STR_PAD_LEFT);
    }

    private function generateReference(string $type, Carbon $date): string
    {
        $prefixes = [
            'audit' => 'AUD',
            'conseil' => 'CON',
            'formation' => 'FOR',
            'expertise' => 'EXP',
            'autre' => 'MIS',
        ];

        $prefix = $prefixes[$type] ?? 'DOS';

        do {
            $numero = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
            $reference = $prefix . '-' . $date->format('Y') . '-' . $numero;
        } while (\App\Models\Dossier::where('reference', $reference)->exists());

        return $reference;
    }


    private function generateDescription(string $type, string $client): string
    {
        $descriptions = [
            'audit' => "Audit complet des processus et procédures de $client. Vérification de la conformité réglementaire et identification des axes d'amélioration.",
            'conseil' => "Mission de conseil stratégique pour accompagner $client dans l'optimisation de ses activités et le développement de nouvelles opportunités.",
            'formation' => "Programme de formation adapté aux besoins spécifiques de $client. Renforcement des compétences et transfert de connaissances.",
            'expertise' => "Expertise technique approfondie sur des aspects spécifiques de l'activité de $client. Analyse et recommandations détaillées.",
            'autre' => "Mission spéciale pour $client couvrant divers aspects de l'activité. Approche personnalisée et sur mesure.",
        ];

        return $descriptions[$type] ?? "Mission pour $client.";
    }

    private function generateNotes(string $statut): string
    {
        $notes = [
            'ouvert' => "Dossier nouvellement ouvert. Premières réunions prévues.",
            'en_cours' => "Mission en cours de réalisation. Rencontres régulières avec le client.",
            'suspendu' => "Mission suspendue en attente de compléments d'information.",
            'cloture' => "Mission clôturée avec succès. Rapport final remis et accepté.",
            'archive' => "Dossier archivé après clôture. Documentation complète disponible.",
        ];

        return $notes[$statut] ?? "Statut: $statut";
    }
}
