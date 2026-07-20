<?php

namespace Database\Factories;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Producto>
 */
class ProductoFactory extends Factory
{
    protected $model = Producto::class;

    public function definition(): array
    {
        return [
            'tipo' => Producto::FIJA,
            'subtipo' => fake()->randomElement(['MONO BA', 'DUO BA', 'DUO BA + TV', 'TRIO']),
            'descripcion' => fake()->randomElement(['50MB', '200MB', '400MB', '600MB', '1000MB']),
            'cargo_fijo' => fake()->randomFloat(2, 40, 260),
            'estado' => Producto::OFRECIBLE,
        ];
    }

    // A product the catalog no longer offers. It still names the cases sold
    // under it, so it must stay out of the form without disappearing.
    public function retirado(): static
    {
        return $this->state(['estado' => 0]);
    }
}
