<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collaborateur_dossier', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('role')->nullable()->default('collaborateur'); // collaborateur, responsable, etc.
            $table->timestamp('added_at')->useCurrent();
            $table->timestamp('removed_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['dossier_id', 'user_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collaborateur_dossier');
    }
};
