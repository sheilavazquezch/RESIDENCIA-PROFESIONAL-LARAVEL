<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatefondoshistoricosCollection extends Migration
{
    public function up()
    {
        Schema::connection('mongodb')->table('fondoshistoricos', function (Blueprint $collection) {
                $collection->string('fondo')->nullable();
                $collection->string('seccion')->nullable();
                $collection->string('serie')->nullable();
                $collection->string('sub-serie')->nullable();
                $collection->string('expediente')->nullable();
                $collection->file('documento')->nullable();
            });
    }

    public function down()
    {
        // Puedes revertir los cambios en la migración de actualización si es necesario
        // En este caso, simplemente eliminamos los campos agregados
        Schema::connection('mongodb')->table('fondoshistoricos', function (Blueprint $collection) {
                $collection->dropColumn('fondo');
                $collection->dropColumn('seccion');
                $collection->dropColumn('serie');
                $collection->dropColumn('sub-serie');
                $collection->dropColumn('expediente');
                $collection->dropColumn('documento');
            });
    }
}