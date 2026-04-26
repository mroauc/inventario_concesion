<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCajasDiariasTable extends Migration
{
    public function up()
    {
        Schema::create('cajas_diarias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_concession');
            $table->date('fecha');
            $table->enum('estado', ['abierta', 'cerrada'])->default('abierta');
            $table->decimal('apertura_caja', 12, 2)->default(0);
            $table->decimal('deposito_dia', 12, 2)->default(0);
            $table->decimal('cierre_caja', 12, 2)->default(0);
            $table->decimal('apertura_tecnoelectro', 12, 2)->default(0);
            $table->decimal('deposito_tecnoelectro', 12, 2)->default(0);
            $table->decimal('cierre_tecnoelectro', 12, 2)->default(0);
            $table->timestamps();

            $table->foreign('id_concession')->references('id')->on('concessions')->onDelete('cascade');
            $table->unique(['id_concession', 'fecha']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('cajas_diarias');
    }
}
