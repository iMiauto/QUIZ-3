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
        Schema::create('estudiantes', function (Blueprint $table) {
    $table->id();
    $table->string('carne', 10)->unique();
    $table->string('nombre', 60);
    $table->string('apellidos', 80);
    $table->foreignId('consejero_id')->constrained('docentes')->restrictOnDelete();
    $table->timestamps();
});
    }
    
    public function down(): void
    {
        Schema::dropIfExists('estudiantes');
    }
};
