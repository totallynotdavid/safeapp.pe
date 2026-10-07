<?php

namespace Database\Factories;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Usuario>
 */
class UsuarioFactory extends Factory
{
    protected $model = Usuario::class;

    // Default plaintext behind the factory's bcrypt hash, so tests can log in
    // without threading a password through every ->create() call.
    public const CLAVE = 'clave-secreta';

    public function definition(): array
    {
        return [
            'usuario' => fake()->unique()->userName(),
            'clave' => self::CLAVE,
            'id_rol' => Rol::factory(),
            'personal' => fake()->name(),
            'estado' => 1,
            'sexo' => fake()->randomElement(['M', 'F']),
            'id_supervisor' => 0,
            'fecha_nacimiento' => fake()->date(),
            'telefono' => fake()->numerify('9########'),
            'correo' => fake()->safeEmail(),
        ];
    }

    public function inactivo(): static
    {
        return $this->state(['estado' => 0]);
    }

    // An account the cutover created: it cannot sign in until a password is set.
    public function sinClave(): static
    {
        return $this->state(['clave' => null]);
    }

    public function clave(string $plain): static
    {
        return $this->state(['clave' => $plain]);
    }
}
