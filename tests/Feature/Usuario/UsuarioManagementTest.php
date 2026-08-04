<?php

namespace Tests\Feature\Usuario;

use App\Models\Menu;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UsuarioManagementTest extends TestCase
{
    use RefreshDatabase;

    // A user holding the usuarios.manage grant, the gate the whole module hangs off.
    private function manager(): Usuario
    {
        $menu = Menu::factory()->create(['permiso' => 'usuarios.manage']);
        $manager = Usuario::factory()->create();
        $manager->menus()->attach($menu);

        return $manager;
    }

    public function test_requires_the_manage_permiso(): void
    {
        $sinPermiso = Usuario::factory()->create();

        $this->actingAs($sinPermiso)->get(route('usuarios.index'))->assertForbidden();
    }

    public function test_the_list_names_each_account_and_the_rol_it_answers_to(): void
    {
        $rol = Rol::factory()->create(['nombre_rol' => 'SUPERVISOR']);
        Usuario::factory()->create(['usuario' => 'jperez', 'personal' => 'JUAN PEREZ', 'id_rol' => $rol->id_rol]);

        $this
            ->actingAs($this->manager())
            ->get(route('usuarios.index'))
            ->assertOk()
            ->assertSee('jperez')
            ->assertSee('JUAN PEREZ')
            ->assertSee('SUPERVISOR');
    }

    public function test_the_create_screen_exposes_available_roles(): void
    {
        $rol = Rol::factory()->create(['nombre_rol' => 'OPERADOR']);

        $this
            ->actingAs($this->manager())
            ->get(route('usuarios.create'))
            ->assertOk()
            ->assertSee('OPERADOR');
    }

    public function test_creating_a_user_copies_the_roles_acceso_template(): void
    {
        $rol = Rol::factory()->create();
        $granted = Menu::factory()->count(3)->create();
        DB::table('accesoxrol')->insert(
            $granted->map(static fn (Menu $m): array => ['id_rol' => $rol->id_rol, 'id_menu' => $m->id_menu])->all(),
        );

        $this->actingAs($this->manager())->post(route('usuarios.store'), [
            'usuario' => 'nuevo',
            'clave' => 'secreto1',
            'personal' => 'juan perez',
            'id_rol' => $rol->id_rol,
            'sexo' => 'M',
        ])->assertRedirect(route('usuarios.index'));

        $creado = Usuario::query()->where('usuario', 'nuevo')->firstOrFail();
        $this->assertSame('JUAN PEREZ', $creado->personal, 'personal is upper-cased like the legacy insert');
        $this->assertTrue($creado->estado);
        $this->assertEqualsCanonicalizing(
            $granted->pluck('id_menu')->all(),
            $creado->menus()->pluck('menu.id_menu')->all(),
        );
    }

    public function test_hashes_the_password_on_create(): void
    {
        $rol = Rol::factory()->create();

        $this->actingAs($this->manager())->post(route('usuarios.store'), [
            'usuario' => 'nuevo',
            'clave' => 'secreto1',
            'personal' => 'juan perez',
            'id_rol' => $rol->id_rol,
            'sexo' => 'M',
        ]);

        $creado = Usuario::query()->where('usuario', 'nuevo')->firstOrFail();
        $this->assertNotSame('secreto1', $creado->clave);
        $this->assertTrue(password_verify('secreto1', $creado->clave));
    }

    public function test_rejects_a_duplicate_username(): void
    {
        $rol = Rol::factory()->create();
        Usuario::factory()->create(['usuario' => 'existente']);

        $this->actingAs($this->manager())->post(route('usuarios.store'), [
            'usuario' => 'existente',
            'clave' => 'secreto1',
            'personal' => 'otro',
            'id_rol' => $rol->id_rol,
            'sexo' => 'F',
        ])->assertSessionHasErrors('usuario');
    }

    public function test_a_blank_password_on_edit_keeps_the_current_hash(): void
    {
        $target = Usuario::factory()->clave('original')->create();
        $hashAntes = $target->clave;

        $this->actingAs($this->manager())->put(route('usuarios.update', $target), [
            'personal' => 'nombre nuevo',
            'clave' => '',
        ])->assertRedirect(route('usuarios.index'));

        $target->refresh();
        $this->assertSame($hashAntes, $target->clave);
        $this->assertSame('NOMBRE NUEVO', $target->personal);
    }

    public function test_a_new_password_on_edit_replaces_the_hash(): void
    {
        $target = Usuario::factory()->clave('original')->create();

        $this->actingAs($this->manager())->put(route('usuarios.update', $target), [
            'personal' => $target->personal,
            'clave' => 'reemplazo1',
        ]);

        $target->refresh();
        $this->assertTrue(password_verify('reemplazo1', $target->clave));
    }

    public function test_disabling_another_user_flips_estado(): void
    {
        $target = Usuario::factory()->create(['estado' => 1]);

        $this
            ->actingAs($this->manager())
            ->patch(route('usuarios.estado', $target))
            ->assertRedirect(route('usuarios.index'));

        $this->assertFalse($target->fresh()->estado);
    }

    public function test_cannot_disable_your_own_account(): void
    {
        $manager = $this->manager();

        $this->actingAs($manager)->patch(route('usuarios.estado', $manager))->assertForbidden();

        $this->assertTrue($manager->fresh()->estado);
    }
}
