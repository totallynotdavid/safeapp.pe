<?php

namespace App\Cockpit;

/**
 * How many lines a customer holds with each carrier, as one observation.
 *
 * Two sources report this and neither owns the other: the campaign file says
 * what the list believed when it was loaded, a logged contact says what the
 * agent confirmed on the phone. Both are kept, so this is the shape they have
 * in common and the only one a screen renders.
 *
 * Legacy wrote the agent's correction back over the file's counts, which meant
 * a reload silently erased it and there was no way to tell which number came
 * from where.
 */
final readonly class MezclaDeLineas
{
    /**
     * @param  array<string, int>  $porOperador  keyed by carrier name
     */
    private function __construct(
        public array $porOperador,
    ) {}

    /**
     * Reads the counts off any row carrying the q_<carrier> columns, which is
     * both `datos` and `llamada`.
     */
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
