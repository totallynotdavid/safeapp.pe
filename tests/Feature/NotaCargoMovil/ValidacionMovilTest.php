<?php

namespace Tests\Feature\NotaCargoMovil;

use App\Models\NcMovil;
use App\Models\Usuario;

// VALIDACIÓN: the validador's ruling, and the two figures they may correct.
class ValidacionMovilTest extends NotaCargoMovilTestCase
{
    private const PERMISO = 'notacargo.movil.validar';

    public function test_the_validar_permiso_gates_the_screen(): void
    {
        $ruta = route('notas-cargo.movil.validacion.index');

        $this->actingAs(Usuario::factory()->create())->get($ruta)->assertForbidden();
        $this->actingAs($this->usuarioCon(self::PERMISO))->get($ruta)->assertOk();
    }

    public function test_the_queue_holds_only_undecided_cases(): void
    {
        $ejecutivo = Usuario::factory()->create();

        NcMovil::factory()->registrada()->deRuc('20111111111')->create(['id_usuario' => $ejecutivo->id_usuario]);
        NcMovil::factory()->deRuc('20999999999')->create(['id_usuario' => $ejecutivo->id_usuario]);

        $this
            ->actingAs($this->usuarioCon(self::PERMISO))
            ->get(route('notas-cargo.movil.validacion.index'))
            ->assertOk()
            ->assertSee('20111111111')
            ->assertDontSee('20999999999');
    }

    public function test_the_queue_is_not_scoped_to_the_validators_own_region(): void
    {
        // Legacy shipped this screen twice, and the filtered copy matched the
        // case's región against the reviewer's own store. Those are different
        // things: they differ in 1,390 of the 2,006 production rows, so the
        // filtered screen hid most of the queue. Nobody used it.
        $validador = $this->usuarioCon(self::PERMISO);
        $ejecutivo = Usuario::factory()->create();

        NcMovil::factory()->registrada()->deRuc('20111111111')->create(['id_usuario' => $ejecutivo->id_usuario]);
        NcMovil::factory()->registrada()->deRuc('20222222222')->create(['id_usuario' => $ejecutivo->id_usuario]);

        $this
            ->actingAs($validador)
            ->get(route('notas-cargo.movil.validacion.index'))
            ->assertOk()
            ->assertSee('20111111111')
            ->assertSee('20222222222');
    }

    public function test_it_records_the_ruling_and_stamps_the_validator(): void
    {
        $validador = $this->usuarioCon(self::PERMISO);
        $cargo = $this->casoPendiente(Usuario::factory()->create());

        $this->actingAs($validador)->put(route('notas-cargo.movil.validacion.update', $cargo), [
            'validacion' => NcMovil::VALIDACION_PROCEDE,
            'comentario_validador' => 'VALIDACION OK',
            'q_lineas' => 7,
            'cargo_fijo' => '200.00',
        ])->assertRedirect(route('notas-cargo.movil.validacion.index'));

        $fresh = $cargo->fresh();
        $this->assertSame(NcMovil::VALIDACION_PROCEDE, $fresh->validacion);
        $this->assertSame(7, $fresh->q_lineas);
        // Identity and date come from the session and the clock; legacy trusted a
        // hidden field for the validator id.
        $this->assertSame($validador->id_usuario, $fresh->id_validador);
        $this->assertSame(now()->toDateString(), $fresh->fecha_validacion->toDateString());
    }

    public function test_validating_leaves_the_lifecycle_estado_alone(): void
    {
        // estado belongs to the back office. Passing validación admits a case to
        // that stage; it does not move it through one.
        $cargo = $this->casoPendiente(Usuario::factory()->create());

        $this->actingAs($this->usuarioCon(self::PERMISO))->put(
            route('notas-cargo.movil.validacion.update', $cargo),
            [
                'validacion' => NcMovil::VALIDACION_PROCEDE,
                'comentario_validador' => 'OK',
                'q_lineas' => 3,
                'cargo_fijo' => '10.00',
            ],
        );

        $this->assertSame('PENDIENTE', $cargo->fresh()->estado);
    }

    public function test_a_decided_case_cannot_be_ruled_on_again(): void
    {
        // Legacy loaded and overwrote whatever id the form posted, so a second
        // validador could silently replace the first one's decision.
        $cargo = NcMovil::factory()->deRuc(self::RUC)->create(['comentario_validador' => 'PRIMERA DECISION']);

        $this
            ->actingAs($this->usuarioCon(self::PERMISO))
            ->put(route('notas-cargo.movil.validacion.update', $cargo), [
                'validacion' => NcMovil::VALIDACION_RECHAZADO,
                'comentario_validador' => 'SEGUNDA DECISION',
                'q_lineas' => 1,
                'cargo_fijo' => '1.00',
            ])
            ->assertForbidden();

        $this->assertSame('PRIMERA DECISION', $cargo->fresh()->comentario_validador);
    }

    public function test_leaving_a_case_pending_is_not_a_decision(): void
    {
        $cargo = $this->casoPendiente(Usuario::factory()->create());

        $this
            ->actingAs($this->usuarioCon(self::PERMISO))
            ->put(route('notas-cargo.movil.validacion.update', $cargo), [
                'validacion' => NcMovil::VALIDACION_PENDIENTE,
                'comentario_validador' => 'SIGO PENSANDO',
                'q_lineas' => 1,
                'cargo_fijo' => '1.00',
            ])
            ->assertSessionHasErrors('validacion');
    }
}
