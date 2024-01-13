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
            Schema::connection('mongodb')->create('comentarios', function (Blueprint $collection) {

            $collection->id();
            $collection->foreignId('usuario_id');
            $collection->string('documento_id');
            $collection->text('contenido');
            $collection->string('estado')->default('pendiente');
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
        Schema::dropIfExists('comentarios');
    }
};
