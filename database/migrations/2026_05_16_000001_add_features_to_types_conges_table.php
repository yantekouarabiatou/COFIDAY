<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('types_conges', function (Blueprint $table) {
            // Permissions à l'heure (ex: permission horaire)
            $table->boolean('est_horaire')->default(false)->after('est_annuel')
                ->comment('Si true, la durée se saisit en heures et non en jours');

            // Congés spéciaux ne déduisant pas forcément le solde annuel
            $table->boolean('defalque_du_solde')->default(false)->after('est_horaire')
                ->comment('Si true, ce type déduit le solde annuel de l\'employé');

            // Durée légale selon réglementation béninoise (nullable = libre)
            $table->unsignedInteger('duree_legale_jours')->nullable()->after('defalque_du_solde')
                ->comment('Durée fixée par la loi (non modifiable par l\'employé)');

            // Report possible en fin d'année
            $table->boolean('report_possible')->default(false)->after('duree_legale_jours')
                ->comment('Si true, les jours non consommés peuvent être reportés à N+1');

            // Justificatif obligatoire
            $table->boolean('justificatif_requis')->default(false)->after('report_possible')
                ->comment('Si true, un justificatif est obligatoire pour ce type de congé');
        });
    }

    public function down(): void
    {
        Schema::table('types_conges', function (Blueprint $table) {
            $table->dropColumn([
                'est_horaire',
                'defalque_du_solde',
                'duree_legale_jours',
                'report_possible',
                'justificatif_requis',
            ]);
        });
    }
};
