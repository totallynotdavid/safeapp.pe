<?php

namespace App\Cockpit;

use App\Models\AsignacionDato;
use App\Models\Dato;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Owns who holds a campaign record: the unassigned pool, batch assignment, and
 * release. `Dato` owns what a record is and what logging a contact does to it.
 *
 * Assignment is now an open row in `asignacion_datos` rather than three columns
 * overwritten on the record, so releasing keeps the history of who held what.
 */
class DistribucionDeDatos
{
    // Upper bound on one assignment request. An agent may receive fewer.
    public const LOTE = 30;

    /**
     * Every origen with stock in the pool, largest first. Callers offer these as
     * choices rather than accepting a typed origen, because an exact-match
     * string the agent has to guess silently returns nothing.
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
     * Write owner of "an agent takes a batch of records out of the pool".
     *
     * A record is handed to exactly one agent. The select must stay locked and
     * inside the transaction to hold that: without it, two concurrent requests
     * read the same rows and both try to open a hold. The unique index on
     * (id_dato, activa) means the loser now fails loudly instead of silently
     * taking rows from an agent who was already told they were theirs, but
     * failing loudly is still a failure, so the lock is what makes it wait.
     *
     * Ordering is arbitrary but must be deterministic. Randomising it is not a
     * substitute for the lock and sorts the whole pool on every request.
     *
     * @return int how many records the agent actually received
     */
    public static function asignarLote(int $idAgente, int $idTienda, string $origen, string $tipoData): int
    {
        return DB::transaction(static function () use ($idAgente, $idTienda, $origen, $tipoData): int {
            $ids = self::disponibles($tipoData)
                ->where('origen', $origen)
                ->orderBy('id_dato')
                ->limit(self::LOTE)
                ->lockForUpdate()
                ->pluck('id_dato');

            return $ids->isEmpty() ? 0 : self::abrir($ids->all(), $idAgente, $idTienda);
        });
    }

    /**
     * Returns the agent's pending records to the pool. A gestionado record stays
     * with the agent, so its logged calls keep an owner.
     *
     * @return int how many records were returned
     */
    public static function liberarPendientes(int $idAgente, string $tipoData): int
    {
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
     * Opens a hold on each record for one agent.
     *
     * @param  list<int>  $idsDato
     * @return int how many holds were opened
     */
    private static function abrir(array $idsDato, int $idAgente, int $idTienda): int
    {
        $fecha = now()->toDateString();

        AsignacionDato::query()->insert(array_map(
            static fn (int $idDato): array => [
                'id_dato' => $idDato,
                'id_usuario' => $idAgente,
                'id_tienda' => $idTienda,
                // When the agent took the record, not when the import loaded it.
                'fecha_asignacion' => $fecha,
                'activa' => AsignacionDato::ACTIVA,
            ],
            $idsDato,
        ));

        Dato::query()
            ->whereIn('id_dato', $idsDato)
            ->pluck('id_cliente')
            ->each(static fn (int $idCliente) => AccesoAClientes::otorgar($idCliente, $idAgente));

        return count($idsDato);
    }

    /**
     * The pool: nobody is holding the record and nobody has worked it yet.
     *
     * @return Builder<Dato>
     */
    private static function disponibles(string $tipoData): Builder
    {
        return self::sinAgente(
            Dato::query()
                ->where('estado', Dato::ESTADO_PENDIENTE)
                ->where('tipo_data', $tipoData),
        );
    }

    /**
     * @param  Builder<Dato>  $query
     * @return Builder<Dato>
     */
    private static function sinAgente(Builder $query): Builder
    {
        return $query->whereDoesntHave('asignacionActiva');
    }
}
