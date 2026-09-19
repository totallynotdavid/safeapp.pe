<?php

namespace App\Http;

use Illuminate\Http\Request;

/**
 * The período and estado a nota de cargo queue is filtered to, read once from
 * the request. Four screens filter this way (registro and back, over both
 * productos) and each reads it twice, to list and to export; one owner is what
 * keeps an export from disagreeing with the screen it exports.
 *
 * An estado outside the screen's own vocabulary is dropped rather than rejected.
 * These arrive from a query string anyone can edit, and an unknown estado means
 * "no estado filter", not an error page.
 */
final class FiltroPeriodo
{
    private const ANOS_OFRECIDOS = 5;

    private function __construct(
        public readonly int $ano,
        public readonly int $mes,
        public readonly ?string $estado,
    ) {}

    /**
     * @param  list<string>  $estados  the vocabulary this screen offers
     */
    public static function desde(Request $request, array $estados): self
    {
        $estado = $request->query('estado');

        return new self(
            (int) $request->integer('ano', now()->year),
            (int) $request->integer('mes', now()->month),
            in_array($estado, $estados, true) ? $estado : null,
        );
    }

    /**
     * The years the filter offers, most recent first.
     *
     * @return list<int>
     */
    public function anos(): array
    {
        return range(now()->year, now()->year - self::ANOS_OFRECIDOS);
    }
}
