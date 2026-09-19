<?php

namespace App\Cockpit;

final class Operadores
{
    public const TODOS = ['MOVISTAR', 'ENTEL', 'CLARO', 'BITEL'];

    // The carrier the business trades as. Per-carrier counts are facts and
    // must not depend on branding.
    public const PROPIO = 'ENTEL';

    /** @return list<string> */
    public static function competencia(): array
    {
        return array_values(array_diff(self::TODOS, [self::PROPIO]));
    }

    // Sole owner of the q_<carrier> naming convention.
    public static function columna(string $operador): string
    {
        return 'q_'.mb_strtolower($operador);
    }
}
