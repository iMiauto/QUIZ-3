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
      Schema::create('estudiantes_matriculados', function (Blueprint $table) {
        $table->id();
        $table->foreignId('grupo_id')->constrained('grupos')->cascadeOnDelete();
        $table->foreignId('estudiante_id')->constrained('estudiantes')->cascadeOnDelete();
        $table->decimal('nota', 5, 2)->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estudiantes_matriculados');
    }
};
