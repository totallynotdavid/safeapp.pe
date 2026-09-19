<?php

namespace App\Exports;

use App\Models\NcMovil;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Shared by all four móvil screens, registro through liquidación, so every
 * export carries the settlement columns: any screen can show a case that is
 * already settled.
 *
 * Unlike fija, móvil keeps the cartera column: `id_cartera` is set on 576 of the
 * 2,006 production rows, so EJECUTIVO TDP is a real value here.
 */
final class ReporteNotaCargoMovil
{
    private const ENCABEZADOS = [
        'PERSONAL',
        'SUPERVISOR',
        'FECHA INGRESO',
        'FECHA VALIDACION',
        'FECHA ACTUALIZACION',
        'FECHA ESTIMADA',
        'FECHA LIQUIDACION',
        'RUC',
        'RAZON SOCIAL',
        'MODALIDAD',
        'TIPO PRODUCTO',
        'Q LINEAS',
        'CARGO FIJO',
        'REGION',
        'NODO',
        'ETAPA',
        'OPORTUNIDAD',
        'NIVEL NEGOCIACION',
        'CONTACTO',
        'TELEFONO 1',
        'TELEFONO 2',
        'COMENTARIO EJECUTIVO',
        'ESTADO',
        'VALIDACION',
        'VALIDADOR',
        'COMENTARIO VALIDADOR',
        'EJECUTIVO TDP',
        'CASO FWA',
        'CICLO',
        'CUENTA FINANCIERA',
        'COMENTARIO BACK',
        'COMISION',
        'DESCUENTO',
        'OBSERVACION',
        'COMENTARIO LIQUIDADOR',
    ];

    public static function descargar(string $nombre, Builder $consulta): StreamedResponse
    {
        return Reporte::descargar(
            $nombre,
            self::ENCABEZADOS,
            $consulta->with('cliente', 'ejecutivo', 'supervisor', 'validador', 'region', 'cartera'),
            self::fila(...),
        );
    }

    /** @return list<null|float|int|string|\DateTimeInterface> */
    private static function fila(NcMovil $cargo): array
    {
        return [
            $cargo->ejecutivo?->personal,
            $cargo->supervisor?->nombre,
            $cargo->fecha_ingreso,
            $cargo->fecha_validacion,
            $cargo->fecha_actualizacion,
            $cargo->fecha_estimada,
            $cargo->fecha_liquidacion,
            $cargo->cliente?->ruc,
            $cargo->cliente?->razon_social,
            $cargo->modalidad,
            $cargo->tipo_producto,
            $cargo->q_lineas,
            // Cast off the decimal:2 string so the column arrives as a number
            // and sums in the spreadsheet.
            (float) $cargo->cargo_fijo,
            $cargo->region?->nombre,
            $cargo->nodo,
            $cargo->etapa,
            $cargo->oportunidad,
            $cargo->nivel_negociacion,
            $cargo->contacto,
            $cargo->telefono1,
            $cargo->telefono2,
            $cargo->comentario_ejecutivo,
            $cargo->estado,
            $cargo->validacion,
            $cargo->validador?->personal,
            $cargo->comentario_validador,
            $cargo->cartera?->nombre,
            $cargo->casofwa,
            $cargo->ciclo,
            $cargo->cuenta_financiera,
            $cargo->comentario_back,
            $cargo->comision,
            $cargo->descuento,
            $cargo->observacion,
            $cargo->comentario_liquidador,
        ];
    }
}
