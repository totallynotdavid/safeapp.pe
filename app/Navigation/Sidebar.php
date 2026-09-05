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

    /** @return Collection<int, array{title: string, hidden: bool, items: Collection}> */
    public static function forUser(Usuario $user, ?string $currentRoute = null): Collection
    {
        return self::resolve(self::OPERATIONAL, $user, $currentRoute);
    }

    /** @return Collection<int, array{title: string, hidden: bool, items: Collection}> */
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
        $firstOperationalItem = self::forUser($user)
            ->flatMap(static fn (array $section): Collection => $section['items'])
            ->first();

        return $firstOperationalItem['url'] ?? self::settingsUrlForUser($user);
    }

    /**
     * @param  array<int, array{title: string, hidden?: bool, items: array<int, array<string, mixed>>}>  $schema
     * @return Collection<int, array{title: string, hidden: bool, items: Collection}>
     */
    private static function resolve(array $schema, Usuario $user, ?string $currentRoute): Collection
    {
        $grants = $user
            ->menus
            ->pluck('permiso')
            ->filter()
            ->flip();

        return collect($schema)
            ->map(static function (array $section) use ($grants, $currentRoute): array {
                $items = collect($section['items'])
                    ->filter(static function (array $item) use ($grants): bool {
                        if (array_key_exists('permissions', $item)) {
                            return collect($item['permissions'])
                                ->contains(
                                    static fn (string $permission): bool => $grants->has($permission),
                                );
                        }

                        return ($item['permission'] ?? null) === null || $grants->has($item['permission']);
                    })
                    ->map(static function (array $item) use ($currentRoute): array {
                        $patterns = $item['active'] ?? [
                            $item['route'],
                            $item['route'].'.*',
                        ];

                        return [
                            'label' => $item['label'],
                            'url' => route($item['route']),
                            'icon' => $item['icon'],
                            'active' => $currentRoute !== null && Str::is($patterns, $currentRoute),
                        ];
                    })
                    ->values();

                return [
                    'title' => $section['title'],
                    'hidden' => (bool) ($section['hidden'] ?? false),
                    'items' => $items,
                ];
            })
            ->filter(static fn (array $section): bool => $section['items']->isNotEmpty())
            ->values();
    }
}
