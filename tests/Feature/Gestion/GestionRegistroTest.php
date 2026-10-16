<?php

namespace Tests\Feature\Gestion;

use App\Cockpit\Operadores;
use App\Models\Dato;
use App\Models\Llamada;

class GestionRegistroTest extends GestionTestCase
{
    public function test_logging_a_contact_appends_the_call_and_advances_the_record(): void
    {
        $agente = $this->agente('gestion.movil');
        $dato = $this->datoDe($agente);

        $this
            ->actingAs($agente)
            ->post(route('gestion.store', $dato), [
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

        $llamada = $dato->cliente->llamadas()->sole();
        $this->assertSame($agente->id_usuario, $llamada->id_usuario);
        $this->assertSame('JUAN PEREZ', $llamada->nombre_contacto);
        $this->assertSame($dato->cliente->razon_social, $llamada->razon_social);
        $this->assertSame('MOVIL', $llamada->tipo_data);

        // The call names both the customer it is about and the campaign record
        // that prompted it, so a contact outlives the campaign without losing
        // the context it was made in.
        $this->assertSame($dato->id_cliente, $llamada->id_cliente);
        $this->assertSame($dato->id_dato, $llamada->id_dato);

        $this->assertSame(Dato::ESTADO_GESTIONADO, $dato->fresh()->estado);
    }

    public function test_the_line_mix_an_agent_confirms_is_recorded_on_the_call(): void
    {
        // The file's counts and the agent's are two observations, not one
        // mutable field: the next load of the campaign must not erase the
        // agent's.
        $agente = $this->agente('gestion.movil');
        $dato = $this->datoDe($agente, 'MOVIL', ['q_movistar' => 1, 'q_entel' => 1]);

        $this
            ->actingAs($agente)
            ->post(route('gestion.store', $dato), [
                'telefono' => '987654321',
                'id_respuesta' => 2,
                'q_movistar' => 4,
                'q_entel' => 7,
                'q_claro' => 2,
                'q_bitel' => 1,
            ])
            ->assertRedirect();

        $llamada = $dato->cliente->llamadas()->sole();
        $this->assertSame(7, $llamada->q_entel);
        $this->assertSame(4, $llamada->q_movistar);

        $dato->refresh();
        $this->assertSame(1, $dato->q_movistar);
        $this->assertSame(1, $dato->q_entel);
    }

    public function test_the_screen_shows_the_last_confirmed_line_mix_over_the_files(): void
    {
        $agente = $this->agente('gestion.movil');
        $dato = $this->datoDe($agente, 'MOVIL', ['q_entel' => 1]);

        $this->actingAs($agente)->post(route('gestion.store', $dato), [
            'telefono' => '987654321',
            'id_respuesta' => 2,
            'q_entel' => 9,
        ]);

        $texto = $this->textoVisible(
            $this->actingAs($agente)->get(route('gestion.show', $dato))->assertOk(),
        );

        $this->assertStringContainsString('Líneas '.Operadores::PROPIO.' 9', $texto);
    }

    public function test_the_channel_comes_from_the_chosen_respuesta(): void
    {
        $agente = $this->agente('gestion.movil');
        $dato = $this->datoDe($agente);

        // id 15 is a VISITA disposition; the form never states the channel.
        $this
            ->actingAs($agente)
            ->post(route('gestion.store', $dato), [
                'telefono' => '987654321',
                'id_respuesta' => 15,
            ])
            ->assertRedirect();

        $this->assertSame('VISITA', $dato->cliente->llamadas()->value('gestion'));
    }

    public function test_an_agent_cannot_work_a_record_held_by_another_agent(): void
    {
        $ajeno = $this->datoDe($this->agente());
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
        $this->assertSame(Dato::ESTADO_PENDIENTE, $ajeno->fresh()->estado);
    }

    public function test_it_rejects_an_unknown_respuesta_and_leaves_the_record_pending(): void
    {
        $agente = $this->agente('gestion.movil');
        $dato = $this->datoDe($agente);

        $this
            ->actingAs($agente)
            ->post(route('gestion.store', $dato), [
                'telefono' => '987654321',
                'id_respuesta' => 9999,
            ])
            ->assertSessionHasErrors('id_respuesta');

        $this->assertSame(0, Llamada::query()->count());
        $this->assertSame(Dato::ESTADO_PENDIENTE, $dato->fresh()->estado);
    }
}
