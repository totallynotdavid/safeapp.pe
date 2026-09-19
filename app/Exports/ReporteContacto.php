<?php

namespace App\Exports;

use App\Cockpit\Operadores;
use App\Models\Llamada;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Shared by both call-log reports, histórico (the agent's own contacts) and
 * seguimiento (a supervisor's team), which differ only in who is in scope.
 */
final class ReporteContacto
{
    private const ENCABEZADOS = [
        'PERSONAL',
        'FECHA',
        'RUC',
        'RAZON SOCIAL',
        'RESPUESTA',
        'GESTION',
        'TIPO DATA',
        'NOMBRE CONTACTO',
        'TELEFONO',
        'CORREO',
        'COMENTARIO',
    ];

    public static function descargar(string $nombre, Builder $consulta): StreamedResponse
    {
        return Reporte::descargar(
            $nombre,
            [
                ...self::ENCABEZADOS,
                // Derived from Operadores rather than spelled out, so a rebrand
                // stays a one-line change there.
                ...array_map(
                    static fn (string $operador): string => 'LINEAS '.$operador,
                    Operadores::TODOS,
                ),
            ],
            // Every relation a row reads, declared here rather than left to the
            // caller. Both screens happen to load cliente already; a third that
            // did not would get one query per contact and nothing would say so.
            $consulta->with('agente', 'respuesta', 'cliente'),
            self::fila(...),
        );
    }

    /** @return list<null|int|string|\DateTimeInterface> */
    private static function fila(Llamada $contacto): array
    {
        return [
            $contacto->agente?->personal,
            $contacto->fecha,
            $contacto->cliente?->ruc,
            $contacto->razon_social,
            $contacto->respuesta?->descripcion,
            $contacto->gestion,
            $contacto->tipo_data,
            $contacto->nombre_contacto,
            $contacto->telefono,
            $contacto->correo,
            $contacto->comentario,
            ...array_map(
                static fn (string $operador): int => (int) $contacto->{Operadores::columna($operador)},
                Operadores::TODOS,
            ),
        ];
    }
}
