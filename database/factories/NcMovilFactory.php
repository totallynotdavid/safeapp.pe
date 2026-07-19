<?php

namespace Database\Factories;

use App\Models\Cartera;
use App\Models\NcMovil;
use App\Models\Supervisor;
use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NcMovil>
 */
class NcMovilFactory extends Factory
{
    protected $model = NcMovil::class;

    public function definition(): array
    {
        $ingreso = fake()->dateTimeBetween('-6 months', '-1 month');
        $actualizacion = fake()->dateTimeBetween($ingreso, 'now');

        return [
            'estado' => 'INGRESADO',
            'id_usuario' => Usuario::factory(),
            'fecha_ingreso' => $ingreso,
            'q_lineas' => fake()->numberBetween(1, 30),
            'cargo_fijo' => fake()->randomFloat(2, 20, 500),
            'modalidad' => fake()->randomElement(['PORTABILIDAD', 'ALTA NUEVA', 'RENOVACION']),
            'tipo' => 'MOVIL',
            'ruc' => fake()->numerify('20#########'),
            'razon_social' => mb_strtoupper(fake()->company()),
            'contacto' => mb_strtoupper(fake()->name()),
            'telefono1' => fake()->numerify('9########'),
            'telefono2' => null,
            'comentario_ejecutivo' => fake()->sentence(),
            'zonal' => Tienda::factory(),

            // Most production cases are attributed to nobody (64 of 72), so
            // unattributed is the default and conCartera() opts in.
            'id_cartera' => 0,
            'id_supervisor' => 0,

            // Validación defaults to PROCEDE so a plain factory row lands in the
            // liquidación queue; the improcede() state flips it for negative tests.
            'id_validador' => 0,
            'validacion' => NcMovil::VALIDACION_PROCEDE,
            'fecha_validacion' => $actualizacion,
            'comentario_validador' => fake()->sentence(),

            'fecha_actualizacion' => $actualizacion,
            'id_back' => 0,
            'comentario_back' => fake()->sentence(),
            'oportunidad' => fake()->randomElement(['ALTA', 'MEDIA', 'BAJA']),
            'ejecutivo_telefonica' => mb_strtoupper(fake()->name()),
            'zonal_telefonica' => null,
            'nodo' => fake()->bothify('NODO-###'),
            'nivel_negociacion' => null,
            'fecha_estimada' => null,
            'casofwa' => null,
            'tipo_producto' => fake()->randomElement(['POSPAGO', 'PREPAGO', 'CONTROL']),
            'etapa' => null,
            'tipo_etapa' => null,

            // Liquidación starts empty: these are what the ported screen fills in.
            'comision' => null,
            'observacion' => null,
            'descuento' => null,
            'fecha_liquidacion' => null,
            'comentario_liquidador' => null,

            'ciclo' => null,
            'cuenta_financiera' => null,
        ];
    }

    public function estado(string $estado): static
    {
        return $this->state(['estado' => $estado]);
    }

    // A case attributed to a carrier account executive and a supervisor, which
    // is what the liquidación screen displays as context.
    public function atribuida(Cartera $cartera, Supervisor $supervisor): static
    {
        return $this->state([
            'id_cartera' => $cartera->id_cartera,
            'id_supervisor' => $supervisor->id_supervisor,
        ]);
    }

    // A case the validator rejected: it must never appear in the queue.
    public function improcede(): static
    {
        return $this->state(['validacion' => 'IMPROCEDE']);
    }
}
