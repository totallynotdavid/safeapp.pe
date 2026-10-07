<?php

namespace App\Http\Controllers;

use App\Http\Requests\Rol\StoreRolRequest;
use App\Http\Requests\Rol\UpdateRolRequest;
use App\Models\Menu;
use App\Models\Rol;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class RolController extends Controller
{
    public function index(): View
    {
        $roles = Rol::query()
            ->withCount(['usuarios', 'menus'])
            ->orderBy('nombre_rol')
            ->get();

        return view('roles.index', ['roles' => $roles]);
    }

    public function create(): View
    {
        return view('roles.create', [
            'menus' => Menu::query()->orderBy('id_menu')->get(),
            'selected' => [],
        ]);
    }

    public function store(StoreRolRequest $request): RedirectResponse
    {
        $nombre = $request->string('nombre_rol')->toString();
        $menus = $this->grantSet($request->enteros('menus'));

        DB::connection()->transaction(static function () use ($nombre, $menus): void {
            $rol = Rol::query()->create(['nombre_rol' => $nombre]);
            $rol->menus()->sync($menus);
        });

        return redirect()->route('roles.index')->with('status', 'Rol creado.');
    }

    public function edit(Rol $rol): View
    {
        return view('roles.edit', [
            'rol' => $rol,
            'menus' => Menu::query()->orderBy('id_menu')->get(),
            'selected' => $rol->menus()->pluck('menu.id_menu')->all(),
        ]);
    }

    public function update(UpdateRolRequest $request, Rol $rol): RedirectResponse
    {
        $nombre = $request->string('nombre_rol')->toString();
        $menus = $this->grantSet($request->enteros('menus'));

        DB::connection()->transaction(static function () use ($nombre, $menus, $rol): void {
            $rol->update(['nombre_rol' => $nombre]);
            $rol->menus()->sync($menus);
        });

        return redirect()->route('roles.index')->with('status', 'Rol actualizado.');
    }

    // A role with users assigned can't be deleted: usuario.id_rol has no
    // cascade and would be left pointing at a missing role. Reassign or disable
    // those users first. Deleting your own role is blocked by the same rule,
    // since your account is one of those users.
    public function destroy(Rol $rol): RedirectResponse
    {
        if ($rol->usuarios()->exists()) {
            return back()->with('error', 'No se puede eliminar un rol con usuarios asignados.');
        }

        DB::connection()->transaction(static function () use ($rol): void {
            $rol->menus()->detach();
            $rol->delete();
        });

        return redirect()->route('roles.index')->with('status', 'Rol eliminado.');
    }

    /**
     * Expand chosen leaf grants to include each leaf's section header. The
     * sidebar only renders a section when the user also holds the header row,
     * so a leaf granted without its parent would never show. Headers carry no
     * permiso, so pulling them in grants no extra ability.
     *
     * @param  list<int>  $leafIds
     * @return list<int>
     */
    private function grantSet(array $leafIds): array
    {
        $headerIds = Menu::query()
            ->whereIn('id_menu', $leafIds)
            ->toBase()
            ->pluck('id_ref')
            ->map(static fn (mixed $id): int => is_numeric($id) ? (int) $id : 0)
            ->filter(static fn (int $id): bool => $id !== 0)
            ->values()
            ->all();

        return array_values(array_unique([...$leafIds, ...$headerIds]));
    }
}
