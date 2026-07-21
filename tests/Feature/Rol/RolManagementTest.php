<?php

namespace Tests\Feature\Rol;

use App\Models\Menu;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\LeeFormulario;
use Tests\TestCase;

class RolManagementTest extends TestCase
{
    use LeeFormulario;
    use RefreshDatabase;

    // A user holding the roles.manage grant, the gate the whole module hangs off.
    private function manager(): Usuario
    {
        $menu = Menu::factory()->create(['permiso' => 'roles.manage']);
        $manager = Usuario::factory()->create();
        $manager->menus()->attach($menu);

        return $manager;
    }

    /**
     * A section header plus its leaves, mirroring the real two-level tree.
     *
     * @return array{header: Menu, leaves: array<int, Menu>}
     */
    private function section(int $leafCount = 2): array
    {
        $header = Menu::factory()->header()->create(['id_ref' => 0]);
        $leaves = Menu::factory()->count($leafCount)->create(['id_ref' => $header->id_menu]);

        return ['header' => $header, 'leaves' => $leaves->all()];
    }

    public function test_the_manage_permiso_gates_the_index(): void
    {
        $this->actingAs(Usuario::factory()->create())->get(route('roles.index'))->assertForbidden();
        $this->actingAs($this->manager())->get(route('roles.index'))->assertOk();
    }

    public function test_renders_the_create_form_with_the_permiso_tree(): void
    {
        ['leaves' => [$leaf]] = $this->section();

        $this
            ->actingAs($this->manager())
            ->get(route('roles.create'))
            ->assertOk()
            ->assertSee($leaf->descripcion);
    }

    public function test_renders_the_edit_form_with_current_grants_checked(): void
    {
        ['header' => $header, 'leaves' => [$otorgado, $libre]] = $this->section();
        $rol = Rol::factory()->create();
        DB::table('accesoxrol')->insert([
            ['id_rol' => $rol->id_rol, 'id_menu' => $header->id_menu],
            ['id_rol' => $rol->id_rol, 'id_menu' => $otorgado->id_menu],
        ]);

        $respuesta = $this->actingAs($this->manager())->get(route('roles.edit', $rol))->assertOk();

        // Both leaves are offered and only the granted one arrives ticked. The
        // header is granted too but renders as a legend rather than a checkbox,
        // so asking only "is anything ticked" would pass on the wrong box.
        $this->assertEquals(
            [(string) $otorgado->id_menu => true, (string) $libre->id_menu => false],
            $this->casillas($respuesta, 'menus[]'),
        );
    }

    public function test_creating_a_role_grants_selected_leaves_and_their_headers(): void
    {
        ['header' => $header, 'leaves' => [$leaf, $otro]] = $this->section();

        $this
            ->actingAs($this->manager())
            ->post(route('roles.store'), [
                'nombre_rol' => 'operador',
                'menus' => [$leaf->id_menu],
            ])
            ->assertRedirect(route('roles.index'));

        $rol = Rol::query()->where('nombre_rol', 'OPERADOR')->firstOrFail();
        $this->assertEqualsCanonicalizing(
            [$leaf->id_menu, $header->id_menu],
            $rol->menus()->pluck('menu.id_menu')->all(),
            'the chosen leaf and its parent header are granted, the unchosen leaf is not',
        );
    }

    public function test_updating_a_role_renames_and_resyncs_permisos(): void
    {
        ['header' => $header, 'leaves' => [$antes, $despues]] = $this->section();
        $rol = Rol::factory()->create(['nombre_rol' => 'VIEJO']);
        DB::table('accesoxrol')->insert([
            ['id_rol' => $rol->id_rol, 'id_menu' => $header->id_menu],
            ['id_rol' => $rol->id_rol, 'id_menu' => $antes->id_menu],
        ]);

        $this
            ->actingAs($this->manager())
            ->put(route('roles.update', $rol), [
                'nombre_rol' => 'nuevo nombre',
                'menus' => [$despues->id_menu],
            ])
            ->assertRedirect(route('roles.index'));

        $rol->refresh();
        $this->assertSame('NUEVO NOMBRE', $rol->nombre_rol);
        $this->assertEqualsCanonicalizing(
            [$despues->id_menu, $header->id_menu],
            $rol->menus()->pluck('menu.id_menu')->all(),
        );
    }

    public function test_cannot_delete_a_role_with_users_assigned(): void
    {
        $rol = Rol::factory()->create();
        Usuario::factory()->create(['id_rol' => $rol->id_rol]);

        $this
            ->actingAs($this->manager())
            ->delete(route('roles.destroy', $rol))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('rol', ['id_rol' => $rol->id_rol]);
    }

    public function test_deletes_an_empty_role_and_its_grants(): void
    {
        $rol = Rol::factory()->create();
        $menu = Menu::factory()->create();
        DB::table('accesoxrol')->insert(['id_rol' => $rol->id_rol, 'id_menu' => $menu->id_menu]);

        $this
            ->actingAs($this->manager())
            ->delete(route('roles.destroy', $rol))
            ->assertRedirect(route('roles.index'));

        $this->assertDatabaseMissing('rol', ['id_rol' => $rol->id_rol]);
        $this->assertDatabaseMissing('accesoxrol', ['id_rol' => $rol->id_rol]);
    }

    public function test_rejects_a_duplicate_role_name_ignoring_case(): void
    {
        Rol::factory()->create(['nombre_rol' => 'EXISTENTE']);

        $this->actingAs($this->manager())->post(route('roles.store'), [
            'nombre_rol' => 'existente',
            'menus' => [],
        ])->assertSessionHasErrors('nombre_rol');
    }
}
