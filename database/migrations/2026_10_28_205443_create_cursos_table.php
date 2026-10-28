<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('cursos', function (Blueprint $table) {
            $table->id();
            $table->string('sigla', 10)->unique();
            $table->string('nombreCurso', 100);
            $table->unsignedTinyInteger('creditos');
            $table->timestamps();
            });
    }

   
    public function down(): void
    {
        Schema::dropIfExists('cursos');
    }
};
