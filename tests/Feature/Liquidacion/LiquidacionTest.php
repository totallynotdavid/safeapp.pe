<?php

namespace Tests\Feature\Liquidacion;

use PHPUnit\Framework\Attributes\DataProvider;

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

    /**
     * The queue lists only validated, settleable cases, but the id in the URL
     * is not trusted: a case outside the queue is refused on both verbs.
     *
     * @return array<string, array{array<string, mixed>}>
     */
    public static function casesOutsideTheQueue(): array
    {
        return [
            'pending validation' => [['validacion' => 'PENDIENTE']],
            'rejected validation' => [['validacion' => 'RECHAZADO']],
            'fallen' => [['estado' => 'CAIDA']],
            'adr' => [['estado' => 'ADR']],
        ];
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    #[DataProvider('casesOutsideTheQueue')]
    public function test_refuses_a_case_outside_the_queue(array $overrides): void
    {
        $cargo = $this->enCola(null, $overrides);
        $user = $this->liquidador();

        $this->actingAs($user)->get(route('liquidaciones.edit', $cargo))->assertForbidden();
        $this->actingAs($user)->put(route('liquidaciones.update', $cargo), [
            'comision' => '150.00',
            'observacion' => 'DEUDA',
            'descuento' => '20',
            'fecha_liquidacion' => now()->toDateString(),
        ])->assertForbidden();

        $this->assertNull($cargo->fresh()->comision);
    }
}
