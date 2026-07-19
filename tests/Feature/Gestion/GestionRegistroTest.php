<?php

namespace Tests\Feature\Gestion;

use App\Models\Cliente;
use App\Models\Llamada;

class GestionRegistroTest extends GestionTestCase
{
    public function test_logging_a_contact_appends_the_call_and_advances_the_client(): void
    {
        $agente = $this->agente('gestion.movil');
        $cliente = $this->clienteDe($agente);

        $this
            ->actingAs($agente)
            ->post(route('gestion.store', $cliente), [
                'telefono' => '987654321',
                'id_respuesta' => 2,
                'nombre_contacto' => 'JUAN PEREZ',
                'correo' => 'juan@example.com',
                'comentario' => 'Interesado',
                'q_movistar' => 3,
                'q_entel' => 5,
                'q_claro' => 1,
                'q_bitel' => 0,
            ])
            ->assertRedirect(route('gestion.movil'));

        $llamada = Llamada::query()->where('ruc', $cliente->ruc)->sole();
        $this->assertSame($agente->id_usuario, $llamada->id_usuario);
        $this->assertSame('JUAN PEREZ', $llamada->nombre_contacto);
        $this->assertSame($cliente->razon_social, $llamada->razon_social);
        $this->assertSame('MOVIL', $llamada->tipo_data);

        // The queue and the log move together.
        $this->assertSame(Cliente::ESTADO_GESTIONADO, $cliente->fresh()->estado);
    }

    public function test_the_contact_refreshes_the_clients_line_mix(): void
    {
        $agente = $this->agente('gestion.movil');
        $cliente = $this->clienteDe($agente);

        $this
            ->actingAs($agente)
            ->post(route('gestion.store', $cliente), [
                'telefono' => '987654321',
                'id_respuesta' => 2,
                'q_movistar' => 4,
                'q_entel' => 7,
                'q_claro' => 2,
                'q_bitel' => 1,
            ])
            ->assertRedirect();

        $cliente = $cliente->fresh();
        $this->assertSame(7, $cliente->lineasPropias());
        $this->assertSame(7, $cliente->lineasCompetencia());
    }

    public function test_the_channel_comes_from_the_chosen_respuesta(): void
    {
        $agente = $this->agente('gestion.movil');
        $cliente = $this->clienteDe($agente);

        // id 15 is a VISITA disposition; the form never states the channel.
        $this
            ->actingAs($agente)
            ->post(route('gestion.store', $cliente), [
                'telefono' => '987654321',
                'id_respuesta' => 15,
            ])
            ->assertRedirect();

        $this->assertSame('VISITA', Llamada::query()->where('ruc', $cliente->ruc)->value('gestion'));
    }

    public function test_an_agent_cannot_work_another_agents_client(): void
    {
        $ajeno = $this->clienteDe($this->agente());
        $agente = $this->agente('gestion.movil');

        $this->actingAs($agente)->get(route('gestion.show', $ajeno))->assertForbidden();
        $this
            ->actingAs($agente)
            ->post(route('gestion.store', $ajeno), [
                'telefono' => '987654321',
                'id_respuesta' => 2,
            ])
            ->assertForbidden();

        $this->assertSame(0, Llamada::query()->count());
        $this->assertSame(Cliente::ESTADO_PENDIENTE, $ajeno->fresh()->estado);
    }

    public function test_it_rejects_an_unknown_respuesta_and_leaves_the_client_pending(): void
    {
        $agente = $this->agente('gestion.movil');
        $cliente = $this->clienteDe($agente);

        $this
            ->actingAs($agente)
            ->post(route('gestion.store', $cliente), [
                'telefono' => '987654321',
                'id_respuesta' => 9999,
            ])
            ->assertSessionHasErrors('id_respuesta');

        $this->assertSame(0, Llamada::query()->count());
        $this->assertSame(Cliente::ESTADO_PENDIENTE, $cliente->fresh()->estado);
    }
}
