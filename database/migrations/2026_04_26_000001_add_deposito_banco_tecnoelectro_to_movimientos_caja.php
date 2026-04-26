<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddDepositoBancoTecnoelectroToMovimientosCaja extends Migration
{
    public function up()
    {
        // MySQL no permite ALTER COLUMN en enums directamente; se redefine la columna
        DB::statement("ALTER TABLE movimientos_caja MODIFY COLUMN medio ENUM('efectivo','credito_debito','transferencia','tecnoelectro','deposito_banco','deposito_banco_tecnoelectro') NOT NULL");
    }

    public function down()
    {
        DB::statement("ALTER TABLE movimientos_caja MODIFY COLUMN medio ENUM('efectivo','credito_debito','transferencia','tecnoelectro','deposito_banco') NOT NULL");
    }
}
