<?php

namespace App\Cockpit;

/**
 * A customer's lines per carrier have two independent sources: what the campaign
 * file claimed at load, and what an agent confirmed on a call. Both are kept, and
 * this is the one shape a screen renders.
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
