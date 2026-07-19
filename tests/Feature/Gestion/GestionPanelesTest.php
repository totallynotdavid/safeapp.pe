<?php

namespace Tests\Feature\Gestion;

use App\Cockpit\Operadores;
use App\Models\Llamada;

// The read panels on the client screen: contact history, the phone numbers
// derived from it, and the per-carrier line mix.
class GestionPanelesTest extends GestionTestCase
{
    public function test_the_history_shows_every_contact_newest_first(): void
    {
        $agente = $this->agente('gestion.movil');
        $cliente = $this->clienteDe($agente);

        Llamada::factory()->create([
            'ruc' => $cliente->ruc,
            'id_usuario' => $agente->id_usuario,
            'fecha' => '2026-01-01 09:00:00',
            'comentario' => 'PRIMER CONTACTO',
        ]);
        Llamada::factory()->create([
            'ruc' => $cliente->ruc,
            'id_usuario' => $agente->id_usuario,
            'fecha' => '2026-02-01 09:00:00',
            'comentario' => 'SEGUNDO CONTACTO',
        ]);

        $this
            ->actingAs($agente)
            ->get(route('gestion.show', $cliente))
            ->assertOk()
            ->assertSeeInOrder(['SEGUNDO CONTACTO', 'PRIMER CONTACTO']);
    }

    public function test_the_history_is_limited_to_this_client(): void
    {
        $agente = $this->agente('gestion.movil');
        $cliente = $this->clienteDe($agente);
        $otro = $this->clienteDe($agente);

        Llamada::factory()->create(['ruc' => $otro->ruc, 'comentario' => 'CONTACTO AJENO']);

        $this
            ->actingAs($agente)
            ->get(route('gestion.show', $cliente))
            ->assertOk()
            ->assertDontSee('CONTACTO AJENO');
    }

    public function test_known_phones_are_derived_from_the_contacts(): void
    {
        $agente = $this->agente('gestion.movil');
        $cliente = $this->clienteDe($agente);

        // Repeats of one number collapse into a single row with a count.
        Llamada::factory()->count(2)->create(['ruc' => $cliente->ruc, 'telefono' => '987654321']);
        Llamada::factory()->create(['ruc' => $cliente->ruc, 'telefono' => '912345678']);

        $telefonos = $cliente->telefonosConocidos();

        $this->assertCount(2, $telefonos);
        $this->assertSame(2, (int) $telefonos->firstWhere('telefono', '987654321')->veces);
        $this->assertSame(1, (int) $telefonos->firstWhere('telefono', '912345678')->veces);
    }

    public function test_logging_a_contact_makes_its_phone_known(): void
    {
        $agente = $this->agente('gestion.movil');
        $cliente = $this->clienteDe($agente);

        $this->assertCount(0, $cliente->telefonosConocidos());

        $this
            ->actingAs($agente)
            ->post(route('gestion.store', $cliente), [
                'telefono' => '987654321',
                'id_respuesta' => 2,
            ])
            ->assertRedirect();

        $this->assertSame('987654321', $cliente->telefonosConocidos()->first()->telefono);
    }

    public function test_the_client_panel_lists_every_operator_line_count(): void
    {
        $agente = $this->agente('gestion.movil');
        $cliente = $this->clienteDe($agente, 'MOVIL', [
            'q_movistar' => 3,
            'q_entel' => 5,
            'q_claro' => 2,
            'q_bitel' => 1,
        ]);

        $respuesta = $this->actingAs($agente)->get(route('gestion.show', $cliente))->assertOk();

        foreach (Operadores::TODOS as $operador) {
            $respuesta->assertSee($operador);
        }

        $this->assertSame(5, $cliente->lineasPropias());
        $this->assertSame(6, $cliente->lineasCompetencia());
    }
}
