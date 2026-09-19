<?php

namespace App\Cockpit;

/**
 * Two sources report a customer's lines per carrier and neither owns the other:
 * the campaign file says what the list believed when it was loaded, a logged
 * contact says what the agent confirmed on the phone. Both are kept, and this is
 * the one shape a screen renders.
 *
 * Legacy wrote the agent's correction back over the file's counts, so a reload
 * silently erased it and nothing recorded which number came from where.
 */
final readonly class MezclaDeLineas
{
    /** @param  array<string, int>  $porOperador  keyed by carrier name */
    private function __construct(
        public array $porOperador,
    ) {}

    /** Accepts any row with the q_<carrier> columns: `datos` or `llamada`. */
    public static function observada(object $fila): self
    {
        $counts = [];

        foreach (Operadores::TODOS as $operador) {
            $counts[$operador] = (int) $fila->{Operadores::columna($operador)};
        }

        return new self($counts);
    }

    public function propias(): int
    {
        return $this->porOperador[Operadores::PROPIO];
    }

    public function competencia(): int
    {
        return array_sum(array_intersect_key(
            $this->porOperador,
            array_flip(Operadores::competencia()),
        ));
    }

    public function cantidad(string $operador): int
    {
        return $this->porOperador[$operador] ?? 0;
    }
}
