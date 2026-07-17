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
        Schema::table('demande_attestations', function (Blueprint $table) {
            $table->date('date_retraite')->nullable()->after('poste');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('demande_attestations', function (Blueprint $table) {
            $table->dropColumn('date_retraite');
        });
    }
};
