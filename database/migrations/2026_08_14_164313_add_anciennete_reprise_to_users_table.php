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
            // Ancienneté "reprise" : expérience professionnelle antérieure
            // (autres entreprises) reconnue par contrat/RH et créditée en plus
            // de l'ancienneté calculée automatiquement depuis date_embauche.
            // Saisie manuelle par les RH  ne remplace jamais date_embauche.
            $table->unsignedSmallInteger('anciennete_reprise_ans')->default(0)->after('date_embauche');
            $table->unsignedTinyInteger('anciennete_reprise_mois')->default(0)->after('anciennete_reprise_ans');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['anciennete_reprise_ans', 'anciennete_reprise_mois']);
        });
    }
};
