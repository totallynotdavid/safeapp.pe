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
            // A validated, completed case: the state most of the pipeline ends
            // in (COMPLETADO is 3,481 of 5,970 production rows) and the one the
            // back queue lists. registrada() winds it back to a freshly filed
            // case and rechazada() flips the validación gate.
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

            // A COMPLETADO case carries both: fecha_actualizacion is when the
            // back office last acted and fecha_liquidacion is the month it
            // settled, which is the column the back queue windows it by.
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

    // A case as the ejecutivo just filed it: nobody has ruled on it and the back
    // office has not touched it, so it is XINGRESAR and PENDIENTE and every
    // stamp downstream is empty. This is what the registro and validación
    // screens work with.
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

    // A case for a client with a known ruc. Every screen shows the client's ruc,
    // so that is what identifies a row on the page.
    public function deRuc(string $ruc): static
    {
        return $this->state(['id_cliente' => Cliente::factory()->create(['ruc' => $ruc])]);
    }

    // A case the validador rejected: the back office must never see it.
    public function rechazada(): static
    {
        return $this->state(['validacion' => NcFija::VALIDACION_RECHAZADO]);
    }
}
