<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\NcFija;
use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NcFija>
 */
class NcFijaFactory extends Factory
{
    protected $model = NcFija::class;

    public function definition(): array
    {
        $ingreso = fake()->dateTimeBetween('-6 months', '-1 month');
        $cierre = fake()->dateTimeBetween($ingreso, 'now');

        return [
            // A validated, completed case, which the back queue lists.
            'estado' => 'COMPLETADO',
            'validacion' => NcFija::VALIDACION_PROCEDE,

            'id_usuario' => Usuario::factory(),
            'id_supervisor' => 0,
            'fecha_ingreso' => $ingreso,
            'id_cliente' => Cliente::factory(),
            'subtipo' => fake()->randomElement(['MONO BA', 'DUO BA', 'DUO BA + TV', 'TRIO']),
            'descripcion' => fake()->randomElement(['200MB', '400MB', '600MB', '1000MB']),
            'cargo_fijo' => fake()->randomFloat(2, 40, 260),
            'modalidad' => 'ALTA',
            'contacto' => mb_strtoupper(fake()->name()),
            'telefono1' => fake()->numerify('9########'),
            'telefono2' => null,
            'direccion' => mb_strtoupper(fake()->streetAddress()),
            'coordenadas' => null,
            'tecnologia' => fake()->randomElement(NcFija::TECNOLOGIAS),
            'zonal' => Tienda::factory(),
            'comentario' => fake()->sentence(),

            'id_validador' => 0,
            'fecha_validacion' => $cierre,
            'comentario_validador' => 'VALIDACION OK',

            // The back queue windows a COMPLETADO case by fecha_liquidacion.
            'fecha_actualizacion' => $cierre,
            'fecha_liquidacion' => $cierre,
            'id_back' => 0,
            'comentario_back' => fake()->sentence(),
            'peticion' => fake()->numerify('##########A'),
            'contrata' => fake()->randomElement(['COBRA', 'LARI', 'COMFICA', 'DOMINION']),
            'nasignado' => null,
            'caso_sf' => null,
            'nodo' => 'NAT',
            'ncfinanciera' => null,
            'ncfe' => null,
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
            'estado' => 'XINGRESAR',
            'validacion' => NcFija::VALIDACION_PENDIENTE,
            'id_validador' => 0,
            'fecha_validacion' => null,
            'comentario_validador' => null,
            'fecha_actualizacion' => null,
            'fecha_liquidacion' => null,
            'id_back' => 0,
            'comentario_back' => null,
            'peticion' => null,
            'contrata' => null,
            'nodo' => 'SINNODO',
        ]);
    }

    // Every screen shows the client's ruc, so it identifies a row on the page.
    public function deRuc(string $ruc): static
    {
        return $this->state(['id_cliente' => Cliente::factory()->create(['ruc' => $ruc])]);
    }

    // A case the validador rejected: the back office must never see it.
    public function rechazada(): static
    {
        return $this->state(['validacion' => NcFija::VALIDACION_RECHAZADO]);
    }

    // All three dates move together: a COMPLETADO case is windowed by
    // fecha_liquidacion, so pinning only fecha_ingreso would miss the default
    // period.
    public function liquidadaEsteMes(): static
    {
        return $this->state([
            'fecha_ingreso' => now(),
            'fecha_actualizacion' => now(),
            'fecha_liquidacion' => now(),
        ]);
    }
}
