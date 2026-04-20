<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tipo_artefactos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->unsignedBigInteger('id_concession')->nullable();
            $table->foreign('id_concession')->references('id')->on('concessions');
            $table->timestamps();
        });

        Schema::table('artefactos', function (Blueprint $table) {
            $table->unsignedBigInteger('tipo_artefacto_id')->nullable()->after('id_concession');
            $table->foreign('tipo_artefacto_id')->references('id')->on('tipo_artefactos');
        });
    }

    public function down()
    {
        Schema::table('artefactos', function (Blueprint $table) {
            $table->dropForeign(['tipo_artefacto_id']);
            $table->dropColumn('tipo_artefacto_id');
        });
        Schema::dropIfExists('tipo_artefactos');
    }
};
