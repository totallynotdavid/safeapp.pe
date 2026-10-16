<?php

namespace Database\Factories;

use App\Models\Cartera;
use App\Models\Tienda;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cartera>
 */
class CarteraFactory extends Factory
{
    protected $model = Cartera::class;

    public function definition(): array
    {
        return [
            // Portfolio codes follow SC-000NN_CIUDAD.
            'nombre' => mb_strtoupper(fake()->numerify('SC-000##_').fake()->city()),
            'propietario' => mb_strtoupper(fake()->name()),
            'id_tienda' => Tienda::factory(),
            'estado' => Cartera::ACTIVA,
            'DNI' => fake()->numerify('########'),
        ];
    }

    // An executive who has left: still joined to by their old cases, never
    // offered as a new choice.
    public function retirada(): static
    {
        return $this->state(['estado' => 0]);
    }
}
