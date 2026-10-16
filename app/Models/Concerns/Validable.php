<?php

namespace App\Models\Concerns;

/**
 * @property string $validacion
 */
trait Validable
{
    public const VALIDACION_PENDIENTE = 'PENDIENTE';

    public const VALIDACION_PROCEDE = 'PROCEDE';

    public const VALIDACION_RECHAZADO = 'RECHAZADO';

    public const VALIDACIONES = [self::VALIDACION_PENDIENTE, self::VALIDACION_PROCEDE, self::VALIDACION_RECHAZADO];

    public function esEditable(): bool
    {
        return $this->validacion === self::VALIDACION_PENDIENTE;
    }

    public function esValidado(): bool
    {
        return $this->validacion === self::VALIDACION_PROCEDE;
    }
}
