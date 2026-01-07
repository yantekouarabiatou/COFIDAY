<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('soldes_conges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->year('annee');
            $table->decimal('jours_acquis', 5, 2)->default(0);
            $table->decimal('jours_pris', 5, 2)->default(0);
            $table->decimal('jours_restants', 5, 2)->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'annee']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soldes_conges');
    }
};
