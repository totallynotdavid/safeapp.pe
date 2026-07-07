<?php

namespace Database\Factories;

use App\Models\Tienda;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tienda>
 */
class TiendaFactory extends Factory
{
    protected $model = Tienda::class;

    public function definition(): array
    {
        return [
            'nombre' => mb_strtoupper(fake()->unique()->city()),
        ];
    }
}
