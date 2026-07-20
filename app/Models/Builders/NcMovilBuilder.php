<?php

namespace App\Models\Builders;

use App\Models\NcMovil;
use Illuminate\Database\Eloquent\Builder;

/**
 * How a nota de cargo móvil queue is windowed, which is a query concern rather
 * than something a case knows about itself. The registro and back screens both
 * list the same rows over the same window and differ only in whose cases they
 * show, so the window has one owner here instead of a copy in each controller.
 *
 * @template TModel of NcMovil
 *
 * @extends Builder<TModel>
 */
class NcMovilBuilder extends Builder
{
    /**
     * The period window, optionally narrowed to one estado.
     *
     * Each estado is dated by a different column, and that is the only real
     * logic in legacy's if/else chain over the filter dropdown. A case still in
     * flight is bounded by year alone, so it stays on the queue however old it
     * is; a case that closed shows in the month it closed, ACTIVADO by
     * fecha_actualizacion and CAIDA by fecha_ingreso. With no estado chosen the
     * list is the union of the three, which legacy spelled out as a SQL UNION.
     */
    public function enPeriodo(int $ano, int $mes, ?string $estado): self
    {
        if ($estado === null) {
            return $this->where(static function (self $grupo) use ($ano, $mes): void {
                $grupo
                    ->where(static fn (self $q) => $q->enCurso(NcMovil::ESTADOS_EN_CURSO, $ano))
                    ->orWhere(static fn (self $q) => $q->cerradoEn('ACTIVADO', 'fecha_actualizacion', $ano, $mes))
                    ->orWhere(static fn (self $q) => $q->cerradoEn('CAIDA', 'fecha_ingreso', $ano, $mes));
            });
        }

        if ($estado === 'ACTIVADO') {
            return $this->cerradoEn('ACTIVADO', 'fecha_actualizacion', $ano, $mes);
        }

        if ($estado === 'CAIDA') {
            return $this->cerradoEn('CAIDA', 'fecha_ingreso', $ano, $mes);
        }

        return $this->enCurso([$estado], $ano);
    }

    /**
     * @param  list<string>  $estados
     */
    private function enCurso(array $estados, int $ano): self
    {
        return $this->whereIn('estado', $estados)->whereYear('fecha_ingreso', '<=', $ano);
    }

    private function cerradoEn(string $estado, string $columna, int $ano, int $mes): self
    {
        return $this->where('estado', $estado)->whereYear($columna, $ano)->whereMonth($columna, $mes);
    }
}
