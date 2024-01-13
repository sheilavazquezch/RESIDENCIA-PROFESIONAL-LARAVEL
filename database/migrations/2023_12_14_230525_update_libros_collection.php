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
                $collection->string('autor_del_libro')->nullable();
                $collection->number('año_de_publicacion')->nullable();
                $collection->file('recurso_digital')->nullable();
                $collection->number('numero_de_paginas')->nullable();
                $collection->string('editorial')->nullable();
            });
    }

    public function down()
    {
        // Puedes revertir los cambios en la migración de actualización si es necesario
        // En este caso, simplemente eliminamos los campos agregados
        Schema::connection('mongodb')->table('libros', function (Blueprint $collection) {
                $collection->dropColumn('título');
                $collection->dropColumn('autor_del_libro');
                $collection->dropColumn('año_de_publicacion');
                $collection->dropColumn('recurso_digital');
                $collection->dropColumn('numero_de_paginas');
                $collection->dropColumn('editorial');
            });
    }
}