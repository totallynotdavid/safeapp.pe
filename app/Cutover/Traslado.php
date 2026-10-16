<?php

namespace App\Cutover;

interface Traslado
{
    /**
     * Rows written keyed by target table, plus each deliberate omission (a
     * dropped row, an unresolved value). The command prints this verbatim.
     *
     * @return array<string, int>
     */
    public function ejecutar(): array;
}
