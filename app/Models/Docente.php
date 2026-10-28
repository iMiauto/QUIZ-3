<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Docente extends Model
{
    protected $table = 'docentes';

    protected $fillable = ['codEmpleado', 'nombreEmpleado', 'apellidosEmpleado'];

    protected function casts(): array
    {
        return ['codEmpleado' => 'integer'];
    }

    public function estudiantes(): HasMany
    {
        return $this->hasMany(Estudiante::class, 'consejero_id');
    }
}