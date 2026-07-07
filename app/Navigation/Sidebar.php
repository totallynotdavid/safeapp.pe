<?php

namespace App\Navigation;

use App\Models\Menu;
use App\Models\Usuario;
use Illuminate\Support\Collection;

// Two-level sidebar: top-level sections (id_ref 0) and the leaves grouped under
// them. Only visible rows appear; menu.estado is inverted, so 0 means visible.
class Sidebar
{
    // permiso -> app route name, filled in as modules are ported. A leaf with
    // no mapping links nowhere yet (its module still lives in legacy).
    private const ROUTES = [
        'usuarios.manage' => 'usuarios.index',
        'roles.manage' => 'roles.index',
        'quiebre.validar' => 'quiebres.validacion.index',
    ];

    /**
     * @return Collection<int, array{descripcion: string, icono: string, children: Collection}>
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

    private static function url(?string $permiso): ?string
    {
        $route = self::ROUTES[$permiso] ?? null;

        return $route ? route($route) : null;
    }
}
