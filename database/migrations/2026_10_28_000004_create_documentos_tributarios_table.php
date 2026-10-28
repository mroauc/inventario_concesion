<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentosTributariosTable extends Migration
{
    public function up()
    {
        Schema::create('documentos_tributarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_concession');
            $table->unsignedSmallInteger('tipo_documento');
            $table->unsignedInteger('folio');
            $table->date('fecha_emision');
            $table->string('rut_emisor', 12);
            $table->string('rut_receptor', 12);
            $table->string('razon_social_receptor', 100);
            $table->integer('monto_neto')->default(0);
            $table->integer('monto_iva')->default(0);
            $table->decimal('tasa_iva', 4, 2)->default(19.00);
            $table->integer('monto_exento')->default(0);
            $table->integer('monto_total');
            $table->longText('xml_documento')->nullable();
            $table->longText('xml_sobre')->nullable();
            $table->text('ted_xml')->nullable();
            $table->enum('estado', ['borrador', 'emitido', 'enviado', 'aceptado', 'rechazado', 'anulado'])
                  ->default('borrador');
            $table->string('track_id', 50)->nullable();
            $table->text('respuesta_sii')->nullable();
            $table->unsignedSmallInteger('envio_intentos')->default(0);
            $table->timestamp('ultimo_envio_at')->nullable();
            $table->unsignedBigInteger('orden_servicio_id')->nullable();
            $table->string('idempotency_key', 64)->unique();
            $table->unsignedBigInteger('caf_folio_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_concession')->references('id')->on('concessions')->onDelete('cascade');
            $table->foreign('orden_servicio_id')->references('id')->on('ordenes_servicio')->onDelete('set null');
            $table->foreign('caf_folio_id')->references('id')->on('caf_folios');
            $table->unique(['id_concession', 'tipo_documento', 'folio'], 'dte_concession_tipo_folio');
            $table->index(['id_concession', 'estado']);
            $table->index(['id_concession', 'fecha_emision']);
            $table->index('orden_servicio_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('documentos_tributarios');
    }
}
