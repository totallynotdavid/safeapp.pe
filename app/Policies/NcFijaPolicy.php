<?php

namespace App\Policies;

use App\Models\NcFija;
use App\Models\Usuario;

// Validación and back are global queues with only a per-state rule,
// NcFija::esEditable/esValidado, applied in those controllers.
class NcFijaPolicy
{
    public function view(Usuario $user, NcFija $ncFija): bool
    {
        return (int) $ncFija->id_usuario === $user->id_usuario;
    }

    // Once the validador has ruled, the figures they ruled on are frozen.
    public function update(Usuario $user, NcFija $ncFija): bool
    {
        return $this->view($user, $ncFija) && $ncFija->esEditable();
    }
}
