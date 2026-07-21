<?php

namespace Database\Factories;

use App\Models\AsignacionTienda;
use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AsignacionTienda>
 */
class AsignacionTiendaFactory extends Factory
{
    protected $model = AsignacionTienda::class;

    public function definition(): array
    {
        return [
            'id_tienda' => Tienda::factory(),
            'id_usuario' => Usuario::factory(),
            'estado' => 0,
        ];
    }
}
