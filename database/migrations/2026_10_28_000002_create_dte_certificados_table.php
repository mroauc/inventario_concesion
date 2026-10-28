<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDteCertificadosTable extends Migration
{
    public function up()
    {
        Schema::create('dte_certificados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_concession');
            $table->string('rut_titular', 12);
            $table->string('nombre_titular', 100);
            $table->string('archivo_path', 255);
            $table->text('password_cifrada');
            $table->date('fecha_vencimiento');
            $table->boolean('activo')->default(false);
            $table->timestamps();

            $table->foreign('id_concession')->references('id')->on('concessions')->onDelete('cascade');
            $table->index(['id_concession', 'activo']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('dte_certificados');
    }
}
