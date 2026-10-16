<?php

namespace Database\Factories;

use App\Models\AsignacionDato;
use App\Models\Cliente;
use App\Models\Dato;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Dato>
 */
class DatoFactory extends Factory
{
    protected $model = Dato::class;

    public function definition(): array
    {
        return [
            'id_cliente' => Cliente::factory(),
            'estado' => Dato::ESTADO_PENDIENTE,
            'origen' => fake()->bothify('DB_????##'),
            'tipo_data' => 'MOVIL',
            'zonal' => mb_strtoupper(fake()->city()),
            'nodo' => fake()->bothify('TELEF: 9######## DNI: ########'),
            'q_movistar' => 0,
            'q_entel' => 0,
            'q_claro' => 0,
            'q_bitel' => 0,
        ];
    }

    /**
     * Lets a fixture name its subject by `ruc` or `razon_social`, resolving the
     * Cliente as AltaDeClientes and the importer do.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function newModel(array $attributes = []): Dato
    {
        if (array_key_exists('ruc', $attributes) || array_key_exists('razon_social', $attributes)) {
            $cliente = Cliente::query()->firstOrCreate(
                ['ruc' => $attributes['ruc'] ?? fake()->unique()->numerify('20#########')],
                ['razon_social' => $attributes['razon_social'] ?? mb_strtoupper(fake()->company())],
            );

            $attributes['id_cliente'] = $cliente->id_cliente;
            unset($attributes['ruc'], $attributes['razon_social']);
        }

        return parent::newModel($attributes);
    }

    // A hold is the only way a record is assigned. Unassigned is the absence of one.
    public function asignadoA(int $idUsuario, ?int $idTienda): static
    {
        return $this->afterCreating(static function (Dato $dato) use ($idUsuario, $idTienda): void {
            AsignacionDato::query()->create([
                'id_dato' => $dato->id_dato,
                'id_usuario' => $idUsuario,
                'id_tienda' => $idTienda,
                'fecha_asignacion' => now()->toDateString(),
                'activa' => AsignacionDato::ACTIVA,
            ]);
        });
    }

    public function gestionado(): static
    {
        return $this->state(['estado' => Dato::ESTADO_GESTIONADO]);
    }

    public function tipoData(string $tipoData): static
    {
        return $this->state(['tipo_data' => $tipoData]);
    }
}
