<?php

namespace App\Policies;

use App\Models\NcMovil;
use App\Models\Usuario;

// Validación and back are global queues with only a per-state rule,
// NcMovil::esEditable/esValidado, applied in those controllers.
class NcMovilPolicy
{
    public function view(Usuario $user, NcMovil $ncMovil): bool
    {
        return (int) $ncMovil->id_usuario === $user->id_usuario;
    }

    // Once the validador has ruled, the figures they ruled on are frozen.
    public function update(Usuario $user, NcMovil $ncMovil): bool
    {
        return $this->view($user, $ncMovil) && $ncMovil->esEditable();
    }
}
