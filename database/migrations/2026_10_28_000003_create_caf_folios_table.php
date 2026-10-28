<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCafFoliosTable extends Migration
{
    public function up()
    {
        Schema::create('caf_folios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_concession');
            $table->unsignedSmallInteger('tipo_documento');
            $table->unsignedInteger('folio_desde');
            $table->unsignedInteger('folio_hasta');
            $table->unsignedInteger('folio_actual');
            $table->text('xml_cifrado');
            $table->date('fecha_autorizacion');
            $table->date('fecha_vencimiento')->nullable();
            $table->unsignedSmallInteger('idk');
            $table->boolean('agotado')->default(false);
            $table->timestamps();

            $table->foreign('id_concession')->references('id')->on('concessions')->onDelete('cascade');
            $table->unique(['id_concession', 'tipo_documento', 'folio_desde', 'folio_hasta'], 'caf_folios_unique');
            $table->index(['id_concession', 'tipo_documento', 'agotado'], 'caf_folios_disponible');
        });
    }

    public function down()
    {
        Schema::dropIfExists('caf_folios');
    }
}
