<?php

namespace App\Http;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * The date range a call-log report is filtered to, read once from the request.
 * Histórico and seguimiento each list and export, so the parsing has four call
 * sites.
 *
 * An unparseable or inverted range falls back rather than erroring. These arrive
 * from a query string anyone can edit, and a report should still render.
 */
final class RangoFechas
{
    private const DIAS_POR_DEFECTO = 7;

    private function __construct(
        public readonly Carbon $desde,
        public readonly Carbon $hasta,
    ) {}

    public static function desde(Request $request): self
    {
        $hoy = Carbon::now();
        $desde = self::fecha($request->query('desde')) ?? Carbon::now()->subDays(self::DIAS_POR_DEFECTO);
        $hasta = self::fecha($request->query('hasta')) ?? $hoy;

        return $desde->greaterThan($hasta) ? new self($hasta, $desde) : new self($desde, $hasta);
    }

    /**
     * The range as whole days, which is what a report over a `fecha` datetime
     * column needs: a contact logged at 16:40 on the last day is inside the
     * range the user asked for.
     *
     * @return array{Carbon, Carbon}
     */
    public function limites(): array
    {
        return [$this->desde->copy()->startOfDay(), $this->hasta->copy()->endOfDay()];
    }

    private static function fecha(mixed $valor): ?Carbon
    {
        if (! is_string($valor) || $valor === '') {
            return null;
        }

        return Carbon::hasFormat($valor, 'Y-m-d') ? Carbon::parse($valor) : null;
    }
}
