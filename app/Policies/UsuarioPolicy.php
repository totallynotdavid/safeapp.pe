<?php

namespace App\Policies;

use App\Models\Usuario;

class UsuarioPolicy
{
    // Entry to the whole module is gated by the `usuarios.manage` permiso on
    // the route. This policy only owns the row-level rules that permiso can't
    // express: an operator must not disable their own account and lock
    // themselves out. Gate::before returns null for the "disable" ability
    // (no menu carries it as a permiso), so this method is actually consulted.
    public function disable(Usuario $actor, Usuario $target): bool
    {
        return $actor->id_usuario !== $target->id_usuario;
    }
}
