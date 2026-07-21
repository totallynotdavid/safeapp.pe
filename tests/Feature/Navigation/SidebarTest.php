<?php

namespace Tests\Feature\Navigation;

use App\Models\Menu;
use App\Models\Usuario;
use App\Navigation\Sidebar;
use Database\Seeders\MenuSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

/**
 * The sidebar's one invariant: every leaf a user can see links to a screen.
 *
 * It held loosely while the strangle was running, because a leaf whose module
 * still lived in the legacy app rendered as greyed-out text. Nothing lives
 * there now, so the renderer stopped carrying that branch and the invariant is
 * checked here instead.
 */
class SidebarTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_seeded_leaf_links_to_a_route(): void
    {
        $this->seed(MenuSeeder::class);

        $user = Usuario::factory()->create();
        // Sections are granted rows too: the sidebar reads the user's acceso, and
        // a leaf whose section header is not granted has nothing to group under.
        $user->menus()->attach(Menu::query()->pluck('id_menu'));

        $enlaces = Sidebar::forUser($user)
            ->flatMap(static fn (array $section) => $section['children'])
            ->pluck('url');

        $this->assertGreaterThan(0, $enlaces->count());
        $this->assertCount(0, $enlaces->filter(static fn (?string $url): bool => $url === null || $url === ''));
    }

    public function test_a_leaf_whose_permiso_has_no_route_is_a_bug_and_not_a_blank_link(): void
    {
        $seccion = Menu::factory()->create(['id_ref' => 0, 'estado' => 0, 'permiso' => null]);
        $hoja = Menu::factory()->create([
            'id_ref' => $seccion->id_menu,
            'estado' => 0,
            'permiso' => 'modulo.inventado',
        ]);

        $user = Usuario::factory()->create();
        $user->menus()->attach([$seccion->id_menu, $hoja->id_menu]);

        $this->expectException(LogicException::class);

        Sidebar::forUser($user);
    }
}
