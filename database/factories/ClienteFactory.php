<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cliente>
 */
class ClienteFactory extends Factory
{
    protected $model = Cliente::class;

    public function definition(): array
    {
        return [
            'ruc' => fake()->unique()->numerify('20#########'),
            'razon_social' => mb_strtoupper(fake()->company()),
        ];
    }
}
