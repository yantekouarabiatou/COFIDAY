<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('demandes_conges', function (Blueprint $table) {
            // Fichier justificatif joint par l'employé
            $table->string('fichier_justificatif')->nullable()
                ->comment('Fichier d\'appui joint par l\'employé (PDF, image...)');

            // L'employé souhaite-t-il une attestation de congé ?
            $table->boolean('demande_attestation')->default(false)
                ->comment('L\'employé souhaite une attestation de congé');
            // Statut de l'attestation (null = non demandée, 'en_attente', 'generée', 'envoyée')
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('demandes_conges', function (Blueprint $table) {
            $table->dropColumn(['fichier_justificatif', 'demande_attestation']);
        });
    }
};
