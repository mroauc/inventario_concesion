<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArtefactoImportsTable extends Migration
{
    public function up()
    {
        Schema::create('artefacto_imports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_user');
            $table->unsignedBigInteger('id_concession');
            $table->string('archivo');
            $table->integer('total_rows')->default(0);
            $table->integer('success_count')->default(0);
            $table->integer('error_count')->default(0);
            $table->json('errors')->nullable();
            $table->timestamps();

            $table->foreign('id_user')->references('id')->on('users');
            $table->foreign('id_concession')->references('id')->on('concessions');
        });
    }

    public function down()
    {
        Schema::dropIfExists('artefacto_imports');
    }
}
