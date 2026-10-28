<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Grupo extends Model
{
    protected $table = 'grupos';

    protected $fillable = ['curso_id', 'numeroCiclo', 'ano', 'numeroGrupo', 'abierto'];

    protected function casts(): array
    {
        return [
            'numeroCiclo' => 'integer',
            'ano' => 'integer',
            'numeroGrupo' => 'integer',
            'abierto' => 'boolean',
        ];
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    
    public function docentes(): BelongsToMany
    {
        return $this->belongsToMany(Docente::class, 'docente_grupo')
            ->withTimestamps();
    }

 
    public function estudiantes(): BelongsToMany
    {
        return $this->belongsToMany(Estudiante::class, 'estudiantes_matriculados')
            ->using(EstudianteMatriculado::class)
            ->withPivot(['id', 'nota'])
            ->withTimestamps();
    }
}