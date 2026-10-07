<?php

namespace App\Cockpit;

use App\Models\AsignacionDato;
use App\Models\Dato;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DistribucionDeDatos
{
    public const LOTE = 30;

    /** @return Collection<int, object{origen: string, disponibles: int}> */
    public static function origenesDisponibles(string $tipoData): Collection
    {
        /** @var Collection<int, object{origen: string, disponibles: int}> */
        return self::disponibles($tipoData)
            ->toBase()
            ->selectRaw('origen, COUNT(*) as disponibles')
            ->groupBy('origen')
            ->orderByDesc('disponibles')
            ->get();
    }

    public static function asignarLote(
        int $idAgente,
        int $idTienda,
        string $origen,
        string $tipoData,
    ): int {
        return DB::connection()->transaction(
            static function () use ($idAgente, $idTienda, $origen, $tipoData): int {
                // The lock has to be taken inside this transaction, or two
                // concurrent requests draw the same records.
                $ids = self::disponibles($tipoData)
                    ->where('origen', $origen)
                    ->orderBy('id_dato')
                    ->limit(self::LOTE)
                    ->lockForUpdate()
                    ->pluck('id_dato')
                    ->map(static fn (mixed $id): int => (int) $id)
                    ->all();

                if ($ids === []) {
                    return 0;
                }

                return self::abrir(
                    array_values($ids),
                    $idAgente,
                    $idTienda,
                );
            },
        );
    }

    public static function liberarPendientes(
        int $idAgente,
        string $tipoData,
    ): int {
        // Managed records stay assigned so their activity keeps an owner.
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

    /** @param  list<int>  $idsDato */
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

    /** @return Builder<Dato> */
    private static function disponibles(string $tipoData): Builder
    {
        return Dato::query()
            ->where('estado', Dato::ESTADO_PENDIENTE)
            ->where('tipo_data', $tipoData)
            ->whereDoesntHave('asignacionActiva');
    }
}
