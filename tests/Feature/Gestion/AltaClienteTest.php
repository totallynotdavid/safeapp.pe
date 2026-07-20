<?php

namespace Tests\Feature\Gestion;

use App\Cockpit\AltaDeClientes;
use App\Models\Cliente;
use App\Models\Dato;

// Registering a customer the import never delivered. Resolving a RUC that is
// already known belongs to BusquedaClienteTest.
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

    public function test_registering_creates_a_pendiente_record_held_by_the_agent(): void
    {
        $agente = $this->agente('clientes.crear', 'gestion.movil');

        $this
            ->actingAs($agente)
            ->post(route('clientes.store'), $this->datosValidos())
            ->assertRedirect();

        $dato = $this->datoRegistrado();

        $this->assertSame($agente->id_usuario, $dato->asignacionActiva?->id_usuario);
        $this->assertSame($this->tienda->id_tienda, $dato->asignacionActiva?->id_tienda);
        // Registering is not contacting: the first contact is logged on the
        // record's own screen, which is what advances the estado.
        $this->assertSame(Dato::ESTADO_PENDIENTE, $dato->estado);
        $this->assertSame(4, $dato->q_entel);
        $this->assertSame('CLIENTE NUEVO SAC', $dato->cliente->razon_social);
    }

    public function test_provenance_is_recorded_in_origen_and_never_in_the_segment(): void
    {
        $agente = $this->agente('clientes.crear');

        $this->actingAs($agente)->post(route('clientes.store'), $this->datosValidos(['tipo_data' => 'FIJA']));

        $dato = $this->datoRegistrado();

        $this->assertSame(AltaDeClientes::ORIGEN, $dato->origen);
        // The regression that stranded 2,677 legacy clients: provenance written
        // into the column the queue filters on.
        $this->assertSame('FIJA', $dato->tipo_data);
        $this->assertContains($dato->tipo_data, Dato::TIPOS_DATA);
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

    /**
     * Registering the same RUC twice is a double submit, not an error to show
     * an agent. It resolves to the record they already hold rather than
     * colliding with the natural key on `datos`.
     */
    public function test_registering_the_same_ruc_twice_returns_the_same_record(): void
    {
        $agente = $this->agente('clientes.crear');

        $this->actingAs($agente)->post(route('clientes.store'), $this->datosValidos());
        $this->actingAs($agente)->post(route('clientes.store'), $this->datosValidos())->assertSessionHasNoErrors();

        $this->assertDatabaseCount('cliente', 1);
        $this->assertDatabaseCount('datos', 1);
        $this->assertDatabaseCount('asignacion_datos', 1);
    }

    /**
     * The customer exists but no campaign ever delivered them, which is the
     * normal state for someone first met through a charge note or an avería.
     * The old unique-RUC rule made this unregisterable.
     */
    public function test_a_customer_known_only_from_another_workflow_can_be_registered(): void
    {
        $agente = $this->agente('clientes.crear');
        Cliente::resolver('20123456789', 'CLIENTE DE NOTA DE CARGO');

        $this
            ->actingAs($agente)
            ->post(route('clientes.store'), $this->datosValidos())
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('cliente', 1);
        $this->assertSame($agente->id_usuario, $this->datoRegistrado()->asignacionActiva?->id_usuario);
        // Identity already on file is never overwritten by a later mention.
        $this->assertSame('CLIENTE DE NOTA DE CARGO', Cliente::porRuc('20123456789')?->razon_social);
    }

    /**
     * Two segments of the same customer are two jobs, so both are workable and
     * neither displaces the other.
     */
    public function test_the_same_customer_can_be_registered_in_both_segments(): void
    {
        $agente = $this->agente('clientes.crear');

        $this->actingAs($agente)->post(route('clientes.store'), $this->datosValidos());
        $this->actingAs($agente)->post(route('clientes.store'), $this->datosValidos(['tipo_data' => 'FIJA']));

        $this->assertDatabaseCount('cliente', 1);
        $this->assertDatabaseCount('datos', 2);
    }

    private function datoRegistrado(): Dato
    {
        return Cliente::query()
            ->where('ruc', '20123456789')
            ->firstOrFail()
            ->datos()
            ->with(['asignacionActiva', 'cliente'])
            ->sole();
    }
}
