<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiciosTable extends Migration
{
    public function up()
    {
        Schema::create('servicios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_servicio');
            $table->decimal('precio', 10, 2);
            $table->text('descripcion')->nullable();
            $table->integer('duracion_estimada')->nullable();
            $table->boolean('estado')->default(true);
            $table->boolean('requiere_repuestos')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('servicios');
    }
}