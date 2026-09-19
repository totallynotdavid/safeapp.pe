<?php

namespace App\Cutover;

use Illuminate\Support\Facades\DB;

/**
 * Row-at-a-time inserts would make the contact log alone 276,006 round trips.
 *
 * Timing is the caller's: it flushes at the end of each source chunk, in
 * dependency order, because one step writes `datos` and `asignacion_datos`
 * together and a hold cannot be inserted before the record it points at. A
 * buffer that chose its own timing put those two out of order and the foreign
 * key caught it.
 *
 * The one thing decided here is the ceiling, because it is a limit of the
 * database rather than a choice: a prepared statement takes 65,535 placeholders,
 * so a wide table runs out of room at far fewer rows than a narrow one.
 */
final class Lote
{
    private const MAX_PLACEHOLDERS = 65_535;

    /** @var list<array<string, mixed>> */
    private array $pendientes = [];

    private int $escritas = 0;

    private int $maximo = 0;

    public function __construct(
        private readonly string $tabla,
    ) {}

    /** @param  array<string, mixed>  $fila */
    public function agregar(array $fila): void
    {
        if ($this->maximo === 0) {
            $this->maximo = intdiv(self::MAX_PLACEHOLDERS, max(count($fila), 1));
        }

        $this->pendientes[] = $fila;

        if (count($this->pendientes) >= $this->maximo) {
            $this->vaciar();
        }
    }

    /** Called at the end of each source chunk, which bounds the buffer to one chunk of rows. */
    public function vaciar(): void
    {
        if ($this->pendientes === []) {
            return;
        }

        DB::table($this->tabla)->insert($this->pendientes);
        $this->escritas += count($this->pendientes);
        $this->pendientes = [];
    }

    /** A traslado that returns without calling this loses the rows still buffered. */
    public function cerrar(): int
    {
        $this->vaciar();

        return $this->escritas;
    }
}
