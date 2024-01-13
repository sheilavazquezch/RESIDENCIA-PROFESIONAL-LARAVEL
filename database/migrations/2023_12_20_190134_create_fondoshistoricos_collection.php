<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatefondoshistoricosCollection extends Migration
{
    public function up()
    {
        // Crea la colección en MongoDB si no existe
        if (!Schema::connection('mongodb')->hasCollection('fondoshistoricos')) {
            Schema::connection('mongodb')->create('fondoshistoricos', function (Blueprint $collection) {
                $collection->string('fondo');
                $collection->string('seccion');
                $collection->string('serie');
                $collection->string('sub-serie');
                $collection->string('expediente');
                $collection->file('documento');
                $collection->timestamps();
            });        }
    }

    public function down()
    {
        // Elimina la colección en MongoDB
        Schema::connection('mongodb')->dropIfExists('fondoshistoricos');
    }
}