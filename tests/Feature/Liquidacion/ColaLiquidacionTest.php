<?php

namespace Tests\Feature\Liquidacion;

use App\Models\NcMovil;
use App\Models\Usuario;

class ColaLiquidacionTest extends LiquidacionTestCase
{
    public function test_the_gestionar_permiso_gates_the_index(): void
    {
        $this->actingAs(Usuario::factory()->create())->get(route('liquidaciones.index'))->assertForbidden();
        $this->actingAs($this->liquidador())->get(route('liquidaciones.index'))->assertOk();
    }

    public function test_lists_only_validated_cases(): void
    {
        $this->enCola('20111111111');
        NcMovil::factory()->rechazada()->deRuc('20999999999')->create(['fecha_actualizacion' => now()]);

        $this
            ->actingAs($this->liquidador())
            ->get(route('liquidaciones.index'))
            ->assertOk()
            ->assertSee('20111111111')
            ->assertDontSee('20999999999');
    }

    public function test_a_validated_case_that_fell_through_is_not_settleable(): void
    {
        $this->enCola('20111111111');
        // CAIDA passes validación, so only the estado keeps it out of the queue.
        NcMovil::factory()->caida()->deRuc('20999999999')->create(['fecha_actualizacion' => now()]);

        $this
            ->actingAs($this->liquidador())
            ->get(route('liquidaciones.index'))
            ->assertOk()
            ->assertSee('20111111111')
            ->assertDontSee('20999999999');
    }

    public function test_filters_by_estado(): void
    {
        $this->enCola('20000000001', ['estado' => 'ACTIVADO']);
        $this->enCola('20000000002', ['estado' => 'DELIVERY']);

        $this
            ->actingAs($this->liquidador())
            ->get(route('liquidaciones.index', ['estado' => 'ACTIVADO']))
            ->assertOk()
            ->assertSee('20000000001')
            ->assertDontSee('20000000002');
    }

    public function test_the_estado_filter_offers_only_settleable_states(): void
    {
        // The settlement outcome vocabulary is not the estado vocabulary, so
        // DEUDA and friends are not offered as estado filter values.
        $response = $this->actingAs($this->liquidador())->get(route('liquidaciones.index'))->assertOk();

        foreach (NcMovil::OBSERVACIONES as $observacion) {
            $response->assertDontSee('value="'.$observacion.'"', false);
        }

        $response->assertSee('value="ACTIVADO"', false);
    }

    public function test_excludes_cases_from_other_years(): void
    {
        $this->enCola('20222222222', ['fecha_actualizacion' => now()->subYears(2)]);

        $this
            ->actingAs($this->liquidador())
            ->get(route('liquidaciones.index'))
            ->assertOk()
            ->assertDontSee('20222222222');
    }
}
