<?php

namespace App\Cockpit;

use App\Models\Cliente;
use App\Models\Dato;
use App\Support\LectorCsv;
use App\Support\LectorXlsx;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * The bulk door: a campaign file, loaded by RUC into `cliente` and `datos`.
 *
 * AltaDeClientes owns the one-at-a-time door and DistribucionDeDatos owns who
 * holds a record. This owns only the load, and what it can reach is now decided
 * by the schema rather than by a list. A load writes customer identity and
 * campaign facts. Ownership lives on `asignacion_datos` and progress lives in
 * `datos.estado`, neither of which this touches, so a load cannot reassign,
 * unassign or un-work anything. There is no column list to keep correct.
 *
 * That was not true before, twice over. Legacy's CARGAR DATOS button copied a
 * staging table row-for-row into `datos`, ownership columns included, so a load
 * could take clients away from agents who were working them. It also re-promoted
 * the entire staging table on every click, ran one SELECT and one write per row
 * with no transaction, and returned only the last statement's result, so a
 * failure halfway through reported success. The first port fixed that with a
 * hand-maintained list of touchable columns; the schema split removed the need
 * for the list.
 *
 * A file is all or nothing. Every row is checked before anything is written, and
 * one bad row rejects the file. The alternative, loading what parses and skipping
 * the rest, leaves an operator asking which rows are missing and no way to find
 * out; rejecting means they fix the source and upload again, which is safe
 * because the load is an idempotent upsert.
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

    /**
     * @return list<string>
     */
    private static function errores(string $ruta, string $extension): array
    {
        $lector = self::lector($ruta, $extension);

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

    private static function cargar(string $ruta, string $extension): ResultadoImportacion
    {
        $nuevos = 0;
        $actualizados = 0;

        DB::transaction(static function () use ($ruta, $extension, &$nuevos, &$actualizados): void {
            foreach (self::lotes($ruta, $extension) as $lote) {
                $campana = self::registrosDeCampana($lote);

                $repetidos = self::yaCargados($campana);

                $nuevos += count($campana) - $repetidos;
                $actualizados += $repetidos;

                // estado is absent from the update list, so a reload cannot
                // un-work a record an agent has already gestionado. Ownership is
                // absent because it is not on this table at all.
                Dato::query()->upsert(
                    $campana,
                    ['id_cliente', 'origen', 'tipo_data'],
                    ['zonal', 'nodo', ...array_map(Operadores::columna(...), Operadores::TODOS)],
                );
            }
        });

        return ResultadoImportacion::cargada($nuevos, $actualizados);
    }

    /**
     * How many of these campaign records the load will update rather than
     * create.
     *
     * Matched on the full natural key, because one file may carry several
     * campaigns and the same customer may legitimately appear in each.
     *
     * @param  list<array<string, mixed>>  $campana
     */
    private static function yaCargados(array $campana): int
    {
        $clave = static fn (array $r): string => "{$r['id_cliente']}\0{$r['origen']}\0{$r['tipo_data']}";

        $existentes = Dato::query()
            ->whereIn('id_cliente', array_column($campana, 'id_cliente'))
            ->get(['id_cliente', 'origen', 'tipo_data'])
            ->map(static fn (Dato $d): string => $clave($d->toArray()))
            ->flip();

        return count(array_filter(
            $campana,
            static fn (array $r): bool => $existentes->has($clave($r)),
        ));
    }

    /**
     * Resolves one batch of rows to campaign records, creating any customer the
     * file introduces.
     *
     * Identity is upserted first and read back in one query rather than resolved
     * per row: a 200k-row file would otherwise be 200k lookups.
     *
     * @param  list<array<string, string>>  $lote
     * @return list<array<string, mixed>>
     */
    private static function registrosDeCampana(array $lote): array
    {
        // The feed is authoritative for razon_social, which is why this
        // overwrites where Cliente::resolver() does not. A file is a deliberate
        // statement about these customers; a charge note mentioning a RUC in
        // passing is not.
        Cliente::query()->upsert(
            array_map(FormatoDeImportacion::valoresIdentidad(...), $lote),
            ['ruc'],
            ['razon_social'],
        );

        $ids = Cliente::query()
            ->whereIn('ruc', array_column($lote, 'ruc'))
            ->pluck('id_cliente', 'ruc');

        return array_map(
            static fn (array $fila): array => FormatoDeImportacion::valoresCampana($fila, $ids[$fila['ruc']]),
            $lote,
        );
    }

    /**
     * @return iterable<int, list<array<string, string>>>
     */
    private static function lotes(string $ruta, string $extension): iterable
    {
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

    private static function lector(string $ruta, string $extension): LectorCsv|LectorXlsx
    {
        return strtolower($extension) === 'xlsx'
            ? LectorXlsx::abrir($ruta)
            : LectorCsv::abrir($ruta);
    }
}
