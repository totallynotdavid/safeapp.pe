<?php

namespace App\Cockpit;

use RuntimeException;

// Never reveal why the client is unavailable. Doing so would expose another
// executive's operational history.
final class ClienteNoDisponible extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('El cliente no está disponible para esta gestión.');
    }
}
