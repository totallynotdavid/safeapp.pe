<?php

namespace App\Models\Builders;

use App\Models\NcMovil;

/**
 * @template TModel of NcMovil
 *
 * @extends NotaCargoBuilder<TModel>
 */
class NcMovilBuilder extends NotaCargoBuilder
{
    protected function estadosEnCurso(): array
    {
        return NcMovil::ESTADOS_EN_CURSO;
    }

    /** CAIDA is dated by fecha_ingreso, not by when it fell. */
    protected function columnasDeCierre(): array
    {
        return [
            'ACTIVADO' => 'fecha_actualizacion',
            'CAIDA' => 'fecha_ingreso',
        ];
    }
}
