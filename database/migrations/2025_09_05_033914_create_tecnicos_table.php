<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTecnicosTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('tecnicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('nombre');
            $table->string('especialidad');
            $table->string('telefono_contacto')->nullable();
            $table->string('email_contacto')->nullable();
            $table->string('zona_cobertura')->nullable();
            $table->text('certificaciones')->nullable();
            $table->enum('disponibilidad', ['disponible', 'ocupado', 'de_baja'])->default('disponible');
            $table->text('nota')->nullable();
            $table->timestamps();
        });

        Schema::table('ordenes_servicio', function (Blueprint $table) {
            $table->foreign('tecnico_id')->references('id')->on('tecnicos')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('ordenes_servicio', function (Blueprint $table) {
            $table->dropForeign(['tecnico_id']);
        });

        Schema::dropIfExists('tecnicos');
    }
}
