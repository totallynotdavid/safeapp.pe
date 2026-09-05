<?php

namespace App\Http\Controllers;

use App\Http\Requests\Personal\UpdatePersonalRequest;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PersonalController extends Controller
{
    public function index(): View
    {
        $usuarios = Usuario::query()
            ->where('estado', 1)
            ->with(['rol', 'supervisor'])
            ->orderBy('personal')
            ->get();

        return view('personal.index', ['usuarios' => $usuarios]);
    }

    public function edit(Usuario $usuario): View
    {
        return view('personal.edit', ['usuario' => $usuario]);
    }

    public function update(UpdatePersonalRequest $request, Usuario $usuario): RedirectResponse
    {
        $data = $request->validated();

        $usuario->personal = Str::upper($data['personal']);

        // Missing optional fields clear their existing values.
        $usuario->fecha_nacimiento = $data['fecha_nacimiento'] ?? null;
        $usuario->telefono = $data['telefono'] ?? null;
        $usuario->correo = $data['correo'] ?? null;

        $usuario->save();

        return redirect()
            ->route('equipo.show', $usuario)
            ->with('status', 'Datos personales actualizados.');
    }
}
