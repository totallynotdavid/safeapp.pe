<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cliente>
 */
class ClienteFactory extends Factory
{
    protected $model = Cliente::class;

    public function definition(): array
    {
        return [
            'ruc' => fake()->unique()->numerify('20#########'),
            'razon_social' => mb_strtoupper(fake()->company()),
            // Unassigned by default: the pool an agent draws from. asignadoA()
            // puts a record in a specific agent's queue.
            'id_usuario' => 0,
            'id_tienda' => null,
            'estado' => Cliente::ESTADO_PENDIENTE,
            'origen' => fake()->bothify('DB_????##'),
            'tipo_data' => 'MOVIL',
            'fecha_asignacion' => null,
            'zonal' => mb_strtoupper(fake()->city()),
            'nodo' => fake()->bothify('TELEF: 9######## DNI: ########'),
            'q_movistar' => 0,
            'q_entel' => 0,
            'q_claro' => 0,
            'q_bitel' => 0,
        ];
    }

    public function asignadoA(int $idUsuario, ?int $idTienda): static
    {
        return $this->state([
            'id_usuario' => $idUsuario,
            'id_tienda' => $idTienda,
            'fecha_asignacion' => now(),
        ]);
    }

    public function gestionado(): static
    {
        return $this->state(['estado' => Cliente::ESTADO_GESTIONADO]);
    }

    public function tipoData(string $tipoData): static
    {
        return $this->state(['tipo_data' => $tipoData]);
    }
}
