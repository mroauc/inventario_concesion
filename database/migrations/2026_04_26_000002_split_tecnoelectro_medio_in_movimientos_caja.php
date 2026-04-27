<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class SplitTecnoelectroMedioInMovimientosCaja extends Migration
{
    public function up()
    {
        // Paso 1: ampliar ENUM manteniendo 'tecnoelectro' para no truncar datos existentes
        DB::statement("ALTER TABLE movimientos_caja MODIFY COLUMN medio ENUM('efectivo','credito_debito','transferencia','efectivo_tecno','credito_debito_tecno','tecnoelectro','deposito_banco','deposito_banco_tecnoelectro') NOT NULL");

        // Paso 2: migrar registros 'tecnoelectro' → 'efectivo_tecno' (conservador)
        DB::statement("UPDATE movimientos_caja SET medio = 'efectivo_tecno' WHERE medio = 'tecnoelectro'");

        // Paso 3: quitar 'tecnoelectro' del ENUM ahora que no hay registros con ese valor
        DB::statement("ALTER TABLE movimientos_caja MODIFY COLUMN medio ENUM('efectivo','credito_debito','transferencia','efectivo_tecno','credito_debito_tecno','deposito_banco','deposito_banco_tecnoelectro') NOT NULL");
    }

    public function down()
    {
        // Revertir datos migrados
        DB::statement("UPDATE movimientos_caja SET medio = 'tecnoelectro' WHERE medio IN ('efectivo_tecno','credito_debito_tecno')");

        DB::statement("ALTER TABLE movimientos_caja MODIFY COLUMN medio ENUM('efectivo','credito_debito','transferencia','tecnoelectro','deposito_banco','deposito_banco_tecnoelectro') NOT NULL");
    }
}
