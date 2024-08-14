<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConcessionsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('concessions', function (Blueprint $table) {
            $table->id('id');
            $table->string('name');
            $table->string('address')->nullable();
            $table->bigInteger('id_representative')->unsigned();
            $table->foreign('id_representative')->references('id')->on('representative');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('concessions');
    }
}
