<?php

namespace Tests\Feature\NotaCargoFija;

use App\Models\NcFija;

class ColaRegistroFijaTest extends NotaCargoFijaTestCase
{
    private const PERMISO = 'notacargo.fija.registrar';

    public function test_it_shows_only_the_signed_in_ejecutivos_cases(): void
    {
        $ejecutivo = $this->usuarioCon(self::PERMISO);
        $otro = $this->usuarioCon(self::PERMISO);

        NcFija::factory()->registrada()->deRuc('20111111111')->create(['id_usuario' => $ejecutivo->id_usuario]);
        NcFija::factory()->registrada()->deRuc('20999999999')->create(['id_usuario' => $otro->id_usuario]);

        $this
            ->actingAs($ejecutivo)
            ->get(route('notas-cargo.fija.index'))
            ->assertOk()
            ->assertSee('20111111111')
            ->assertDontSee('20999999999');
    }

    public function test_a_rejected_case_leaves_the_queue(): void
    {
        $ejecutivo = $this->usuarioCon(self::PERMISO);

        NcFija::factory()->registrada()->deRuc('20111111111')->create(['id_usuario' => $ejecutivo->id_usuario]);
        NcFija::factory()->rechazada()->deRuc('20999999999')->create(['id_usuario' => $ejecutivo->id_usuario]);

        $this
            ->actingAs($ejecutivo)
            ->get(route('notas-cargo.fija.index'))
            ->assertOk()
            ->assertSee('20111111111')
            ->assertDontSee('20999999999');
    }

    public function test_an_open_case_filed_in_an_earlier_year_is_still_on_the_queue(): void
    {
        // In-flight cases are bounded by fecha_ingreso <= the chosen year, so an
        // agent sees the still-open cases they filed in an earlier one.
        $ejecutivo = $this->usuarioCon(self::PERMISO);

        NcFija::factory()
            ->registrada()
            ->deRuc('20111111111')
            ->create([
                'id_usuario' => $ejecutivo->id_usuario,
                'fecha_ingreso' => now()->subYears(2),
            ]);

        $this
            ->actingAs($ejecutivo)
            ->get(route('notas-cargo.fija.index'))
            ->assertOk()
            ->assertSee('20111111111');
    }

    public function test_a_completed_case_is_listed_in_the_month_it_settled(): void
    {
        // A closed case is windowed by month, and for COMPLETADO the month is
        // fecha_liquidacion: the month it settled, not the month it was filed.
        $ejecutivo = $this->usuarioCon(self::PERMISO);
        $cargo = ['id_usuario' => $ejecutivo->id_usuario, 'fecha_ingreso' => now()->subMonths(4)];

        NcFija::factory()
            ->deRuc('20111111111')
            ->create($cargo + ['fecha_liquidacion' => now()]);
        NcFija::factory()
            ->deRuc('20999999999')
            ->create($cargo + ['fecha_liquidacion' => now()->subMonths(3)]);

        $this
            ->actingAs($ejecutivo)
            ->get(route('notas-cargo.fija.index'))
            ->assertOk()
            ->assertSee('20111111111')
            ->assertDontSee('20999999999');
    }

    public function test_another_ejecutivo_cannot_open_a_case(): void
    {
        // The detail endpoint is scoped like the list, not trusted on the id.
        $cargo = $this->casoPendiente($this->usuarioCon(self::PERMISO));

        $this
            ->actingAs($this->usuarioCon(self::PERMISO))
            ->get(route('notas-cargo.fija.edit', $cargo))
            ->assertForbidden();
    }

    public function test_the_ejecutivo_amends_their_own_pending_case(): void
    {
        $ejecutivo = $this->usuarioCon(self::PERMISO);
        $cargo = $this->casoPendiente($ejecutivo);

        $this->actingAs($ejecutivo)->put(route('notas-cargo.fija.update', $cargo), [
            'contacto' => 'JOSE RAMOS',
            'telefono1' => '911111111',
            'direccion' => 'JR. LIMA 500',
            'tecnologia' => 'HFC',
            'comentario' => 'CAMBIO DE DIRECCION',
        ])->assertRedirect(route('notas-cargo.fija.index'));

        $fresh = $cargo->fresh();
        $this->assertSame('JR. LIMA 500', $fresh->direccion);
        $this->assertSame('HFC', $fresh->tecnologia);
        $this->assertSame('CAMBIO DE DIRECCION', $fresh->comentario);
    }

    public function test_a_validated_case_is_frozen(): void
    {
        // The validador ruled on this case, so a posted form is refused too.
        $ejecutivo = $this->usuarioCon(self::PERMISO);
        $cargo = NcFija::factory()->deRuc(self::RUC)->create(['id_usuario' => $ejecutivo->id_usuario]);

        $this
            ->actingAs($ejecutivo)
            ->put(route('notas-cargo.fija.update', $cargo), [
                'contacto' => 'NO DEBERIA PASAR',
                'telefono1' => '911111111',
                'direccion' => 'JR. LIMA 500',
                'tecnologia' => 'HFC',
            ])
            ->assertForbidden();

        $this->assertNotSame('NO DEBERIA PASAR', $cargo->fresh()->contacto);
    }
}
