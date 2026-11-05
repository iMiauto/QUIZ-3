<?php

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Grupo extends Model
{
    public const MIN_DOCENTES = 1;

    public const MAX_DOCENTES = 2;

    public const MIN_ESTUDIANTES = 15;

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

    /**
     * Asigna un docente al grupo respetando el máximo de 2 docentes.
     *
     * @throws DomainException si el grupo ya tiene el máximo de docentes.
     */
    public function asignarDocente(Docente $docente): void
    {
        // si ya está asignado no se hace nada
        if ($this->docentes()->whereKey($docente->id)->exists()) {
            return;
        }

        if ($this->docentes()->count() >= self::MAX_DOCENTES) {
            throw new DomainException('Un grupo no puede tener más de '.self::MAX_DOCENTES.' docentes.');
        }

        $this->docentes()->attach($docente->id);
        $this->unsetRelation('docentes');
    }

    /**
     * El grupo puede abrirse si tiene entre 1 y 2 docentes y al menos 15 estudiantes.
     */
    public function puedeAbrirse(): bool
    {
        $docentes = $this->docentes()->count();
        $estudiantes = $this->estudiantes()->count();

        return $docentes >= self::MIN_DOCENTES
            && $docentes <= self::MAX_DOCENTES
            && $estudiantes >= self::MIN_ESTUDIANTES;
    }

    /**
     * Abre el grupo si cumple las condiciones.
     *
     * @throws DomainException si el grupo no cumple las condiciones para abrirse.
     */
    public function abrir(): void
    {
        if (! $this->puedeAbrirse()) {
            throw new DomainException(
                'El grupo requiere entre '.self::MIN_DOCENTES.' y '.self::MAX_DOCENTES
                .' docentes y al menos '.self::MIN_ESTUDIANTES.' estudiantes para abrirse.'
            );
        }

        $this->abierto = true;
        $this->save();
    }
}
