<?php

namespace App\Exports;

use App\Models\NcFija;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ReporteNotaCargoFija
{
    private const ENCABEZADOS = [
        'PERSONAL',
        'SUPERVISOR',
        'FECHA INGRESO',
        'FECHA VALIDACION',
        'FECHA ACTUALIZACION',
        'FECHA LIQUIDACION',
        'RUC',
        'RAZON SOCIAL',
        'MODALIDAD',
        'SUBTIPO',
        'DESCRIPCION',
        'CARGO FIJO',
        'TECNOLOGIA',
        'REGION',
        'NODO',
        'DIRECCION',
        'COORDENADAS',
        'CONTACTO',
        'TELEFONO 1',
        'TELEFONO 2',
        'COMENTARIO EJECUTIVO',
        'ESTADO',
        'VALIDACION',
        'VALIDADOR',
        'COMENTARIO VALIDADOR',
        'PETICION',
        'NUMERO ASIGNADO',
        'CONTRATA',
        'CASO SF',
        'COMENTARIO BACK',
    ];

    /** @param  Builder<NcFija>  $consulta */
    public static function descargar(string $nombre, Builder $consulta): StreamedResponse
    {
        return Reporte::descargar(
            $nombre,
            self::ENCABEZADOS,
            $consulta->with('cliente', 'ejecutivo', 'supervisor', 'validador', 'region'),
            self::fila(...),
        );
    }

    /** @return list<null|float|string|\DateTimeInterface> */
    private static function fila(NcFija $cargo): array
    {
        return [
            ...self::seguimiento($cargo),
            ...self::servicio($cargo),
            ...self::contactoYValidacion($cargo),
            ...self::back($cargo),
        ];
    }

    /** @return list<null|string|\DateTimeInterface> */
    private static function seguimiento(NcFija $cargo): array
    {
        return [
            $cargo->ejecutivo?->personal,
            $cargo->supervisor?->nombre,
            $cargo->fecha_ingreso,
            $cargo->fecha_validacion,
            $cargo->fecha_actualizacion,
            $cargo->fecha_liquidacion,
        ];
    }

    /** @return list<null|float|string> */
    private static function servicio(NcFija $cargo): array
    {
        return [
            $cargo->cliente?->ruc,
            $cargo->cliente?->razon_social,
            $cargo->modalidad,
            $cargo->subtipo,
            $cargo->descripcion,
            // Cast off the decimal:2 string so the column arrives as a number
            // and sums in the spreadsheet.
            floatval($cargo->cargo_fijo),
            $cargo->tecnologia,
            $cargo->region?->nombre,
            $cargo->nodo,
            $cargo->direccion,
            $cargo->coordenadas,
        ];
    }

    /** @return list<null|string> */
    private static function contactoYValidacion(NcFija $cargo): array
    {
        return [
            $cargo->contacto,
            $cargo->telefono1,
            $cargo->telefono2,
            $cargo->comentario,
            $cargo->estado,
            $cargo->validacion,
            $cargo->validador?->personal,
            $cargo->comentario_validador,
        ];
    }

    /** @return list<null|string> */
    private static function back(NcFija $cargo): array
    {
        return [
            $cargo->peticion,
            $cargo->nasignado,
            $cargo->contrata,
            $cargo->caso_sf,
            $cargo->comentario_back,
        ];
    }
}
