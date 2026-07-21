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

    // menu.icono -> Blade Icons component. The column holds the FontAwesome 4
    // classes the pre-rewrite app rendered; the cutover copies them across, so
    // they stay the stored value and are translated here instead. Users
    // navigate this menu by icon after years of it, so each one keeps the same
    // subject rather than being redrawn: fa-bank was a bank building, fa-money
    // was banknotes, fa-area-chart was a chart.
    private const ICONOS = [
        'fa fa-area-chart' => 'heroicon-o-chart-bar',
        'fa fa-bank' => 'heroicon-o-building-library',
        'fa fa-briefcase' => 'heroicon-o-briefcase',
        'fa fa-cogs' => 'heroicon-o-cog-6-tooth',
        'fa fa-database' => 'heroicon-o-circle-stack',
        'fa fa-download' => 'heroicon-o-arrow-down-tray',
        'fa fa-file' => 'heroicon-o-document',
        'fa fa-key' => 'heroicon-o-key',
        'fa fa-mobile' => 'heroicon-o-device-phone-mobile',
        'fa fa-money' => 'heroicon-o-banknotes',
        'fa fa-phone' => 'heroicon-o-phone',
        'fa fa-plus' => 'heroicon-o-plus',
        'fa fa-users' => 'heroicon-o-users',
        'fa fa-wifi' => 'heroicon-o-wifi',
    ];

    /**
     * The menu tree a user may see, with the leaf they are currently on marked.
     *
     * @param  string|null  $rutaActual  current route name, null on an unnamed route
     * @return Collection<int, array{descripcion: string, icono: string, abierta: bool, children: Collection}>
     *
     * @throws LogicException if a visible row carries a permiso or icono with no mapping
     */
    public static function forUser(Usuario $user, ?string $rutaActual = null): Collection
    {
        $activa = self::rutaActiva($rutaActual);

        $byParent = $user
            ->menus()
            ->where('estado', 0)
            ->orderBy('id_menu')
            ->get()
            ->groupBy('id_ref');

        return ($byParent->get(0) ?? collect())
            ->map(static function (Menu $section) use ($byParent, $activa): array {
                $children = ($byParent->get($section->id_menu) ?? collect())
                    ->map(static function (Menu $leaf) use ($activa): array {
                        $ruta = self::ruta($leaf->permiso);

                        return [
                            'descripcion' => $leaf->descripcion,
                            'icono' => self::icono($leaf->icono),
                            'url' => route($ruta),
                            'activa' => $ruta === $activa,
                        ];
                    })
                    ->values();

                return [
                    'descripcion' => $section->descripcion,
                    'icono' => self::icono($section->icono),
                    // Sections start closed, as they did before the rewrite. The
                    // one holding the current screen opens so the user can see
                    // where they are without reopening it on every page load.
                    'abierta' => $children->contains(static fn (array $leaf): bool => $leaf['activa']),
                    'children' => $children,
                ];
            })
            ->filter(static fn (array $section): bool => $section['children']->isNotEmpty())
            ->values();
    }

    /**
     * The leaf route the current screen belongs to, or null when it sits under
     * no leaf (the dashboard, the tienda switcher).
     *
     * A leaf owns its own inner screens, so usuarios.edit has to light up
     * USUARIO. Matching on a plain prefix is not enough:
     * notas-cargo.movil.validacion.edit starts with notas-cargo.movil, which
     * would light up the registro leaf as well. The leaf sharing the most
     * dotted segments wins, and the shorter route breaks a tie, which is the
     * module's own landing screen rather than one of its sub-queues.
     *
     * Two leaves can still tie honestly: gestion.show is the same cockpit
     * whether the client came from the móvil or the fija queue. Declaration
     * order settles those, and either answer is defensible.
     */
    private static function rutaActiva(?string $rutaActual): ?string
    {
        if ($rutaActual === null) {
            return null;
        }

        $ganadora = null;
        $mejor = 0;

        foreach (self::ROUTES as $ruta) {
            $comunes = self::segmentosComunes($ruta, $rutaActual);

            if ($comunes === 0 || $comunes < $mejor) {
                continue;
            }

            if ($comunes === $mejor && strlen($ruta) >= strlen((string) $ganadora)) {
                continue;
            }

            $ganadora = $ruta;
            $mejor = $comunes;
        }

        return $ganadora;
    }

    private static function segmentosComunes(string $ruta, string $rutaActual): int
    {
        $actual = explode('.', $rutaActual);
        $comunes = 0;

        foreach (explode('.', $ruta) as $posicion => $segmento) {
            if (($actual[$posicion] ?? null) !== $segmento) {
                break;
            }

            $comunes++;
        }

        return $comunes;
    }

    /**
     * The route a leaf points at. Every visible leaf links somewhere: one
     * without a mapping used to render as greyed-out text, which is how a
     * module still living in the legacy app appeared while the strangle was
     * running. None does, and
     * MenuSeeder is the only writer of menu rows, so a missing entry is a
     * seeder bug rather than a state to render. SidebarTest owns the invariant;
     * this fails loudly rather than shipping a leaf that goes nowhere.
     */
    private static function ruta(?string $permiso): string
    {
        $ruta = self::ROUTES[$permiso] ?? null;

        if ($ruta === null) {
            throw new LogicException("El menú «{$permiso}» no tiene ruta en Sidebar::ROUTES.");
        }

        return $ruta;
    }

    /**
     * Same rule as ruta(): MenuSeeder is the only writer of menu rows, so an
     * icono with no mapping is a seeder bug. Failing here surfaces it in
     * SidebarTest instead of rendering a section with a hole where the icon is.
     */
    private static function icono(string $icono): string
    {
        $componente = self::ICONOS[$icono] ?? null;

        if ($componente === null) {
            throw new LogicException("El icono «{$icono}» no tiene equivalente en Sidebar::ICONOS.");
        }

        return $componente;
    }
}
