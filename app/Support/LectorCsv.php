<?php

namespace App\Support;

use Generator;
use RuntimeException;

/**
 * Reads a CSV as a stream of header-keyed rows, tolerating what Excel actually
 * produces.
 *
 * Two Excel quirks are handled here rather than in every caller:
 *
 * - Excel on a Spanish locale writes semicolon-separated files, English writes
 *   commas, and neither announces which. The delimiter is taken from whichever
 *   character appears more often in the header line.
 * - Excel prefixes a UTF-8 BOM, which would otherwise become part of the first
 *   header name and make that column silently unfindable.
 *
 * Rows are yielded one at a time so a large file never has to be held whole.
 */
final class LectorCsv
{
    private const DELIMITADORES = [';', ','];

    /**
     * @param  resource  $manejador
     * @param  list<string>  $encabezados
     */
    private function __construct(
        private $manejador,
        private readonly string $delimitador,
        public readonly array $encabezados,
    ) {}

    public static function abrir(string $ruta): self
    {
        $manejador = fopen($ruta, 'r');
        if ($manejador === false) {
            throw new RuntimeException("No se pudo abrir el archivo: {$ruta}");
        }

        $primera = fgets($manejador);
        if ($primera === false) {
            fclose($manejador);

            throw new RuntimeException('El archivo está vacío.');
        }

        // The delimiter has to be known before the header can be parsed, so the
        // first line is read raw, sniffed, and then read again as CSV.
        $delimitador = self::delimitadorDe($primera);
        rewind($manejador);
        $encabezados = fgetcsv($manejador, separator: $delimitador, escape: '');

        return new self($manejador, $delimitador, self::normalizar($encabezados === false ? [] : $encabezados));
    }

    /**
     * Header-keyed rows, keyed by their 1-based line number in the file so a
     * caller can point an operator at the line to fix.
     *
     * A row with the wrong number of fields is yielded as-is rather than
     * skipped: deciding what to do about it belongs to whoever knows what the
     * columns mean.
     *
     * @return Generator<int, array<string, string>>
     */
    public function filas(): Generator
    {
        $linea = 1;

        while (($campos = fgetcsv($this->manejador, separator: $this->delimitador, escape: '')) !== false) {
            $linea++;

            // fgetcsv yields [null] for a blank line. Trailing blank lines are
            // normal in exported files and are not an operator's mistake.
            if ($campos === [null] || $campos === []) {
                continue;
            }

            yield $linea => $this->asociar($campos);
        }

        fclose($this->manejador);
    }

    private static function delimitadorDe(string $encabezado): string
    {
        $conteos = array_map(
            static fn (string $delimitador): int => substr_count($encabezado, $delimitador),
            self::DELIMITADORES,
        );

        return self::DELIMITADORES[array_search(max($conteos), $conteos, true)];
    }

    /**
     * @param  list<?string>  $encabezados
     * @return list<string>
     */
    private static function normalizar(array $encabezados): array
    {
        return array_map(
            static fn (?string $nombre): string => mb_strtolower(trim((string) $nombre, " \t\n\r\0\x0B\u{FEFF}")),
            $encabezados,
        );
    }

    /**
     * @param  list<?string>  $campos
     * @return array<string, string>
     */
    private function asociar(array $campos): array
    {
        $fila = [];

        foreach ($this->encabezados as $indice => $nombre) {
            $fila[$nombre] = trim((string) ($campos[$indice] ?? ''));
        }

        return $fila;
    }
}
