<?php

namespace Tests\Feature\Liquidacion;

use App\Models\Cartera;
use App\Models\NcMovil;
use App\Models\Supervisor;
use App\Models\Tienda;

// The carrier account executive catalog and the attribution the liquidación
// screen displays from it.
class CarteraTest extends LiquidacionTestCase
{
    public function test_only_active_executives_of_the_tienda_are_selectable(): void
    {
        $tienda = Tienda::factory()->create();
        $otra = Tienda::factory()->create();

        $activa = Cartera::factory()->create(['id_tienda' => $tienda->id_tienda, 'propietario' => 'ANA VIGIL']);
        Cartera::factory()->retirada()->create(['id_tienda' => $tienda->id_tienda, 'propietario' => 'LUIS RETIRADO']);
        Cartera::factory()->create(['id_tienda' => $otra->id_tienda, 'propietario' => 'JOSE AJENO']);

        $seleccionables = Cartera::query()->seleccionables($tienda->id_tienda)->get();

        $this->assertSame([$activa->id_cartera], $seleccionables->pluck('id_cartera')->all());
    }

    public function test_a_retired_executive_still_names_their_old_cases(): void
    {
        $cartera = Cartera::factory()->retirada()->create(['propietario' => 'LUIS RETIRADO']);
        $cargo = NcMovil::factory()->create([
            'id_cartera' => $cartera->id_cartera,
            'fecha_actualizacion' => now(),
        ]);

        $this
            ->actingAs($this->liquidador())
            ->get(route('liquidaciones.edit', $cargo))
            ->assertOk()
            ->assertSee('LUIS RETIRADO');
    }

    public function test_the_case_shows_its_executive_and_supervisor(): void
    {
        $cartera = Cartera::factory()->create(['propietario' => 'ANA VIGIL', 'nombre' => 'SC-00058_LIMA']);
        $supervisor = Supervisor::factory()->create(['nombre' => 'JORGE MONZON']);
        $cargo = NcMovil::factory()
            ->atribuida($cartera, $supervisor)
            ->create(['fecha_actualizacion' => now()]);

        $this
            ->actingAs($this->liquidador())
            ->get(route('liquidaciones.edit', $cargo))
            ->assertOk()
            ->assertSee('ANA VIGIL')
            ->assertSee('SC-00058_LIMA')
            ->assertSee('JORGE MONZON');
    }

    public function test_an_unattributed_case_still_opens(): void
    {
        // id_cartera and id_supervisor stay 0 for most production cases, which is
        // not a row that exists, so both relations must resolve to null.
        $cargo = $this->enCola();

        $this->actingAs($this->liquidador())->get(route('liquidaciones.edit', $cargo))->assertOk();

        $this->assertNull($cargo->cartera);
        $this->assertNull($cargo->supervisor);
    }
}
