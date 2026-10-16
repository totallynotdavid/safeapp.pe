<?php

namespace App\Cutover;

use Illuminate\Support\Facades\DB;

/**
 * The caller flushes at the end of each source chunk, in dependency order: a
 * hold cannot be inserted before the record it points at. Only the ceiling is
 * decided here, because it is a database limit.
 */
final class Lote
{
    // A prepared statement takes 65,535 placeholders, so a wide table fits
    // fewer rows than a narrow one.
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
