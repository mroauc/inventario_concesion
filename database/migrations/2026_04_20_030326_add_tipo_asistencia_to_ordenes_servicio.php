<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTipoAsistenciaToOrdenesServicio extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ordenes_servicio', function (Blueprint $table) {
            $table->string('tipo_asistencia')->nullable()->after('folio_garantia');
        });
    }

    public function down()
    {
        Schema::table('ordenes_servicio', function (Blueprint $table) {
            $table->dropColumn('tipo_asistencia');
        });
    }
}
