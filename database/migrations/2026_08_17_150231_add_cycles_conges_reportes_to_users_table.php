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
        Schema::table('users', function (Blueprint $table) {
            // Nombre de cycles d'ancienneté (périodes de 12 mois depuis
            // date_embauche) déjà clôturés et reportés dans soldes_conges.
            // NULL = jamais initialisé (bootstrap au premier calcul, sans
            // report rétroactif faute d'historique par cycle).
            $table->unsignedInteger('cycles_conges_reportes')->nullable()->after('date_embauche');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('cycles_conges_reportes');
        });
    }
};
