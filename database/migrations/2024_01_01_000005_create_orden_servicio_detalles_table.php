<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdenServicioDetallesTable extends Migration
{
    public function up()
    {
        Schema::create('orden_servicio_detalles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('orden_servicio_id');
            $table->unsignedBigInteger('producto_id')->nullable();
            $table->unsignedBigInteger('servicio_id')->nullable();
            $table->integer('cantidad')->default(1);
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->text('nota')->nullable();
            $table->timestamps();

            $table->foreign('orden_servicio_id')->references('id')->on('ordenes_servicio')->onDelete('cascade');
            $table->foreign('producto_id')->references('id')->on('products');
            $table->foreign('servicio_id')->references('id')->on('servicios');
        });
    }

    public function down()
    {
        Schema::dropIfExists('orden_servicio_detalles');
    }
}