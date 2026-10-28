<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuditDteTable extends Migration
{
    public function up()
    {
        Schema::create('audit_dte', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_concession');
            $table->unsignedBigInteger('documento_id')->nullable();
            $table->string('accion', 50);
            $table->json('detalle')->nullable();
            $table->string('estado_anterior', 20)->nullable();
            $table->string('estado_nuevo', 20)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('id_concession')->references('id')->on('concessions')->onDelete('cascade');
            $table->foreign('documento_id')->references('id')->on('documentos_tributarios')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['id_concession', 'documento_id']);
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('audit_dte');
    }
}
