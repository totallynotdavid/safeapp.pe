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

    public static function columna(string $operador): string
    {
        return 'q_'.mb_strtolower($operador);
    }

    /**
     * @return array<string, list<string>>
     */
    public static function reglas(): array
    {
        $reglas = [];

        foreach (self::TODOS as $operador) {
            $reglas[self::columna($operador)] = ['nullable', 'integer', 'min:0', 'max:100000'];
        }

        return $reglas;
    }

    /**
     * An omitted input reads as zero rather than null.
     *
     * @param  callable(string, int): mixed  $input  reads a request input by key with a default
     * @return array<string, int>
     */
    public static function conteos(callable $input): array
    {
        $conteos = [];

        foreach (self::TODOS as $operador) {
            $columna = self::columna($operador);
            $conteos[$columna] = (int) $input($columna, 0);
        }

        return $conteos;
    }
}
