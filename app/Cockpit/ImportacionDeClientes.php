<?php

namespace App\Cockpit;

use App\Support\LectorCsv;
use App\Support\LectorXlsx;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * The file is validated completely before anything is written, so a failed
 * import can be corrected and retried as an idempotent upsert.
 */
final class ImportacionDeClientes
{
    private const POR_LOTE = 500;

    private const MAX_ERRORES = 50;

    /**
     * Validation and loading each stream the file, so an import stays
     * all-or-nothing without holding every row in memory.
     */
    public static function desdeArchivo(string $ruta, string $extension): ResultadoImportacion
    {
        try {
            $errores = self::errores($ruta, $extension);
        } catch (\Throwable) {
            return ResultadoImportacion::rechazada([
                'No se pudo leer el archivo. Descarga una plantilla nueva e inténtalo otra vez.',
            ]);
        }

        return $errores === []
            ? self::cargar($ruta, $extension)
            : ResultadoImportacion::rechazada($errores);
    }

    /** @return list<string> */
    private static function errores(string $ruta, string $extension): array
    {
        $lector = self::lector($ruta, $extension);

        $faltantes = array_diff(
            FormatoDeImportacion::obligatorias(),
            $lector->encabezados,
        );

        if ($faltantes !== []) {
            return [
                'Faltan columnas obligatorias en el encabezado: '.implode(', ', $faltantes).'.',
            ];
        }

        $errores = [];
        $vistos = [];

        foreach ($lector->filas() as $linea => $fila) {
            foreach (self::erroresDeFila($fila, $vistos) as $mensaje) {
                $errores[] = "Línea {$linea}: {$mensaje}";
            }

            $vistos[$fila['ruc']] = $linea;

            if (count($errores) >= self::MAX_ERRORES) {
                return array_slice($errores, 0, self::MAX_ERRORES);
            }
        }

        return $errores;
    }

    /**
     * @param  array<string, string>  $fila
     * @param  array<string, int>  $vistos
     * @return list<string>
     */
    private static function erroresDeFila(array $fila, array $vistos): array
    {
        $errores = Validator::make(
            $fila,
            FormatoDeImportacion::reglas(),
        )->errors()->all();

        // The upsert would silently keep the last occurrence of a repeated RUC.
        $lineaAnterior = $vistos[$fila['ruc']] ?? null;

        if ($lineaAnterior !== null) {
            $errores[] = "el RUC {$fila['ruc']} ya aparece en la línea {$lineaAnterior}.";
        }

        return array_values($errores);
    }

    private static function cargar(
        string $ruta,
        string $extension,
    ): ResultadoImportacion {
        $nuevos = 0;
        $actualizados = 0;

        DB::transaction(
            static function () use ($ruta, $extension, &$nuevos, &$actualizados): void {
                foreach (self::lotes($ruta, $extension) as $lote) {
                    $cargado = CargaDeLote::registrar($lote);

                    $nuevos += $cargado['nuevos'];
                    $actualizados += $cargado['actualizados'];
                }
            },
        );

        return ResultadoImportacion::cargada($nuevos, $actualizados);
    }

    /** @return iterable<int, list<array<string, string>>> */
    private static function lotes(
        string $ruta,
        string $extension,
    ): iterable {
        $lote = [];

        foreach (self::lector($ruta, $extension)->filas() as $fila) {
            $lote[] = $fila;

            if (count($lote) === self::POR_LOTE) {
                yield $lote;
                $lote = [];
            }
        }

        if ($lote !== []) {
            yield $lote;
        }
    }

    private static function lector(
        string $ruta,
        string $extension,
    ): LectorCsv|LectorXlsx {
        return strtolower($extension) === 'xlsx'
            ? LectorXlsx::abrir($ruta)
            : LectorCsv::abrir($ruta);
    }
}
