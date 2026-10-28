<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    protected $table = 'cursos';

    protected $fillable = ['sigla', 'nombreCurso', 'creditos'];

    protected function casts(): array
    {
        return ['creditos' => 'integer'];
    }
}