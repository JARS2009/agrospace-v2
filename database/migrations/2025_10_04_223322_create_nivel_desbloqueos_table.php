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
        Schema::create('nivel_desbloqueos', function (Blueprint $table) {
            $table->id('id_desbloqueo');
            $table->unsignedBigInteger('id_nivel');
            $table->enum('tipo_desbloqueo', ['planta', 'riego', 'herramienta', 'otro']);
            $table->integer('id_elemento');
            $table->timestamps();
            
            $table->foreign('id_nivel')->references('id_nivel')->on('niveles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nivel_desbloqueos');
    }
};
