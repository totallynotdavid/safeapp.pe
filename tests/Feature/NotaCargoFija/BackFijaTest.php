<?php

namespace Tests\Feature\NotaCargoFija;

use App\Models\NcFija;
use App\Models\Tienda;
use App\Models\Usuario;

// BACK: the office that owns `estado` and moves a validated case through it.
class BackFijaTest extends NotaCargoFijaTestCase
{
    private const PERMISO = 'notacargo.fija.back';

    /**
     * A COMPLETADO case is windowed by fecha_liquidacion, the month it settled,
     * so that is the date a fixture on this queue has to pin.
     *
     * @return array<string, mixed>
     */
    private function cerradoEsteMes(): array
    {
        return ['fecha_ingreso' => now(), 'fecha_actualizacion' => now(), 'fecha_liquidacion' => now()];
    }

    public function test_the_back_permiso_gates_the_screen(): void
    {
        $ruta = route('notas-cargo.fija.back.index');

        $this->actingAs(Usuario::factory()->create())->get($ruta)->assertForbidden();
        $this->actingAs($this->usuarioCon(self::PERMISO))->get($ruta)->assertOk();
    }

    public function test_the_queue_holds_only_validated_cases(): void
    {
        NcFija::factory()->deRuc('20111111111')->create($this->cerradoEsteMes());
        NcFija::factory()->registrada()->deRuc('20222222222')->create($this->cerradoEsteMes());
        NcFija::factory()->rechazada()->deRuc('20999999999')->create($this->cerradoEsteMes());

        $this
            ->actingAs($this->usuarioCon(self::PERMISO))
            ->get(route('notas-cargo.fija.back.index'))
            ->assertOk()
            ->assertSee('20111111111')
            ->assertDontSee('20222222222')
            ->assertDontSee('20999999999');
    }

    public function test_it_records_the_estado_and_stamps_the_operator(): void
    {
        $operador = $this->usuarioCon(self::PERMISO);
        $region = Tienda::factory()->create();
        $cargo = NcFija::factory()->deRuc(self::RUC)->create(['estado' => 'XINGRESAR']);

        $this->actingAs($operador)->put(route('notas-cargo.fija.back.update', $cargo), [
            'estado' => 'COMPLETADO',
            'fecha_liquidacion' => now()->toDateString(),
            'comentario_back' => 'INSTALADO',
            'zonal' => $region->id_tienda,
            'nodo' => 'NAT',
            'contrata' => 'cobra',
        ])->assertRedirect(route('notas-cargo.fija.back.index'));

        $fresh = $cargo->fresh();
        $this->assertSame('COMPLETADO', $fresh->estado);
        $this->assertSame('INSTALADO', $fresh->comentario_back);
        $this->assertSame($operador->id_usuario, $fresh->id_back);
        // Stamped from the clock, which is what makes it "when the back office
        // last acted" and is how a cancelled case is windowed.
        $this->assertSame(now()->toDateString(), $fresh->fecha_actualizacion->toDateString());
    }

    public function test_a_completed_case_must_say_which_month_it_settled(): void
    {
        // Without it the case falls out of every queue that windows by month.
        // Seven of the 3,481 legacy COMPLETADO rows are in exactly that hole.
        $cargo = NcFija::factory()->deRuc(self::RUC)->create(['estado' => 'XINGRESAR']);

        $this
            ->actingAs($this->usuarioCon(self::PERMISO))
            ->put(route('notas-cargo.fija.back.update', $cargo), [
                'estado' => 'COMPLETADO',
                'comentario_back' => 'INSTALADO',
                'zonal' => Tienda::factory()->create()->id_tienda,
                'nodo' => 'NAT',
            ])
            ->assertSessionHasErrors('fecha_liquidacion');
    }

    public function test_the_contractor_is_stored_the_same_way_however_it_is_typed(): void
    {
        // Free text with no catalog produced COMFICA and COMFICA2, JBA and JB.
        // Case and padding are at least not part of the problem.
        $cargo = NcFija::factory()->deRuc(self::RUC)->create(['estado' => 'XINGRESAR']);

        $this->actingAs($this->usuarioCon(self::PERMISO))->put(route('notas-cargo.fija.back.update', $cargo), [
            'estado' => 'OBSERVADO',
            'comentario_back' => 'PENDIENTE FFTT',
            'zonal' => Tienda::factory()->create()->id_tienda,
            'nodo' => 'NAT',
            'contrata' => '  cobra  ',
        ]);

        $this->assertSame('COBRA', $cargo->fresh()->contrata);
    }

    public function test_it_corrects_the_region_in_place(): void
    {
        // Legacy wrote the correction to a second column (zonal_telefonica) and
        // left the original standing, so the two disagreed on 1,835 of the 4,478
        // cases where both were set, and different screens read different ones.
        $destino = Tienda::factory()->create();
        $cargo = NcFija::factory()->deRuc(self::RUC)->create(['zonal' => Tienda::factory()]);

        $this->actingAs($this->usuarioCon(self::PERMISO))->put(route('notas-cargo.fija.back.update', $cargo), [
            'estado' => 'OBSERVADO',
            'comentario_back' => 'REGION CORREGIDA',
            'zonal' => $destino->id_tienda,
            'nodo' => 'SINNODO',
        ]);

        $fresh = $cargo->fresh();
        $this->assertSame($destino->id_tienda, $fresh->zonal);
        $this->assertArrayNotHasKey('zonal_telefonica', $fresh->getAttributes());
    }

    public function test_an_unvalidated_case_cannot_be_moved_through_the_lifecycle(): void
    {
        // Legacy trusted the posted id, so a rejected case could be completed.
        $cargo = NcFija::factory()->rechazada()->deRuc(self::RUC)->create(['estado' => 'XINGRESAR']);

        $this
            ->actingAs($this->usuarioCon(self::PERMISO))
            ->put(route('notas-cargo.fija.back.update', $cargo), [
                'estado' => 'COMPLETADO',
                'fecha_liquidacion' => now()->toDateString(),
                'comentario_back' => 'NO DEBERIA PASAR',
                'zonal' => Tienda::factory()->create()->id_tienda,
                'nodo' => 'SINNODO',
            ])
            ->assertForbidden();

        $this->assertSame('XINGRESAR', $cargo->fresh()->estado);
    }

    public function test_the_estado_filter_offers_the_whole_lifecycle(): void
    {
        // The legacy per-tienda screen omitted ARBITRAJE and ADR that its global
        // twin offered, so a case set to either was unfilterable on one of the
        // two screens showing it.
        $response = $this
            ->actingAs($this->usuarioCon(self::PERMISO))
            ->get(route('notas-cargo.fija.back.index'))
            ->assertOk();

        foreach (NcFija::ESTADOS as $estado) {
            $response->assertSee('value="'.$estado.'"', false);
        }
    }
}
