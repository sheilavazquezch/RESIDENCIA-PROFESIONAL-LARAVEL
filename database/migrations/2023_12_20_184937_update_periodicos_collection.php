<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateperiodicosCollection extends Migration
{
    public function up()
    {
        Schema::connection('mongodb')->table('periodicos', function (Blueprint $collection) {
                $collection->string('título')->nullable();
                $collection->string('autor')->nullable();
                $collection->number('año')->nullable();
                $collection->file('recurso_digital')->nullable();
                $collection->string('editorial')->nullable();
            });
    }

    public function down()
    {
        // Puedes revertir los cambios en la migración de actualización si es necesario
        // En este caso, simplemente eliminamos los campos agregados
        Schema::connection('mongodb')->table('periodicos', function (Blueprint $collection) {
                $collection->dropColumn('título');
                $collection->dropColumn('autor');
                $collection->dropColumn('año');
                $collection->dropColumn('recurso_digital');
                $collection->dropColumn('editorial');
            });
    }
}