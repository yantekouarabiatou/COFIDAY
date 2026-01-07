<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('historiques_conges', function (Blueprint $table) {
            $table->id();

            $table->foreignId('demande_conge_id')
                  ->constrained('demandes_conges')
                  ->cascadeOnDelete();

            $table->string('action');
            $table->foreignId('effectue_par')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();

            $table->text('commentaire')->nullable();
            $table->timestamp('date_action')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historiques_conges');
    }
};
