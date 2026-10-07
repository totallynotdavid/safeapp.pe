<?php

namespace Tests\Feature\Auth;

use App\Models\Menu;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_grants_an_ability_the_user_has_an_acceso_row_for(): void
    {
        $menu = Menu::factory()->create(['permiso' => 'usuarios.manage']);
        $usuario = Usuario::factory()->createOne();
        $usuario->menus()->attach($menu);

        $this->assertTrue($usuario->can('usuarios.manage'));
    }

    public function test_denies_an_ability_the_user_has_no_grant_for(): void
    {
        Menu::factory()->create(['permiso' => 'usuarios.manage']);
        $usuario = Usuario::factory()->createOne();

        $this->assertFalse($usuario->can('usuarios.manage'));
    }

    public function test_a_section_header_grant_confers_no_permission(): void
    {
        $header = Menu::factory()->header()->create();
        $usuario = Usuario::factory()->createOne();
        $usuario->menus()->attach($header);

        // Regression: a null permiso must not match the empty ability (loose ==).
        $this->assertFalse($usuario->can(''));
    }

    public function test_the_same_grant_cannot_be_recorded_twice(): void
    {
        $menu = Menu::factory()->create(['permiso' => 'usuarios.manage']);
        $usuario = Usuario::factory()->createOne();
        $usuario->menus()->attach($menu);

        // Today the composite primary key refuses the second write, but a
        // duplicate being dropped silently would satisfy this just as well. What
        // must not happen is two rows: the gate would still answer true, while
        // every acceso count runs high and a revoke leaves one copy standing.
        rescue(static fn () => $usuario->menus()->attach($menu), report: false);

        $this->assertSame(1, $usuario->menus()->count());
    }
}
