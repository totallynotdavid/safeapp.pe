<?php

namespace Tests\Feature\Gestion;

use App\Cockpit\AccesoAClientes;
use App\Cockpit\AltaDeClientes;
use App\Models\Cliente;
use App\Models\Dato;

class AltaClienteTest extends GestionTestCase
{
    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function datosValidos(array $overrides = []): array
    {
        return array_merge([
            'ruc' => '20123456789',
            'razon_social' => 'CLIENTE NUEVO SAC',
            'zonal' => 'LIMA',
            'q_entel' => 4,
            'q_claro' => 2,
        ], $overrides);
    }

    public function test_contextual_intake_creates_a_private_movil_record(): void
    {
        $agente = $this->agente('gestion.movil');

        $this
            ->actingAs($agente)
            ->post(route('gestion.movil.agregar.store'), $this->datosValidos(['tipo_data' => 'FIJA']))
            ->assertRedirect();

        $dato = Dato::query()->with(['cliente', 'asignacionActiva'])->sole();
        $this->assertSame('MOVIL', $dato->tipo_data);
        $this->assertSame(AltaDeClientes::ORIGEN, $dato->origen);
        $this->assertSame(Dato::ESTADO_PENDIENTE, $dato->estado);
        $this->assertSame($agente->id_usuario, $dato->asignacionActiva?->id_usuario);
        $this->assertDatabaseHas('cliente_acceso', [
            'id_cliente' => $dato->id_cliente,
            'id_usuario' => $agente->id_usuario,
        ]);
    }

    public function test_intake_is_gated_by_the_route_owned_segment(): void
    {
        $agente = $this->agente('gestion.movil');

        $this->actingAs($agente)->get(route('gestion.fija.agregar'))->assertForbidden();
        $this->actingAs($agente)->post(route('gestion.fija.agregar.store'), $this->datosValidos())->assertForbidden();
    }

    public function test_posted_segment_is_not_part_of_the_intake_contract(): void
    {
        $agente = $this->agente('gestion.fija');

        $this
            ->actingAs($agente)
            ->post(route('gestion.fija.agregar.store'), $this->datosValidos(['tipo_data' => 'MOVIL']))
            ->assertRedirect();

        $this->assertSame('FIJA', Dato::query()->sole()->tipo_data);
    }

    public function test_manual_intake_cannot_claim_an_inaccessible_identity(): void
    {
        $otro = $this->agente('gestion.movil');
        $cliente = Cliente::factory()->create(['ruc' => '20999999999', 'razon_social' => 'IDENTIDAD PRIVADA']);
        AccesoAClientes::otorgar($cliente, $otro);
        $agente = $this->agente('gestion.movil');

        $this
            ->actingAs($agente)
            ->post(
                route('gestion.movil.agregar.store'),
                $this->datosValidos([
                    'ruc' => $cliente->ruc,
                    'razon_social' => 'NOMBRE ENVIADO',
                ]),
            )
            ->assertSessionHas('error', 'El cliente no está disponible para esta gestión.');

        $this->assertDatabaseCount('datos', 0);
        $this->assertDatabaseMissing('cliente_acceso', [
            'id_cliente' => $cliente->id_cliente,
            'id_usuario' => $agente->id_usuario,
        ]);
    }
}
