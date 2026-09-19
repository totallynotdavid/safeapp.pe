<?php

namespace App\Cockpit;

use App\Models\AsignacionDato;
use App\Models\Dato;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

class AltaDeClientes
{
    // `origen` records provenance; `tipo_data` selects the queue.
    public const ORIGEN = 'INHOUSE';

    /** @param  array<string, mixed>  $payload */
    public static function registrar(
        array $payload,
        string $tipoData,
        Usuario $agente,
        int $idTienda,
    ): Dato {
        return DB::transaction(
            static function () use ($payload, $tipoData, $agente, $idTienda): Dato {
                $cliente = AccesoAClientes::resolverOcrear(
                    $agente,
                    $payload['ruc'],
                    $payload['razon_social'],
                );

                $clave = [
                    'id_cliente' => $cliente->id_cliente,
                    'origen' => self::ORIGEN,
                    'tipo_data' => $tipoData,
                ];

                $dato = Dato::query()->where($clave)->first() ?? new Dato;

                $dato->forceFill([
                    ...$clave,
                    ...array_diff_key(
                        $payload,
                        array_flip(['ruc', 'razon_social']),
                    ),
                    // Re-registering must not reset an already worked record.
                    'estado' => $dato->exists
                        ? $dato->estado
                        : Dato::ESTADO_PENDIENTE,
                ])->save();

                if ($dato->asignacionActiva === null) {
                    AsignacionDato::query()->create([
                        'id_dato' => $dato->id_dato,
                        'id_usuario' => $agente->id_usuario,
                        'id_tienda' => $idTienda,
                        'fecha_asignacion' => now()->toDateString(),
                        'activa' => AsignacionDato::ACTIVA,
                    ]);
                }

                return $dato;
            },
        );
    }
}
