<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Décharge de remise de l'attestation de congé signée par la Direction
     * Générale : trace qui (secrétariat/RH/admin) l'a remise en main propre à
     * l'employé, et quand. La signature DG elle-même reste physique/hors
     * système ; seule la remise finale au salarié est tracée ici.
     */
    public function up(): void
    {
        Schema::table('demandes_conges', function (Blueprint $table) {
            $table->timestamp('attestation_remise_le')->nullable()->after('demande_attestation');
            $table->foreignId('attestation_remise_par')->nullable()->after('attestation_remise_le')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('demandes_conges', function (Blueprint $table) {
            $table->dropConstrainedForeignId('attestation_remise_par');
            $table->dropColumn('attestation_remise_le');
        });
    }
};
