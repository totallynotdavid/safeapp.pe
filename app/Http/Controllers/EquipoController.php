<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;

class EquipoController extends Controller
{
    private const PERMISSIONS = [
        'usuarios.manage',
        'personal.gestionar',
        'asignacion.gestionar',
    ];

    public function index(#[CurrentUser] Usuario $agente): View
    {
        $this->authorizeDirectory($agente);

        $usuarios = Usuario::query()
            ->with(['rol', 'supervisor', 'asignacionActiva.tienda'])
            ->orderBy('personal')
            ->get();

        return view('equipo.index', ['usuarios' => $usuarios]);
    }

    public function show(Usuario $usuario, #[CurrentUser] Usuario $agente): View
    {
        $this->authorizeDirectory($agente);

        return view('equipo.show', [
            'usuario' => $usuario->load([
                'rol',
                'supervisor',
                'asignacionActiva.tienda',
                'asignaciones.tienda',
            ]),
        ]);
    }

    private function authorizeDirectory(Usuario $agente): void
    {
        abort_unless(array_any(self::PERMISSIONS, $agente->hasPermiso(...)), 403);
    }
}
