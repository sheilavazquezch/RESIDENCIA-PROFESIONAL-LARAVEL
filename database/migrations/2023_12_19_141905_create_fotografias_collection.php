<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatefotografiasCollection extends Migration
{
    public function up()
    {
        // Crea la colección en MongoDB si no existe
        if (!Schema::connection('mongodb')->hasCollection('fotografias')) {
            Schema::connection('mongodb')->create('fotografias', function (Blueprint $collection) {
                $collection->string('título');
                $collection->string('autor');
                $collection->number('año_de_publicación');
                $collection->file('recurso_digital');
                $collection->timestamps();
            });        }
    }

    public function down()
    {
        // Elimina la colección en MongoDB
        Schema::connection('mongodb')->dropIfExists('fotografias');
    }
}