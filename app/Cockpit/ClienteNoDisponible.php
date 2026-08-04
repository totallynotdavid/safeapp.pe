<?php

namespace App\Cockpit;

use RuntimeException;

// The caller intentionally never receives a reason.  Saying whether the RUC
// exists would reveal another executive's private operational history.
final class ClienteNoDisponible extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('El cliente no está disponible para esta gestión.');
    }
}
