<?php

namespace App\Cutover;

final class Saneamiento
{
    // A junk filter, not a business rule: a larger value is a phone number typed
    // into the count field.
    private const MAX_LINEAS = 100;

    /** Truncates rather than rejects. Omit $max for a target column typed `text`. */
    public static function texto(mixed $valor, ?int $max = null): ?string
    {
        $texto = Mojibake::reparado(trim((string) $valor));

        if ($texto === '') {
            return null;
        }

        return $max === null ? $texto : mb_substr($texto, 0, $max);
    }

    public static function fecha(mixed $valor): ?string
    {
        $fecha = trim((string) $valor);

        if ($fecha === '' || str_starts_with($fecha, '0000-00-00')) {
            return null;
        }

        return $fecha;
    }

    public static function conteo(mixed $valor): int
    {
        $conteo = (int) $valor;

        return $conteo < 0 || $conteo > self::MAX_LINEAS ? 0 : $conteo;
    }

    /** The target columns are decimal(10,2), so a value that does not fit becomes 0 instead of being rounded. */
    public static function monto(mixed $valor): float
    {
        $monto = floatval($valor);

        return $monto < 0 || $monto >= 100_000_000 ? 0.0 : round($monto, 2);
    }

    /** For ids stored in text columns, where '' or '0' means none chosen. */
    public static function idEnTexto(mixed $valor): ?int
    {
        $texto = trim((string) $valor);

        if ($texto === '' || ! ctype_digit($texto) || (int) $texto === 0) {
            return null;
        }

        return (int) $texto;
    }
}
