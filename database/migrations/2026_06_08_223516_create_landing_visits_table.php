<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLandingVisitsTable extends Migration
{
    public function up()
    {
        Schema::create('landing_visits', function (Blueprint $table) {
            $table->id();
            $table->string('pagina');       // 'home', 'repuestos', 'conocenos', 'contacto'
            $table->string('ip', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('referrer')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('landing_visits');
    }
}
