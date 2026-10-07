<?php

namespace Tests\Feature\NotaCargoMovil;

use App\Models\NcMovil;
use App\Models\Tienda;
use App\Models\Usuario;

class BackMovilTest extends NotaCargoMovilTestCase
{
    private const PERMISO = 'notacargo.movil.back';

    public function test_the_back_permiso_gates_the_screen(): void
    {
        $ruta = route('notas-cargo.movil.back.index');

        $this->actingAs(Usuario::factory()->create())->get($ruta)->assertForbidden();
        $this->actingAs($this->usuarioCon(self::PERMISO))->get($ruta)->assertOk();
    }

    public function test_the_queue_holds_only_validated_cases(): void
    {
        // An ACTIVADO case is windowed by fecha_actualizacion, the month it
        // closed in, so that is the date this fixture has to pin.
        $cerradoHoy = ['fecha_ingreso' => now(), 'fecha_actualizacion' => now()];

        NcMovil::factory()->deRuc('20111111111')->create($cerradoHoy);
        NcMovil::factory()->registrada()->deRuc('20222222222')->create($cerradoHoy);
        NcMovil::factory()->rechazada()->deRuc('20999999999')->create($cerradoHoy);

        $this
            ->actingAs($this->usuarioCon(self::PERMISO))
            ->get(route('notas-cargo.movil.back.index'))
            ->assertOk()
            ->assertSee('20111111111')
            ->assertDontSee('20222222222')
            ->assertDontSee('20999999999');
    }

    public function test_it_records_the_estado_and_stamps_the_operator(): void
    {
        $operador = $this->usuarioCon(self::PERMISO);
        $region = Tienda::factory()->create();
        $cargo = NcMovil::factory()->deRuc(self::RUC)->create(['estado' => 'PENDIENTE']);

        $this->actingAs($operador)->put(route('notas-cargo.movil.back.update', $cargo), [
            'estado' => 'ACTIVADO',
            'fecha_actualizacion' => now()->toDateString(),
            'comentario_back' => 'ACTIVADO HOY',
            'zonal' => $region->id_tienda,
            'etapa' => 'F1-GANADA',
            'nodo' => 'NAT',
        ])->assertRedirect(route('notas-cargo.movil.back.index'));

        $fresh = $cargo->fresh();
        $this->assertSame('ACTIVADO', $fresh->estado);
        $this->assertSame('ACTIVADO HOY', $fresh->comentario_back);
        $this->assertSame($operador->id_usuario, $fresh->id_back);
    }

    public function test_it_corrects_the_region_in_place(): void
    {
        // Legacy wrote the correction to a second column (zonal_telefonica) and
        // left the original standing, so the two disagreed on 46 of the 1,202
        // cases where both were set, and different screens read different ones.
        $destino = Tienda::factory()->create();
        $cargo = NcMovil::factory()->deRuc(self::RUC)->createOne(['zonal' => Tienda::factory()]);

        $this->actingAs($this->usuarioCon(self::PERMISO))->put(route('notas-cargo.movil.back.update', $cargo), [
            'estado' => 'ACTIVADO',
            'fecha_actualizacion' => now()->toDateString(),
            'comentario_back' => 'REGION CORREGIDA',
            'zonal' => $destino->id_tienda,
            'etapa' => 'F1-GANADA',
            'nodo' => 'SINNODO',
        ]);

        $fresh = $cargo->fresh();
        $this->assertNotNull($fresh);
        $this->assertSame($destino->id_tienda, $fresh->zonal);
        $this->assertArrayNotHasKey('zonal_telefonica', $fresh->getAttributes());
    }

    public function test_an_unvalidated_case_cannot_be_moved_through_the_lifecycle(): void
    {
        // Legacy trusted the posted id, so a rejected case could be activated.
        $cargo = NcMovil::factory()->rechazada()->deRuc(self::RUC)->create(['estado' => 'PENDIENTE']);

        $this
            ->actingAs($this->usuarioCon(self::PERMISO))
            ->put(route('notas-cargo.movil.back.update', $cargo), [
                'estado' => 'ACTIVADO',
                'fecha_actualizacion' => now()->toDateString(),
                'comentario_back' => 'NO DEBERIA PASAR',
                'zonal' => Tienda::factory()->create()->id_tienda,
                'etapa' => 'F1-GANADA',
                'nodo' => 'SINNODO',
            ])
            ->assertForbidden();

        $this->assertSame('PENDIENTE', $cargo->fresh()->estado);
    }

    public function test_the_estado_filter_offers_the_whole_lifecycle(): void
    {
        $response = $this
            ->actingAs($this->usuarioCon(self::PERMISO))
            ->get(route('notas-cargo.movil.back.index'))
            ->assertOk();

        foreach (['PENDIENTE', 'ACTIVADO', 'CAIDA', 'LIQUIDADO', 'ADR'] as $estado) {
            $response->assertSee('value="'.$estado.'"', false);
        }
    }
}
