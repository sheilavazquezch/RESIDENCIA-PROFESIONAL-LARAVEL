<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatelibrosCollection extends Migration
{
    public function up()
    {
        // Crea la colección en MongoDB si no existe
        if (!Schema::connection('mongodb')->hasCollection('libros')) {
            Schema::connection('mongodb')->create('libros', function (Blueprint $collection) {
                $collection->string('titulo');
                $collection->string('autor');
                $collection->string('editorial');
                $collection->number('numero_de_paginas');
                $collection->timestamps();
            });        }
    }

    public function down()
    {
        // Elimina la colección en MongoDB
        Schema::connection('mongodb')->dropIfExists('libros');
    }
}