<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Nouveaux types de congés conformes à la réglementation béninoise
        $types = [
            // ── Congés spéciaux (Code du travail Bénin) ────────────────────────
            [
                'libelle'            => 'Congé spécial - Mariage (employé)',
                'nombre_jours_max'   => 5,
                'est_paye'           => true,
                'est_annuel'         => false,
                'est_horaire'        => false,
                'defalque_du_solde'  => false,
                'duree_legale_jours' => 5,
                'report_possible'    => false,
                'justificatif_requis'=> true,
                'actif'              => true,
                'couleur'            => '#F472B6',
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'libelle'            => 'Congé spécial - Mariage (enfant)',
                'nombre_jours_max'   => 2,
                'est_paye'           => true,
                'est_annuel'         => false,
                'est_horaire'        => false,
                'defalque_du_solde'  => false,
                'duree_legale_jours' => 2,
                'report_possible'    => false,
                'justificatif_requis'=> true,
                'actif'              => true,
                'couleur'            => '#F9A8D4',
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'libelle'            => 'Congé spécial - Décès (conjoint / enfant)',
                'nombre_jours_max'   => 3,
                'est_paye'           => true,
                'est_annuel'         => false,
                'est_horaire'        => false,
                'defalque_du_solde'  => false,
                'duree_legale_jours' => 3,
                'report_possible'    => false,
                'justificatif_requis'=> true,
                'actif'              => true,
                'couleur'            => '#6B7280',
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'libelle'            => 'Congé spécial - Décès (parent / frère / sœur)',
                'nombre_jours_max'   => 3,
                'est_paye'           => true,
                'est_annuel'         => false,
                'est_horaire'        => false,
                'defalque_du_solde'  => false,
                'duree_legale_jours' => 3,
                'report_possible'    => false,
                'justificatif_requis'=> true,
                'actif'              => true,
                'couleur'            => '#4B5563',
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'libelle'            => 'Congé spécial - Déménagement',
                'nombre_jours_max'   => 1,
                'est_paye'           => true,
                'est_annuel'         => false,
                'est_horaire'        => false,
                'defalque_du_solde'  => false,
                'duree_legale_jours' => 1,
                'report_possible'    => false,
                'justificatif_requis'=> false,
                'actif'              => true,
                'couleur'            => '#FBBF24',
                'created_at'         => now(),
                'updated_at'         => now(),
            ],

            // ── Congé sabbatique ────────────────────────────────────────────────
            [
                'libelle'            => 'Congé sabbatique',
                'nombre_jours_max'   => 365,
                'est_paye'           => false,
                'est_annuel'         => false,
                'est_horaire'        => false,
                'defalque_du_solde'  => false,
                'duree_legale_jours' => null,
                'report_possible'    => false,
                'justificatif_requis'=> true,
                'actif'              => true,
                'couleur'            => '#7C3AED',
                'created_at'         => now(),
                'updated_at'         => now(),
            ],

            // ── Permission horaire ───────────────────────────────────────────────
            [
                'libelle'            => 'Permission horaire',
                'nombre_jours_max'   => null,
                'est_paye'           => true,
                'est_annuel'         => false,
                'est_horaire'        => true,
                'defalque_du_solde'  => false,
                'duree_legale_jours' => null,
                'report_possible'    => false,
                'justificatif_requis'=> false,
                'actif'              => true,
                'couleur'            => '#0EA5E9',
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
        ];

        // Insert uniquement si le libellé n'existe pas déjà
        foreach ($types as $type) {
            DB::table('types_conges')->updateOrInsert(
                ['libelle' => $type['libelle']],
                $type
            );
        }

        // Marquer les types existants nécessitant un justificatif médical
        DB::table('types_conges')
            ->whereIn('libelle', ['Congés maladie', 'Congés maternité', 'Congés paternité'])
            ->update(['justificatif_requis' => true]);
    }

    public function down(): void
    {
        $libelles = [
            'Congé spécial - Mariage (employé)',
            'Congé spécial - Mariage (enfant)',
            'Congé spécial - Décès (conjoint / enfant)',
            'Congé spécial - Décès (parent / frère / sœur)',
            'Congé spécial - Déménagement',
            'Congé sabbatique',
            'Permission horaire',
        ];
        DB::table('types_conges')->whereIn('libelle', $libelles)->delete();
    }
};
