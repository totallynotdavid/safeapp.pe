<?php

namespace Database\Factories;

use App\Models\Quiebre;
use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quiebre>
 */
class QuiebreFactory extends Factory
{
    protected $model = Quiebre::class;

    public function definition(): array
    {
        $registro = fake()->dateTimeBetween('-6 months', 'now');

        return [
            'estado' => 'PENDIENTE',
            'id_validador' => 0,
            'fecha_validacion' => null,
            'casosf' => null,
            'comentario_validador' => null,

            'id_usuario' => Usuario::factory(),
            'id_supervisor' => 0,
            'zonal_telefonica' => Tienda::factory(),
            'ruc' => fake()->numerify('20#########'),
            'razon_social' => mb_strtoupper(fake()->company()),
            'servicio' => fake()->randomElement(['INTERNET', 'TELEFONIA', 'CABLE']),
            'numero_problema' => (string) fake()->numberBetween(1, 20),
            'tipo_averia' => fake()->randomElement(['SIN SEÑAL', 'INTERMITENCIA', 'LENTITUD']),
            'problema_equipo' => fake()->sentence(3),
            'detalle_equipo' => fake()->sentence(),
            'fecha_inicio_averia' => $registro,
            'fecha_activacion' => fake()->dateTimeBetween('-1 year', $registro),
            'fecha_inicio' => $registro,
            'ticket_atencion' => fake()->randomElement(['SI', 'NO']),
            'numero_ticket' => (string) fake()->numberBetween(100_000, 999_999),
            'fecha_ticket_atencion' => $registro,
            'contacto1' => mb_strtoupper(fake()->name()),
            'celular1' => fake()->numerify('9########'),
            'correo1' => fake()->safeEmail(),
            'contacto2' => null,
            'celular2' => null,
            'correo2' => null,
            'comentario_ejecutivo' => fake()->sentence(),
            'back' => fake()->randomElement(['SI', 'NO']),
            'fecha_registro' => $registro,
            'validacion' => null,
        ];
    }

    public function estado(string $estado): static
    {
        return $this->state(['estado' => $estado]);
    }
}
