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
}
