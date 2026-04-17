<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MakeTipoServicioNullableInOrdenesServicio extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE ordenes_servicio MODIFY COLUMN tipo_servicio ENUM('mantenimiento','reparacion','instalacion','garantia') NULL DEFAULT NULL");
    }

    public function down()
    {
        DB::statement("ALTER TABLE ordenes_servicio MODIFY COLUMN tipo_servicio ENUM('mantenimiento','reparacion','instalacion','garantia') NOT NULL");
    }
}
