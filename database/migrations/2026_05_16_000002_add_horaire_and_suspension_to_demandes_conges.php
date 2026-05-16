<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demandes_conges', function (Blueprint $table) {
            // Permissions à l'heure
            $table->time('heure_debut')->nullable()->after('date_debut')
                ->comment('Heure de début pour les permissions horaires');
            $table->time('heure_fin')->nullable()->after('date_fin')
                ->comment('Heure de fin pour les permissions horaires');
            $table->decimal('nombre_heures', 4, 2)->nullable()->after('nombre_jours')
                ->comment('Durée en heures pour les permissions horaires');

            // Suspension de congé
            $table->enum('statut_suspension', ['actif', 'suspendu', 'repris'])->default('actif')->after('statut')
                ->comment('Gestion de la suspension d\'un congé en cours');
            $table->date('date_suspension')->nullable()->after('statut_suspension')
                ->comment('Date à laquelle le congé a été suspendu');
            $table->integer('jours_restitues')->default(0)->after('date_suspension')
                ->comment('Jours restitués au solde suite à la suspension');
            $table->text('motif_suspension')->nullable()->after('jours_restitues');

            // Justificatif maladie uploadé au retour
            $table->string('justificatif_retour')->nullable()->after('fichier_justificatif')
                ->comment('Justificatif fourni au retour (congé maladie)');
            $table->date('date_depot_justificatif')->nullable()->after('justificatif_retour');
        });
    }

    public function down(): void
    {
        Schema::table('demandes_conges', function (Blueprint $table) {
            $table->dropColumn([
                'heure_debut', 'heure_fin', 'nombre_heures',
                'statut_suspension', 'date_suspension', 'jours_restitues', 'motif_suspension',
                'justificatif_retour', 'date_depot_justificatif',
            ]);
        });
    }
};
