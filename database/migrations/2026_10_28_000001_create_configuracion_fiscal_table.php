<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfiguracionFiscalTable extends Migration
{
    public function up()
    {
        Schema::create('configuracion_fiscal', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_concession')->unique();
            $table->string('rut_empresa', 12);
            $table->string('razon_social', 100);
            $table->string('giro', 80);
            $table->unsignedInteger('actividad_economica');
            $table->string('direccion', 70);
            $table->string('comuna', 20);
            $table->string('ciudad', 20);
            $table->string('telefono', 20)->nullable();
            $table->string('email_dte', 100)->nullable();
            $table->unsignedInteger('sucursal_sii')->default(0);
            $table->date('fecha_resolucion');
            $table->integer('numero_resolucion');
            $table->enum('ambiente', ['certificacion', 'produccion'])->default('certificacion');
            $table->boolean('activa')->default(false);
            $table->timestamps();

            $table->foreign('id_concession')->references('id')->on('concessions')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('configuracion_fiscal');
    }
}
