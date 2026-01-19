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
            $table->foreignId('superieur_hierarchique_id')
                  ->nullable()
                  ->after('user_id')
                  ->constrained('users')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('demandes_conges', function (Blueprint $table) {
            $table->dropForeign(['superieur_hierarchique_id']);
            $table->dropColumn('superieur_hierarchique_id');
        });
    }
};
