<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class SoldesCongesSeeder extends Seeder
{
    /**
     * Données extraites du fichier Excel.
     *
     * Chaque valeur représente le SOLDE RESTANT à la fin de l'année concernée.
     * Ces soldes sont la source de vérité : on ne recrée pas les jours pris
     * depuis ici (ce sera le rôle du DemandesCongesSeeder).
     *
     * IMPORTANT : avec la logique FIFO multi-années, chaque année reste un
     * enregistrement DISTINCT dans soldes_conges. Le contrôleur déduit les jours
     * des années les plus anciennes en premier. On ne fusionne donc PAS les
     * reports dans l'année courante.
     */
    private array $soldesData = [
        ['email' => 'ahdomingo@cofima.cc',      'soldes' => [2022 => 0,  2023 => 0,  2024 => 0,  2025 => 23]],
        ['email' => 'agaba@cofima.cc',           'soldes' => [2022 => 0,  2023 => 18, 2024 => 24, 2025 => 24]],
        ['email' => 'canani@cofima.cc',          'soldes' => [2022 => 0,  2023 => 6,  2024 => 0,  2025 => 24]],
        ['email' => 'houorouzoumarou@cofima.cc', 'soldes' => [2022 => 0,  2023 => 0,  2024 => 0,  2025 => 0]],
        ['email' => 'akinkpo@cofima.cc',         'soldes' => [2022 => 0,  2023 => 0,  2024 => 0,  2025 => 19]],
        ['email' => 'falli@cofima.cc',           'soldes' => [2022 => 0,  2023 => 0,  2024 => 0,  2025 => 22]],
        ['email' => 'hzohoun@cofima.cc',         'soldes' => [2022 => 0,  2023 => 0,  2024 => 0,  2025 => 22]],
        ['email' => 'isossa@cofima.cc',          'soldes' => [2022 => 0,  2023 => 0,  2024 => 0,  2025 => 24]],
        ['email' => 'ehoundonougbo@cofima.cc',   'soldes' => [2022 => 0,  2023 => 0,  2024 => 5,  2025 => 24]],
        ['email' => 'jmavande@cofima.cc',        'soldes' => [2022 => 0,  2023 => 0,  2024 => 30, 2025 => 30]],
        ['email' => 'akouzoubanda@cofima.cc',    'soldes' => [2022 => 18, 2023 => 0,  2024 => 24, 2025 => 24]],
        ['email' => 'biroko@cofima.cc',          'soldes' => [2022 => 0,  2023 => 0,  2024 => 0,  2025 => 0]],
        ['email' => 'ryantekoua@cofima.cc',      'soldes' => [2022 => 0,  2023 => 0,  2024 => 0,  2025 => 0]],
        ['email' => 'plima@cofima.cc',           'soldes' => [2022 => 0,  2023 => 0,  2024 => 24, 2025 => 24]],
        ['email' => 'meguagie@cofima.cc',        'soldes' => [2022 => 0,  2023 => 0,  2024 => 0,  2025 => 11]],
        ['email' => 'kouessiahouangnimonpierreguida@gmail.com', 'soldes' => [2022 => 0, 2023 => 0, 2024 => 14, 2025 => 24]],
        ['email' => 'jegbessoua@cofimabenin.cc', 'soldes' => [2022 => 0,  2023 => 0,  2024 => 0,  2025 => 8]],
        ['email' => 'nsacari@cofima.cc',         'soldes' => [2022 => 0,  2023 => 0,  2024 => 0,  2025 => 14]],
    ];

    // Quota annuel standard (peut être surchargé par RegleConge si besoin)
    private const QUOTA_ANNUEL  = 24;
    private const ANNEE_COURANTE = 2026;

    // ─────────────────────────────────────────────────────────────────────────

    public function run(): void
    {
        $this->command->info('🚀 Import des soldes de congés 2022–' . self::ANNEE_COURANTE . '…');

        // ── 1. Insérer / mettre à jour les années 2022-2025 ─────────────────
        $this->importerAnneesHistoriques();

        // ── 2. Créer l'année courante (2026) pour chaque utilisateur ─────────
        //       SANS reporter dans le solde 2026 : les soldes antérieurs
        //       restent disponibles séparément (logique FIFO du contrôleur).
        $this->creerAnneeCourante();

        // ── 3. Pour les utilisateurs NON présents dans $soldesData, créer
        //       un solde 2026 vierge afin qu'ils puissent quand même
        //       soumettre des demandes.
        $this->creerSoldesManquants();

        $this->command->info('✅ Soldes de congés importés avec succès.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Étape 1 : années historiques (2022 → 2025)
    // ─────────────────────────────────────────────────────────────────────────

    private function importerAnneesHistoriques(): void
    {
        foreach ($this->soldesData as $data) {
            $user = User::where('email', $data['email'])->first();

            if (!$user) {
                $this->command->warn("  ⚠  Utilisateur non trouvé : {$data['email']}");
                continue;
            }

            foreach ($data['soldes'] as $annee => $soldeRestant) {
                // Le solde restant provient directement des données Excel.
                // On fixe le quota à la constante standard ; si un employé a
                // un solde restant > quota (cas de report), le quota s'adapte.
                $quota     = max(self::QUOTA_ANNUEL, $soldeRestant);
                $joursPris = max(0, $quota - $soldeRestant);

                DB::table('soldes_conges')->updateOrInsert(
                    [
                        'user_id' => $user->id,
                        'annee'   => $annee,
                    ],
                    [
                        'jours_acquis'   => $quota,
                        'jours_pris'     => $joursPris,
                        'jours_restants' => $soldeRestant,  // ← valeur Excel = source de vérité
                        'jours_reportes' => 0,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]
                );
            }

            // Résumé par utilisateur
            $total = collect($data['soldes'])->filter(fn($v) => $v > 0)->sum();
            $this->command->line("  ✔  {$data['email']} — total disponible (années passées) : {$total} j.");
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Étape 2 : année courante (2026) pour les utilisateurs du fichier Excel
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * POURQUOI on ne fusionne pas le report dans jours_restants de 2026 :
     *
     * Avec la logique FIFO du CongeController, quand un employé pose un congé
     * en 2026, le système regarde d'abord tous les soldes WHERE jours_restants > 0,
     * triés par annee ASC. Il déduit donc automatiquement les soldes 2024 ou 2025
     * avant de toucher au solde 2026.
     *
     * Si on fusionnait les reports dans jours_restants 2026, on perdrait la
     * traçabilité par année et la restitution en cas d'annulation serait fausse.
     *
     * Résultat : l'affichage de la vue "Solde" montrera, par exemple :
     *   2024 : 14 j. disponibles
     *   2025 : 24 j. disponibles
     *   2026 : 24 j. (quota annuel, non encore entamé)
     *   Total : 62 j.
     */
    private function creerAnneeCourante(): void
    {
        $emailsImportes = array_column($this->soldesData, 'email');

        foreach ($this->soldesData as $data) {
            $user = User::where('email', $data['email'])->first();
            if (!$user) {
                continue;
            }

            // Calculer le quota réel pour 2026 (RegleConge si disponible, sinon constante)
            $quota2026 = $this->getQuotaAnnuel();

            // Jours déjà pris en 2026 (si le seeder est rejoué en cours d'année,
            // on ne réinitialise pas les jours_pris existants)
            $existant = DB::table('soldes_conges')
                ->where('user_id', $user->id)
                ->where('annee', self::ANNEE_COURANTE)
                ->first();

            if ($existant) {
                // Le solde existe déjà : on ne touche qu'à jours_acquis si nécessaire
                DB::table('soldes_conges')
                    ->where('user_id', $user->id)
                    ->where('annee', self::ANNEE_COURANTE)
                    ->update([
                        'jours_acquis'   => $quota2026,
                        // Recalculer jours_restants à partir des jours_pris enregistrés
                        'jours_restants' => max(0, $quota2026 - $existant->jours_pris),
                        'jours_reportes' => 0,  // pas de report dans la ligne 2026
                        'updated_at'     => now(),
                    ]);

                $this->command->line(
                    "  ↺  {$data['email']} (2026) — solde existant conservé, "
                    . "jours_pris={$existant->jours_pris}, jours_restants=" . max(0, $quota2026 - $existant->jours_pris)
                );
            } else {
                // Créer un solde 2026 vierge (les reports 2025 restent dans leur propre ligne)
                DB::table('soldes_conges')->insert([
                    'user_id'        => $user->id,
                    'annee'          => self::ANNEE_COURANTE,
                    'jours_acquis'   => $quota2026,
                    'jours_pris'     => 0,
                    'jours_restants' => $quota2026,
                    'jours_reportes' => 0,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);

                $this->command->line(
                    "  ✔  {$data['email']} (2026) — {$quota2026} j. acquis créés."
                );
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Étape 3 : solde 2026 vierge pour les utilisateurs hors fichier Excel
    // ─────────────────────────────────────────────────────────────────────────

    private function creerSoldesManquants(): void
    {
        $emailsImportes = collect($this->soldesData)->pluck('email')->toArray();
        $quota2026      = $this->getQuotaAnnuel();

        $usersManquants = User::whereNotIn('email', $emailsImportes)->get();

        foreach ($usersManquants as $user) {
            // Vérifier si un solde 2026 existe déjà (créé par le contrôleur)
            $existe = DB::table('soldes_conges')
                ->where('user_id', $user->id)
                ->where('annee', self::ANNEE_COURANTE)
                ->exists();

            if ($existe) {
                continue; // ne pas écraser un solde déjà géré
            }

            DB::table('soldes_conges')->insert([
                'user_id'        => $user->id,
                'annee'          => self::ANNEE_COURANTE,
                'jours_acquis'   => $quota2026,
                'jours_pris'     => 0,
                'jours_restants' => $quota2026,
                'jours_reportes' => 0,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            $this->command->line(
                "  ➕  {$user->email} (non listé) — solde 2026 vierge créé ({$quota2026} j.)."
            );
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helper : récupère le quota annuel depuis RegleConge ou utilise la constante
    // ─────────────────────────────────────────────────────────────────────────

    private function getQuotaAnnuel(): int
    {
        try {
            $regles = DB::table('regles_conges')->first();
            if ($regles && isset($regles->jours_par_mois) && $regles->jours_par_mois > 0) {
                return (int) ($regles->jours_par_mois * 12);
            }
        } catch (\Exception $e) {
            // Table non disponible (ex : premier run avant migration) → fallback
        }

        return self::QUOTA_ANNUEL;
    }
}
