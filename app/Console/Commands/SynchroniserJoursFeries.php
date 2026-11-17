<?php

namespace App\Console\Commands;

use App\Models\DemandeConge;
use App\Models\HistoriqueConge;
use App\Models\SoldeConge;
use App\Services\JoursFeriesService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Automatisation des jours fériés :
 *  1. Garantit le 1er janvier et le 25 décembre comme fériés récurrents,
 *     et ajoute les fériés calculables (Lundi de Pentecôte) pour l'année
 *     en cours et la suivante.
 *  2. Recalcule le nombre de jours ouvrés des demandes de congé en cours ou
 *     à venir (en attente / pré-approuvées / approuvées). Si un férié tombe
 *     dans la période, le jour est rendu au solde (et inversement si un
 *     férié a été retiré, dans la limite du solde disponible).
 *  3. Signale si les fériés mobiles (Vodun days, Tabaski, Maouloud…) de
 *     l'année suivante n'ont pas encore été saisis.
 */
class SynchroniserJoursFeries extends Command
{
    protected $signature = 'conges:synchroniser-jours-feries
                            {--depuis= : Recalculer les demandes dont la date de fin est >= cette date (AAAA-MM-JJ). Défaut : aujourd\'hui}
                            {--dry-run : Simulation, aucune modification}';

    protected $description = 'Génère les jours fériés et réajuste le nombre de jours / les soldes des demandes de congé concernées';

    private const STATUTS_ACTIFS = ['en_attente', 'pre_approuve', 'approuve'];

    public function handle(): int
    {
        $simulation = (bool) $this->option('dry-run');
        $depuis     = $this->option('depuis') ? Carbon::parse($this->option('depuis'))->startOfDay() : now()->startOfDay();
        $annee      = (int) now()->year;

        if ($simulation) {
            $this->warn('Mode simulation : aucune modification ne sera enregistrée.');
        }

        // ── 1. Génération des fériés ─────────────────────────────────────────
        $ajoutes = JoursFeriesService::completerAnnees([$annee, $annee + 1], $simulation);
        foreach ($ajoutes as $f) {
            $this->info("Férié ajouté : {$f['nom']} - {$f['date']}" . ($f['annee'] ? "-{$f['annee']}" : ' (chaque année)'));
        }

        // ── 2. Rappel fériés mobiles de l'année suivante ─────────────────────
        if (now()->month >= 11 && !JoursFeriesService::anneeAFeriesMobilesSaisis($annee + 1)) {
            $msg = 'Les jours fériés mobiles de ' . ($annee + 1) . ' (Vodun days, Tabaski, Maouloud, Ramadan…) '
                . 'ne sont pas encore saisis dans Règles de congés.';
            $this->warn($msg);
            Log::warning('[JoursFeries] ' . $msg);
        }

        // ── 3. Recalcul des demandes ─────────────────────────────────────────
        $demandes = DemandeConge::with('typeConge')
            ->whereIn('statut', self::STATUTS_ACTIFS)
            ->whereDate('date_fin', '>=', $depuis)
            ->whereNull('nombre_heures')
            ->where(fn($q) => $q->whereNull('statut_suspension')->orWhere('statut_suspension', 'actif'))
            ->where(fn($q) => $q->whereNull('jours_restitues')->orWhere('jours_restitues', 0))
            ->orderBy('id')
            ->get();

        $corrigees = 0;

        foreach ($demandes as $demande) {
            $type = $demande->typeConge;
            if (!$type || ($type->est_horaire ?? false)) {
                continue;
            }

            $ancien  = (float) $demande->getRawOriginal('nombre_jours');
            $nouveau = (float) JoursFeriesService::calculerJoursOuvres($demande->date_debut, $demande->date_fin);

            if ($nouveau <= 0 || abs($nouveau - $ancien) < 0.01 || floor($ancien) != $ancien) {
                continue; // inchangé, ou demi-journée saisie manuellement
            }

            $diff  = $nouveau - $ancien;
            $label = "#{$demande->id} ({$demande->date_debut->format('d/m/Y')} → {$demande->date_fin->format('d/m/Y')}) : {$ancien} j. → {$nouveau} j.";

            if ($simulation) {
                $this->line("[simulation] {$label}");
                $corrigees++;
                continue;
            }

            try {
                DB::transaction(function () use ($demande, $type, $ancien, $nouveau, $diff, $label) {
                    $deductions = $demande->meta_deductions ?? [];

                    if ($type->est_annuel) {
                        $deductions = $diff < 0
                            ? $this->restituer($demande, abs($diff), $deductions)
                            : $this->deduire($demande, $diff, $deductions);
                    }

                    $demande->update([
                        'nombre_jours'    => $nouveau,
                        'meta_deductions' => !empty($deductions) ? $deductions : $demande->meta_deductions,
                    ]);

                    HistoriqueConge::create([
                        'demande_conge_id' => $demande->id,
                        'action'           => 'solde_ajuste',
                        'effectue_par'     => null,
                        'commentaire'      => "Recalcul automatique (jours fériés) : {$ancien} j. → {$nouveau} j."
                            . ($type->est_annuel
                                ? ($diff < 0 ? ' - ' . abs($diff) . ' j. restitué(s) au solde.' : " - {$diff} j. supplémentaire(s) déduit(s).")
                                : ''),
                    ]);
                });

                $this->info("Corrigée {$label}");
                Log::info("[JoursFeries] Demande corrigée {$label}");
                $corrigees++;
            } catch (\Throwable $e) {
                $this->error("Ignorée {$label} : {$e->getMessage()}");
                Log::warning("[JoursFeries] Demande ignorée {$label} : {$e->getMessage()}");
            }
        }

        $this->info("Terminé : {$demandes->count()} demande(s) vérifiée(s), {$corrigees} corrigée(s).");

        return self::SUCCESS;
    }

    /**
     * Rend des jours au solde, en commençant par la déduction la plus récente.
     */
    private function restituer(DemandeConge $demande, float $jours, array $deductions): array
    {
        if (empty($deductions)) {
            $deductions = [['annee' => Carbon::parse($demande->date_debut)->year, 'jours_pris' => $demande->getRawOriginal('nombre_jours')]];
        }

        $reste = $jours;
        for ($i = count($deductions) - 1; $i >= 0 && $reste > 0; $i--) {
            $rendu = min((float) $deductions[$i]['jours_pris'], $reste);
            if ($rendu <= 0) {
                continue;
            }

            $solde = SoldeConge::where('user_id', $demande->user_id)
                ->where('annee', $deductions[$i]['annee'])
                ->lockForUpdate()
                ->first();

            if ($solde) {
                $solde->update([
                    'jours_pris'     => max(0, $solde->jours_pris - $rendu),
                    'jours_restants' => $solde->jours_restants + $rendu,
                ]);
            }

            $deductions[$i]['jours_pris'] = (float) $deductions[$i]['jours_pris'] - $rendu;
            $reste -= $rendu;
        }

        return array_values(array_filter($deductions, fn($d) => (float) $d['jours_pris'] > 0));
    }

    /**
     * Déduit des jours supplémentaires (FIFO, années les plus anciennes d'abord).
     *
     * @throws \RuntimeException si le solde est insuffisant
     */
    private function deduire(DemandeConge $demande, float $jours, array $deductions): array
    {
        $soldes = SoldeConge::where('user_id', $demande->user_id)
            ->where('jours_restants', '>', 0)
            ->orderBy('annee')
            ->lockForUpdate()
            ->get();

        if ($soldes->sum('jours_restants') < $jours) {
            throw new \RuntimeException("solde insuffisant pour déduire {$jours} j. supplémentaire(s)");
        }

        $reste = $jours;
        foreach ($soldes as $solde) {
            if ($reste <= 0) {
                break;
            }
            $pris = min($solde->jours_restants, $reste);
            $solde->update([
                'jours_pris'     => $solde->jours_pris + $pris,
                'jours_restants' => $solde->jours_restants - $pris,
            ]);

            $trouve = false;
            foreach ($deductions as &$d) {
                if ((string) $d['annee'] === (string) $solde->annee) {
                    $d['jours_pris'] = (float) $d['jours_pris'] + $pris;
                    $trouve = true;
                    break;
                }
            }
            unset($d);
            if (!$trouve) {
                $deductions[] = ['annee' => $solde->annee, 'jours_pris' => $pris];
            }

            $reste -= $pris;
        }

        return $deductions;
    }
}
