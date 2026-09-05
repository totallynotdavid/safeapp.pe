<?php

namespace App\Policies;

use App\Models\Quiebre;
use App\Models\Usuario;

class QuiebrePolicy
{
    // Validation is limited to quiebres from the user's assigned tienda.
    public function validar(Usuario $user, Quiebre $quiebre): bool
    {
        $tienda = $user->tiendaAsignada();

        return $tienda !== null && (int) $quiebre->zonal_telefonica === $tienda;
    }

    public function view(Usuario $user, Quiebre $quiebre): bool
    {
        return (int) $quiebre->id_usuario === $user->id_usuario;
    }

    public function update(Usuario $user, Quiebre $quiebre): bool
    {
        return $this->view($user, $quiebre) && $quiebre->esEditable();
    }
}
