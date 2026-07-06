<?php

namespace Database\Factories;

use App\Models\Menu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Menu>
 */
class MenuFactory extends Factory
{
    protected $model = Menu::class;

    public function definition(): array
    {
        return [
            'descripcion' => mb_strtoupper(fake()->unique()->words(2, true)),
            'icono' => 'fa fa-circle',
            // estado 0 = visible, inverted from usuario.estado.
            'estado' => 0,
            'id_ref' => 0,
            'permiso' => fake()->unique()->slug(2),
        ];
    }

    // Section-header row: groups leaves in the sidebar, holds no permission.
    public function header(): static
    {
        return $this->state(['permiso' => null]);
    }
}
