<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdaterevistasCollection extends Migration
{
    public function up()
    {
        Schema::connection('mongodb')->table('revistas', function (Blueprint $collection) {
                $collection->string('nuevocampo')->nullable();
                $collection->string('nuevoCampo1')->nullable();
                $collection->string('nuevoCampo2')->nullable();
            });
    }

    public function down()
    {
        // Puedes revertir los cambios en la migración de actualización si es necesario
        // En este caso, simplemente eliminamos los campos agregados
        Schema::connection('mongodb')->table('revistas', function (Blueprint $collection) {
                $collection->dropColumn('nuevocampo');
                $collection->dropColumn('nuevoCampo1');
                $collection->dropColumn('nuevoCampo2');
            });
    }
}