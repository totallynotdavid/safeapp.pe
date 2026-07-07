<?php

namespace App\Policies;

use App\Models\Quiebre;
use App\Models\Usuario;

class QuiebrePolicy
{
    // Entry to the module is gated by the `quiebre.validar` permiso on the
    // route. This owns the row-level rule permiso can't express: a validator
    // may only act on quiebres in their own assigned tienda. Legacy scoped only
    // the list and left the detail/update endpoints open to any id; this closes
    // that. Gate::before returns null for "validar" (no menu carries it as a
    // permiso), so this method is consulted.
    public function validar(Usuario $user, Quiebre $quiebre): bool
    {
        $tienda = $user->tiendaAsignada();

        return $tienda !== null && (int) $quiebre->zonal_telefonica === $tienda;
    }

    // REGISTRO is the ejecutivo's own queue: a case is theirs to read only if
    // they registered it. Legacy scoped only the list; the detail/update
    // endpoints trusted the posted id, so any user could load or edit any case.
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
