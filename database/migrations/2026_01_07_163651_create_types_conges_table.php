<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('types_conges', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->integer('nombre_jours_max')->nullable();
            $table->boolean('est_paye')->default(true);
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('types_conges');
    }
};
