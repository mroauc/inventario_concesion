<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEnviosSiiTable extends Migration
{
    public function up()
    {
        Schema::create('envios_sii', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_concession');
            $table->enum('tipo_envio', ['boleta', 'dte', 'rcof']);
            $table->unsignedBigInteger('documento_id')->nullable();
            $table->string('xml_enviado_hash', 64);
            $table->text('respuesta_raw')->nullable();
            $table->string('track_id', 50)->nullable();
            $table->smallInteger('status_code')->nullable();
            $table->smallInteger('http_status')->nullable();
            $table->unsignedSmallInteger('intentos')->default(1);
            $table->enum('ambiente', ['certificacion', 'produccion']);
            $table->timestamps();

            $table->foreign('id_concession')->references('id')->on('concessions')->onDelete('cascade');
            $table->foreign('documento_id')->references('id')->on('documentos_tributarios')->onDelete('set null');
            $table->index(['id_concession', 'tipo_envio', 'created_at'], 'envios_sii_concession_tipo_fecha');
            $table->index('track_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('envios_sii');
    }
}
