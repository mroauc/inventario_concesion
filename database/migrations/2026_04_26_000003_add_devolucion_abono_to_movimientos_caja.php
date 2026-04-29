<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddDevolucionAbonoToMovimientosCaja extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE movimientos_caja MODIFY COLUMN medio ENUM('efectivo','credito_debito','transferencia','efectivo_tecno','credito_debito_tecno','devolucion_abono','deposito_banco','deposito_banco_tecnoelectro') NOT NULL");
    }

    public function down()
    {
        DB::statement("ALTER TABLE movimientos_caja MODIFY COLUMN medio ENUM('efectivo','credito_debito','transferencia','efectivo_tecno','credito_debito_tecno','deposito_banco','deposito_banco_tecnoelectro') NOT NULL");
    }
}
