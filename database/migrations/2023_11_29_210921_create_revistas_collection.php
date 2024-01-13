<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreaterevistasCollection extends Migration
{
    public function up()
    {
        // Crea la colección en MongoDB si no existe
        if (!Schema::connection('mongodb')->hasCollection('revistas')) {
            Schema::connection('mongodb')->create('revistas', function (Blueprint $collection) {
                $collection->string('titulo');
                $collection->string('autor');
                $collection->string('paginas');
                $collection->string('editorial');
                $collection->string('volumen');
                $collection->timestamps();
            });        }
    }

    public function down()
    {
        // Elimina la colección en MongoDB
        Schema::connection('mongodb')->dropIfExists('revistas');
    }
}