<?php

namespace App\Cockpit;

use App\Models\AsignacionDato;
use App\Models\Cliente;
use App\Models\Dato;
use Illuminate\Support\Facades\DB;

/**
 * How a customer the import never delivered enters the system: an agent has a
 * RUC nobody has loaded, and registers it so it can be worked like any other.
 *
 * `Dato` owns what a campaign record is; `DistribucionDeDatos` owns who holds
 * one. This owns only the intake door.
 */
class AltaDeClientes
{
    // Where a record came from when an agent registered it by hand. Provenance
    // belongs here and nowhere else. Legacy wrote this same fact a second time
    // into tipo_data as 'CONVERGENTE', which is the column that decides which
    // queue works the record, so 2,677 clients became unreachable. Keep the two
    // apart: origen answers "where from", tipo_data answers "which queue".
    public const ORIGEN = 'INHOUSE';

    /**
     * Registers a customer the import never delivered, already held by the agent
     * who found them.
     *
     * The record is created PENDIENTE, not gestionado: registering is not
     * contacting. The caller sends the agent straight to its screen, where the
     * existing gestión form logs the first contact and advances the estado, so
     * there is exactly one write path for "a contact happened".
     *
     * @param  array<string, mixed>  $payload  validated payload from StoreClienteRequest
     */
    public static function registrar(array $payload, int $idAgente, int $idTienda): Dato
    {
        return DB::transaction(static function () use ($payload, $idAgente, $idTienda): Dato {
            // Identity may already exist even when no campaign delivered it: a
            // charge note or an avería resolves the same customer.
            $cliente = Cliente::resolver($payload['ruc'], $payload['razon_social']);

            // Idempotent on the campaign record's natural key, so a double
            // submit returns the record the agent already has instead of
            // colliding with the unique index.
            $clave = [
                'id_cliente' => $cliente->id_cliente,
                'origen' => self::ORIGEN,
                'tipo_data' => $payload['tipo_data'],
            ];

            $dato = Dato::query()->where($clave)->first() ?? new Dato;

            // Nothing on Dato is mass-assignable: every value is either agent
            // input already validated at the boundary, or server state. estado
            // is set only on insert, since re-registering must not un-work a
            // record already gestionado.
            $dato->forceFill([
                ...$clave,
                ...array_diff_key($payload, array_flip(['ruc', 'razon_social', 'tipo_data'])),
                'estado' => $dato->exists ? $dato->estado : Dato::ESTADO_PENDIENTE,
            ])->save();

            if ($dato->asignacionActiva === null) {
                AsignacionDato::query()->create([
                    'id_dato' => $dato->id_dato,
                    'id_usuario' => $idAgente,
                    'id_tienda' => $idTienda,
                    'fecha_asignacion' => now()->toDateString(),
                    'activa' => AsignacionDato::ACTIVA,
                ]);
            }

            return $dato;
        });
    }
}
