<?php

namespace App\Policies;

use App\Models\Usuario;

class UsuarioPolicy
{
    // Gate::before returns null for "disable" because no menu carries it as a
    // permiso, so this method is consulted.
    public function disable(Usuario $actor, Usuario $target): bool
    {
        return $actor->id_usuario !== $target->id_usuario;
    }
}
