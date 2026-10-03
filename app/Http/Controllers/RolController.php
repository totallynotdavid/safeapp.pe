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
        $data = $request->validated();

        DB::transaction(function () use ($data): void {
            $rol = Rol::create(['nombre_rol' => $data['nombre_rol']]);
            $rol->menus()->sync($this->grantSet($data['menus'] ?? []));
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
        $data = $request->validated();

        DB::transaction(function () use ($data, $rol): void {
            $rol->update(['nombre_rol' => $data['nombre_rol']]);
            $rol->menus()->sync($this->grantSet($data['menus'] ?? []));
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

        DB::transaction(static function () use ($rol): void {
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
     * @param  list<int|string>  $leafIds
     * @return list<int>
     */
    private function grantSet(array $leafIds): array
    {
        $leafIds = array_map('intval', $leafIds);

        $headerIds = Menu::query()
            ->whereIn('id_menu', $leafIds)
            ->pluck('id_ref')
            ->filter(static fn (int $id): bool => $id !== 0)
            ->all();

        return array_values(array_unique([...$leafIds, ...$headerIds]));
    }
}
