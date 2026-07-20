<?php

namespace App\Policies;

use App\Models\NcMovil;
use App\Models\Usuario;

// Row-level rules for a nota de cargo móvil that depend on who is asking. Entry
// to each screen is gated by its permiso on the route. Legacy scoped only the
// lists and left every detail and update endpoint trusting the posted id, so any
// user could open or overwrite any case.
//
// The validación and back screens have no per-user rule (both are global
// queues), only a per-state one; that guard is NcMovil::esEditable/esValidado
// applied at those controllers rather than a policy method that ignores its
// user.
class NcMovilPolicy
{
    // REGISTRO is the ejecutivo's own queue: a case is theirs to read only if
    // they registered it.
    public function view(Usuario $user, NcMovil $ncMovil): bool
    {
        return (int) $ncMovil->id_usuario === $user->id_usuario;
    }

    // Amending narrows view: an owned case may be changed only while it is still
    // awaiting validación. Once the validador has ruled, the figures they ruled
    // on are frozen, which legacy enforced in JavaScript alone.
    public function update(Usuario $user, NcMovil $ncMovil): bool
    {
        return $this->view($user, $ncMovil) && $ncMovil->esEditable();
    }
}
