<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateperiodicosCollection extends Migration
{
    public function up()
    {
        // Crea la colección en MongoDB si no existe
        if (!Schema::connection('mongodb')->hasCollection('periodicos')) {
            Schema::connection('mongodb')->create('periodicos', function (Blueprint $collection) {
                $collection->string('nombre');
                $collection->date('fecha');
                $collection->string('pais');
                $collection->number('edicion');
                $collection->timestamps();
            });        }
    }

    public function down()
    {
        // Elimina la colección en MongoDB
        Schema::connection('mongodb')->dropIfExists('periodicos');
    }
}