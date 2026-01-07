<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('regles_conges', function (Blueprint $table) {
            $table->id();
            $table->decimal('jours_par_mois', 4, 2)->default(2.5);
            $table->boolean('report_autorise')->default(true);
            $table->integer('limite_report')->nullable();
            $table->boolean('validation_multiple')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regles_conges');
    }
};
