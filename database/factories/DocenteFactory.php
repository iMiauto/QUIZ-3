<?php

namespace Database\Factories;

use App\Models\Docente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Docente>
 */
class DocenteFactory extends Factory
{
    protected $model = Docente::class;

    public function definition(): array
    {
        return [
            // 2000..9999 para no chocar con los docentes fijos 1001, 1002 y 1003
            'codEmpleado' => fake()->unique()->numberBetween(2000, 9999),
            'nombreEmpleado' => fake()->firstName(),
            'apellidosEmpleado' => fake()->lastName().' '.fake()->lastName(),
        ];
    }
}
