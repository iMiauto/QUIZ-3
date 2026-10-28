<?php

namespace Tests\Unit;

use App\Models\Curso;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Grupo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GrupoTest extends TestCase
{
    use RefreshDatabase;

    public function test_instancia_de_grupo_con_toda_su_informacion(): void
    {
        $curso = Curso::create(['sigla' => 'IF6100', 'nombreCurso' => 'Análisis y Diseño de Sistemas', 'creditos' => 4]);
        $docente = Docente::create(['codEmpleado' => 1001, 'nombreEmpleado' => 'Carlos', 'apellidosEmpleado' => 'Méndez Rojas']);

       
        $grupo = new Grupo();
        $grupo->numeroCiclo = 2;
        $grupo->ano = 2026;
        $grupo->numeroGrupo = 1;
        $grupo->abierto = true;
        $grupo->curso()->associate($curso);
        $grupo->save();

     
        $grupo->docentes()->attach($docente->id);

     
        for ($i = 1; $i <= 15; $i++) {
            $estudiante = Estudiante::create([
                'carne' => 'C4I4'.str_pad($i, 2, '0', STR_PAD_LEFT),
                'nombre' => fake('es_ES')->firstName(),
                'apellidos' => fake('es_ES')->lastName().' '.fake('es_ES')->lastName(),
                'consejero_id' => $docente->id,
            ]);
            $grupo->estudiantes()->attach($estudiante->id, ['nota' => fake()->randomFloat(1, 6, 10)]);
        }

        $grupo->load(['curso', 'docentes', 'estudiantes.consejero']);

        $this->assertSame(2, $grupo->numeroCiclo);
        $this->assertSame(2026, $grupo->ano);
        $this->assertTrue($grupo->abierto);
        $this->assertSame('IF6100', $grupo->curso->sigla);
        $this->assertCount(1, $grupo->docentes);
        $this->assertCount(15, $grupo->estudiantes);
        $this->assertSame('Carlos', $grupo->estudiantes->first()->consejero->nombreEmpleado);
        $this->assertNotNull($grupo->estudiantes->first()->pivot->nota);
    }
}
