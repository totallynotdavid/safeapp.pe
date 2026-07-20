<?php

namespace App\Models\Builders;

use App\Models\NcFija;

/**
 * The fija half of the period window. See NotaCargoBuilder for the rule.
 *
 * @template TModel of NcFija
 *
 * @extends NotaCargoBuilder<TModel>
 */
class NcFijaBuilder extends NotaCargoBuilder
{
    protected function estadosEnCurso(): array
    {
        return NcFija::ESTADOS_EN_CURSO;
    }

    /**
     * CANCELADO is dated by fecha_actualizacion, which is when the back office
     * cancelled it. Legacy dated it by fecha_ingreso on the registro screen and
     * by fecha_validacion on the back screen, so the same case landed in two
     * different months depending on who opened it, and neither column was when
     * anything was cancelled.
     */
    protected function columnasDeCierre(): array
    {
        return [
            'COMPLETADO' => 'fecha_liquidacion',
            'CANCELADO' => 'fecha_actualizacion',
        ];
    }
}
