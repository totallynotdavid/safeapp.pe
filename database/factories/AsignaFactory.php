<?php

namespace Database\Factories;

use App\Models\Asigna;
use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Asigna>
 */
class AsignaFactory extends Factory
{
    protected $model = Asigna::class;

    public function definition(): array
    {
        return [
            'id_tienda' => Tienda::factory(),
            'id_usuario' => Usuario::factory(),
            'estado' => 0,
        ];
    }
}
