<?php

namespace Tests\Feature\Liquidacion;

use App\Models\NcMovil;
use App\Models\Usuario;

// What reaches the liquidador's queue, and what must not.
class ColaLiquidacionTest extends LiquidacionTestCase
{
    public function test_the_gestionar_permiso_gates_the_index(): void
    {
        $this->actingAs(Usuario::factory()->create())->get(route('liquidaciones.index'))->assertForbidden();
        $this->actingAs($this->liquidador())->get(route('liquidaciones.index'))->assertOk();
    }

    public function test_lists_only_validated_cases(): void
    {
        $this->enCola(['ruc' => '20111111111']);
        NcMovil::factory()->rechazada()->create(['ruc' => '20999999999', 'fecha_actualizacion' => now()]);

        $this
            ->actingAs($this->liquidador())
            ->get(route('liquidaciones.index'))
            ->assertOk()
            ->assertSee('20111111111')
            ->assertDontSee('20999999999');
    }

    public function test_a_validated_case_that_fell_through_is_not_settleable(): void
    {
        $this->enCola(['ruc' => '20111111111']);
        // CAIDA passes validación but is a quarter of production, so leaving it
        // in the queue would put fallen cases in front of the liquidador.
        NcMovil::factory()->caida()->create(['ruc' => '20999999999', 'fecha_actualizacion' => now()]);

        $this
            ->actingAs($this->liquidador())
            ->get(route('liquidaciones.index'))
            ->assertOk()
            ->assertSee('20111111111')
            ->assertDontSee('20999999999');
    }

    public function test_filters_by_estado(): void
    {
        $this->enCola(['estado' => 'ACTIVADO', 'ruc' => '20000000001']);
        $this->enCola(['estado' => 'DELIVERY', 'ruc' => '20000000002']);

        $this
            ->actingAs($this->liquidador())
            ->get(route('liquidaciones.index', ['estado' => 'ACTIVADO']))
            ->assertOk()
            ->assertSee('20000000001')
            ->assertDontSee('20000000002');
    }

    public function test_the_estado_filter_offers_only_settleable_states(): void
    {
        // The settlement outcome vocabulary is not the estado vocabulary. Legacy
        // offered DEUDA and friends as estado filter values, which no row could
        // ever match.
        $response = $this->actingAs($this->liquidador())->get(route('liquidaciones.index'))->assertOk();

        foreach (NcMovil::OBSERVACIONES as $observacion) {
            $response->assertDontSee('value="'.$observacion.'"', false);
        }

        $response->assertSee('value="ACTIVADO"', false);
    }

    public function test_excludes_cases_from_other_years(): void
    {
        $this->enCola(['ruc' => '20222222222', 'fecha_actualizacion' => now()->subYears(2)]);

        $this
            ->actingAs($this->liquidador())
            ->get(route('liquidaciones.index'))
            ->assertOk()
            ->assertDontSee('20222222222');
    }
}
