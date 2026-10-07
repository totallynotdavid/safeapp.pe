<?php

namespace App\Http\Controllers;

use App\Http\Requests\Usuario\StoreUsuarioRequest;
use App\Http\Requests\Usuario\UpdateUsuarioRequest;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class UsuarioController extends Controller
{
    public function index(): View
    {
        $usuarios = Usuario::query()
            ->with('rol')
            ->orderBy('usuario')
            ->get();

        return view('usuarios.index', ['usuarios' => $usuarios]);
    }

    public function create(): View
    {
        $roles = Rol::query()
            ->orderBy('nombre_rol')
            ->get();

        return view('usuarios.create', ['roles' => $roles]);
    }

    public function store(StoreUsuarioRequest $request): RedirectResponse
    {
        $data = $request->datos();

        $usuario = DB::connection()->transaction(static function () use ($data): Usuario {
            $usuario = new Usuario([
                'usuario' => $data['usuario'],
                'clave' => $data['clave'],
                'personal' => Str::upper($data['personal']),
                'id_rol' => $data['id_rol'],
                'sexo' => $data['sexo'],
            ]);

            // estado is guarded, so new users are enabled explicitly.
            $usuario->estado = true;
            $usuario->save();

            // The role provides the initial menu grants; user grants may diverge later.
            $menuIds = DB::table('accesoxrol')
                ->where('id_rol', $data['id_rol'])
                ->pluck('id_menu');

            $usuario->menus()->attach($menuIds);

            return $usuario;
        });

        return redirect()
            ->route('equipo.show', $usuario)
            ->with('status', 'Usuario creado.');
    }

    public function edit(Usuario $usuario): View
    {
        return view('usuarios.edit', ['usuario' => $usuario]);
    }

    public function update(
        UpdateUsuarioRequest $request,
        Usuario $usuario,
    ): RedirectResponse {
        $usuario->personal = Str::upper($request->personal());

        $clave = $request->clave();
        if ($clave !== null) {
            $usuario->clave = $clave;
        }

        $usuario->save();

        return redirect()
            ->route('equipo.show', $usuario)
            ->with('status', 'Usuario actualizado.');
    }

    // Users are disabled instead of deleted because legacy tables reference id_usuario.
    public function toggleEstado(Usuario $usuario): RedirectResponse
    {
        Gate::authorize('disable', $usuario);

        $usuario->estado = ! $usuario->estado;
        $usuario->save();

        return redirect()
            ->route('equipo.show', $usuario)
            ->with('status', 'Estado actualizado.');
    }
}
