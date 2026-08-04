<?php

namespace App\Cockpit;

use App\Models\Cliente;
use App\Models\Dato;
use App\Support\LectorCsv;
use App\Support\LectorXlsx;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Imports campaign files into clientes and datos without changing assignment or
 * progress state.
 *
 * The file is validated completely before anything is written. A failed import
 * can therefore be corrected and retried safely as an idempotent upsert.
 */
final class ImportacionDeClientes
{
    private const POR_LOTE = 500;

    private const MAX_ERRORES = 50;

    /**
     * The file is read once for validation and again for loading so imports stay
     * all-or-nothing without keeping every row in memory.
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

        return $errores;
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
                    $campana = self::registrosDeCampana($lote);
                    $repetidos = self::yaCargados($campana);

                    $nuevos += count($campana) - $repetidos;
                    $actualizados += $repetidos;

                    // Excluding estado prevents a reload from resetting progress.
                    Dato::query()->upsert(
                        $campana,
                        ['id_cliente', 'origen', 'tipo_data'],
                        [
                            'zonal',
                            'nodo',
                            ...array_map(
                                Operadores::columna(...),
                                Operadores::TODOS,
                            ),
                        ],
                    );
                }
            },
        );

        return ResultadoImportacion::cargada($nuevos, $actualizados);
    }

    /**
     * Counts records that already exist under the full campaign key.
     *
     * @param  list<array<string, mixed>>  $campana
     */
    private static function yaCargados(array $campana): int
    {
        $clave = static fn (array $registro): string => implode("\0", [
            $registro['id_cliente'],
            $registro['origen'],
            $registro['tipo_data'],
        ]);

        $existentes = Dato::query()
            ->whereIn('id_cliente', array_column($campana, 'id_cliente'))
            ->get(['id_cliente', 'origen', 'tipo_data'])
            ->map(
                static fn (Dato $dato): string => $clave($dato->toArray()),
            )
            ->flip();

        return count(array_filter(
            $campana,
            static fn (array $registro): bool => $existentes->has(
                $clave($registro),
            ),
        ));
    }

    /**
     * Resolves a batch into campaign records, creating or updating its clients
     * first and then loading their identifiers in one query.
     *
     * @param  list<array<string, string>>  $lote
     * @return list<array<string, mixed>>
     */
    private static function registrosDeCampana(array $lote): array
    {
        // The import file is authoritative for razon_social.
        Cliente::query()->upsert(
            array_map(FormatoDeImportacion::valoresIdentidad(...), $lote),
            ['ruc'],
            ['razon_social'],
        );

        $ids = Cliente::query()
            ->whereIn('ruc', array_column($lote, 'ruc'))
            ->pluck('id_cliente', 'ruc');

        return array_map(
            static fn (array $fila): array => FormatoDeImportacion::valoresCampana(
                $fila,
                $ids[$fila['ruc']],
            ),
            $lote,
        );
    }

    /**
     * @return iterable<int, list<array<string, string>>>
     */
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
