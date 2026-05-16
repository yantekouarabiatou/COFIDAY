<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('types_conges')
            ->whereIn('libelle', ['Congés maladie', 'Congés maternité', 'Congés paternité'])
            ->update(['justificatif_requis' => true]);
    }

    public function down(): void
    {
        DB::table('types_conges')
            ->whereIn('libelle', ['Congés maladie', 'Congés maternité', 'Congés paternité'])
            ->update(['justificatif_requis' => false]);
    }
};
