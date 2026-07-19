<?php

namespace Tests\Feature\Gestion;

use App\Models\Llamada;
use App\Models\Usuario;

// HISTORICO: the agent's own contacts over a date range.
class HistoricoTest extends GestionTestCase
{
    public function test_the_ver_permiso_gates_the_screen(): void
    {
        $this->actingAs(Usuario::factory()->create())->get(route('historico.index'))->assertForbidden();
        $this->actingAs($this->agente('historico.ver'))->get(route('historico.index'))->assertOk();
    }

    public function test_it_shows_only_the_signed_in_agents_contacts(): void
    {
        $agente = $this->agente('historico.ver');
        $otro = $this->agente('historico.ver');

        Llamada::factory()->create([
            'id_usuario' => $agente->id_usuario,
            'fecha' => now(),
            'comentario' => 'CONTACTO PROPIO',
        ]);
        Llamada::factory()->create([
            'id_usuario' => $otro->id_usuario,
            'fecha' => now(),
            'comentario' => 'CONTACTO AJENO',
        ]);

        $this
            ->actingAs($agente)
            ->get(route('historico.index'))
            ->assertOk()
            ->assertSee('CONTACTO PROPIO')
            ->assertDontSee('CONTACTO AJENO');
    }

    public function test_the_range_filters_and_defaults_to_the_last_week(): void
    {
        $agente = $this->agente('historico.ver');

        Llamada::factory()->create([
            'id_usuario' => $agente->id_usuario,
            'fecha' => now()->subDays(2),
            'comentario' => 'RECIENTE',
        ]);
        Llamada::factory()->create([
            'id_usuario' => $agente->id_usuario,
            'fecha' => now()->subMonths(3),
            'comentario' => 'ANTIGUO',
        ]);

        $this
            ->actingAs($agente)
            ->get(route('historico.index'))
            ->assertOk()
            ->assertSee('RECIENTE')
            ->assertDontSee('ANTIGUO');

        $this
            ->actingAs($agente)
            ->get(route('historico.index', [
                'desde' => now()->subMonths(4)->toDateString(),
                'hasta' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('ANTIGUO');
    }

    public function test_an_unparseable_range_falls_back_instead_of_erroring(): void
    {
        $agente = $this->agente('historico.ver');
        Llamada::factory()->create(['id_usuario' => $agente->id_usuario, 'fecha' => now(), 'comentario' => 'VISIBLE']);

        // The dates come from a query string anyone can edit.
        $this
            ->actingAs($agente)
            ->get(route('historico.index', ['desde' => 'no-es-fecha', 'hasta' => '']))
            ->assertOk()
            ->assertSee('VISIBLE');
    }
}
