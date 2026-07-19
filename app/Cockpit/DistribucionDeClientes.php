<?php

namespace App\Cockpit;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Owns who currently holds a client: the unassigned pool, batch assignment, and
 * release. Cliente owns what a client is and what logging a contact does to it.
 */
class DistribucionDeClientes
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
            ->whereNotNull('origen')
            ->groupBy('origen')
            ->orderByDesc('disponibles')
            ->get();
    }

    /**
     * Write owner of "an agent takes a batch of clients out of the pool".
     *
     * A client is handed to exactly one agent. The select must stay locked and
     * inside the transaction to hold that: without it, two concurrent requests
     * read the same rows and the later write silently takes them from the
     * earlier agent, who was already told the rows were theirs.
     *
     * Ordering is arbitrary but must be deterministic. Randomising it is not a
     * substitute for the lock and sorts the whole pool on every request.
     *
     * @return int how many clients the agent actually received
     */
    public static function asignarLote(int $idAgente, int $idTienda, string $origen, string $tipoData): int
    {
        return DB::transaction(static function () use ($idAgente, $idTienda, $origen, $tipoData): int {
            $ids = self::disponibles($tipoData)
                ->where('origen', $origen)
                ->orderBy('id_cliente')
                ->limit(self::LOTE)
                ->lockForUpdate()
                ->pluck('id_cliente');

            if ($ids->isEmpty()) {
                return 0;
            }

            // fecha_asignacion records when the agent took the client, not when
            // the import loaded it.
            return Cliente::query()
                ->whereIn('id_cliente', $ids)
                ->update([
                    'id_usuario' => $idAgente,
                    'id_tienda' => $idTienda,
                    'fecha_asignacion' => Carbon::now('America/Lima')->toDateString(),
                ]);
        });
    }

    /**
     * Returns the agent's pending clients to the pool. A gestionado client stays
     * with the agent, so its logged calls keep an owner.
     *
     * @return int how many clients were returned
     */
    public static function liberarPendientes(int $idAgente, string $tipoData): int
    {
        return Cliente::query()
            ->where('id_usuario', $idAgente)
            ->where('estado', Cliente::ESTADO_PENDIENTE)
            ->where('tipo_data', $tipoData)
            ->update([
                'id_usuario' => Cliente::SIN_AGENTE,
                'id_tienda' => null,
                'fecha_asignacion' => null,
            ]);
    }

    /**
     * The pool: nobody owns the row and nobody has worked it yet.
     *
     * @return Builder<Cliente>
     */
    private static function disponibles(string $tipoData): Builder
    {
        return Cliente::query()
            ->where('id_usuario', Cliente::SIN_AGENTE)
            ->where('estado', Cliente::ESTADO_PENDIENTE)
            ->where('tipo_data', $tipoData);
    }
}
