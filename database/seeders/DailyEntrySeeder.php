<?php

namespace Database\Seeders;

use App\Models\DailyEntry;
use App\Models\TimeEntry;
use App\Models\User;
use App\Models\Dossier;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DailyEntrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Désactiver temporairement les contraintes de clé étrangère
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Nettoyer les tables existantes (dans le bon ordre)
        TimeEntry::truncate();
        DailyEntry::truncate();

        // Réactiver les contraintes
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Récupérer quelques utilisateurs et dossiers
        $users = User::take(5)->get();
        if ($users->isEmpty()) {
            $this->command->error('❌ Aucun utilisateur trouvé. Exécutez d\'abord UserSeeder.');
            return;
        }

        $dossiers = Dossier::take(10)->get();
        if ($dossiers->isEmpty()) {
            $this->command->error('❌ Aucun dossier trouvé. Exécutez d\'abord DossierSeeder.');
            return;
        }

        // Dates pour janvier (du 1er janvier jusqu'à aujourd'hui)
        $currentYear = date('Y');
        $startDate = Carbon::create($currentYear, 1, 1); // 1er janvier de l'année en cours
        $endDate = Carbon::today(); // Aujourd'hui

        // Si nous sommes après janvier, limiter à la fin du mois de janvier
        if ($endDate->month > 1) {
            $endDate = Carbon::create($currentYear, 1, 31);
        }

        $this->command->info('📅 Génération des feuilles de temps du ' . $startDate->format('d/m/Y') . ' au ' . $endDate->format('d/m/Y'));

        $dailyEntriesCreated = 0;
        $timeEntriesCreated = 0;

        foreach ($users as $user) {
            $currentDate = $startDate->copy();

            $this->command->info('👤 Utilisateur: ' . $user->prenom . ' ' . $user->nom);

            while ($currentDate <= $endDate) {
                // Ne pas créer d'entrées pour les week-ends (optionnel)
                if (!$this->shouldCreateEntryForDate($currentDate, $user)) {
                    $currentDate->addDay();
                    continue;
                }

                // Déterminer le statut
                $statut = $this->getRandomStatus($currentDate);

                // Créer la DailyEntry
                $dailyEntry = DailyEntry::create([
                    'user_id' => $user->id,
                    'jour' => $currentDate->format('Y-m-d'),
                    'heures_theoriques' => $this->getTheoreticalHours($currentDate, $user),
                    'heures_reelles' => 0, // Sera calculé automatiquement
                    'commentaire' => $this->getDailyComment($currentDate),
                    'statut' => $statut,
                    'valide_par' => ($statut == 'validé')
                        ? User::where('id', '!=', $user->id)->inRandomOrder()->first()->id
                        : null,
                    'valide_le' => ($statut == 'validé')
                        ? $currentDate->copy()->addDays(rand(1, 3))->format('Y-m-d H:i:s')
                        : null,
                    'motif_refus' => ($statut == 'refusé')
                        ? $this->getRandomRefusalReason()
                        : null,
                ]);

                $dailyEntriesCreated++;

                // Créer 1 à 4 TimeEntries pour cette journée
                $numActivities = rand(1, 4);
                $timeEntriesCount = $this->createTimeEntries($dailyEntry, $dossiers, $numActivities, $currentDate);
                $timeEntriesCreated += $timeEntriesCount;

                $this->command->info('   ✅ ' . $currentDate->format('d/m/Y') . ' : ' . $numActivities . ' activité(s) créée(s)');

                $currentDate->addDay();
            }
        }

        // Recalculer les heures réelles pour toutes les DailyEntries
        $this->recalculateAllHours();

        // Statistiques
        $totalHeures = DailyEntry::sum('heures_reelles');

        $this->command->info(PHP_EOL . '✅ Seeder DailyEntry et TimeEntry terminé !');
        $this->command->info('📊 Statistiques :');
        $this->command->info('   • DailyEntries créés: ' . $dailyEntriesCreated);
        $this->command->info('   • TimeEntries créés: ' . $timeEntriesCreated);
        $this->command->info('   • Total heures travaillées: ' . number_format($totalHeures, 2) . 'h');
        $this->command->info('   • Période: ' . $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y'));
        $this->command->info(PHP_EOL . '📈 Répartition par statut:');

        $statuts = DailyEntry::select('statut', DB::raw('count(*) as count'))
            ->groupBy('statut')
            ->get();

        foreach ($statuts as $statut) {
            $this->command->info('   • ' . ucfirst($statut->statut) . ': ' . $statut->count);
        }
    }

    /**
     * Détermine si une entrée doit être créée pour cette date
     */
    private function shouldCreateEntryForDate(Carbon $date, User $user): bool
    {
        // Jours fériés en janvier (France)
        $feriesJanvier = [
            $date->year . '-01-01', // Nouvel an
        ];

        // Ne pas créer pour le 1er janvier (jour férié)
        if (in_array($date->format('Y-m-d'), $feriesJanvier)) {
            return false;
        }

        // Ne pas créer pour les week-ends
        if ($date->isWeekend()) {
            // 15% de chance d'avoir une entrée le week-end (travail exceptionnel)
            return rand(1, 100) <= 15;
        }

        // Pour les jours de semaine : 90% de chance d'avoir une entrée
        return rand(1, 100) <= 90;
    }

    /**
     * Détermine les heures théoriques selon le jour
     */
    private function getTheoreticalHours(Carbon $date, User $user): float
    {
        if ($date->isWeekend()) {
            return rand(4, 6) + (rand(0, 3) * 0.25); // Horaires réduits le week-end
        }

        // Heures selon le type d'utilisateur (avec variations aléatoires)
        $baseHours = match($user->type_contrat ?? 'temps_plein') {
            'temps_plein' => 8.00,
            'temps_partiel' => 6.00,
            'stagiaire' => 7.00,
            default => 7.50,
        };

        // Ajouter une petite variation aléatoire (±0.5h)
        $variation = rand(-2, 2) * 0.25;
        return max(4, min(10, $baseHours + $variation)); // Limiter entre 4 et 10h
    }

    /**
     * Génère un commentaire pour la journée
     */
    private function getDailyComment(Carbon $date): ?string
    {
        // 20% de chance d'avoir un commentaire nul
        if (rand(1, 100) <= 20) {
            return null;
        }

        $commentsJanvier = [
            'Journée normale',
            'Reprise après les fêtes',
            'Réunion de début d\'année',
            'Planification des objectifs ' . $date->year,
            'Bilan annuel à finaliser',
            'Préparation des déclarations fiscales',
            'Inventaire de fin d\'année',
            'Formation nouveaux outils',
            'Réunion stratégique Q1',
            'Audit de clôture d\'exercice',
            'Préparation du budget ' . $date->year,
            'Nettoyage des dossiers archivés',
            'Révision des processus internes',
            'Team building post-fêtes',
        ];

        // Commentaires spécifiques pour début janvier
        if ($date->month == 1 && $date->day <= 5) {
            $commentsSpecifiques = [
                'Reprise progressive',
                'Retour de congés',
                'Mise à jour après les fêtes',
                'Démarrage des projets ' . $date->year,
                'Réorganisation du planning',
            ];
            return $commentsSpecifiques[array_rand($commentsSpecifiques)];
        }

        return $commentsJanvier[array_rand($commentsJanvier)];
    }

    /**
     * Retourne un statut aléatoire basé sur la date
     */
    private function getRandomStatus(Carbon $date): string
    {
        // Si c'est aujourd'hui ou dans le futur, probablement brouillon
        if ($date->isToday() || $date->isFuture()) {
            return 'brouillon';
        }

        // Calculer le nombre de jours écoulés
        $daysAgo = $date->diffInDays(Carbon::today());

        // Logique de statut selon l'ancienneté
        if ($daysAgo <= 0) {
            // Aujourd'hui
            return 'brouillon';
        } elseif ($daysAgo <= 2) {
            // Hier et avant-hier
            $options = ['soumis' => 70, 'brouillon' => 25, 'validé' => 5];
        } elseif ($daysAgo <= 7) {
            // Cette semaine
            $options = ['validé' => 50, 'soumis' => 40, 'refusé' => 10];
        } else {
            // Plus d'une semaine
            $options = ['validé' => 80, 'refusé' => 15, 'soumis' => 5];
        }

        return $this->weightedRandom($options);
    }

    /**
     * Génère un motif de refus aléatoire
     */
    private function getRandomRefusalReason(): string
    {
        $reasons = [
            'Heures manquantes ou incomplètes',
            'Activités non détaillées',
            'Dossier incorrect sélectionné',
            'Plage horaire invalide',
            'Heures dépassant le temps théorique',
            'Description des travaux insuffisante',
            'Heures de nuit non autorisées',
            'Dossier fermé ou suspendu',
            'Manque de justification pour heures supplémentaires',
            'Format de saisie incorrect',
            'Justificatifs manquants',
            'Heures saisies pendant les congés',
        ];

        return $reasons[array_rand($reasons)];
    }

    /**
     * Crée les TimeEntries pour une DailyEntry
     */
    private function createTimeEntries(DailyEntry $dailyEntry, $dossiers, int $count, Carbon $date): int
    {
        $startTimes = ['08:00', '08:30', '09:00', '09:30', '10:00', '13:00', '14:00', '15:00'];
        $created = 0;

        for ($i = 0; $i < $count; $i++) {
            // Sélectionner un dossier aléatoire
            $dossier = $dossiers->random();

            // Déterminer l'heure de début
            $heureDebut = $startTimes[array_rand($startTimes)];

            // Déterminer la durée (1 à 4 heures, par quarts d'heure)
            $duree = rand(1, 4) + (rand(0, 3) * 0.25);

            // Calculer l'heure de fin
            $heureFin = Carbon::createFromFormat('H:i', $heureDebut)
                ->addMinutes($duree * 60)
                ->format('H:i');

            // Créer la TimeEntry
            TimeEntry::create([
                'daily_entry_id' => $dailyEntry->id,
                'user_id' => $dailyEntry->user_id,
                'dossier_id' => $dossier->id,
                'heure_debut' => $heureDebut,
                'heure_fin' => $heureFin,
                'heures_reelles' => $duree,
                'travaux' => $this->getRandomWorkDescription($dossier, $date),
                'rendu' => $this->getRandomDeliverable($dossier),
            ]);

            $created++;

            // Ajuster les heures de début suivantes pour éviter les chevauchements
            $nextStart = Carbon::createFromFormat('H:i', $heureFin)->addMinutes(15);
            $startTimes = array_filter($startTimes, function($time) use ($nextStart) {
                $timeCarbon = Carbon::createFromFormat('H:i', $time);
                return $timeCarbon >= $nextStart;
            });

            // Réinitialiser si plus d'options
            if (empty($startTimes)) {
                $startTimes = ['08:00', '08:30', '09:00', '09:30', '10:00', '13:00', '14:00', '15:00'];
            }
        }

        return $created;
    }

    /**
     * Génère une description de travaux aléatoire avec contexte de janvier
     */
    private function getRandomWorkDescription(Dossier $dossier, Carbon $date): string
    {
        // Activités spécifiques pour janvier
        $activitiesJanvier = [
            'Analyse des résultats de fin d\'année',
            'Préparation des déclarations fiscales annuelles',
            'Clôture de l\'exercice comptable',
            'Révision des objectifs pour l\'année ' . $date->year,
            'Planification des audits Q1',
            'Mise à jour des procédures internes',
            'Formation sur les nouvelles réglementations',
            'Inventaire des dossiers en cours',
            'Élaboration du plan d\'action ' . $date->year,
            'Réunion de lancement des projets annuels',
            'Analyse budgétaire prévisionnelle',
            'Préparation des rapports annuels',
            'Audit de conformité début d\'année',
            'Nettoyage des archives',
            'Mise à jour des outils de gestion',
        ];

        // Activités générales
        $activitiesGenerales = [
            'Analyse des documents financiers',
            'Réunion avec le client pour clarification',
            'Rédaction du rapport d\'audit',
            'Vérification des procédures internes',
            'Contrôle des transactions bancaires',
            'Étude de marché préliminaire',
            'Préparation de la présentation',
            'Formation des équipes sur les nouvelles procédures',
            'Révision des contrats',
            'Audit de conformité réglementaire',
        ];

        // Mélanger les activités (60% spécifiques janvier, 40% générales)
        $pool = rand(1, 100) <= 60 ? $activitiesJanvier : $activitiesGenerales;

        return $pool[array_rand($pool)];
    }

    /**
     * Génère un rendu aléatoire
     */
    private function getRandomDeliverable(Dossier $dossier): ?string
    {
        // 30% de chance d'avoir un rendu nul
        if (rand(1, 100) <= 30) {
            return null;
        }

        $deliverables = [
            'Rapport préliminaire v1.0',
            'Présentation Powerpoint',
            'Fichier Excel d\'analyse',
            'Document Word de procédures',
            'Email de synthèse au client',
            'Checklist de vérification',
            'Tableau de bord des indicateurs',
            'Note de service interne',
            'Diaporama de formation',
            'Documentation technique',
            'Plan d\'action détaillé',
            'Compte-rendu de réunion',
            'Proposition commerciale',
            'Budget prévisionnel ' . date('Y'),
        ];

        return $deliverables[array_rand($deliverables)];
    }

    /**
     * Recalcule les heures réelles pour toutes les DailyEntries
     */
    private function recalculateAllHours(): void
    {
        $dailyEntries = DailyEntry::all();

        $this->command->info('🔄 Recalcul des heures réelles...');

        foreach ($dailyEntries as $dailyEntry) {
            $dailyEntry->recalculerHeuresReelles();
        }
    }

    /**
     * Retourne une valeur aléatoire avec probabilités pondérées
     */
    private function weightedRandom(array $weights)
    {
        $total = array_sum($weights);
        $random = rand(1, $total);

        foreach ($weights as $key => $weight) {
            $random -= $weight;
            if ($random <= 0) {
                return $key;
            }
        }

        return array_key_first($weights);
    }
}
