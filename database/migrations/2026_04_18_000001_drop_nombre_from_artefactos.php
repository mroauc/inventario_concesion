<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('artefactos', function (Blueprint $table) {
            $table->dropColumn('nombre');
        });
    }

    public function down()
    {
        Schema::table('artefactos', function (Blueprint $table) {
            $table->string('nombre')->after('id');
        });
    }
};
