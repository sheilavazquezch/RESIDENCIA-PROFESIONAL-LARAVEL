<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateaudiosCollection extends Migration
{
    public function up()
    {
        // Crea la colección en MongoDB si no existe
        if (!Schema::connection('mongodb')->hasCollection('audios')) {
            Schema::connection('mongodb')->create('audios', function (Blueprint $collection) {
                $collection->string('autor');
                $collection->string('idioma');
                $collection->date('fecha');
                $collection->file('audio');
                $collection->timestamps();
            });        }
    }

    public function down()
    {
        // Elimina la colección en MongoDB
        Schema::connection('mongodb')->dropIfExists('audios');
    }
}