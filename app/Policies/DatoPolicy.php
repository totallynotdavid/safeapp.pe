<?php

namespace App\Policies;

use App\Http\Controllers\GestionController;
use App\Models\Cliente;
use App\Models\Dato;
use App\Models\Usuario;
use Illuminate\Auth\Access\Response;

class DatoPolicy
{
    // Reading a gestión and recording one are gated by the same rule: the
    // permiso for the dato's tipo, an active assignment to this agent, and a
    // cliente the agent may see.
    public function view(Usuario $user, Dato $dato): Response
    {
        $permiso = GestionController::PERMISOS[$dato->tipo_data] ?? null;

        if ($permiso === null) {
            return Response::denyAsNotFound();
        }

        $permitido =
            $user->can($permiso)
            && $dato->asignacionActiva?->id_usuario === $user->id_usuario
            && Cliente::visiblePara($user)->whereKey($dato->id_cliente)->exists();

        return $permitido ? Response::allow() : Response::deny();
    }
}
