<?php

namespace Tests\Feature\Gestion;

use App\Models\Dato;
use App\Models\Usuario;

// The off-list door: an agent has a RUC that is not in their queue. Every state
// that RUC can be in is one request, and each one ends somewhere the agent can
// act. Registering a genuinely new customer is AltaClienteTest.
class BusquedaClienteTest extends GestionTestCase
{
    public function test_the_crear_permiso_gates_the_screen(): void
    {
        $this->actingAs(Usuario::factory()->create())->get(route('clientes.create'))->assertForbidden();
        $this->actingAs($this->agente('clientes.crear'))->get(route('clientes.create'))->assertOk();
    }

    public function test_a_ruc_already_in_the_agents_queue_goes_straight_to_gestion(): void
    {
        $agente = $this->agente('clientes.crear', 'gestion.movil');
        $dato = $this->datoDe($agente, 'MOVIL', ['ruc' => '20999999999']);

        $this
            ->actingAs($agente)
            ->get(route('clientes.create', ['ruc' => '20999999999']))
            ->assertRedirect(route('gestion.show', $dato));
    }

    public function test_a_ruc_held_by_another_agent_is_never_offered_for_registration(): void
    {
        $otro = $this->agente('gestion.movil');
        $this->datoDe($otro, 'MOVIL', ['ruc' => '20888888888', 'razon_social' => 'CLIENTE AJENO']);

        $this
            ->actingAs($this->agente('clientes.crear'))
            ->get(route('clientes.create', ['ruc' => '20888888888']))
            ->assertOk()
            ->assertSee('CLIENTE AJENO')
            ->assertSee('Solicite la reasignación')
            ->assertDontSee('name="razon_social"', false);
    }

    public function test_taking_a_pooled_record_assigns_it_once(): void
    {
        $primero = $this->agente('clientes.crear');
        $segundo = $this->agente('clientes.crear');
        $dato = Dato::factory()->create(['ruc' => '20777777777']);

        $this->actingAs($primero)->post(route('clientes.tomar', $dato))->assertRedirect();

        // The second agent must be told, not silently handed a record that now
        // belongs to someone else.
        $this
            ->actingAs($segundo)
            ->post(route('clientes.tomar', $dato))
            ->assertSessionHas('error');

        $this->assertSame($primero->id_usuario, $dato->fresh()->asignacionActiva?->id_usuario);
    }
}
