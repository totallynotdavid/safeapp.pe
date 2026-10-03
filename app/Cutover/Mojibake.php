<?php

namespace App\Cutover;

/**
 * UTF-8 that was read as Latin-1 and re-encoded, repeatedly. 1,359 values
 * across `datos`, `llamada`, `nc_fija` and `nc_movil` carry it, almost all of
 * them customer names with an ñ: PEÑA is stored as PE followed by sixteen
 * mangled characters, four rounds deep. Each round is one decode, so the loop
 * runs until the tell-tale characters are gone.
 *
 * Kept out of Saneamiento because it repairs damage the data suffered on the way
 * in, and is the only part of the cutover that changes what a value says rather
 * than what shape it has.
 */
final class Mojibake
{
    /**
     * Characters that do not occur in Peruvian business names written
     * correctly, which is what keeps this from touching the other 297,000
     * values in the export.
     */
    private const SOSPECHOSOS = '/[ÃÂ]/u';

    private const MAX_VUELTAS = 10;

    /**
     * All-or-nothing on purpose: a partly decoded name is no more readable than
     * the mangled one and is a worse thing to write, so the 62 values the loop
     * cannot resolve, mostly ü, are carried through exactly as legacy holds
     * them.
     */
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
