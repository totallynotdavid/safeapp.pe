<?php

namespace Tests\Feature\Auth;

use App\Models\Menu;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_grants_only_the_abilities_the_user_has_an_acceso_row_for(): void
    {
        $menu = Menu::factory()->create(['permiso' => 'usuarios.manage']);
        $sinAcceso = Usuario::factory()->createOne();
        $usuario = Usuario::factory()->createOne();
        $usuario->menus()->attach($menu);

        $this->assertTrue($usuario->can('usuarios.manage'));
        $this->assertFalse($sinAcceso->can('usuarios.manage'));
    }

    public function test_a_section_header_grant_confers_no_permission(): void
    {
        $header = Menu::factory()->header()->create();
        $usuario = Usuario::factory()->createOne();
        $usuario->menus()->attach($header);

        // A null permiso must not match the empty ability (loose ==).
        $this->assertFalse($usuario->can(''));
    }

    public function test_the_same_grant_cannot_be_recorded_twice(): void
    {
        $menu = Menu::factory()->create(['permiso' => 'usuarios.manage']);
        $usuario = Usuario::factory()->createOne();
        $usuario->menus()->attach($menu);

        // Refusing the second write and dropping it silently both satisfy this.
        // Two rows would not: every acceso count runs high and a revoke leaves
        // one copy standing.
        rescue(static fn () => $usuario->menus()->attach($menu), report: false);

        $this->assertSame(1, $usuario->menus()->count());
    }
}
