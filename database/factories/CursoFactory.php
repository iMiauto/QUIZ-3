<?php

namespace Database\Factories;

use App\Models\Curso;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Curso>
 */
class CursoFactory extends Factory
{
    protected $model = Curso::class;

    public function definition(): array
    {
        return [
            'sigla' => 'IF'.fake()->unique()->numerify('####'),
            'nombreCurso' => fake()->sentence(3),
            'creditos' => fake()->numberBetween(1, 4),
        ];
    }
}
