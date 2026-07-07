<?php

namespace App\Http\Controllers;

use App\Http\Requests\Personal\UpdatePersonalRequest;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

// Personnel contact data: the "datos personal" flow the usuarios module defers
// birthdate/phone/email to. A separate, lower-privilege surface so it can be
// granted (e.g. to supervisors) without full account administration.
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

        // Upper-case the name the same way the usuarios module does, so it is
        // stored consistently whichever screen writes it.
        // The form is a full contact record: an omitted optional field clears
        // the column rather than leaving a stale value. `nullable` rules drop
        // absent keys from validated(), so coalesce them to null.
        $usuario->personal = Str::upper($data['personal']);
        $usuario->fecha_nacimiento = $data['fecha_nacimiento'] ?? null;
        $usuario->telefono = $data['telefono'] ?? null;
        $usuario->correo = $data['correo'] ?? null;
        $usuario->save();

        return redirect()->route('personal.index')->with('status', 'Datos personales actualizados.');
    }
}
