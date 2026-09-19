<?php

namespace Tests\Feature\NotaCargoMovil;

use App\Models\NcMovil;

class ColaRegistroMovilTest extends NotaCargoMovilTestCase
{
    private const PERMISO = 'notacargo.movil.registrar';

    public function test_it_shows_only_the_signed_in_ejecutivos_cases(): void
    {
        $ejecutivo = $this->usuarioCon(self::PERMISO);
        $otro = $this->usuarioCon(self::PERMISO);

        NcMovil::factory()->registrada()->deRuc('20111111111')->create(['id_usuario' => $ejecutivo->id_usuario]);
        NcMovil::factory()->registrada()->deRuc('20999999999')->create(['id_usuario' => $otro->id_usuario]);

        $this
            ->actingAs($ejecutivo)
            ->get(route('notas-cargo.movil.index'))
            ->assertOk()
            ->assertSee('20111111111')
            ->assertDontSee('20999999999');
    }

    public function test_a_rejected_case_leaves_the_queue(): void
    {
        // The ejecutivo cannot act on it, and legacy excluded RECHAZADO from
        // every branch of the list.
        $ejecutivo = $this->usuarioCon(self::PERMISO);

        NcMovil::factory()->registrada()->deRuc('20111111111')->create(['id_usuario' => $ejecutivo->id_usuario]);
        NcMovil::factory()->rechazada()->deRuc('20999999999')->create(['id_usuario' => $ejecutivo->id_usuario]);

        $this
            ->actingAs($ejecutivo)
            ->get(route('notas-cargo.movil.index'))
            ->assertOk()
            ->assertSee('20111111111')
            ->assertDontSee('20999999999');
    }

    public function test_an_in_flight_case_stays_on_the_queue_however_old_it_is(): void
    {
        // In-flight estados are bounded by year alone, with no month, so a case
        // still waiting does not fall off the list as the months pass. Estados
        // that closed are windowed to the month they closed in.
        $ejecutivo = $this->usuarioCon(self::PERMISO);

        NcMovil::factory()
            ->registrada()
            ->deRuc('20111111111')
            ->create([
                'id_usuario' => $ejecutivo->id_usuario,
                'fecha_ingreso' => now()->subMonths(5),
            ]);
        NcMovil::factory()
            ->estado('CAIDA')
            ->deRuc('20999999999')
            ->create([
                'id_usuario' => $ejecutivo->id_usuario,
                'fecha_ingreso' => now()->subMonths(5),
            ]);

        $this
            ->actingAs($ejecutivo)
            ->get(route('notas-cargo.movil.index'))
            ->assertOk()
            ->assertSee('20111111111')
            ->assertDontSee('20999999999');
    }

    public function test_another_ejecutivo_cannot_open_a_case(): void
    {
        // Legacy scoped only the list; the detail endpoint trusted the posted id.
        $cargo = $this->casoPendiente($this->usuarioCon(self::PERMISO));

        $this
            ->actingAs($this->usuarioCon(self::PERMISO))
            ->get(route('notas-cargo.movil.edit', $cargo))
            ->assertForbidden();
    }

    public function test_the_ejecutivo_amends_their_own_pending_case(): void
    {
        $ejecutivo = $this->usuarioCon(self::PERMISO);
        $cargo = $this->casoPendiente($ejecutivo);

        $this->actingAs($ejecutivo)->put(route('notas-cargo.movil.update', $cargo), [
            'q_lineas' => 9,
            'cargo_fijo' => '333.00',
            'telefono1' => '911111111',
            'nivel_negociacion' => '100',
            'fecha_estimada' => now()->addWeek()->toDateString(),
            'etapa' => 'F3-NEGOCIACION',
            'comentario_ejecutivo' => 'SUBE LA PROBABILIDAD',
        ])->assertRedirect(route('notas-cargo.movil.index'));

        $fresh = $cargo->fresh();
        $this->assertSame(9, $fresh->q_lineas);
        $this->assertSame('100', $fresh->nivel_negociacion);
        $this->assertSame('SUBE LA PROBABILIDAD', $fresh->comentario_ejecutivo);
    }

    public function test_a_validated_case_is_frozen(): void
    {
        // The validador ruled on these figures. Legacy showed the form anyway and
        // enforced this in JavaScript, so a posted form still went through.
        $ejecutivo = $this->usuarioCon(self::PERMISO);
        $cargo = NcMovil::factory()->deRuc(self::RUC)->create(['id_usuario' => $ejecutivo->id_usuario]);

        $this
            ->actingAs($ejecutivo)
            ->put(route('notas-cargo.movil.update', $cargo), [
                'q_lineas' => 99,
                'cargo_fijo' => '1.00',
                'telefono1' => '911111111',
                'nivel_negociacion' => '25',
                'fecha_estimada' => now()->toDateString(),
                'etapa' => 'F1-PERDIDA',
            ])
            ->assertForbidden();

        $this->assertNotSame(99, $cargo->fresh()->q_lineas);
    }
}
