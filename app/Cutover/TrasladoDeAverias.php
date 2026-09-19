<?php

namespace App\Cutover;

use Illuminate\Support\Facades\DB;

final class TrasladoDeAverias implements Traslado
{
    public function __construct(
        private readonly MapaDeClientes $clientes,
    ) {}

    public function ejecutar(): array
    {
        $averias = new Lote('quiebre');
        $sinCliente = 0;

        DB::connection('legacy')
            ->table('quiebre')
            ->orderBy('id_quiebre')
            ->chunk(1_000, function (iterable $filas) use ($averias, &$sinCliente): void {
                foreach ($filas as $fila) {
                    $idCliente = $this->clientes->resolver($fila->ruc);

                    if ($idCliente === null) {
                        $sinCliente++;

                        continue;
                    }

                    $averias->agregar([
                        'id_quiebre' => (int) $fila->id_quiebre,
                        'id_cliente' => $idCliente,
                        'estado' => Saneamiento::texto($fila->estado, 20) ?? 'PENDIENTE',
                        'id_validador' => (int) $fila->id_validador,
                        'fecha_validacion' => Saneamiento::fecha($fila->fecha_validacion),
                        'caso_sf' => Saneamiento::texto($fila->casosf, 50),
                        'comentario_validador' => Saneamiento::texto($fila->comentario_validador),
                        'id_usuario' => (int) $fila->id_usuario,
                        'id_supervisor' => (int) $fila->id_supervisor,
                        'zonal_telefonica' => Saneamiento::idEnTexto($fila->zonal_telefonica),
                        'servicio' => Saneamiento::texto($fila->servicio, 100),
                        'numero_problema' => Saneamiento::texto($fila->numero_problema, 50),
                        'tipo_averia' => Saneamiento::texto($fila->tipo_averia, 100),
                        'problema_equipo' => Saneamiento::texto($fila->problema_equipo, 255),
                        'detalle_equipo' => Saneamiento::texto($fila->detalle_equipo),
                        'fecha_inicio_averia' => Saneamiento::fecha($fila->fecha_inicio_averia),
                        'fecha_activacion' => Saneamiento::fecha($fila->fecha_activacion),
                        'fecha_inicio' => Saneamiento::fecha($fila->fecha_inicio),
                        'ticket_atencion' => Saneamiento::texto($fila->ticket_atencion, 50),
                        'numero_ticket' => Saneamiento::texto($fila->numero_ticket, 50),
                        'fecha_ticket_atencion' => Saneamiento::fecha($fila->fecha_ticket_atencion),
                        'contacto1' => Saneamiento::texto($fila->contacto1, 100),
                        'celular1' => Saneamiento::texto($fila->celular1, 50),
                        'correo1' => Saneamiento::texto($fila->correo1, 100),
                        'contacto2' => Saneamiento::texto($fila->contacto2, 100),
                        'celular2' => Saneamiento::texto($fila->celular2, 50),
                        'correo2' => Saneamiento::texto($fila->correo2, 100),
                        'comentario_ejecutivo' => Saneamiento::texto($fila->comentario_ejecutivo),
                        'back' => Saneamiento::texto($fila->back, 50),
                        'fecha_registro' => Saneamiento::fecha($fila->fecha_registro),
                        'validacion' => Saneamiento::texto($fila->validacion, 50),
                    ]);
                }

                $averias->vaciar();
            });

        return [
            'quiebre' => $averias->cerrar(),
            'averias sin cliente' => $sinCliente,
        ];
    }
}
