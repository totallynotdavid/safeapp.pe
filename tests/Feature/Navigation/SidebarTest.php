<?php

namespace Tests\Feature\Navigation;

use App\Models\Menu;
use App\Models\Usuario;
use App\Navigation\Sidebar;
use Database\Seeders\MenuSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The sidebar's invariants: every leaf a user can see links to a screen, every
 * row's icono renders, and exactly one leaf claims the screen being viewed.
 *
 * The first held loosely while the strangle was running, because a leaf whose
 * module still lived in the legacy app rendered as greyed-out text. Nothing
 * lives there now, so the renderer stopped carrying that branch and the
 * invariant is checked here instead.
 */
class SidebarTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_seeded_leaf_links_to_a_route(): void
    {
        $enlaces = $this
            ->menuCompleto()
            ->flatMap(static fn (array $section) => $section['children'])
            ->pluck('url');

        $this->assertGreaterThan(0, $enlaces->count());
        $this->assertCount(0, $enlaces->filter(static fn (?string $url): bool => $url === null || $url === ''));
    }

    public function test_every_seeded_icono_maps_to_an_icon_component(): void
    {
        $iconos = $this
            ->menuCompleto()
            ->flatMap(static fn (array $section) => $section['children']->concat([$section]))
            ->pluck('icono');

        $this->assertGreaterThan(0, $iconos->count());
        $this->assertCount(0, $iconos->reject(static fn (string $icono): bool => str_starts_with($icono, 'heroicon-')));
    }

    public function test_a_leaf_whose_permiso_has_no_route_is_a_bug_and_not_a_blank_link(): void
    {
        $this->expectException(LogicException::class);

        Sidebar::forUser($this->usuarioConHoja(['permiso' => 'modulo.inventado']));
    }

    public function test_a_row_whose_icono_has_no_mapping_is_a_bug_and_not_a_gap(): void
    {
        $this->expectException(LogicException::class);

        Sidebar::forUser($this->usuarioConHoja([
            'permiso' => 'usuarios.manage',
            'icono' => 'fa fa-inventado',
        ]));
    }

    /**
     * @param  list<string>  $esperadas
     */
    #[DataProvider('pantallas')]
    public function test_the_screen_being_viewed_marks_the_leaf_it_belongs_to(
        string $rutaActual,
        array $esperadas,
    ): void {
        $activas = $this
            ->menuCompleto($rutaActual)
            ->flatMap(static fn (array $section) => $section['children'])
            ->filter(static fn (array $leaf): bool => $leaf['activa'])
            ->pluck('descripcion')
            ->all();

        $this->assertSame($esperadas, $activas);
    }

    /**
     * A leaf owns its own inner screens, and the deepest leaf wins. Every
     * notas-cargo móvil route starts with the registro leaf's own route, so a
     * plain prefix match would light up two leaves on the validación screens.
     *
     * @return array<string, array{string, list<string>}>
     */
    public static function pantallas(): array
    {
        return [
            'inner screen' => ['usuarios.edit', ['USUARIO']],
            'sub-queue screen' => ['notas-cargo.movil.validacion.edit', ['VALIDACION MOVIL']],
            'module landing screen' => ['notas-cargo.movil.create', ['MOVIL']],
            'screen under no leaf' => ['dashboard', []],
        ];
    }

    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function menuCompleto(?string $rutaActual = null)
    {
        $this->seed(MenuSeeder::class);

        $user = Usuario::factory()->create();
        // Sections are granted rows too: the sidebar reads the user's acceso, and
        // a leaf whose section header is not granted has nothing to group under.
        $user->menus()->attach(Menu::query()->pluck('id_menu'));

        return Sidebar::forUser($user, $rutaActual);
    }

    /**
     * @param  array<string, mixed>  $hoja
     */
    private function usuarioConHoja(array $hoja): Usuario
    {
        $seccion = Menu::factory()->header()->create();
        $hoja = Menu::factory()->create([...$hoja, 'id_ref' => $seccion->id_menu]);

        $user = Usuario::factory()->create();
        $user->menus()->attach([$seccion->id_menu, $hoja->id_menu]);

        return $user;
    }
}
