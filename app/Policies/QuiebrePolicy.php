<?php

namespace App\Policies;

use App\Models\Quiebre;
use App\Models\Usuario;

class QuiebrePolicy
{
    // Row-level rule: a validator may only act on quiebres in their own
    // assigned tienda. This enforces a constraint permiso can't express.
    // Gate::before returns null for "validar", so this method is consulted.
    public function validar(Usuario $user, Quiebre $quiebre): bool
    {
        $tienda = $user->tiendaAsignada();

        return $tienda !== null && (int) $quiebre->zonal_telefonica === $tienda;
    }

    // REGISTRO queue: an ejecutivo may read their own cases only.
    public function view(Usuario $user, Quiebre $quiebre): bool
    {
        return (int) $quiebre->id_usuario === $user->id_usuario;
    }

    // The followup edit narrows view: an owned case may be amended only while it
    // is still in an editable state (a validated case is frozen).
    public function update(Usuario $user, Quiebre $quiebre): bool
    {
        return $this->view($user, $quiebre) && $quiebre->esEditable();
    }
}
