<?php

namespace App\Cockpit;

use App\Models\Cliente;
use Illuminate\Support\Facades\DB;

/**
 * How a client that the import never delivered enters the system: an agent has
 * a RUC nobody has loaded, and registers it so it can be worked like any other.
 *
 * Cliente owns what a client is; DistribucionDeClientes owns who holds one.
 * This owns only the intake door.
 */
class AltaDeClientes
{
    // Where a client came from when an agent registered it by hand. Provenance
    // belongs here and nowhere else. Legacy wrote this same fact a second time
    // into tipo_data as 'CONVERGENTE', which is the column that decides which
    // queue works the client, so 2,677 clients became unreachable. Keep the two
    // apart: origen answers "where from", tipo_data answers "which queue".
    public const ORIGEN = 'INHOUSE';

    /**
     * Registers a client the import never delivered, already held by the agent
     * who found it.
     *
     * It is created PENDIENTE, not gestionado: registering is not contacting.
     * The caller sends the agent straight to the client's screen, where the
     * existing gestión form logs the first contact and advances the estado, so
     * there is exactly one write path for "a contact happened".
     *
     * @param  array<string, mixed>  $datos  validated payload from StoreClienteRequest
     */
    public static function registrar(array $datos, int $idAgente, int $idTienda): Cliente
    {
        return DB::transaction(static function () use ($datos, $idAgente, $idTienda): Cliente {
            $cliente = new Cliente;

            // Nothing here is mass-assignable: every value is either agent input
            // already validated at the boundary, or server state.
            $cliente->forceFill([
                ...$datos,
                'origen' => self::ORIGEN,
                'id_usuario' => $idAgente,
                'id_tienda' => $idTienda,
                'estado' => Cliente::ESTADO_PENDIENTE,
            ])->save();

            return $cliente;
        });
    }
}
