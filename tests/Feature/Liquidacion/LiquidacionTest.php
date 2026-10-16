<?php

namespace Tests\Feature\Liquidacion;

class LiquidacionTest extends LiquidacionTestCase
{
    public function test_settles_a_case(): void
    {
        $cargo = $this->enCola(null, ['estado' => 'ACTIVADO']);

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
        // estado belongs to the back-office stage. The outcome the liquidador
        // records goes in observacion.
        $this->assertSame('ACTIVADO', $fresh->estado);
    }

    public function test_rejects_an_invalid_observacion(): void
    {
        $cargo = $this->enCola();

        $this->actingAs($this->liquidador())->put(route('liquidaciones.update', $cargo), [
            'comision' => '100',
            'observacion' => 'INVENTADO',
            'descuento' => '0',
            'fecha_liquidacion' => now()->toDateString(),
        ])->assertSessionHasErrors('observacion');
    }

    public function test_requires_the_settlement_fields(): void
    {
        $cargo = $this->enCola();

        $this
            ->actingAs($this->liquidador())
            ->put(route('liquidaciones.update', $cargo), [])
            ->assertSessionHasErrors(['comision', 'observacion', 'descuento', 'fecha_liquidacion']);
    }
}
