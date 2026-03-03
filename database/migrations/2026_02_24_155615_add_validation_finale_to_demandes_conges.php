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
            // Nouveau statut intermédiaire : 'pre_approuve'
            // Statuts possibles : en_attente | pre_approuve | approuve | refuse | annule
            $table->string('statut_final')->nullable(); // approuve | refuse
            $table->foreignId('valide_par_final')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('date_validation_finale')->nullable();
            $table->text('commentaire_final')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('demandes_conges', function (Blueprint $table) {
            //
        });
    }
};
