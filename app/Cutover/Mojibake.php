<?php

namespace App\Cutover;

/**
 * Each round of damage is one decode, so the loop runs until the tell-tale
 * characters are gone. It is the only part of the cutover that changes what a
 * value says rather than its shape, which is why it is not in Saneamiento.
 */
final class Mojibake
{
    // Characters that do not occur in correctly written Peruvian business names,
    // so correct text is never touched.
    private const SOSPECHOSOS = '/[ÃÂ]/u';

    private const MAX_VUELTAS = 10;

    /** All-or-nothing: a partly decoded name is no more readable, so an unresolved value is returned as it came. */
    public static function reparado(string $texto): string
    {
        if (! preg_match(self::SOSPECHOSOS, $texto)) {
            return $texto;
        }

        $actual = $texto;

        for ($vuelta = 0; $vuelta < self::MAX_VUELTAS; $vuelta++) {
            $previo = mb_convert_encoding($actual, 'ISO-8859-1', 'UTF-8');

            if ($previo === false || $previo === '' || $previo === $actual || ! mb_check_encoding($previo, 'UTF-8')) {
                return $texto;
            }

            $actual = $previo;

            if (! preg_match(self::SOSPECHOSOS, $actual)) {
                return $actual;
            }
        }

        return $texto;
    }
}
