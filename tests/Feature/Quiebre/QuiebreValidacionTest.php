<?php

namespace Tests\Feature\Quiebre;

use App\Models\AsignacionTienda;
use App\Models\Menu;
use App\Models\Quiebre;
use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class QuiebreValidacionTest extends TestCase
{
    use RefreshDatabase;

    // A validator holding the quiebre.validar grant and assigned to a tienda.
    private function agente(Tienda $tienda): Usuario
    {
        $menu = Menu::factory()->create(['permiso' => 'quiebre.validar']);
        $user = Usuario::factory()->create();
        $user->menus()->attach($menu);
        AsignacionTienda::factory()->create([
            'id_usuario' => $user->id_usuario,
            'id_tienda' => $tienda->id_tienda,
            'estado' => 0,
        ]);

        return $user;
    }

    public function test_the_validar_permiso_gates_the_index(): void
    {
        $tienda = Tienda::factory()->create();

        $this->actingAs(Usuario::factory()->create())->get(route('quiebres.validacion.index'))->assertForbidden();
        $this->actingAs($this->agente($tienda))->get(route('quiebres.validacion.index'))->assertOk();
    }

    public function test_lists_only_open_quiebres_in_the_agents_tienda(): void
    {
        $tienda = Tienda::factory()->create();
        $otra = Tienda::factory()->create();
        $agente = $this->agente($tienda);

        Quiebre::factory()
            ->deRuc('20111111111')
            ->create([
                'zonal_telefonica' => $tienda->id_tienda,
                'fecha_registro' => now(),
            ]);
        Quiebre::factory()
            ->deRuc('20999999999')
            ->create([
                'zonal_telefonica' => $otra->id_tienda,
                'fecha_registro' => now(),
            ]);

        $this
            ->actingAs($agente)
            ->get(route('quiebres.validacion.index'))
            ->assertOk()
            ->assertSee('20111111111')
            ->assertDontSee('20999999999');
    }

    public function test_filters_by_estado(): void
    {
        $tienda = Tienda::factory()->create();
        $agente = $this->agente($tienda);

        Quiebre::factory()
            ->deRuc('20000000001')
            ->estado('PENDIENTE')
            ->create(['zonal_telefonica' => $tienda->id_tienda, 'fecha_registro' => now()]);
        Quiebre::factory()
            ->deRuc('20000000002')
            ->estado('DEVUELTO')
            ->create(['zonal_telefonica' => $tienda->id_tienda, 'fecha_registro' => now()]);

        $this
            ->actingAs($agente)
            ->get(route('quiebres.validacion.index', ['estado' => 'DEVUELTO']))
            ->assertOk()
            ->assertSee('20000000002')
            ->assertDontSee('20000000001');
    }

    public function test_shows_a_notice_when_the_agent_has_no_assignment(): void
    {
        $menu = Menu::factory()->create(['permiso' => 'quiebre.validar']);
        $user = Usuario::factory()->create();
        $user->menus()->attach($menu);

        $this
            ->actingAs($user)
            ->get(route('quiebres.validacion.index'))
            ->assertOk()
            ->assertSee('No tienes una tienda asignada');
    }

    public function test_renders_the_detail_for_an_in_tienda_quiebre(): void
    {
        $tienda = Tienda::factory()->create();
        $agente = $this->agente($tienda);
        $quiebre = Quiebre::factory()->deRuc('20123123123')->create(['zonal_telefonica' => $tienda->id_tienda]);

        $this
            ->actingAs($agente)
            ->get(route('quiebres.validacion.edit', $quiebre))
            ->assertOk()
            ->assertSee('20123123123')
            ->assertSee('Guardar validación');
    }

    public function test_records_a_validation_decision(): void
    {
        $tienda = Tienda::factory()->create();
        $agente = $this->agente($tienda);
        $quiebre = Quiebre::factory()->create(['zonal_telefonica' => $tienda->id_tienda]);

        $this->actingAs($agente)->put(route('quiebres.validacion.update', $quiebre), [
            'estado' => 'ATENDIDO',
            'caso_sf' => 'SF-123',
            'comentario_validador' => 'Revisado y atendido.',
        ])->assertRedirect(route('quiebres.validacion.index'));

        $quiebre->refresh();
        $this->assertSame('ATENDIDO', $quiebre->estado);
        $this->assertSame('SF-123', $quiebre->caso_sf);
        $this->assertSame(
            $agente->id_usuario,
            $quiebre->id_validador,
            'validator comes from the session, not the form',
        );
        $this->assertSame(Carbon::now('America/Lima')->toDateString(), $quiebre->fecha_validacion->toDateString());
    }

    public function test_cannot_validate_a_quiebre_outside_the_agents_tienda(): void
    {
        $tienda = Tienda::factory()->create();
        $otra = Tienda::factory()->create();
        $agente = $this->agente($tienda);
        $ajeno = Quiebre::factory()->create(['zonal_telefonica' => $otra->id_tienda]);

        $this->actingAs($agente)->put(route('quiebres.validacion.update', $ajeno), [
            'estado' => 'ATENDIDO',
            'caso_sf' => 'SF-1',
        ])->assertForbidden();

        $this->assertSame('PENDIENTE', $ajeno->fresh()->estado);
    }

    public function test_rejects_an_invalid_estado(): void
    {
        $tienda = Tienda::factory()->create();
        $agente = $this->agente($tienda);
        $quiebre = Quiebre::factory()->create(['zonal_telefonica' => $tienda->id_tienda]);

        $this->actingAs($agente)->put(route('quiebres.validacion.update', $quiebre), [
            'estado' => 'INVENTADO',
            'caso_sf' => 'SF-1',
        ])->assertSessionHasErrors('estado');

        $this->assertSame('PENDIENTE', $quiebre->fresh()->estado);
    }
}
