<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Décharge de remise de l'attestation de travail signée par la Direction
     * Générale : trace qui (secrétariat/RH/admin) l'a remise en main propre à
     * l'employé, et quand. La signature elle-même reste un acte physique hors
     * système ; seule cette remise finale est tracée. Même mécanisme que
     * demandes_conges.attestation_remise_le / attestation_remise_par.
     */
    public function up(): void
    {
        Schema::table('demande_attestations', function (Blueprint $table) {
            $table->timestamp('remise_le')->nullable()->after('numero_reference');
            $table->foreignId('remise_par')->nullable()->after('remise_le')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('demande_attestations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('remise_par');
            $table->dropColumn('remise_le');
        });
    }
};
