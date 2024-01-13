<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatemapasCollection extends Migration
{
    public function up()
    {
        // Crea la colección en MongoDB si no existe
        if (!Schema::connection('mongodb')->hasCollection('mapas')) {
            Schema::connection('mongodb')->create('mapas', function (Blueprint $collection) {
                $collection->string('nombre');
                $collection->string('titulo');
                $collection->date('año');
                $collection->number('escala');
                $collection->timestamps();
            });        }
    }

    public function down()
    {
        // Elimina la colección en MongoDB
        Schema::connection('mongodb')->dropIfExists('mapas');
    }
}