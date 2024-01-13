<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatelibrosCollection extends Migration
{
    public function up()
    {
        Schema::connection('mongodb')->table('libros', function (Blueprint $collection) {
                $collection->string('título')->nullable();
                $collection->string('autor')->nullable();
                $collection->number('año_de_publicacion')->nullable();
                $collection->string('género_del_libro')->nullable();
                $collection->file('recurso_digital')->nullable();
            });
    }

    public function down()
    {
        // Puedes revertir los cambios en la migración de actualización si es necesario
        // En este caso, simplemente eliminamos los campos agregados
        Schema::connection('mongodb')->table('libros', function (Blueprint $collection) {
                $collection->dropColumn('título');
                $collection->dropColumn('autor');
                $collection->dropColumn('año_de_publicacion');
                $collection->dropColumn('género_del_libro');
                $collection->dropColumn('recurso_digital');
            });
    }
}