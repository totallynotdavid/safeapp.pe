<?php

namespace App\Http\Controllers;

use App\Http\Requests\Usuario\StoreUsuarioRequest;
use App\Http\Requests\Usuario\UpdateUsuarioRequest;
use App\Models\Rol;
use App\Models\Usuario;
use App\Services\Auth\LegacyPasswordHasher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    public function __construct(
        private readonly LegacyPasswordHasher $hasher,
    ) {}

    public function index(): View
    {
        $usuarios = Usuario::query()->with('rol')->orderBy('usuario')->get();

        return view('usuarios.index', ['usuarios' => $usuarios]);
    }

    public function create(): View
    {
        return view('usuarios.create', ['roles' => Rol::query()->orderBy('nombre_rol')->get()]);
    }

    public function store(StoreUsuarioRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $usuario = DB::transaction(function () use ($data): Usuario {
            $usuario = new Usuario([
                'usuario' => $data['usuario'],
                'clave' => $this->hasher->hash($data['clave']),
                'personal' => Str::upper($data['personal']),
                'id_rol' => $data['id_rol'],
                'sexo' => $data['sexo'],
            ]);
            // estado is intentionally guarded (not in $fillable), so set it
            // directly rather than through mass assignment. New users start
            // enabled.
            $usuario->estado = 1;
            $usuario->save();

            // Materialize the role's menu template into per-user grants. acceso
            // is what the permission gate reads; the role is only the template,
            // and the copy is allowed to drift afterwards.
            $menuIds = DB::table('accesoxrol')->where('id_rol', $data['id_rol'])->pluck('id_menu');
            $usuario->menus()->attach($menuIds);

            return $usuario;
        });

        return redirect()->route('equipo.show', $usuario)->with('status', 'Usuario creado.');
    }

    public function edit(Usuario $usuario): View
    {
        return view('usuarios.edit', ['usuario' => $usuario]);
    }

    public function update(UpdateUsuarioRequest $request, Usuario $usuario): RedirectResponse
    {
        $data = $request->validated();

        $usuario->personal = Str::upper($data['personal']);
        if (filled($data['clave'])) {
            $usuario->clave = $this->hasher->hash($data['clave']);
        }
        $usuario->save();

        return redirect()->route('equipo.show', $usuario)->with('status', 'Usuario actualizado.');
    }

    // Enable/disable is the only "removal": a hard delete would orphan the
    // llamada/acceso/asigna rows that reference id_usuario.
    public function toggleEstado(Usuario $usuario): RedirectResponse
    {
        Gate::authorize('disable', $usuario);

        $usuario->estado = $usuario->estado ? 0 : 1;
        $usuario->save();

        return redirect()->route('equipo.show', $usuario)->with('status', 'Estado actualizado.');
    }
}
