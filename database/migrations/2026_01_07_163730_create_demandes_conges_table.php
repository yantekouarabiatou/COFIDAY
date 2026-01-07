<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('demandes_conges', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('type_conge_id')
                  ->constrained('types_conges')
                  ->restrictOnDelete();

            $table->date('date_debut');
            $table->date('date_fin');
            $table->decimal('nombre_jours', 5, 2);

            $table->text('motif')->nullable();

            $table->enum('statut', [
                'en_attente',
                'approuve',
                'refuse',
                'annule'
            ])->default('en_attente');

            $table->foreignId('valide_par')
                  ->nullable()
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();

            $table->timestamp('date_validation')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demandes_conges');
    }
};
