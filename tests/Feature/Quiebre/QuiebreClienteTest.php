<?php

namespace Tests\Feature\Quiebre;

use App\Cockpit\AccesoAClientes;
use App\Models\Dato;
use App\Models\Quiebre;
use App\Models\Tienda;

// How a quiebre gets the client it belongs to. Legacy let the ejecutivo type the
// RUC and the razón social into the case, so the same RUC could carry different
// names across quiebres and a case could name a client that does not exist.
class QuiebreClienteTest extends QuiebreTestCase
{
    public function test_the_form_appears_only_once_a_ruc_resolves_to_a_client(): void
    {
        $ejecutivo = $this->ejecutivo();
        $dato = Dato::factory()->create(['ruc' => self::RUC, 'razon_social' => 'ACME SAC']);
        AccesoAClientes::otorgar($dato->id_cliente, $ejecutivo);

        $this
            ->actingAs($ejecutivo)
            ->get(route('quiebres.registro.create'))
            ->assertOk();

        $this
            ->actingAs($ejecutivo)
            ->get(route('quiebres.registro.create', ['ruc' => '20000000000']))
            ->assertOk()
            ->assertSee('Cliente no disponible');

        $this
            ->actingAs($ejecutivo)
            ->get(route('quiebres.registro.create', ['ruc' => self::RUC]))
            ->assertOk()
            ->assertSee('ACME SAC');
    }

    public function test_the_client_name_is_never_an_input(): void
    {
        $ejecutivo = $this->ejecutivo();
        $cliente = Dato::factory()->create(['ruc' => '20555555555', 'razon_social' => 'NOMBRE REAL']);
        AccesoAClientes::otorgar($cliente->id_cliente, $ejecutivo);

        // A posted name must be ignored, not stored: the client record owns it.
        $this->actingAs($ejecutivo)->post(
            route('quiebres.registro.store'),
            $this->payload(Tienda::factory()->create(), [
                'id_cliente' => $cliente->id_cliente,
                'razon_social' => 'NOMBRE FALSO',
            ]),
        );

        $quiebre = Quiebre::query()->sole();
        $this->assertSame('NOMBRE REAL', $quiebre->cliente->razon_social);
        $this->assertArrayNotHasKey('razon_social', $quiebre->getAttributes());
    }

    public function test_a_quiebre_cannot_be_filed_against_a_client_that_does_not_exist(): void
    {
        $this
            ->actingAs($this->ejecutivo())
            ->post(
                route('quiebres.registro.store'),
                $this->payload(Tienda::factory()->create(), ['id_cliente' => 999_999]),
            )
            ->assertSessionHasErrors('id_cliente');

        $this->assertDatabaseCount('quiebre', 0);
    }
}
