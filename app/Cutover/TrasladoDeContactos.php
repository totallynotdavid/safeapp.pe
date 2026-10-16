<?php

namespace App\Cutover;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

final class TrasladoDeContactos implements Traslado
{
    public function __construct(
        private readonly MapaDeClientes $clientes,
    ) {}

    public function ejecutar(): array
    {
        $llamadas = new Lote('llamada');
        $sinCliente = 0;

        DB::connection('legacy')
            ->table('llamada')
            ->orderBy('id_llamada')
            ->chunk(
                10_000,
                /** @param Collection<int, stdClass> $filas */ function (Collection $filas) use (
                    $llamadas,
                    &$sinCliente,
                ): void {
                    foreach ($filas as $fila) {
                        $idCliente = $this->clientes->resolver($fila->ruc);

                        if ($idCliente === null) {
                            $sinCliente++;

                            continue;
                        }

                        $llamadas->agregar([
                            'id_llamada' => (int) $fila->id_llamada,
                            'id_cliente' => $idCliente,
                            // A contact outlives the campaign that prompted it.
                            'id_dato' => null,
                            'id_usuario' => (int) $fila->id_usuario,
                            // 0 means no disposition was chosen. The history panel
                            // renders it as a dash.
                            'id_respuesta' => (int) $fila->id_respuesta,
                            'fecha' => $fila->fecha,
                            'razon_social' => Saneamiento::texto($fila->razon_social, 255),
                            'telefono' => Saneamiento::texto($fila->telefono, 30),
                            'nombre_contacto' => Saneamiento::texto($fila->nombre_contacto, 150),
                            'correo' => Saneamiento::texto($fila->correo, 150),
                            'comentario' => Saneamiento::texto($fila->comentario),
                            'gestion' => Saneamiento::texto($fila->gestion, 100),
                            'tipo_data' => Saneamiento::texto($fila->tipo_data, 30),
                            'q_movistar' => Saneamiento::conteo($fila->q_lineas),
                            'q_entel' => Saneamiento::conteo($fila->q_entel),
                            'q_claro' => Saneamiento::conteo($fila->q_claro),
                            'q_bitel' => Saneamiento::conteo($fila->q_bitel),
                        ]);
                    }

                    $llamadas->vaciar();
                },
            );

        return [
            'llamada' => $llamadas->cerrar(),
            'contactos sin cliente' => $sinCliente,
        ];
    }
}
