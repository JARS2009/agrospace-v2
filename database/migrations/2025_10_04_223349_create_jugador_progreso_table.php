<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jugador_progreso', function (Blueprint $table) {
            $table->id('id_jugador');
            $table->string('nombre', 50);
            $table->unsignedBigInteger('nivel_actual')->default(1);
            $table->integer('xp_actual')->default(0);
            $table->timestamps();
            
            $table->foreign('nivel_actual')->references('id_nivel')->on('niveles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jugador_progreso');
    }
};
