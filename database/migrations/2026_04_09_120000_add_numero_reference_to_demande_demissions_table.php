<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demande_demissions', function (Blueprint $table) {
            if (! Schema::hasColumn('demande_demissions', 'numero_reference')) {
                $table->string('numero_reference')->nullable()->unique()->after('commentaire_dg');
            }
        });
    }

    public function down(): void
    {
        Schema::table('demande_demissions', function (Blueprint $table) {
            if (Schema::hasColumn('demande_demissions', 'numero_reference')) {
                $table->dropUnique(['numero_reference']);
                $table->dropColumn('numero_reference');
            }
        });
    }
};
