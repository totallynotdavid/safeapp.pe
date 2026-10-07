<?php

namespace App\Cockpit;

use Illuminate\Database\Eloquent\Model;

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
        // A model keeps its columns in attributes, not in object properties.
        $columnas = $fila instanceof Model ? $fila->getAttributes() : get_object_vars($fila);
        $counts = [];

        foreach (Operadores::TODOS as $operador) {
            $counts[$operador] = intval($columnas[Operadores::columna($operador)] ?? 0);
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
