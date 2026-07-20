<?php

namespace App\Cutover;

use Illuminate\Support\Facades\DB;

/**
 * Copies the nota de cargo móvil cases.
 *
 * Four legacy columns are not carried: `tipo`, `ejecutivo_telefonica`,
 * `tipo_etapa`, and `zonal_telefonica`, which is folded into `zonal` the same
 * way fija's is. `ejecutivo_tdp` is not dropped, it becomes `id_cartera`.
 */
final class TrasladoDeNotasMovil implements Traslado
{
    public function __construct(
        private readonly MapaDeClientes $clientes,
    ) {}

    public function ejecutar(): array
    {
        $casos = new Lote('nc_movil');
        $sinCliente = 0;

        DB::connection('legacy')
            ->table('nc_movil')
            ->orderBy('id_cargo')
            ->chunk(5_000, function (iterable $filas) use ($casos, &$sinCliente): void {
                foreach ($filas as $fila) {
                    $idCliente = $this->clientes->resolver($fila->ruc);

                    if ($idCliente === null) {
                        $sinCliente++;

                        continue;
                    }

                    $casos->agregar([
                        'id_cargo' => (int) $fila->id_cargo,
                        'id_cliente' => $idCliente,
                        'estado' => Saneamiento::texto($fila->estado, 30) ?? 'PENDIENTE',
                        'validacion' => Saneamiento::texto($fila->validacion, 30) ?? 'PENDIENTE',
                        'id_usuario' => (int) $fila->id_usuario,
                        'id_supervisor' => (int) $fila->id_supervisor,
                        'fecha_ingreso' => Saneamiento::fecha($fila->fecha_ingreso),
                        'q_lineas' => Saneamiento::conteo($fila->q_lineas),
                        'cargo_fijo' => Saneamiento::monto($fila->cargo_fijo),
                        'modalidad' => Saneamiento::texto($fila->modalidad, 100),
                        'contacto' => Saneamiento::texto($fila->contacto, 100),
                        'telefono1' => Saneamiento::texto($fila->telefono1, 50),
                        'telefono2' => Saneamiento::texto($fila->telefono2, 50),
                        'comentario_ejecutivo' => Saneamiento::texto($fila->comentario_ejecutivo),
                        // Back's correction wins, as in fija: zonal_telefonica
                        // is set in none of the 329 cases back never touched.
                        'zonal' =>
                            Saneamiento::idEnTexto($fila->zonal_telefonica) ?? Saneamiento::idEnTexto($fila->zonal),
                        // Two columns held the same attribution and different
                        // screens joined cartera through different ones. The
                        // populated one wins: ejecutivo_tdp carries 576 rows to
                        // id_cartera's 3, and every value in it resolves to a
                        // real cartera. The two rows where both are set disagree
                        // (65 vs 85, 87 vs 34), so one of them has to be chosen
                        // and it is the column the screens actually filled.
                        'id_cartera' => Saneamiento::idEnTexto($fila->ejecutivo_tdp) ?? (int) $fila->id_cartera,
                        'oportunidad' => Saneamiento::texto($fila->oportunidad, 100),
                        'nivel_negociacion' => Saneamiento::texto($fila->nivel_negociacion, 10),
                        'fecha_estimada' => Saneamiento::fecha($fila->fecha_estimada),
                        'tipo_producto' => Saneamiento::texto($fila->tipo_producto, 100),
                        'etapa' => Saneamiento::texto($fila->etapa, 100),
                        'id_validador' => (int) $fila->id_validador,
                        'fecha_validacion' => Saneamiento::fecha($fila->fecha_validacion),
                        'comentario_validador' => Saneamiento::texto($fila->comentario_validador),
                        'fecha_actualizacion' => Saneamiento::fecha($fila->fecha_actualizacion),
                        'id_back' => (int) $fila->id_back,
                        'comentario_back' => Saneamiento::texto($fila->comentario_back),
                        'nodo' => Saneamiento::texto($fila->nodo, 100) ?? 'SINNODO',
                        'casofwa' => Saneamiento::texto($fila->casofwa, 100),
                        'ciclo' => Saneamiento::texto($fila->ciclo, 50),
                        'cuenta_financiera' => Saneamiento::texto($fila->cuenta_financiera, 100),
                        'comision' => Saneamiento::texto($fila->comision, 50),
                        'observacion' => Saneamiento::texto($fila->observacion, 50),
                        'descuento' => Saneamiento::texto($fila->descuento, 50),
                        'fecha_liquidacion' => Saneamiento::fecha($fila->fecha_liquidacion),
                        'comentario_liquidador' => Saneamiento::texto($fila->comentario_liquidador),
                    ]);
                }

                $casos->vaciar();
            });

        return [
            'nc_movil' => $casos->cerrar(),
            'casos sin cliente' => $sinCliente,
        ];
    }
}
