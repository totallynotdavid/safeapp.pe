<?php

namespace App\Models\Builders;

use App\Models\NcMovil;

/**
 * The móvil half of the period window. See NotaCargoBuilder for the rule.
 *
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

    /**
     * CAIDA is dated by fecha_ingreso rather than by when it fell, which is what
     * legacy did on every móvil screen consistently. It is kept because the
     * screens agree on it, unlike fija's CANCELADO, and changing it would move
     * 399 production cases between months for no defect being fixed.
     */
    protected function columnasDeCierre(): array
    {
        return [
            'ACTIVADO' => 'fecha_actualizacion',
            'CAIDA' => 'fecha_ingreso',
        ];
    }
}
