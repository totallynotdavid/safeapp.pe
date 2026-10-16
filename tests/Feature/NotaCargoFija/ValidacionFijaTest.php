<?php

namespace Tests\Feature\NotaCargoFija;

use App\Models\NcFija;
use App\Models\Tienda;
use App\Models\Usuario;

class ValidacionFijaTest extends NotaCargoFijaTestCase
{
    private const PERMISO = 'notacargo.fija.validar';

    public function test_the_validar_permiso_gates_the_screen(): void
    {
        $ruta = route('notas-cargo.fija.validacion.index');

        $this->actingAs(Usuario::factory()->create())->get($ruta)->assertForbidden();
        $this->actingAs($this->usuarioCon(self::PERMISO))->get($ruta)->assertOk();
    }

    public function test_the_queue_holds_only_undecided_cases(): void
    {
        NcFija::factory()->registrada()->deRuc('20111111111')->create();
        NcFija::factory()->deRuc('20222222222')->create();
        NcFija::factory()->rechazada()->deRuc('20999999999')->create();

        $this
            ->actingAs($this->usuarioCon(self::PERMISO))
            ->get(route('notas-cargo.fija.validacion.index'))
            ->assertOk()
            ->assertSee('20111111111')
            ->assertDontSee('20222222222')
            ->assertDontSee('20999999999');
    }

    public function test_the_queue_is_not_scoped_to_the_validators_own_region(): void
    {
        // The queue is global: a case in a far región still shows to a
        // validador whose store is elsewhere.
        $lejos = Tienda::factory()->create();
        NcFija::factory()->registrada()->deRuc('20111111111')->create(['zonal' => $lejos->id_tienda]);

        $this
            ->actingAs($this->usuarioCon(self::PERMISO))
            ->get(route('notas-cargo.fija.validacion.index'))
            ->assertOk()
            ->assertSee('20111111111');
    }

    public function test_it_records_the_ruling_and_stamps_the_validator(): void
    {
        $validador = $this->usuarioCon(self::PERMISO);
        $cargo = NcFija::factory()->registrada()->deRuc(self::RUC)->createOne();

        $this->actingAs($validador)->put(route('notas-cargo.fija.validacion.update', $cargo), [
            'validacion' => NcFija::VALIDACION_PROCEDE,
            'comentario_validador' => 'VALIDACION OK',
        ])->assertRedirect(route('notas-cargo.fija.validacion.index'));

        $fresh = $cargo->fresh();
        $this->assertSame(NcFija::VALIDACION_PROCEDE, $fresh->validacion);
        $this->assertSame('VALIDACION OK', $fresh->comentario_validador);
        // Identity and date come from the session and the clock, not the form.
        $this->assertSame($validador->id_usuario, $fresh->id_validador);
        $this->assertSame(now()->toDateString(), $fresh->fecha_validacion->toDateString());
    }

    public function test_validating_leaves_the_lifecycle_estado_alone(): void
    {
        // `estado` belongs to the back office. A validated case is still
        // XINGRESAR until the back office moves it.
        $cargo = NcFija::factory()->registrada()->deRuc(self::RUC)->createOne();

        $this->actingAs($this->usuarioCon(self::PERMISO))->put(route('notas-cargo.fija.validacion.update', $cargo), [
            'validacion' => NcFija::VALIDACION_PROCEDE,
            'comentario_validador' => 'VALIDACION OK',
        ]);

        $this->assertSame('XINGRESAR', $cargo->fresh()->estado);
    }

    public function test_a_decided_case_cannot_be_ruled_on_again(): void
    {
        // A second validador cannot silently replace the first one's decision.
        $cargo = NcFija::factory()->deRuc(self::RUC)->create(['comentario_validador' => 'VALIDACION OK']);

        $this
            ->actingAs($this->usuarioCon(self::PERMISO))
            ->put(route('notas-cargo.fija.validacion.update', $cargo), [
                'validacion' => NcFija::VALIDACION_RECHAZADO,
                'comentario_validador' => 'NO DEBERIA PASAR',
            ])
            ->assertForbidden();

        $this->assertSame(NcFija::VALIDACION_PROCEDE, $cargo->fresh()->validacion);
    }

    public function test_leaving_a_case_pending_is_not_a_decision(): void
    {
        $cargo = NcFija::factory()->registrada()->deRuc(self::RUC)->createOne();

        $this
            ->actingAs($this->usuarioCon(self::PERMISO))
            ->put(route('notas-cargo.fija.validacion.update', $cargo), [
                'validacion' => NcFija::VALIDACION_PENDIENTE,
                'comentario_validador' => 'SIN DECIDIR',
            ])
            ->assertSessionHasErrors('validacion');
    }
}
