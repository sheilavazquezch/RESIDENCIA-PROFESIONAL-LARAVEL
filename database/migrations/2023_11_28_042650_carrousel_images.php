<?php

use Jenssegers\Mongodb\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mongodb')->create('carrousel_images', function (Blueprint $collection) {
            $collection->id();
            $collection->string('imagen');
            $collection->date('fecha_inicio');
            $collection->date('fecha_fin');
            $collection->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mongodb')->dropIfExists('carrousel_images');
    }
};
