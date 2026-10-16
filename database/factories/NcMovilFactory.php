<?php

namespace Database\Factories;

use App\Models\Cartera;
use App\Models\Cliente;
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
            // A validated, activated case, which both the back and liquidación
            // queues list.
            'estado' => 'ACTIVADO',
            'validacion' => NcMovil::VALIDACION_PROCEDE,

            'id_usuario' => Usuario::factory(),
            'id_supervisor' => 0,
            'fecha_ingreso' => $ingreso,
            'id_cliente' => Cliente::factory(),
            'q_lineas' => fake()->numberBetween(1, 30),
            'cargo_fijo' => fake()->randomFloat(2, 20, 500),
            'modalidad' => fake()->randomElement(NcMovil::MODALIDADES),
            'contacto' => mb_strtoupper(fake()->name()),
            'telefono1' => fake()->numerify('9########'),
            'telefono2' => null,
            'comentario_ejecutivo' => fake()->sentence(),
            'zonal' => Tienda::factory(),

            // Unattributed by default. atribuida() opts in.
            'id_cartera' => 0,
            'oportunidad' => fake()->word(),
            'nivel_negociacion' => fake()->randomElement(NcMovil::NIVELES_NEGOCIACION),
            'fecha_estimada' => fake()->dateTimeBetween($ingreso, '+2 months'),
            'tipo_producto' => fake()->randomElement(NcMovil::TIPOS_PRODUCTO),
            'etapa' => fake()->randomElement(NcMovil::ETAPAS),

            'id_validador' => 0,
            'fecha_validacion' => $actualizacion,
            'comentario_validador' => fake()->sentence(),

            'fecha_actualizacion' => $actualizacion,
            'id_back' => 0,
            'comentario_back' => fake()->sentence(),
            'nodo' => 'SINNODO',
            'casofwa' => null,
            'ciclo' => null,
            'cuenta_financiera' => null,

            // Liquidación starts empty: these are what that screen fills in.
            'comision' => null,
            'observacion' => null,
            'descuento' => null,
            'fecha_liquidacion' => null,
            'comentario_liquidador' => null,
        ];
    }

    public function estado(string $estado): static
    {
        return $this->state(['estado' => $estado]);
    }

    // A case as the ejecutivo just filed it: every stamp downstream is empty.
    public function registrada(): static
    {
        return $this->state([
            'estado' => 'PENDIENTE',
            'validacion' => NcMovil::VALIDACION_PENDIENTE,
            'id_validador' => 0,
            'fecha_validacion' => null,
            'comentario_validador' => null,
            'fecha_actualizacion' => null,
            'id_back' => 0,
            'comentario_back' => null,
        ]);
    }

    // Every screen shows the client's ruc, so it identifies a row on the page.
    public function deRuc(string $ruc): static
    {
        return $this->state(['id_cliente' => Cliente::factory()->create(['ruc' => $ruc])]);
    }

    public function atribuida(Cartera $cartera, Supervisor $supervisor): static
    {
        return $this->state([
            'id_cartera' => $cartera->id_cartera,
            'id_supervisor' => $supervisor->id_supervisor,
        ]);
    }

    public function rechazada(): static
    {
        return $this->state(['validacion' => NcMovil::VALIDACION_RECHAZADO]);
    }

    // Validated but fallen through, so not settleable.
    public function caida(): static
    {
        return $this->state(['estado' => 'CAIDA']);
    }
}
