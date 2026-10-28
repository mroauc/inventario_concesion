<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentoTributarioDetallesTable extends Migration
{
    public function up()
    {
        Schema::create('documento_tributario_detalles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('documento_id');
            $table->unsignedSmallInteger('nro_linea');
            $table->string('nombre_item', 80);
            $table->string('descripcion', 1000)->nullable();
            $table->decimal('cantidad', 12, 6)->default(1);
            $table->string('unidad_medida', 4)->nullable();
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('descuento_pct', 5, 2)->default(0);
            $table->integer('descuento_monto')->default(0);
            $table->integer('monto_item');
            $table->unsignedBigInteger('producto_id')->nullable();
            $table->unsignedBigInteger('servicio_id')->nullable();
            $table->timestamps();

            $table->foreign('documento_id')->references('id')->on('documentos_tributarios')->onDelete('cascade');
            $table->foreign('producto_id')->references('id')->on('products')->onDelete('set null');
            $table->foreign('servicio_id')->references('id')->on('servicios')->onDelete('set null');
            $table->index('documento_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('documento_tributario_detalles');
    }
}
