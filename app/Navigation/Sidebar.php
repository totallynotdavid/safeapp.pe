<?php

namespace App\Navigation;

use App\Models\Usuario;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Legacy menu tables store grants. Navigation structure and presentation live
 * here.
 */
class Sidebar
{
    private const OPERATIONAL = [
        [
            'title' => 'Gestión comercial',
            'items' => [
                [
                    'label' => 'Gestión móvil',
                    'route' => 'gestion.movil',
                    'permission' => 'gestion.movil',
                    'icon' => 'device-phone-mobile',
                ],
                [
                    'label' => 'Gestión fija',
                    'route' => 'gestion.fija',
                    'permission' => 'gestion.fija',
                    'icon' => 'wifi',
                ],
            ],
        ],
        [
            'title' => 'Notas de cargo',
            'items' => [
                [
                    'label' => 'Registro móvil',
                    'route' => 'notas-cargo.movil.index',
                    'permission' => 'notacargo.movil.registrar',
                    'icon' => 'device-phone-mobile',
                    'active' => [
                        'notas-cargo.movil.index',
                        'notas-cargo.movil.create',
                        'notas-cargo.movil.edit',
                    ],
                ],
                [
                    'label' => 'Registro fija',
                    'route' => 'notas-cargo.fija.index',
                    'permission' => 'notacargo.fija.registrar',
                    'icon' => 'wifi',
                    'active' => [
                        'notas-cargo.fija.index',
                        'notas-cargo.fija.create',
                        'notas-cargo.fija.edit',
                    ],
                ],
                [
                    'label' => 'Validación móvil',
                    'route' => 'notas-cargo.movil.validacion.index',
                    'permission' => 'notacargo.movil.validar',
                    'icon' => 'shield-check',
                ],
                [
                    'label' => 'Validación fija',
                    'route' => 'notas-cargo.fija.validacion.index',
                    'permission' => 'notacargo.fija.validar',
                    'icon' => 'shield-check',
                ],
                [
                    'label' => 'Back móvil',
                    'route' => 'notas-cargo.movil.back.index',
                    'permission' => 'notacargo.movil.back',
                    'icon' => 'wrench-screwdriver',
                ],
                [
                    'label' => 'Back fija',
                    'route' => 'notas-cargo.fija.back.index',
                    'permission' => 'notacargo.fija.back',
                    'icon' => 'wrench-screwdriver',
                ],
            ],
        ],
        [
            'title' => 'Control operativo',
            'items' => [
                [
                    'label' => 'Registrar quiebre',
                    'route' => 'quiebres.registro.index',
                    'permission' => 'quiebre.registrar',
                    'icon' => 'exclamation-triangle',
                ],
                [
                    'label' => 'Validar quiebres',
                    'route' => 'quiebres.validacion.index',
                    'permission' => 'quiebre.validar',
                    'icon' => 'check-circle',
                ],
                [
                    'label' => 'Liquidaciones',
                    'route' => 'liquidaciones.index',
                    'permission' => 'liquidacion.gestionar',
                    'icon' => 'banknotes',
                ],
            ],
        ],
        [
            'title' => 'Análisis',
            'items' => [
                [
                    'label' => 'Histórico',
                    'route' => 'historico.index',
                    'permission' => 'historico.ver',
                    'icon' => 'calendar-days',
                ],
                [
                    'label' => 'Seguimiento de equipo',
                    'route' => 'seguimiento.index',
                    'permission' => 'seguimiento.equipo',
                    'icon' => 'user-group',
                ],
                [
                    'label' => 'Producción',
                    'route' => 'produccion.index',
                    'permission' => 'produccion.ver',
                    'icon' => 'presentation-chart-line',
                ],
            ],
        ],
    ];

    private const SETTINGS = [
        [
            'title' => 'Cuenta',
            'items' => [
                [
                    'label' => 'Información personal',
                    'route' => 'configuracion.perfil',
                    'icon' => 'user',
                ],
                [
                    'label' => 'Inicio de sesión y seguridad',
                    'route' => 'configuracion.seguridad',
                    'icon' => 'lock-closed',
                ],
                [
                    'label' => 'Notificaciones',
                    'route' => 'configuracion.notificaciones',
                    'icon' => 'bell',
                ],
            ],
        ],
        [
            'title' => 'Equipo',
            'items' => [
                [
                    'label' => 'Equipo',
                    'route' => 'equipo.index',
                    'permissions' => [
                        'usuarios.manage',
                        'personal.gestionar',
                        'asignacion.gestionar',
                    ],
                    'icon' => 'users',
                ],
                [
                    'label' => 'Roles y permisos',
                    'route' => 'roles.index',
                    'permission' => 'roles.manage',
                    'icon' => 'key',
                ],
            ],
        ],
        [
            'title' => 'Datos',
            'items' => [
                [
                    'label' => 'Importar clientes',
                    'route' => 'clientes.importar',
                    'permission' => 'clientes.importar',
                    'icon' => 'arrow-up-tray',
                ],
            ],
        ],
    ];

    /** @return Collection<int, array{title: string, items: Collection<int, array{label: string, url: string, icon: string, active: bool}>}> */
    public static function forUser(Usuario $user, ?string $currentRoute = null): Collection
    {
        return self::resolve(self::OPERATIONAL, $user, $currentRoute);
    }

    /** @return Collection<int, array{title: string, items: Collection<int, array{label: string, url: string, icon: string, active: bool}>}> */
    public static function settingsForUser(Usuario $user, ?string $currentRoute = null): Collection
    {
        return self::resolve(self::SETTINGS, $user, $currentRoute);
    }

    public static function settingsUrlForUser(Usuario $user): ?string
    {
        return route('configuracion.index');
    }

    /**
     * Authenticated entry goes to the first permitted operational task, or to
     * Configuración when the user has no operational access.
     */
    public static function startUrlForUser(Usuario $user): ?string
    {
        foreach (self::forUser($user) as $section) {
            $primero = $section['items']->first();

            if ($primero !== null) {
                return $primero['url'];
            }
        }

        return self::settingsUrlForUser($user);
    }

    /**
     * @param  list<array{title: string, items: list<array{label: string, route: string, icon: string, permission?: string, permissions?: list<string>, active?: list<string>}>}>  $schema
     * @return Collection<int, array{title: string, items: Collection<int, array{label: string, url: string, icon: string, active: bool}>}>
     */
    private static function resolve(array $schema, Usuario $user, ?string $currentRoute): Collection
    {
        $grants = [];
        foreach ($user->menus as $menu) {
            if ($menu->permiso === null || $menu->permiso === '') {
                continue;
            }

            $grants[$menu->permiso] = true;
        }

        $sections = [];
        foreach ($schema as $section) {
            $items = [];
            foreach ($section['items'] as $item) {
                if (! self::permitido($item, $grants)) {
                    continue;
                }

                $items[] = [
                    'label' => $item['label'],
                    'url' => route($item['route']),
                    'icon' => $item['icon'],
                    'active' =>
                        $currentRoute !== null
                            && Str::is($item['active'] ?? [$item['route'], $item['route'].'.*'], $currentRoute),
                ];
            }

            // A section with nothing the user may open is not shown.
            if ($items !== []) {
                $sections[] = ['title' => $section['title'], 'items' => new Collection($items)];
            }
        }

        return new Collection($sections);
    }

    /**
     * @param  array{permission?: string, permissions?: list<string>, ...<string, mixed>}  $item
     * @param  array<string, true>  $grants
     */
    private static function permitido(array $item, array $grants): bool
    {
        $cualquiera = $item['permissions'] ?? null;

        if ($cualquiera !== null) {
            return array_any($cualquiera, static fn (string $permiso): bool => array_key_exists($permiso, $grants));
        }

        $requerido = $item['permission'] ?? null;

        return $requerido === null || array_key_exists($requerido, $grants);
    }
}
