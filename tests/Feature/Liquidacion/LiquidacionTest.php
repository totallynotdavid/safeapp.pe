<?php

namespace Tests\Feature\Liquidacion;

use App\Models\Menu;
use App\Models\NcMovil;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiquidacionTest extends TestCase
{
    use RefreshDatabase;

    // A liquidador holding the liquidacion.gestionar grant.
    private function liquidador(): Usuario
    {
        $menu = Menu::factory()->create(['permiso' => 'liquidacion.gestionar']);
        $user = Usuario::factory()->create();
        $user->menus()->attach($menu);

        return $user;
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function validado(array $overrides = []): NcMovil
    {
        return NcMovil::factory()->create(array_merge(['fecha_actualizacion' => now()], $overrides));
    }

    public function test_the_gestionar_permiso_gates_the_index(): void
    {
        $this->actingAs(Usuario::factory()->create())->get(route('liquidaciones.index'))->assertForbidden();
        $this->actingAs($this->liquidador())->get(route('liquidaciones.index'))->assertOk();
    }

    public function test_lists_only_validated_cases(): void
    {
        $this->validado(['ruc' => '20111111111']);
        NcMovil::factory()->improcede()->create(['ruc' => '20999999999', 'fecha_actualizacion' => now()]);

        $this
            ->actingAs($this->liquidador())
            ->get(route('liquidaciones.index'))
            ->assertOk()
            ->assertSee('20111111111')
            ->assertDontSee('20999999999');
    }

    public function test_filters_by_estado(): void
    {
        $this->validado(['estado' => 'DEUDA', 'ruc' => '20000000001']);
        $this->validado(['estado' => 'BAJA', 'ruc' => '20000000002']);

        $this
            ->actingAs($this->liquidador())
            ->get(route('liquidaciones.index', ['estado' => 'DEUDA']))
            ->assertOk()
            ->assertSee('20000000001')
            ->assertDontSee('20000000002');
    }

    public function test_excludes_cases_from_other_years(): void
    {
        $this->validado(['ruc' => '20222222222', 'fecha_actualizacion' => now()->subYears(2)]);

        $this
            ->actingAs($this->liquidador())
            ->get(route('liquidaciones.index'))
            ->assertOk()
            ->assertDontSee('20222222222');
    }

    public function test_settles_a_case(): void
    {
        $cargo = $this->validado();

        $this->actingAs($this->liquidador())->put(route('liquidaciones.update', $cargo), [
            'comision' => '150.00',
            'observacion' => 'DEUDA',
            'descuento' => '20',
            'fecha_liquidacion' => now()->toDateString(),
            'comentario_liquidador' => 'ACUERDO CERRADO',
        ])->assertRedirect(route('liquidaciones.index'));

        $fresh = $cargo->fresh();
        $this->assertSame('150.00', $fresh->comision);
        $this->assertSame('DEUDA', $fresh->observacion);
        $this->assertSame('20', $fresh->descuento);
        $this->assertSame('ACUERDO CERRADO', $fresh->comentario_liquidador);
    }

    public function test_rejects_an_invalid_observacion(): void
    {
        $cargo = $this->validado();

        $this->actingAs($this->liquidador())->put(route('liquidaciones.update', $cargo), [
            'comision' => '100',
            'observacion' => 'INVENTADO',
            'descuento' => '0',
            'fecha_liquidacion' => now()->toDateString(),
        ])->assertSessionHasErrors('observacion');
    }

    public function test_requires_the_settlement_fields(): void
    {
        $cargo = $this->validado();

        $this
            ->actingAs($this->liquidador())
            ->put(route('liquidaciones.update', $cargo), [])
            ->assertSessionHasErrors(['comision', 'observacion', 'descuento', 'fecha_liquidacion']);
    }
}
