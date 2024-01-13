<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatearticulosjournalsCollection extends Migration
{
    public function up()
    {
        // Crea la colección en MongoDB si no existe
        if (!Schema::connection('mongodb')->hasCollection('articulosjournals')) {
            Schema::connection('mongodb')->create('articulosjournals', function (Blueprint $collection) {
                $collection->string('título');
                $collection->string('autor');
                $collection->number('añopublicación');
                $collection->string('género');
                $collection->file('recursodigital');
                $collection->timestamps();
            });        }
    }

    public function down()
    {
        // Elimina la colección en MongoDB
        Schema::connection('mongodb')->dropIfExists('articulosjournals');
    }
}