<?php

namespace Tests\Feature\Gestion;

use App\Cockpit\Operadores;
use App\Models\Llamada;

class GestionPanelesTest extends GestionTestCase
{
    public function test_the_history_shows_every_contact_newest_first(): void
    {
        $agente = $this->agente('gestion.movil');
        $dato = $this->datoDe($agente);

        Llamada::factory()->create([
            'id_cliente' => $dato->id_cliente,
            'id_usuario' => $agente->id_usuario,
            'fecha' => '2026-01-01 09:00:00',
            'comentario' => 'PRIMER CONTACTO',
        ]);
        Llamada::factory()->create([
            'id_cliente' => $dato->id_cliente,
            'id_usuario' => $agente->id_usuario,
            'fecha' => '2026-02-01 09:00:00',
            'comentario' => 'SEGUNDO CONTACTO',
        ]);

        $this
            ->actingAs($agente)
            ->get(route('gestion.show', $dato))
            ->assertOk()
            ->assertSeeInOrder(['SEGUNDO CONTACTO', 'PRIMER CONTACTO']);
    }

    public function test_the_history_is_limited_to_this_client(): void
    {
        $agente = $this->agente('gestion.movil');
        $dato = $this->datoDe($agente);
        $otro = $this->datoDe($agente);

        Llamada::factory()->create(['id_cliente' => $otro->id_cliente, 'comentario' => 'CONTACTO AJENO']);

        $this
            ->actingAs($agente)
            ->get(route('gestion.show', $dato))
            ->assertOk()
            ->assertDontSee('CONTACTO AJENO');
    }

    public function test_history_and_phones_never_show_another_executives_work(): void
    {
        $agente = $this->agente('gestion.movil');
        $otro = $this->agente('gestion.movil');
        $dato = $this->datoDe($agente);

        Llamada::factory()->create([
            'id_cliente' => $dato->id_cliente,
            'id_usuario' => $agente->id_usuario,
            'telefono' => '911111111',
            'comentario' => 'CONTACTO PROPIO',
        ]);
        Llamada::factory()->create([
            'id_cliente' => $dato->id_cliente,
            'id_usuario' => $otro->id_usuario,
            'telefono' => '922222222',
            'comentario' => 'CONTACTO PRIVADO',
        ]);

        $respuesta = $this->actingAs($agente)->get(route('gestion.show', $dato))->assertOk();

        $respuesta->assertSee('CONTACTO PROPIO')->assertDontSee('CONTACTO PRIVADO');
        $this->assertStringContainsString('911111111', $respuesta->getContent());
        $this->assertStringNotContainsString('922222222', $respuesta->getContent());
    }

    public function test_known_phones_are_derived_from_the_contacts(): void
    {
        $agente = $this->agente('gestion.movil');
        $dato = $this->datoDe($agente);

        Llamada::factory()->count(2)->create(['id_cliente' => $dato->id_cliente, 'telefono' => '987654321']);
        Llamada::factory()->create(['id_cliente' => $dato->id_cliente, 'telefono' => '912345678']);

        $telefonos = $dato->cliente->telefonosConocidos();

        $this->assertCount(2, $telefonos);
        $this->assertSame(2, (int) $telefonos->firstWhere('telefono', '987654321')->veces);
        $this->assertSame(1, (int) $telefonos->firstWhere('telefono', '912345678')->veces);
    }

    public function test_logging_a_contact_makes_its_phone_known(): void
    {
        $agente = $this->agente('gestion.movil');
        $dato = $this->datoDe($agente);

        $this->assertCount(0, $dato->cliente->telefonosConocidos());

        $this
            ->actingAs($agente)
            ->post(route('gestion.store', $dato), [
                'telefono' => '987654321',
                'id_respuesta' => 2,
            ])
            ->assertRedirect();

        $this->assertSame('987654321', $dato->cliente->telefonosConocidos()->first()->telefono);
    }

    /**
     * Phones follow the customer, not the campaign. A number learned while
     * working one list is still the number to call when a later list delivers
     * the same customer, which is the whole reason identity is its own table.
     */
    public function test_known_phones_span_every_campaign_the_customer_appears_in(): void
    {
        $agente = $this->agente('gestion.movil');
        $anterior = $this->datoDe($agente);
        Llamada::factory()->create(['id_cliente' => $anterior->id_cliente, 'telefono' => '987654321']);

        $nuevo = $this->datoDe($agente, 'MOVIL', ['id_cliente' => $anterior->id_cliente, 'origen' => 'CAMPAÑA NUEVA']);

        $this->assertSame('987654321', $nuevo->cliente->telefonosConocidos()->sole()->telefono);
    }

    public function test_the_record_panel_lists_every_operator_line_count(): void
    {
        $lineas = ['MOVISTAR' => 3, 'ENTEL' => 5, 'CLARO' => 2, 'BITEL' => 1];

        $agente = $this->agente('gestion.movil');
        $dato = $this->datoDe($agente, 'MOVIL', [
            'q_movistar' => $lineas['MOVISTAR'],
            'q_entel' => $lineas['ENTEL'],
            'q_claro' => $lineas['CLARO'],
            'q_bitel' => $lineas['BITEL'],
        ]);

        $texto = $this->textoVisible(
            $this->actingAs($agente)->get(route('gestion.show', $dato))->assertOk(),
        );

        // Nobody has called yet, so the file's counts are what the screen reads.
        foreach (Operadores::TODOS as $operador) {
            $this->assertStringContainsString($operador.' '.$lineas[$operador], $texto);
        }

        // And the same numbers again as the split the agent actually negotiates
        // against: ours, and everyone else's added up.
        $this->assertStringContainsString('Líneas '.Operadores::PROPIO.' 5', $texto);
        $this->assertStringContainsString('Líneas competencia 6', $texto);
    }
}
