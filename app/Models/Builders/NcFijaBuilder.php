<?php

namespace App\Models\Builders;

use App\Models\NcFija;

/**
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

    /** CANCELADO is dated by fecha_actualizacion, which is when the back office cancelled it. */
    protected function columnasDeCierre(): array
    {
        return [
            'COMPLETADO' => 'fecha_liquidacion',
            'CANCELADO' => 'fecha_actualizacion',
        ];
    }
}
