<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class EquipoController extends Controller
{
    private const PERMISSIONS = [
        'usuarios.manage',
        'personal.gestionar',
        'asignacion.gestionar',
    ];

    public function index(Request $request): View
    {
        $this->authorizeDirectory($request);

        $usuarios = Usuario::query()
            ->with(['rol', 'supervisor', 'asignacionActiva.tienda'])
            ->orderBy('personal')
            ->get();

        return view('equipo.index', ['usuarios' => $usuarios]);
    }

    public function show(Request $request, Usuario $usuario): View
    {
        $this->authorizeDirectory($request);

        return view('equipo.show', [
            'usuario' => $usuario->load([
                'rol',
                'supervisor',
                'asignacionActiva.tienda',
                'asignaciones.tienda',
            ]),
        ]);
    }

    private function authorizeDirectory(Request $request): void
    {
        abort_unless(
            array_any(
                self::PERMISSIONS,
                fn (string $permission): bool => $request->user()->hasPermiso($permission),
            ),
            403,
        );
    }
}
