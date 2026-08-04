<?php

namespace App\Cockpit;

use App\Models\AsignacionDato;
use App\Models\Dato;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Manages the unassigned pool, batch assignment, and release of campaign data.
 */
class DistribucionDeDatos
{
    public const LOTE = 30;

    /**
     * Returns origins with available records, ordered by stock.
     *
     * @return Collection<int, object{origen: string, disponibles: int}>
     */
    public static function origenesDisponibles(string $tipoData): Collection
    {
        return self::disponibles($tipoData)
            ->selectRaw('origen, COUNT(*) as disponibles')
            ->groupBy('origen')
            ->orderByDesc('disponibles')
            ->get();
    }

    /**
     * Assigns up to one batch of records to an agent.
     * The rows must be selected with a lock inside the transaction. Otherwise,
     * concurrent requests may select the same available records.
     *
     * @return int Number of records assigned
     */
    public static function asignarLote(
        int $idAgente,
        int $idTienda,
        string $origen,
        string $tipoData,
    ): int {
        return DB::transaction(
            static function () use ($idAgente, $idTienda, $origen, $tipoData): int {
                $ids = self::disponibles($tipoData)
                    ->where('origen', $origen)
                    ->orderBy('id_dato')
                    ->limit(self::LOTE)
                    ->lockForUpdate()
                    ->pluck('id_dato');

                if ($ids->isEmpty()) {
                    return 0;
                }

                return self::abrir(
                    $ids->all(),
                    $idAgente,
                    $idTienda,
                );
            },
        );
    }

    /**
     * Returns the agent's pending records to the pool.
     * Managed records remain assigned so their activity keeps an owner.
     *
     * @return int Number of records released
     */
    public static function liberarPendientes(
        int $idAgente,
        string $tipoData,
    ): int {
        $pendientes = Dato::query()
            ->where('estado', Dato::ESTADO_PENDIENTE)
            ->where('tipo_data', $tipoData)
            ->select('id_dato');

        return AsignacionDato::query()
            ->where('id_usuario', $idAgente)
            ->where('activa', AsignacionDato::ACTIVA)
            ->whereIn('id_dato', $pendientes)
            ->update([
                'activa' => null,
                'fecha_liberacion' => now()->toDateString(),
            ]);
    }

    /**
     * @param  list<int>  $idsDato
     * @return int Number of assignments created
     */
    private static function abrir(
        array $idsDato,
        int $idAgente,
        int $idTienda,
    ): int {
        $fechaAsignacion = now()->toDateString();

        AsignacionDato::query()->insert(
            array_map(
                static fn (int $idDato): array => [
                    'id_dato' => $idDato,
                    'id_usuario' => $idAgente,
                    'id_tienda' => $idTienda,
                    'fecha_asignacion' => $fechaAsignacion,
                    'activa' => AsignacionDato::ACTIVA,
                ],
                $idsDato,
            ),
        );

        Dato::query()
            ->whereIn('id_dato', $idsDato)
            ->pluck('id_cliente')
            ->each(
                static fn (int $idCliente) => AccesoAClientes::otorgar(
                    $idCliente,
                    $idAgente,
                ),
            );

        return count($idsDato);
    }

    private static function disponibles(string $tipoData): Builder
    {
        return Dato::query()
            ->where('estado', Dato::ESTADO_PENDIENTE)
            ->where('tipo_data', $tipoData)
            ->whereDoesntHave('asignacionActiva');
    }
}
