<?php

namespace App\Http\Controllers;

use App\Http\Requests\Asignacion\UpdateAsignacionRequest;
use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AsignacionController extends Controller
{
    public function index(): View
    {
        $usuarios = Usuario::query()
            ->where('estado', 1)
            ->with('asignacionActiva.tienda')
            ->orderBy('personal')
            ->get();

        return view('asignacion.index', ['usuarios' => $usuarios]);
    }

    public function edit(Usuario $usuario): View
    {
        return view('asignacion.edit', [
            'usuario' => $usuario,
            'tiendas' => Tienda::query()->orderBy('nombre')->get(),
            'asignadas' => $usuario->asignaciones()->pluck('id_tienda')->all(),
            'activa' => $usuario->tiendaAsignada(),
        ]);
    }

    public function update(
        UpdateAsignacionRequest $request,
        Usuario $usuario,
    ): RedirectResponse {
        $data = $request->validated();
        $activa = $data['activa'] ?? null;

        $usuario->sincronizarAsignaciones(
            array_map('intval', $data['tiendas'] ?? []),
            $activa === null ? null : (int) $activa,
        );

        return redirect()
            ->route('equipo.show', $usuario)
            ->with('status', 'Asignación actualizada.');
    }
}
