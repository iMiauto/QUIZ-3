<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Estudiante extends Model
{
    protected $table = 'estudiantes';

    protected $fillable = ['carne', 'nombre', 'apellidos', 'consejero_id'];


    public function consejero(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'consejero_id');
    }
}