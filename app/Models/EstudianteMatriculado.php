<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class EstudianteMatriculado extends Pivot
{
    protected $table = 'estudiantes_matriculados';

    public $incrementing = true;

    protected function casts(): array
    {
        return ['nota' => 'float'];
    }
}