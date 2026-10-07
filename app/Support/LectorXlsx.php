<?php

namespace App\Support;

use DateTimeInterface;
use Generator;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Reader\XLSX\Reader;
use RuntimeException;

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
                $filas = $hoja->getRowIterator();
                $filas->rewind();

                if ($filas->valid()) {
                    return new self(
                        $ruta,
                        self::normalizar(
                            self::valores($filas->current()->cells),
                        ),
                    );
                }
            }
        } finally {
            $lector->close();
        }

        throw new RuntimeException('El archivo está vacío.');
    }

    /** @return Generator<int, array<string, string>> */
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

                    if (
                        array_filter(
                            $campos,
                            static fn (string $valor): bool => $valor !== '',
                        ) === []
                    ) {
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
     * @param array<int, Cell> $celdas
     * @return list<string>
     */
    private static function valores(array $celdas): array
    {
        return array_values(array_map(
            static fn (Cell $celda): string => self::texto($celda->getValue()),
            $celdas,
        ));
    }

    // A date-formatted cell arrives as a DateTimeInterface, which has no string
    // form; the import reads dates as Y-m-d.
    private static function texto(mixed $valor): string
    {
        return trim(match (true) {
            is_string($valor) => $valor,
            is_scalar($valor) => (string) $valor,
            $valor instanceof DateTimeInterface => $valor->format('Y-m-d'),
            default => '',
        });
    }

    /**
     * @param list<string> $encabezados
     * @return list<string>
     */
    private static function normalizar(array $encabezados): array
    {
        return array_map(
            static fn (string $nombre): string => mb_strtolower(
                trim($nombre, " \t\n\r\0\x0B\u{FEFF}"),
            ),
            $encabezados,
        );
    }

    /**
     * @param list<string> $campos
     * @return array<string, string>
     */
    private function asociar(array $campos): array
    {
        $fila = [];

        foreach ($this->encabezados as $indice => $nombre) {
            $fila[$nombre] = $campos[$indice] ?? '';
        }

        return $fila;
    }
}
