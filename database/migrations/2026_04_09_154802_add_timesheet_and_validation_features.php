<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajouter le manager (supérieur hiérarchique) sur les utilisateurs
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('manager_id')
                ->nullable()
                ->after('poste_id')
                ->constrained('users')
                ->nullOnDelete();
        });

        // Ajouter la semaine ISO et marquer les jours manquants
        Schema::table('daily_entries', function (Blueprint $table) {
            $table->unsignedSmallInteger('semaine')->nullable()->after('jour')
                ->comment('Numéro de semaine ISO (1-53)');
            $table->year('annee_semaine')->nullable()->after('semaine')
                ->comment('Année ISO correspondant à la semaine');
            $table->boolean('est_manquant')->default(false)->after('annee_semaine')
                ->comment('True si la feuille est générée automatiquement comme jour manquant');
        });

        // Table pour les rappels / notifications de saisie
        Schema::create('timesheet_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date_rappel');
            $table->enum('type', ['missing_day', 'weekly_summary', 'pending_validation']);
            $table->boolean('sent')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'date_rappel', 'type']);
        });

        // Garder une trace des validations hebdomadaires
        Schema::create('weekly_validations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('validated_by')->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('semaine');
            $table->year('annee');
            $table->enum('statut', ['en_attente', 'validé', 'refusé'])->default('en_attente');
            $table->text('motif_refus')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'semaine', 'annee']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_validations');
        Schema::dropIfExists('timesheet_reminders');

        Schema::table('daily_entries', function (Blueprint $table) {
            $table->dropColumn(['semaine', 'annee_semaine', 'est_manquant']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
            $table->dropColumn('manager_id');
        });
    }
};