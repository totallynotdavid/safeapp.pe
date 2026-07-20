<?php

namespace App\Cutover;

/**
 * The value-level rules every traslado repeats, in one place.
 *
 * Legacy declared almost every column `text NOT NULL` and every date
 * `date NOT NULL`, then stored '' and '0000-00-00' to mean "not set". The new
 * schema says what it means with NULL and real widths, so the same four
 * conversions apply everywhere and are worth having one owner rather than
 * thirty inlined ternaries.
 */
final class Saneamiento
{
    /**
     * The largest number of lines a row is allowed to claim.
     *
     * Not a business rule, a junk filter: 78 contacts carry a q_lineas in the
     * hundreds of millions, and the values are Peruvian mobile numbers
     * (900442964, 963334930), so the count field and the phone field were
     * filled in the wrong order. Anything outside 0..100 is not a line count.
     */
    private const MAX_LINEAS = 100;

    /**
     * Trimmed text, or null when legacy's "not set" placeholder is what it
     * holds. Truncates rather than rejects: the over-long values are all
     * free-text fields where the tail is a repeated address or a pasted
     * coordinate pair, never a key. Omit $max for a target column typed `text`.
     */
    public static function texto(mixed $valor, ?int $max = null): ?string
    {
        $texto = Mojibake::reparado(trim((string) $valor));

        if ($texto === '') {
            return null;
        }

        return $max === null ? $texto : mb_substr($texto, 0, $max);
    }

    /**
     * A date, or null for legacy's zero date. Every date column in the export
     * carries '0000-00-00' somewhere, including all 605 rows of
     * usuario.fecha_nacimiento.
     */
    public static function fecha(mixed $valor): ?string
    {
        $fecha = trim((string) $valor);

        if ($fecha === '' || str_starts_with($fecha, '0000-00-00')) {
            return null;
        }

        return $fecha;
    }

    /**
     * A carrier line count. Negatives (13 campaign rows, 46 contacts) and the
     * phone numbers described above both collapse to 0, because the target
     * columns are unsigned and neither value was ever a count.
     */
    public static function conteo(mixed $valor): int
    {
        $conteo = (int) $valor;

        return $conteo < 0 || $conteo > self::MAX_LINEAS ? 0 : $conteo;
    }

    /**
     * A monthly charge. Legacy typed these `double` and the target columns are
     * decimal(10,2), so anything that will not fit is refused rather than
     * rounded: one móvil case carries 996871768, which is a mobile number in
     * the charge field, the same slip that fills q_lineas with phone numbers.
     * Real charges run from 31.90 to 539.90.
     */
    public static function monto(mixed $valor): float
    {
        $monto = (float) $valor;

        return $monto < 0 || $monto >= 100_000_000 ? 0.0 : round($monto, 2);
    }

    /**
     * An id held in one of legacy's text columns, or null when it holds the ''
     * or '0' that mean "none chosen". Used for zonal (a tienda id stored as
     * text) and ejecutivo_tdp (a cartera id stored as text).
     */
    public static function idEnTexto(mixed $valor): ?int
    {
        $texto = trim((string) $valor);

        if ($texto === '' || ! ctype_digit($texto) || (int) $texto === 0) {
            return null;
        }

        return (int) $texto;
    }
}
