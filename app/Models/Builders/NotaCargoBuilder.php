<?php

namespace App\Models\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 *
 * @extends Builder<TModel>
 */
abstract class NotaCargoBuilder extends Builder
{
    public function enPeriodo(int $ano, int $mes, ?string $estado): static
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
     * Returns ids rather than names: joining `usuario` here would collide with
     * its own `estado` column.
     *
     * @return Collection<int, object{id_usuario: int, estado: string, total: float}>
     */
    public function totalPorEstado(): Collection
    {
        /** @var Collection<int, object{id_usuario: int, estado: string, total: float}> */
        return $this
            ->toBase()
            ->selectRaw('id_usuario, estado, SUM(cargo_fijo) as total')
            ->groupBy('id_usuario', 'estado')
            ->get();
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
    protected function enCurso(array $estados, int $ano): static
    {
        return $this->whereIn('estado', $estados)->whereYear('fecha_ingreso', '<=', $ano);
    }

    protected function cerradoEn(string $estado, string $columna, int $ano, int $mes): static
    {
        return $this->where('estado', $estado)->whereYear($columna, $ano)->whereMonth($columna, $mes);
    }
}
