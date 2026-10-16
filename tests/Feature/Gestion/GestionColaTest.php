<?php

namespace Tests\Feature\Gestion;

use App\Models\Dato;
use App\Models\Tienda;

class GestionColaTest extends GestionTestCase
{
    public function test_each_data_segment_is_gated_by_its_own_permiso(): void
    {
        $this->actingAs($this->agente())->get(route('gestion.movil'))->assertForbidden();

        $soloMovil = $this->agente('gestion.movil');

        $this->actingAs($soloMovil)->get(route('gestion.movil'))->assertOk();
        $this->actingAs($soloMovil)->get(route('gestion.fija'))->assertForbidden();
    }

    public function test_the_queue_lists_only_my_pending_clients_of_that_segment(): void
    {
        $agente = $this->agente('gestion.movil');
        $this->datoDe($agente, 'MOVIL', ['razon_social' => 'CLIENTE EN COLA']);

        Dato::factory()
            ->gestionado()
            ->asignadoA($agente->id_usuario, $this->tienda->id_tienda)
            ->create(['razon_social' => 'CLIENTE GESTIONADO']);
        $this->datoDe($agente, 'FIJA', ['razon_social' => 'CLIENTE FIJA']);
        Dato::factory()
            ->asignadoA($this->agente()->id_usuario, $this->tienda->id_tienda)
            ->create(['razon_social' => 'CLIENTE DE OTRO']);

        $this
            ->actingAs($agente)
            ->get(route('gestion.movil'))
            ->assertOk()
            ->assertSee('CLIENTE EN COLA')
            ->assertDontSee('CLIENTE GESTIONADO')
            ->assertDontSee('CLIENTE FIJA')
            ->assertDontSee('CLIENTE DE OTRO');
    }

    public function test_the_queue_is_scoped_to_the_agents_active_tienda(): void
    {
        $agente = $this->agente('gestion.movil');
        $otraTienda = Tienda::factory()->createOne();
        Dato::factory()
            ->asignadoA($agente->id_usuario, $otraTienda->id_tienda)
            ->create(['razon_social' => 'CLIENTE DE OTRA TIENDA']);

        $this
            ->actingAs($agente)
            ->get(route('gestion.movil'))
            ->assertOk()
            ->assertDontSee('CLIENTE DE OTRA TIENDA');
    }
}
