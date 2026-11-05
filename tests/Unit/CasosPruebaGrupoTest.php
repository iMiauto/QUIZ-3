<?php

namespace Tests\Unit;

use App\Models\Curso;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Grupo;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CasosPruebaGrupoTest extends TestCase
{
    use RefreshDatabase;

    private Curso $curso;

    protected function setUp(): void
    {
        parent::setUp();

        $this->curso = Curso::create([
            'sigla' => 'IF6100',
            'nombreCurso' => 'Análisis y Diseño de Sistemas',
            'creditos' => 4,
        ]);
    }

    private function crearGrupo(int $numeroGrupo = 1, bool $abierto = true): Grupo
    {
        return Grupo::create([
            'curso_id' => $this->curso->id,
            'numeroCiclo' => 2,
            'ano' => 2026,
            'numeroGrupo' => $numeroGrupo,
            'abierto' => $abierto,
        ]);
    }

    private function crearDocentesFijos(): array
    {
        $datos = [
            [1001, 'Ana', 'Mora Vargas'],
            [1002, 'Luis', 'Rojas Solano'],
            [1003, 'Carla', 'Jiménez Araya'],
        ];

        $docentes = [];
        foreach ($datos as [$cod, $nombre, $apellidos]) {
            $docentes[$cod] = Docente::create([
                'codEmpleado' => $cod,
                'nombreEmpleado' => $nombre,
                'apellidosEmpleado' => $apellidos,
            ]);
        }

        return $docentes;
    }

    private function matricularEstudiantes(Grupo $grupo, int $cantidad): void
    {
        $estudiantes = Estudiante::factory()->count($cantidad)->create();

        $grupo->estudiantes()->attach($estudiantes->pluck('id')->all());
    }

    public function test_cp01_crear_y_recuperar_grupo_01(): void
    {
        $creado = $this->crearGrupo(1, true);

        $grupo = Grupo::find($creado->id);

        $this->assertNotNull($grupo);
        $this->assertSame(2, $grupo->numeroCiclo);
        $this->assertSame(2026, $grupo->ano);
        $this->assertSame(1, $grupo->numeroGrupo);
        $this->assertSame(true, $grupo->abierto);
        $this->assertSame('IF6100', $grupo->curso->sigla);
        $this->assertSame(4, $grupo->curso->creditos);
        $this->assertDatabaseCount('grupos', 1);
    }

    public function test_cp02_curso_con_tres_grupos(): void
    {
        $this->crearGrupo(1, true);
        $this->crearGrupo(2, true);
        $this->crearGrupo(3, false);

        $grupos = $this->curso->grupos;
        $abiertos = $grupos->where('abierto', true);

        $this->assertCount(3, $grupos);
        $this->assertCount(2, $abiertos);
        $this->assertEqualsCanonicalizing([1, 2], $abiertos->pluck('numeroGrupo')->all());
        $this->assertSame([$this->curso->id], $grupos->pluck('curso_id')->unique()->values()->all());
    }

    public function test_cp03_asignar_dos_docentes(): void
    {
        $grupo = $this->crearGrupo();
        $docentes = $this->crearDocentesFijos();

        $grupo->asignarDocente($docentes[1001]);
        $grupo->asignarDocente($docentes[1002]);

        $this->assertCount(2, $grupo->docentes);
        $this->assertEqualsCanonicalizing(
            [1001, 1002],
            $grupo->docentes->pluck('codEmpleado')->all()
        );
        $this->assertTrue($docentes[1001]->grupos->contains($grupo));
        $this->assertDatabaseCount('docente_grupo', 2);
    }

    public function test_cp04_tercer_docente_lanza_excepcion(): void
    {
        $grupo = $this->crearGrupo();
        $docentes = $this->crearDocentesFijos();
        $grupo->asignarDocente($docentes[1001]);
        $grupo->asignarDocente($docentes[1002]);

        try {
            $grupo->asignarDocente($docentes[1003]);
            $this->fail('Se esperaba DomainException al asignar un tercer docente.');
        } catch (DomainException $e) {
        }

        $this->assertCount(2, $grupo->fresh()->docentes);
        $this->assertDatabaseMissing('docente_grupo', [
            'docente_id' => $docentes[1003]->id,
        ]);
    }

    public function test_asignar_un_docente_ya_asignado_no_lanza_excepcion(): void
    {
        $grupo = $this->crearGrupo();
        $docentes = $this->crearDocentesFijos();
        $grupo->asignarDocente($docentes[1001]);
        $grupo->asignarDocente($docentes[1002]);

        try {
            $grupo->asignarDocente($docentes[1001]);
        } catch (DomainException $e) {
            $this->fail('No se esperaba DomainException al reasignar un docente ya asignado.');
        }

        $this->assertCount(2, $grupo->fresh()->docentes);
        $this->assertDatabaseCount('docente_grupo', 2);
    }

    public function test_cp05_grupo_con_14_estudiantes_no_abre(): void
    {
        $grupo = $this->crearGrupo(1, false);
        $docentes = $this->crearDocentesFijos();
        $grupo->asignarDocente($docentes[1001]);
        $this->matricularEstudiantes($grupo, 14);

        $this->assertFalse($grupo->puedeAbrirse());

        try {
            $grupo->abrir();
            $this->fail('Se esperaba DomainException al abrir un grupo con 14 estudiantes.');
        } catch (DomainException $e) {
        }

        $this->assertFalse($grupo->fresh()->abierto);
    }

    public function test_cp06_grupo_con_15_estudiantes_abre(): void
    {
        $grupo = $this->crearGrupo(1, false);
        $docentes = $this->crearDocentesFijos();
        $grupo->asignarDocente($docentes[1001]);
        $this->matricularEstudiantes($grupo, 15);

        $this->assertTrue($grupo->puedeAbrirse());

        $grupo->abrir();

        $this->assertTrue($grupo->fresh()->abierto);
    }
}
