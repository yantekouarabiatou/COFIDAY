<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Table attestations ────────────────────────────────────────────────
        Schema::create('demande_attestations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            /*
             * Types :
             *   attestation_simple    → démarches admin courantes
             *   attestation_banque    → usage banque/crédit (salaire optionnel)
             *   attestation_ambassade → ambassade / visa
             *   attestation_autre     → format libre — le RH rédige manuellement
             */
            $table->enum('type', [
                'attestation_simple',
                'attestation_banque',
                'attestation_ambassade',
                'attestation_autre',
            ]);

            $table->text('motif');

            // Champs spécifiques selon le type
            $table->string('destinataire')->nullable();
            $table->decimal('salaire_net', 12, 2)->nullable();
            $table->boolean('inclure_salaire')->default(false);

            // Workflow
            $table->enum('statut', ['en_attente', 'approuve', 'refuse'])->default('en_attente');
            $table->foreignId('valide_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('date_validation')->nullable();
            $table->text('commentaire_dg')->nullable();
            $table->string('numero_reference')->nullable()->unique();

            $table->timestamps();
            $table->softDeletes();
        });

        // ── Table démissions ──────────────────────────────────────────────────
        Schema::create('demande_demissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->date('date_depart_souhaitee');
            $table->text('lettre');

            // Workflow
            $table->enum('statut', ['en_attente', 'acceptee', 'refusee'])->default('en_attente');
            $table->foreignId('valide_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('date_validation')->nullable();
            $table->text('commentaire_dg')->nullable();

            // Certificat de travail généré automatiquement à l'acceptation
            $table->string('numero_certificat')->nullable()->unique();
            $table->boolean('certificat_genere')->default(false);
            $table->timestamp('date_generation_certificat')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demande_demissions');
        Schema::dropIfExists('demande_attestations');
    }
};
