<?php

namespace App\Models\Concerns;

/**
 * A case that a validador rules on before the back office can fulfil it.
 *
 * The three-way decision and the two state questions asked of it are identical
 * for every producto, and both the validación and back controllers guard on
 * them, so they have one owner here rather than a copy per nota de cargo table.
 * What differs per producto is the `estado` lifecycle the back office then runs,
 * which is why that stays on each model.
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
