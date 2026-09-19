<?php

namespace App\Policies;

use App\Models\NcFija;
use App\Models\Usuario;

// Row-level rules for a nota de cargo fija that depend on who is asking. Entry
// to each screen is gated by its permiso on the route; these cover the detail and
// update endpoints, which must not trust the posted id.
//
// Validación and back have no per-user rule, both being global queues, only a
// per-state one: NcFija::esEditable/esValidado, applied at those controllers
// rather than a policy method that ignores its user.
class NcFijaPolicy
{
    // REGISTRO is the ejecutivo's own queue: a case is theirs to read only if
    // they registered it.
    public function view(Usuario $user, NcFija $ncFija): bool
    {
        return (int) $ncFija->id_usuario === $user->id_usuario;
    }

    // Amending narrows view: an owned case may be changed only while it is still
    // awaiting validación. Once the validador has ruled, the figures they ruled
    // on are frozen, which legacy enforced in JavaScript alone.
    public function update(Usuario $user, NcFija $ncFija): bool
    {
        return $this->view($user, $ncFija) && $ncFija->esEditable();
    }
}
