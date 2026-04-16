<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('concessions', function (Blueprint $table) {
            $table->unsignedInteger('numero_orden_siguiente')->default(1)->after('address');
        });
    }

    public function down()
    {
        Schema::table('concessions', function (Blueprint $table) {
            $table->dropColumn('numero_orden_siguiente');
        });
    }
};
