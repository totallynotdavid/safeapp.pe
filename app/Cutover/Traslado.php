<?php

namespace App\Cutover;

/**
 * One step of the cutover: read a group of legacy tables, write the target
 * tables they became.
 *
 * Grouped by module rather than one implementation per table, because the
 * interesting steps are not one-to-one. `datos` becomes two tables, the five
 * customer-bearing tables become one, and the grants only make sense next to
 * the menus they point at.
 */
interface Traslado
{
    /**
     * Rows written, keyed by target table, plus any deliberate omission worth
     * seeing in the report (a dropped row, a value that could not be resolved).
     * The command prints this verbatim, so the keys are what a reader compares
     * against the legacy counts.
     *
     * @return array<string, int>
     */
    public function ejecutar(): array;
}
