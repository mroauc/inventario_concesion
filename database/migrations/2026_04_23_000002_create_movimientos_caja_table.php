<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMovimientosCajaTable extends Migration
{
    public function up()
    {
        Schema::create('movimientos_caja', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('caja_id');
            $table->unsignedBigInteger('id_concession');
            $table->date('fecha');
            $table->enum('tipo_movimiento', ['ingreso', 'egreso']);
            $table->enum('medio', ['efectivo', 'credito_debito', 'transferencia', 'tecnoelectro', 'deposito_banco']);
            $table->decimal('monto', 12, 2);
            $table->string('detalle', 255)->nullable();
            $table->boolean('anulado')->default(false);
            $table->unsignedBigInteger('usuario_id');
            $table->timestamps();

            $table->foreign('caja_id')->references('id')->on('cajas_diarias')->onDelete('cascade');
            $table->foreign('id_concession')->references('id')->on('concessions')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('movimientos_caja');
    }
}
