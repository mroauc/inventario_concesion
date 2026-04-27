<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogsFlujoCajaTable extends Migration
{
    public function up()
    {
        Schema::create('logs_flujo_caja', function (Blueprint $table) {
            $table->id();
            $table->string('activity');
            $table->string('content');
            $table->unsignedBigInteger('id_user');
            $table->unsignedBigInteger('id_concession');
            $table->timestamps();

            $table->foreign('id_user')->references('id')->on('users');
            $table->foreign('id_concession')->references('id')->on('concessions');
        });
    }

    public function down()
    {
        Schema::dropIfExists('logs_flujo_caja');
    }
}
