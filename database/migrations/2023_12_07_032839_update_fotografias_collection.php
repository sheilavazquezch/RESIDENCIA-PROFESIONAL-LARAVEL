<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatefotografiasCollection extends Migration
{
    public function up()
    {
        Schema::connection('mongodb')->table('fotografias', function (Blueprint $collection) {
                $collection->string('título')->nullable();
                $collection->string('autor')->nullable();
                $collection->date('fecha')->nullable();
                $collection->string('descripcion')->nullable();
                $collection->file('foto')->nullable();
            });
    }

    public function down()
    {
        // Puedes revertir los cambios en la migración de actualización si es necesario
        // En este caso, simplemente eliminamos los campos agregados
        Schema::connection('mongodb')->table('fotografias', function (Blueprint $collection) {
                $collection->dropColumn('título');
                $collection->dropColumn('autor');
                $collection->dropColumn('fecha');
                $collection->dropColumn('descripcion');
                $collection->dropColumn('foto');
            });
    }
}