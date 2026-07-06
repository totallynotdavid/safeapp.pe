<?php

namespace Tests\Feature\Auth;

use App\Models\Menu;
use App\Models\Usuario;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_grants_an_ability_the_user_has_an_acceso_row_for(): void
    {
        $menu = Menu::factory()->create(['permiso' => 'usuarios.manage']);
        $usuario = Usuario::factory()->create();
        $usuario->menus()->attach($menu);

        $this->assertTrue($usuario->can('usuarios.manage'));
    }

    public function test_denies_an_ability_the_user_has_no_grant_for(): void
    {
        Menu::factory()->create(['permiso' => 'usuarios.manage']);
        $usuario = Usuario::factory()->create();

        $this->assertFalse($usuario->can('usuarios.manage'));
    }

    public function test_a_section_header_grant_confers_no_permission(): void
    {
        $header = Menu::factory()->header()->create();
        $usuario = Usuario::factory()->create();
        $usuario->menus()->attach($header);

        // Regression: a null permiso must not match the empty ability (loose ==).
        $this->assertFalse($usuario->can(''));
    }

    public function test_duplicate_grant_is_rejected_by_the_composite_primary_key(): void
    {
        $menu = Menu::factory()->create(['permiso' => 'usuarios.manage']);
        $usuario = Usuario::factory()->create();
        $usuario->menus()->attach($menu);

        $this->expectException(QueryException::class);
        $usuario->menus()->attach($menu);
    }
}
