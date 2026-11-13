<?php

use App\Models\Curso;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\EstudianteMatriculado;
use App\Models\Grupo;
use Illuminate\Database\QueryException;

mutates(Grupo::class);

// ---------------------------------------------------------------------
// Funciones ayudantes para los datos fijos
// ---------------------------------------------------------------------

function crearCursoIF6100(): Curso
{
    return Curso::create([
        'sigla' => 'IF6100',
        'nombreCurso' => 'Análisis y Diseño de Sistemas',
        'creditos' => 4,
    ]);
}

function crearGrupoFijo(?Curso $curso = null, int $numeroGrupo = 1, bool $abierto = false): Grupo
{
    $curso ??= crearCursoIF6100();

    return Grupo::create([
        'curso_id' => $curso->id,
        'numeroCiclo' => 2,
        'ano' => 2026,
        'numeroGrupo' => $numeroGrupo,
        'abierto' => $abierto,
    ]);
}

function crearDocenteFijo(int $codigo): Docente
{
    $datos = [
        1001 => ['Ana', 'Mora Vargas'],
        1002 => ['Luis', 'Rojas Solano'],
        1003 => ['Carla', 'Jiménez Araya'],
    ];

    return Docente::create([
        'codEmpleado' => $codigo,
        'nombreEmpleado' => $datos[$codigo][0],
        'apellidosEmpleado' => $datos[$codigo][1],
    ]);
}

function crearEstudianteMaria(Docente $consejero): Estudiante
{
    return Estudiante::create([
        'carne' => 'C12345',
        'nombre' => 'María',
        'apellidos' => 'Solís Quesada',
        'consejero_id' => $consejero->id,
    ]);
}

function matricularEstudiantesFactory(Grupo $grupo, int $cantidad): void
{
    $estudiantes = Estudiante::factory()->count($cantidad)->create();

    $grupo->estudiantes()->attach($estudiantes->pluck('id')->all());
}

function buscarMatricula(Grupo $grupo, Estudiante $estudiante): ?EstudianteMatriculado
{
    return EstudianteMatriculado::where('grupo_id', $grupo->id)
        ->where('estudiante_id', $estudiante->id)
        ->first();
}

// ---------------------------------------------------------------------
// CP-01 a CP-06
// ---------------------------------------------------------------------

test('CP-01 crea y recupera el grupo 01', function () {
    $creado = crearGrupoFijo(abierto: true);

    $grupo = Grupo::find($creado->id);

    expect($grupo)->not->toBeNull();
    expect($grupo->numeroCiclo)->toBe(2);
    expect($grupo->ano)->toBe(2026);
    expect($grupo->numeroGrupo)->toBe(1);
    expect($grupo->abierto)->toBeTrue();
    expect($grupo->curso->sigla)->toBe('IF6100');
    expect($grupo->curso->creditos)->toBe(4);
    $this->assertDatabaseCount('grupos', 1);
});

test('CP-02 un curso tiene tres grupos y dos abiertos', function () {
    $curso = crearCursoIF6100();
    crearGrupoFijo($curso, 1, true);
    crearGrupoFijo($curso, 2, true);
    crearGrupoFijo($curso, 3, false);

    $grupos = $curso->grupos;
    $abiertos = $grupos->where('abierto', true);

    expect($grupos)->toHaveCount(3);
    expect($abiertos)->toHaveCount(2);
    expect($abiertos->pluck('numeroGrupo')->sort()->values()->all())->toBe([1, 2]);
    expect($grupos->pluck('curso_id')->unique()->values()->all())->toBe([$curso->id]);
});

test('CP-03 asigna dos docentes al grupo', function () {
    $grupo = crearGrupoFijo();
    $d1 = crearDocenteFijo(1001);
    $d2 = crearDocenteFijo(1002);

    $grupo->asignarDocente($d1);
    $grupo->asignarDocente($d2);

    expect($grupo->docentes)->toHaveCount(2);
    expect($grupo->docentes->pluck('codEmpleado')->sort()->values()->all())->toBe([1001, 1002]);
    expect($d1->grupos->contains($grupo))->toBeTrue();
    $this->assertDatabaseCount('grupo_docente', 2);
});

test('CP-04 rechaza un tercer docente', function () {
    $grupo = crearGrupoFijo();
    $d1 = crearDocenteFijo(1001);
    $d2 = crearDocenteFijo(1002);
    $d3 = crearDocenteFijo(1003);
    $grupo->asignarDocente($d1);
    $grupo->asignarDocente($d2);

    try {
        $grupo->asignarDocente($d3);
        $this->fail('Se esperaba DomainException al asignar un tercer docente.');
    } catch (DomainException $e) {
        expect($e)->toBeInstanceOf(DomainException::class);
    }

    expect($grupo->fresh()->docentes)->toHaveCount(2);
    $this->assertDatabaseMissing('grupo_docente', [
        'docente_id' => $d3->id,
    ]);
});

test('CP-04 asignar un docente ya asignado no lanza excepción', function () {
    $grupo = crearGrupoFijo();
    $d1 = crearDocenteFijo(1001);
    $d2 = crearDocenteFijo(1002);
    $grupo->asignarDocente($d1);
    $grupo->asignarDocente($d2);

    try {
        $grupo->asignarDocente($d1);
    } catch (DomainException $e) {
        $this->fail('No se esperaba DomainException al reasignar un docente ya asignado.');
    }

    expect($grupo->fresh()->docentes)->toHaveCount(2);
    $this->assertDatabaseCount('grupo_docente', 2);
});

test('CP-05 un grupo con 14 estudiantes no abre', function () {
    $grupo = crearGrupoFijo();
    $grupo->asignarDocente(crearDocenteFijo(1001));
    matricularEstudiantesFactory($grupo, 14);

    expect($grupo->puedeAbrirse())->toBeFalse();

    try {
        $grupo->abrir();
        $this->fail('Se esperaba DomainException al abrir un grupo con 14 estudiantes.');
    } catch (DomainException $e) {
        expect($e)->toBeInstanceOf(DomainException::class);
    }

    expect($grupo->fresh()->abierto)->toBeFalse();
});

test('CP-06 un grupo con 15 estudiantes abre', function () {
    $grupo = crearGrupoFijo();
    $grupo->asignarDocente(crearDocenteFijo(1001));
    matricularEstudiantesFactory($grupo, 15);

    expect($grupo->puedeAbrirse())->toBeTrue();

    $grupo->abrir();

    expect($grupo->fresh()->abierto)->toBeTrue();
});

// ---------------------------------------------------------------------
// CP-07 a CP-13
// ---------------------------------------------------------------------

test('CP-07 un grupo sin docentes no abre aunque tenga 20 estudiantes', function () {
    $grupo = crearGrupoFijo();
    matricularEstudiantesFactory($grupo, 20);

    expect($grupo->puedeAbrirse())->toBeFalse();

    try {
        $grupo->abrir();
        $this->fail('Se esperaba DomainException al abrir un grupo sin docentes.');
    } catch (DomainException $e) {
        expect($e)->toBeInstanceOf(DomainException::class);
    }

    expect($grupo->fresh()->abierto)->toBeFalse();
    $this->assertDatabaseHas('grupos', ['id' => $grupo->id, 'abierto' => false]);
});

test('CP-08 un estudiante tiene un consejero y el docente lo tiene como aconsejado', function () {
    $docente = crearDocenteFijo(1001);

    $estudiante = crearEstudianteMaria($docente);

    expect($estudiante->consejero->codEmpleado)->toBe(1001);
    $aconsejados = $docente->fresh()->estudiantes;
    expect($aconsejados)->toHaveCount(1);
    expect($aconsejados->first()->carne)->toBe('C12345');
    $this->assertDatabaseHas('estudiantes', [
        'carne' => 'C12345',
        'consejero_id' => $docente->id,
    ]);
});

test('CP-09 un estudiante solo tiene un consejero al cambiarlo', function () {
    $d1 = crearDocenteFijo(1001);
    $d2 = crearDocenteFijo(1002);
    $estudiante = crearEstudianteMaria($d1);

    $estudiante->consejero()->associate($d2);
    $estudiante->save();

    $d1 = $d1->fresh();
    $d2 = $d2->fresh();
    expect($estudiante->fresh()->consejero->codEmpleado)->toBe(1002);
    expect($d1->estudiantes)->toHaveCount(0);
    expect($d2->estudiantes)->toHaveCount(1);
    $this->assertDatabaseHas('estudiantes', [
        'carne' => 'C12345',
        'consejero_id' => $d2->id,
    ]);
    $this->assertDatabaseCount('estudiantes', 1);
});

test('CP-10 matricular un estudiante crea su registro sin nota', function () {
    $grupo = crearGrupoFijo();
    $estudiante = crearEstudianteMaria(crearDocenteFijo(1001));

    $grupo->estudiantes()->attach($estudiante->id);

    $pivot = $grupo->estudiantes->first()->pivot;
    expect($pivot)->toBeInstanceOf(EstudianteMatriculado::class);
    expect($pivot->nota)->toBeNull();
    $this->assertDatabaseCount('estudiantes_matriculados', 1);
    $this->assertDatabaseHas('estudiantes_matriculados', [
        'grupo_id' => $grupo->id,
        'estudiante_id' => $estudiante->id,
    ]);
});

test('CP-11 registra la nota solo del estudiante indicado', function () {
    $grupo = crearGrupoFijo();
    $estudiante = crearEstudianteMaria(crearDocenteFijo(1001));
    $otro = Estudiante::factory()->create();
    $grupo->estudiantes()->attach([$estudiante->id, $otro->id]);
    $antes = buscarMatricula($grupo, $estudiante);

    $grupo->registrarNota($estudiante, 8.5);

    $matricula = buscarMatricula($grupo, $estudiante);
    expect($matricula->nota)->toBe(8.5);
    expect($matricula->nota)->toBeFloat();
    expect($matricula->id)->toBe($antes->id);
    expect($matricula->grupo_id)->toBe($grupo->id);
    expect($matricula->estudiante_id)->toBe($estudiante->id);
    expect(buscarMatricula($grupo, $otro)->nota)->toBeNull();
    $this->assertDatabaseCount('estudiantes_matriculados', 2);
});

test('CP-12 rechaza una nota fuera del rango de 0 a 10', function (float|int $nota) {
    $grupo = crearGrupoFijo();
    $estudiante = crearEstudianteMaria(crearDocenteFijo(1001));
    $grupo->estudiantes()->attach($estudiante->id);

    try {
        $grupo->registrarNota($estudiante, $nota);
        $this->fail('Se esperaba InvalidArgumentException para la nota '.$nota.'.');
    } catch (InvalidArgumentException $e) {
        expect($e)->toBeInstanceOf(InvalidArgumentException::class);
    }

    expect(buscarMatricula($grupo, $estudiante)->nota)->toBeNull();
})->with([-1, 10.5]);

test('CP-13 no permite matricular dos veces al mismo estudiante en el grupo', function () {
    $grupo = crearGrupoFijo();
    $estudiante = crearEstudianteMaria(crearDocenteFijo(1001));
    $grupo->estudiantes()->attach($estudiante->id);

    try {
        $grupo->estudiantes()->attach($estudiante->id);
        $this->fail('Se esperaba QueryException al matricular dos veces al estudiante.');
    } catch (QueryException $e) {
        expect($e)->toBeInstanceOf(QueryException::class);
    }

    $this->assertDatabaseCount('estudiantes_matriculados', 1);
    expect(
        EstudianteMatriculado::where('grupo_id', $grupo->id)
            ->where('estudiante_id', $estudiante->id)
            ->count()
    )->toBe(1);
});

// ---------------------------------------------------------------------
// C3, pruebas agregadas tras la primera corrida de mutación
// ---------------------------------------------------------------------

test('C3 acepta las notas límite 0 y 10', function (int $nota) {
    $grupo = crearGrupoFijo();
    $estudiante = crearEstudianteMaria(crearDocenteFijo(1001));
    $grupo->estudiantes()->attach($estudiante->id);

    $grupo->registrarNota($estudiante, $nota);

    $matricula = buscarMatricula($grupo, $estudiante);
    expect($matricula->nota)->toBe((float) $nota);
    expect($matricula->nota)->toBeFloat();
    $this->assertDatabaseHas('estudiantes_matriculados', [
        'grupo_id' => $grupo->id,
        'estudiante_id' => $estudiante->id,
        'nota' => $nota,
    ]);
    $this->assertDatabaseCount('estudiantes_matriculados', 1);
})->with([0, 10]);

test('C3 rechaza registrar nota a un estudiante no matriculado', function () {
    $grupo = crearGrupoFijo();
    $estudiante = crearEstudianteMaria(crearDocenteFijo(1001));

    try {
        $grupo->registrarNota($estudiante, 8.5);
        $this->fail('Se esperaba DomainException al registrar nota a un estudiante no matriculado.');
    } catch (DomainException $e) {
        expect($e)->toBeInstanceOf(DomainException::class);
    }

    $this->assertDatabaseCount('estudiantes_matriculados', 0);
});
