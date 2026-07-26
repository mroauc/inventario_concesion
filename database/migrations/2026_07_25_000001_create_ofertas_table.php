<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ofertas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 12, 2);
            $table->boolean('vendido')->default(false);
            $table->boolean('estado')->default(true);
            // ponytail: JSON en vez de tabla oferta_fotos — 3 fotos sin metadata no justifican un join.
            $table->json('fotos')->nullable();
            $table->unsignedBigInteger('id_concession')->nullable();
            $table->foreign('id_concession')->references('id')->on('concessions');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ofertas');
    }
};
