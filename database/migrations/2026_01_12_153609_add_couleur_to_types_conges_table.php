<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCouleurToTypesCongesTable extends Migration
{
    public function up()
    {
        Schema::table('types_conges', function (Blueprint $table) {
            $table->string('couleur', 7)->default('#3B82F6')->after('actif');
        });
    }

    public function down()
    {
        Schema::table('types_conges', function (Blueprint $table) {
            $table->dropColumn('couleur');
        });
    }
}
