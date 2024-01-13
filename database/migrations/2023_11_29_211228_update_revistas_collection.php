<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdaterevistasCollection extends Migration
{
    public function up()
    {
        Schema::connection('mongodb')->table('revistas', function (Blueprint $collection) {
                $collection->string('titulo')->nullable();
                $collection->string('autor')->nullable();
                $collection->string('paginas')->nullable();
                $collection->string('editorial')->nullable();
            });
    }

    public function down()
    {
        // Puedes revertir los cambios en la migración de actualización si es necesario
        // En este caso, simplemente eliminamos los campos agregados
        Schema::connection('mongodb')->table('revistas', function (Blueprint $collection) {
                $collection->dropColumn('titulo');
                $collection->dropColumn('autor');
                $collection->dropColumn('paginas');
                $collection->dropColumn('editorial');
            });
    }
}