<?php

namespace App\Cockpit;

use App\Models\Cliente;
use App\Support\LectorCsv;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * The bulk door into `datos`: a campaign file of clients, loaded by RUC.
 *
 * AltaDeClientes owns the one-at-a-time door and DistribucionDeClientes owns who
 * holds a client. This owns only the load, and the split is what makes the
 * central rule expressible: FormatoDeImportacion::actualizables() lists every
 * column a load may overwrite, and `id_usuario`, `id_tienda`, `estado` and
 * `fecha_asignacion` are not in it. A load therefore cannot reassign, unassign,
 * or un-work a client, by construction rather than by remembering to.
 *
 * That was not true before. Legacy's CARGAR DATOS button copied a staging table
 * row-for-row into `datos`, ownership columns included, so a load could take
 * clients away from agents who were working them or hand them clients silently.
 * It also re-promoted the entire staging table on every click, ran one SELECT
 * and one write per row with no transaction, and returned only the last
 * statement's result, so a failure halfway through reported success.
 *
 * A file is all or nothing. Every row is checked before anything is written, and
 * one bad row rejects the file. The alternative, loading what parses and skipping
 * the rest, leaves an operator asking which rows are missing and no way to find
 * out; rejecting means they fix the source and upload again, which is safe
 * because the load is an upsert keyed on a unique `ruc`.
 */
final class ImportacionDeClientes
{
    private const POR_LOTE = 500;

    // Enough for an operator to see the shape of what is wrong without rendering
    // a page per row of a broken file.
    private const MAX_ERRORES = 50;

    /**
     * Reads the file twice on purpose: once to check, once to load. Holding every
     * parsed row in memory to avoid the second read would scale with the file,
     * and rejecting the file is only meaningful if nothing was written before the
     * last row was checked.
     */
    public static function desdeArchivo(string $ruta): ResultadoImportacion
    {
        $errores = self::errores($ruta);

        return $errores === []
            ? self::cargar($ruta)
            : ResultadoImportacion::rechazada($errores);
    }

    /**
     * @return list<string>
     */
    private static function errores(string $ruta): array
    {
        $lector = LectorCsv::abrir($ruta);

        $faltantes = array_diff(FormatoDeImportacion::obligatorias(), $lector->encabezados);
        if ($faltantes !== []) {
            return ['Faltan columnas obligatorias en el encabezado: '.implode(', ', $faltantes).'.'];
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
     * @param  array<string, int>  $vistos  line each RUC was last seen on
     * @return list<string>
     */
    private static function erroresDeFila(array $fila, array $vistos): array
    {
        $errores = Validator::make($fila, FormatoDeImportacion::reglas())->errors()->all();

        // A RUC repeated inside one file is not caught by the unique index: the
        // upsert would keep whichever copy came last and say nothing.
        $repetido = $vistos[$fila['ruc']] ?? null;
        if ($repetido !== null) {
            $errores[] = "el RUC {$fila['ruc']} ya aparece en la línea {$repetido}.";
        }

        return $errores;
    }

    private static function cargar(string $ruta): ResultadoImportacion
    {
        $nuevos = 0;
        $actualizados = 0;

        DB::transaction(static function () use ($ruta, &$nuevos, &$actualizados): void {
            foreach (self::lotes($ruta) as $lote) {
                $existentes = Cliente::query()->whereIn('ruc', array_column($lote, 'ruc'))->count();

                $nuevos += count($lote) - $existentes;
                $actualizados += $existentes;

                Cliente::query()->upsert($lote, ['ruc'], FormatoDeImportacion::actualizables());
            }
        });

        return ResultadoImportacion::cargada($nuevos, $actualizados);
    }

    /**
     * @return iterable<int, list<array<string, mixed>>>
     */
    private static function lotes(string $ruta): iterable
    {
        $lote = [];

        foreach (LectorCsv::abrir($ruta)->filas() as $fila) {
            $lote[] = FormatoDeImportacion::valores($fila);

            if (count($lote) === self::POR_LOTE) {
                yield $lote;
                $lote = [];
            }
        }

        if ($lote !== []) {
            yield $lote;
        }
    }
}
