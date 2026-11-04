<?php

namespace Tests\Feature;

use App\Models\Curso;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrearCursoTest extends TestCase
{
    use RefreshDatabase;

    public function test_crear_curso_lo_guarda_en_la_base_de_datos(): void
    {
        $respuesta = $this->postJson('/api/cursos', [
            'sigla' => 'IF6100',
            'nombreCurso' => 'Análisis y Diseño de Sistemas',
            'creditos' => 4,
        ]);

        $respuesta->assertStatus(201);

        $this->assertDatabaseHas('cursos', [
            'sigla' => 'IF6100',
            'nombreCurso' => 'Análisis y Diseño de Sistemas',
            'creditos' => 4,
        ]);
    }

    public function test_no_guarda_un_curso_sin_sigla(): void
    {
        $respuesta = $this->postJson('/api/cursos', [
            'nombreCurso' => 'Análisis y Diseño de Sistemas',
            'creditos' => 4,
        ]);

        $respuesta->assertStatus(422);

        $this->assertDatabaseCount('cursos', 0);
    }

    public function test_no_guarda_dos_cursos_con_la_misma_sigla(): void
    {
        Curso::create([
            'sigla' => 'IF6100',
            'nombreCurso' => 'Análisis y Diseño de Sistemas',
            'creditos' => 4,
        ]);

        $respuesta = $this->postJson('/api/cursos', [
            'sigla' => 'IF6100',
            'nombreCurso' => 'Otro curso',
            'creditos' => 3,
        ]);

        $respuesta->assertStatus(422);

        $this->assertDatabaseCount('cursos', 1);
    }
}