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
            'clave' => password_hash(self::CLAVE, PASSWORD_BCRYPT),
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

    // Stores a raw unsalted MD5 hash, reproducing an account that predates the
    // bcrypt migration, to exercise LegacyPasswordHasher's fallback + upgrade.
    public function claveMd5(string $plain): static
    {
        return $this->state(['clave' => md5($plain)]);
    }

    public function clave(string $plain): static
    {
        return $this->state(['clave' => password_hash($plain, PASSWORD_BCRYPT)]);
    }
}
