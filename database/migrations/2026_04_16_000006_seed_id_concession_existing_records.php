<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::table('clientes')->whereNull('id_concession')->update(['id_concession' => 1]);
        DB::table('artefactos')->whereNull('id_concession')->update(['id_concession' => 1]);
        DB::table('servicios')->whereNull('id_concession')->update(['id_concession' => 1]);
        DB::table('tecnicos')->whereNull('id_concession')->update(['id_concession' => 1]);
        DB::table('representative')->whereNull('id_concession')->update(['id_concession' => 1]);
    }

    public function down()
    {
        // No se puede revertir de forma segura sin saber el valor original
    }
};
