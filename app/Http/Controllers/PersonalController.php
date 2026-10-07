<?php

namespace App\Http\Controllers;

use App\Http\Requests\Personal\UpdatePersonalRequest;
use App\Models\Usuario;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

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
        $usuario->personal = Str::upper($request->string('personal')->toString());

        // Missing optional fields clear their existing values.
        $usuario->fecha_nacimiento = $request->date('fecha_nacimiento');
        $usuario->telefono = $request->filled('telefono') ? $request->string('telefono')->toString() : null;
        $usuario->correo = $request->filled('correo') ? $request->string('correo')->toString() : null;

        $usuario->save();

        return redirect()
            ->route('equipo.show', $usuario)
            ->with('status', 'Datos personales actualizados.');
    }
}
