<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mongodb')->create('users', function (Blueprint $collection) {
            $collection->id();
            $collection->string('email')->unique();
            $collection->string('password');
            $collection->string('role');
            $collection->string('nombre');
            $collection->string('apellido_paterno');
            $collection->string('apellido_materno');
            $collection->integer('edad');
            $collection->string('estado');
            $collection->string('ocupacion');
            $collection->string('escolaridad');
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
        Schema::connection('mongodb')->dropIfExists('users');
    }
};
