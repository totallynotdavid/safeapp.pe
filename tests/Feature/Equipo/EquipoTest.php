<?php

namespace Tests\Feature\Equipo;

use App\Models\AsignacionTienda;
use App\Models\Menu;
use App\Models\Rol;
use App\Models\Tienda;
use App\Models\Usuario;
use Dom\Element;
use Dom\HTMLDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EquipoTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_with_none_of_the_team_permisos_is_refused(): void
    {
        $persona = Usuario::factory()->createOne();

        $this->actingAs(Usuario::factory()->create())->get(route('equipo.index'))->assertForbidden();
        $this->actingAs(Usuario::factory()->create())->get(route('equipo.show', $persona))->assertForbidden();
    }

    public function test_each_permiso_adds_its_own_columns_to_the_directory(): void
    {
        $rol = Rol::factory()->create(['nombre_rol' => 'SUPERVISOR']);
        $tienda = Tienda::factory()->create(['nombre' => 'LIMA CENTRO']);
        $persona = Usuario::factory()->create([
            'usuario' => 'jperez',
            'personal' => 'JUAN PEREZ',
            'correo' => 'juan@example.com',
            'id_rol' => $rol->id_rol,
        ]);
        AsignacionTienda::factory()->create([
            'id_usuario' => $persona->id_usuario,
            'id_tienda' => $tienda->id_tienda,
            'estado' => 0,
        ]);

        $this
            ->actingAs($this->conPermiso('usuarios.manage'))
            ->get(route('equipo.index'))
            ->assertOk()
            ->assertSee('JUAN PEREZ')
            ->assertSee('jperez')
            ->assertSee('SUPERVISOR')
            ->assertDontSee('juan@example.com')
            ->assertDontSee('LIMA CENTRO');

        $this
            ->actingAs($this->conPermiso('personal.gestionar'))
            ->get(route('equipo.index'))
            ->assertOk()
            ->assertSee('juan@example.com')
            ->assertDontSee('LIMA CENTRO');

        $this
            ->actingAs($this->conPermiso('asignacion.gestionar'))
            ->get(route('equipo.index'))
            ->assertOk()
            ->assertSee('LIMA CENTRO')
            ->assertDontSee('juan@example.com');
    }

    public function test_the_person_page_offers_a_form_that_disables_and_re_enables_the_account(): void
    {
        $admin = $this->conPermiso('usuarios.manage');
        $persona = Usuario::factory()->create(['estado' => 1]);

        foreach ([false, true] as $esperado) {
            $action = route('usuarios.estado', $persona);
            $html = $this->actingAs($admin)->get(route('equipo.show', $persona))->assertOk()->getContent();
            $form = HTMLDocument::createFromString((string) $html, LIBXML_NOERROR)->querySelector(
                sprintf('form[action="%s"]', $action),
            );
            $this->assertNotNull($form);

            $campos = [];
            foreach ($form->querySelectorAll('input[type=hidden]') as $input) {
                $this->assertInstanceOf(Element::class, $input);
                $campos[(string) $input->getAttribute('name')] = (string) $input->getAttribute('value');
            }
            $this->assertSame('PATCH', $campos['_method'] ?? null);

            $this->post($action, $campos)->assertRedirect(route('equipo.show', $persona));
            $this->assertSame($esperado, $persona->fresh()->estado);
        }

        $this
            ->actingAs($admin)
            ->get(route('equipo.show', $admin))
            ->assertOk()
            ->assertDontSee(
                route('usuarios.estado', $admin),
            );
    }

    private function conPermiso(string $permiso): Usuario
    {
        $usuario = Usuario::factory()->createOne();
        $usuario->menus()->attach(Menu::factory()->create(['permiso' => $permiso]));

        return $usuario;
    }
}
