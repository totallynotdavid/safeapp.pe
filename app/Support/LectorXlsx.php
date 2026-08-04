<?php

namespace App\Support;

use Generator;
use OpenSpout\Reader\XLSX\Reader;
use RuntimeException;

/**
 * Streams the first worksheet of an XLSX import using the same header-keyed
 * row contract as LectorCsv. A campaign template has one worksheet, so making
 * the first sheet explicit prevents silently combining unrelated workbook tabs.
 */
final class LectorXlsx
{
    /** @param list<string> $encabezados */
    private function __construct(
        private readonly string $ruta,
        public readonly array $encabezados,
    ) {}

    public static function abrir(string $ruta): self
    {
        $lector = new Reader;
        $lector->open($ruta);

        try {
            foreach ($lector->getSheetIterator() as $hoja) {
                foreach ($hoja->getRowIterator() as $fila) {
                    return new self($ruta, self::normalizar(self::valores($fila->cells)));
                }
            }
        } finally {
            $lector->close();
        }

        throw new RuntimeException('El archivo está vacío.');
    }

    /**
     * @return Generator<int, array<string, string>>
     */
    public function filas(): Generator
    {
        $lector = new Reader;
        $lector->open($this->ruta);

        try {
            foreach ($lector->getSheetIterator() as $hoja) {
                $linea = 0;

                foreach ($hoja->getRowIterator() as $fila) {
                    $linea++;
                    if ($linea === 1) {
                        continue;
                    }

                    $campos = self::valores($fila->cells);
                    if (array_filter($campos, static fn (string $valor): bool => $valor !== '') === []) {
                        continue;
                    }

                    yield $linea => $this->asociar($campos);
                }

                return;
            }
        } finally {
            $lector->close();
        }
    }

    /**
     * @param iterable<\OpenSpout\Common\Entity\Cell> $celdas
     * @return list<string>
     */
    private static function valores(iterable $celdas): array
    {
        return array_map(static fn ($celda): string => trim((string) ($celda->getValue() ?? '')), [...$celdas]);
    }

    /** @param list<string> $encabezados @return list<string> */
    private static function normalizar(array $encabezados): array
    {
        return array_map(
            static fn (string $nombre): string => mb_strtolower(trim($nombre, " \t\n\r\0\x0B\u{FEFF}")),
            $encabezados,
        );
    }

    /** @param list<string> $campos @return array<string, string> */
    private function asociar(array $campos): array
    {
        $fila = [];

        foreach ($this->encabezados as $indice => $nombre) {
            $fila[$nombre] = $campos[$indice] ?? '';
        }

        return $fila;
    }
}
