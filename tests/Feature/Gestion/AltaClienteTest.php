<?php

namespace Tests\Feature\Gestion;

use App\Cockpit\AltaDeClientes;
use App\Models\Cliente;
use App\Models\Usuario;

// The off-list door: resolving a RUC, and registering a client the import never
// delivered.
class AltaClienteTest extends GestionTestCase
{
    /**
     * @return array<string, mixed>
     */
    private function datosValidos(array $overrides = []): array
    {
        return array_merge([
            'ruc' => '20123456789',
            'razon_social' => 'CLIENTE NUEVO SAC',
            'tipo_data' => 'MOVIL',
            'zonal' => 'LIMA',
            'q_entel' => 4,
            'q_claro' => 2,
        ], $overrides);
    }

    public function test_the_crear_permiso_gates_the_screen(): void
    {
        $this->actingAs(Usuario::factory()->create())->get(route('clientes.create'))->assertForbidden();
        $this->actingAs($this->agente('clientes.crear'))->get(route('clientes.create'))->assertOk();
    }

    public function test_a_ruc_already_in_the_agents_queue_goes_straight_to_gestion(): void
    {
        $agente = $this->agente('clientes.crear', 'gestion.movil');
        $cliente = $this->clienteDe($agente, 'MOVIL', ['ruc' => '20999999999']);

        $this
            ->actingAs($agente)
            ->get(route('clientes.create', ['ruc' => '20999999999']))
            ->assertRedirect(route('gestion.show', $cliente));
    }

    public function test_a_ruc_held_by_another_agent_is_never_offered_for_registration(): void
    {
        $otro = $this->agente('gestion.movil');
        $this->clienteDe($otro, 'MOVIL', ['ruc' => '20888888888', 'razon_social' => 'CLIENTE AJENO']);

        $this
            ->actingAs($this->agente('clientes.crear'))
            ->get(route('clientes.create', ['ruc' => '20888888888']))
            ->assertOk()
            ->assertSee('CLIENTE AJENO')
            ->assertSee('Solicite la reasignación')
            ->assertDontSee('name="razon_social"', false);
    }

    public function test_registering_creates_a_pendiente_client_owned_by_the_agent(): void
    {
        $agente = $this->agente('clientes.crear', 'gestion.movil');

        $this
            ->actingAs($agente)
            ->post(route('clientes.store'), $this->datosValidos())
            ->assertRedirect();

        $cliente = Cliente::query()->where('ruc', '20123456789')->firstOrFail();

        $this->assertSame($agente->id_usuario, $cliente->id_usuario);
        $this->assertSame($this->tienda->id_tienda, $cliente->id_tienda);
        // Registering is not contacting: the first contact is logged on the
        // client's own screen, which is what advances the estado.
        $this->assertSame(Cliente::ESTADO_PENDIENTE, $cliente->estado);
        $this->assertSame(4, $cliente->q_entel);
    }

    public function test_provenance_is_recorded_in_origen_and_never_in_the_segment(): void
    {
        $agente = $this->agente('clientes.crear');

        $this->actingAs($agente)->post(route('clientes.store'), $this->datosValidos(['tipo_data' => 'FIJA']));

        $cliente = Cliente::query()->where('ruc', '20123456789')->firstOrFail();

        $this->assertSame(AltaDeClientes::ORIGEN, $cliente->origen);
        // The regression that stranded 2,677 legacy clients: provenance written
        // into the column the queue filters on.
        $this->assertSame('FIJA', $cliente->tipo_data);
        $this->assertContains($cliente->tipo_data, Cliente::TIPOS_DATA);
    }

    public function test_a_segment_without_a_queue_is_rejected(): void
    {
        $this
            ->actingAs($this->agente('clientes.crear'))
            ->post(route('clientes.store'), $this->datosValidos(['tipo_data' => 'CONVERGENTE']))
            ->assertSessionHasErrors('tipo_data');

        $this->assertDatabaseCount('datos', 0);
    }

    public function test_the_segment_is_required(): void
    {
        $datos = $this->datosValidos();
        unset($datos['tipo_data']);

        $this
            ->actingAs($this->agente('clientes.crear'))
            ->post(route('clientes.store'), $datos)
            ->assertSessionHasErrors('tipo_data');
    }

    public function test_a_duplicate_ruc_is_rejected(): void
    {
        $agente = $this->agente('clientes.crear');
        $this->clienteDe($agente, 'MOVIL', ['ruc' => '20123456789']);

        $this
            ->actingAs($agente)
            ->post(route('clientes.store'), $this->datosValidos())
            ->assertSessionHasErrors('ruc');

        $this->assertDatabaseCount('datos', 1);
    }

    public function test_taking_a_pooled_client_assigns_it_once(): void
    {
        $primero = $this->agente('clientes.crear');
        $segundo = $this->agente('clientes.crear');
        $cliente = Cliente::factory()->create(['ruc' => '20777777777']);

        $this->actingAs($primero)->post(route('clientes.tomar', $cliente))->assertRedirect();

        // The second agent must be told, not silently handed a client that now
        // belongs to someone else.
        $this
            ->actingAs($segundo)
            ->post(route('clientes.tomar', $cliente))
            ->assertSessionHas('error');

        $this->assertSame($primero->id_usuario, $cliente->fresh()->id_usuario);
    }
}
