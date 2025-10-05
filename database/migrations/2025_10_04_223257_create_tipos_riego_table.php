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
        Schema::create('tipos_riego', function (Blueprint $table) {
            $table->id('id_riego');
            $table->string('nombre', 50);
            $table->decimal('eficiencia', 3, 2)->default(1.0);
            $table->integer('costo')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipos_riego');
    }
};
