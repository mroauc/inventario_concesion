<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdenesServicioTable extends Migration
{
    public function up()
    {
        Schema::create('ordenes_servicio', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->string('folio_garantia')->nullable();
            $table->enum('tipo_servicio', ['mantenimiento', 'reparacion', 'instalacion', 'garantia']);
            $table->datetime('fecha_orden');
            $table->datetime('fecha_visita')->nullable();
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('artefacto_id')->nullable();
            $table->text('descripcion_falla');
            $table->text('observaciones')->nullable();
            $table->enum('tipo_atencion', ['taller', 'terreno']);
            $table->decimal('valor_visita', 10, 2)->nullable();
            $table->decimal('costo_total', 10, 2)->default(0);
            $table->unsignedBigInteger('tecnico_id')->nullable();
            $table->enum('estado', ['pendiente', 'en_progreso', 'finalizada', 'cancelada'])->default('pendiente');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('cliente_id')->references('id')->on('clientes');
            $table->foreign('artefacto_id')->references('id')->on('artefactos');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ordenes_servicio');
    }
}