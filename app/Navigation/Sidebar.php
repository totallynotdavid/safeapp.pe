<?php

namespace App\Navigation;

use App\Models\Menu;
use App\Models\Usuario;
use Illuminate\Support\Collection;
use LogicException;

// Two-level sidebar: top-level sections (id_ref 0) and the leaves grouped under
// them. Only visible rows appear; menu.estado is inverted, so 0 means visible.
class Sidebar
{
    // permiso -> app route name, one entry per leaf MenuSeeder writes.
    private const ROUTES = [
        'usuarios.manage' => 'usuarios.index',
        'roles.manage' => 'roles.index',
        'quiebre.registrar' => 'quiebres.registro.index',
        'quiebre.validar' => 'quiebres.validacion.index',
        'liquidacion.gestionar' => 'liquidaciones.index',
        'notacargo.movil.registrar' => 'notas-cargo.movil.index',
        'notacargo.movil.validar' => 'notas-cargo.movil.validacion.index',
        'notacargo.movil.back' => 'notas-cargo.movil.back.index',
        'notacargo.fija.registrar' => 'notas-cargo.fija.index',
        'notacargo.fija.validar' => 'notas-cargo.fija.validacion.index',
        'notacargo.fija.back' => 'notas-cargo.fija.back.index',
        'personal.gestionar' => 'personal.index',
        'asignacion.gestionar' => 'asignacion.index',
        'gestion.movil' => 'gestion.movil',
        'gestion.fija' => 'gestion.fija',
        'clientes.crear' => 'clientes.create',
        'clientes.importar' => 'clientes.importar',
        'historico.ver' => 'historico.index',
        'seguimiento.equipo' => 'seguimiento.index',
        'produccion.ver' => 'produccion.index',
    ];

    /**
     * @return Collection<int, array{descripcion: string, icono: string, children: Collection}>
     *
     * @throws LogicException if a visible leaf carries a permiso with no route
     */
    public static function forUser(Usuario $user): Collection
    {
        $byParent = $user
            ->menus()
            ->where('estado', 0)
            ->orderBy('id_menu')
            ->get()
            ->groupBy('id_ref');

        return ($byParent->get(0) ?? collect())
            ->map(static fn (Menu $section): array => [
                'descripcion' => $section->descripcion,
                'icono' => $section->icono,
                'children' => ($byParent->get($section->id_menu) ?? collect())
                    ->map(static fn (Menu $leaf): array => [
                        'descripcion' => $leaf->descripcion,
                        'icono' => $leaf->icono,
                        'url' => self::url($leaf->permiso),
                    ])
                    ->values(),
            ])
            ->filter(static fn (array $section): bool => $section['children']->isNotEmpty())
            ->values();
    }

    /**
     * Every visible leaf links somewhere. A leaf without a mapping used to
     * render as greyed-out text, which is how a module still living in the
     * legacy app appeared while the strangle was running. None does, and
     * MenuSeeder is the only writer of menu rows, so a missing entry is a
     * seeder bug rather than a state to render. SidebarTest owns the invariant;
     * this fails loudly rather than shipping a leaf that goes nowhere.
     */
    private static function url(?string $permiso): string
    {
        $route = self::ROUTES[$permiso] ?? null;

        if ($route === null) {
            throw new LogicException("El menú «{$permiso}» no tiene ruta en Sidebar::ROUTES.");
        }

        return route($route);
    }
}
