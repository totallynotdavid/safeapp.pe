<?php

namespace App\Models\Concerns;

/**
 * A case a validador rules on before the back office can fulfil it. The
 * three-way decision and the two state questions are identical for every
 * producto, so they have one owner here. The `estado` lifecycle the back office
 * then runs differs per producto and stays on each model.
 *
 * @property string $validacion
 */
trait Validable
{
    public const VALIDACION_PENDIENTE = 'PENDIENTE';

    public const VALIDACION_PROCEDE = 'PROCEDE';

    public const VALIDACION_RECHAZADO = 'RECHAZADO';

    /** The validador's three-way decision; a case starts PENDIENTE. */
    public const VALIDACIONES = [self::VALIDACION_PENDIENTE, self::VALIDACION_PROCEDE, self::VALIDACION_RECHAZADO];

    // Still awaiting a decision, so the ejecutivo may amend it and the validador
    // may rule on it. Both screens ask this before writing.
    public function esEditable(): bool
    {
        return $this->validacion === self::VALIDACION_PENDIENTE;
    }

    // Passed validación, so the back office has something to fulfil. A rejected
    // or undecided case never reaches that stage.
    public function esValidado(): bool
    {
        return $this->validacion === self::VALIDACION_PROCEDE;
    }
}
