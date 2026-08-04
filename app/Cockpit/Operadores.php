<?php

namespace App\Cockpit;

/**
 * The carriers line counts are kept for, and which of them the business trades
 * as. A shared catalog rather than a field on Cliente or Llamada, since both
 * tables carry one line-count column per carrier and need the same answer.
 */
final class Operadores
{
    public const TODOS = ['MOVISTAR', 'ENTEL', 'CLARO', 'BITEL'];

    // The carrier the business trades as. Never encode this in a column name, a
    // screen, or a report: the per-carrier counts are facts that outlive a rebrand.
    public const PROPIO = 'ENTEL';

    /**
     * @return list<string>
     */
    public static function competencia(): array
    {
        return array_values(array_diff(self::TODOS, [self::PROPIO]));
    }

    // Sole owner of the q_<carrier> column naming convention, used by `datos`
    // and `llamada`.
    public static function columna(string $operador): string
    {
        return 'q_'.mb_strtolower($operador);
    }
}
