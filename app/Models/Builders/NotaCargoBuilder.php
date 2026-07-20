<?php

namespace App\Models\Builders;

use Illuminate\Database\Eloquent\Builder;

/**
 * How a nota de cargo queue is windowed to a period, which every screen over
 * every producto does the same way.
 *
 * The rule has two halves. A case still in flight is bounded by year alone, so
 * it stays on the queue however old it is. A case that closed shows in the month
 * it closed, dated by whichever column records that closing: móvil dates
 * ACTIVADO by fecha_actualizacion, fija dates COMPLETADO by fecha_liquidacion.
 * With no estado chosen the list is the union, which legacy spelled out as a SQL
 * UNION per screen.
 *
 * Legacy re-derived this per screen and got it wrong in two places. The móvil
 * and fija registro queues bounded in-flight cases with `year(fecha) = $ano`
 * rather than `<=`, so an agent looking at the current year could not see the
 * still-open cases they filed in an earlier one: 1,044 of the 5,970 fija rows.
 * The fija back and registro screens also disagreed about which column dates a
 * CANCELADO case, so the same case fell in two different months depending on who
 * was looking. One owner here, one answer.
 *
 * @template TModel of \Illuminate\Database\Eloquent\Model
 *
 * @extends Builder<TModel>
 */
abstract class NotaCargoBuilder extends Builder
{
    public function enPeriodo(int $ano, int $mes, ?string $estado): self
    {
        $cierres = $this->columnasDeCierre();

        if ($estado === null) {
            $enCurso = $this->estadosEnCurso();

            return $this->where(static function (self $grupo) use ($ano, $mes, $cierres, $enCurso): void {
                $grupo->where(static fn (self $q) => $q->enCurso($enCurso, $ano));

                foreach ($cierres as $cerrado => $columna) {
                    $grupo->orWhere(static fn (self $q) => $q->cerradoEn($cerrado, $columna, $ano, $mes));
                }
            });
        }

        $columna = $cierres[$estado] ?? null;
        if ($columna !== null) {
            return $this->cerradoEn($estado, $columna, $ano, $mes);
        }

        return $this->enCurso([$estado], $ano);
    }

    /**
     * Estados that mean the case is still moving, bounded by year only.
     *
     * @return list<string>
     */
    abstract protected function estadosEnCurso(): array;

    /**
     * Each closing estado mapped to the column that records when it closed.
     *
     * @return array<string, string>
     */
    abstract protected function columnasDeCierre(): array;

    /**
     * @param  list<string>  $estados
     */
    protected function enCurso(array $estados, int $ano): self
    {
        return $this->whereIn('estado', $estados)->whereYear('fecha_ingreso', '<=', $ano);
    }

    protected function cerradoEn(string $estado, string $columna, int $ano, int $mes): self
    {
        return $this->where('estado', $estado)->whereYear($columna, $ano)->whereMonth($columna, $mes);
    }
}
