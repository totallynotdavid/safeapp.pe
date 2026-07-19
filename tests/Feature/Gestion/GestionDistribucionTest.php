<?php

namespace Tests\Feature\Gestion;

use App\Cockpit\DistribucionDeClientes;
use App\Models\Cliente;
use App\Models\Menu;
use App\Models\Usuario;

class GestionDistribucionTest extends GestionTestCase
{
    public function test_the_queue_offers_each_origen_with_its_available_stock(): void
    {
        Cliente::factory()->count(3)->create(['origen' => 'DB_UNO']);
        Cliente::factory()->count(1)->create(['origen' => 'DB_DOS']);
        // Neither of these is available: one is taken, the other is another segment.
        Cliente::factory()->create(['origen' => 'DB_TRES', 'id_usuario' => 99]);
        Cliente::factory()->tipoData('FIJA')->create(['origen' => 'DB_CUATRO']);

        $this
            ->actingAs($this->agente('gestion.movil'))
            ->get(route('gestion.movil'))
            ->assertOk()
            ->assertSee('DB_UNO (3 disponibles)')
            ->assertSee('DB_DOS (1 disponibles)')
            ->assertDontSee('DB_TRES')
            ->assertDontSee('DB_CUATRO');
    }

    public function test_assigning_a_batch_moves_clients_into_the_agents_queue(): void
    {
        $agente = $this->agente('gestion.movil');
        Cliente::factory()->count(5)->create(['origen' => 'DB_UNO']);

        $this
            ->actingAs($agente)
            ->post(route('gestion.movil.asignar'), ['origen' => 'DB_UNO'])
            ->assertRedirect();

        $mios = Cliente::query()->where('id_usuario', $agente->id_usuario)->get();
        $this->assertCount(5, $mios);
        // The queue is scoped by tienda, so the batch has to carry it.
        $this->assertTrue($mios->every(fn (Cliente $c): bool => $c->id_tienda === $this->tienda->id_tienda));
        $this->assertTrue($mios->every(static fn (Cliente $c): bool => $c->fecha_asignacion !== null));
    }

    public function test_a_batch_is_capped_and_never_crosses_segments(): void
    {
        $agente = $this->agente('gestion.movil');
        Cliente::factory()
            ->count(DistribucionDeClientes::LOTE + 10)
            ->create(['origen' => 'DB_UNO']);
        Cliente::factory()->count(4)->tipoData('FIJA')->create(['origen' => 'DB_UNO']);

        $this
            ->actingAs($agente)
            ->post(route('gestion.movil.asignar'), ['origen' => 'DB_UNO'])
            ->assertRedirect();

        $mios = Cliente::query()->where('id_usuario', $agente->id_usuario)->get();
        $this->assertCount(DistribucionDeClientes::LOTE, $mios);
        $this->assertTrue($mios->every(static fn (Cliente $c): bool => $c->tipo_data === 'MOVIL'));
    }

    public function test_two_agents_never_receive_the_same_client(): void
    {
        $primero = $this->agente('gestion.movil');
        $segundo = $this->agente('gestion.movil');

        // Only one batch worth exists, so the second grab must come back empty
        // rather than re-handing out rows the first agent already holds.
        Cliente::factory()->count(DistribucionDeClientes::LOTE)->create(['origen' => 'DB_UNO']);

        DistribucionDeClientes::asignarLote($primero->id_usuario, $this->tienda->id_tienda, 'DB_UNO', 'MOVIL');
        $segundoLote = DistribucionDeClientes::asignarLote(
            $segundo->id_usuario,
            $this->tienda->id_tienda,
            'DB_UNO',
            'MOVIL',
        );

        $this->assertSame(0, $segundoLote);
        $this->assertSame(
            DistribucionDeClientes::LOTE,
            Cliente::query()->where('id_usuario', $primero->id_usuario)->count(),
        );
        $this->assertSame(0, Cliente::query()->where('id_usuario', $segundo->id_usuario)->count());
    }

    public function test_an_exhausted_origen_reports_back_instead_of_assigning(): void
    {
        $agente = $this->agente('gestion.movil');

        $this
            ->actingAs($agente)
            ->post(route('gestion.movil.asignar'), ['origen' => 'DB_VACIO'])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(0, Cliente::query()->where('id_usuario', $agente->id_usuario)->count());
    }

    public function test_an_agent_without_an_active_tienda_cannot_take_clients(): void
    {
        // Otherwise the batch would be written with a null tienda and disappear
        // from the queue that is scoped by it.
        $agente = Usuario::factory()->create();
        $agente->menus()->attach(Menu::factory()->create(['permiso' => 'gestion.movil']));
        Cliente::factory()->count(3)->create(['origen' => 'DB_UNO']);

        $this
            ->actingAs($agente)
            ->post(route('gestion.movil.asignar'), ['origen' => 'DB_UNO'])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(3, Cliente::query()->where('id_usuario', Cliente::SIN_AGENTE)->count());
    }

    public function test_releasing_returns_only_the_clients_not_yet_worked(): void
    {
        $agente = $this->agente('gestion.movil');
        $pendiente = $this->clienteDe($agente);
        $gestionado = Cliente::factory()
            ->gestionado()
            ->asignadoA($agente->id_usuario, $this->tienda->id_tienda)
            ->create();

        $this
            ->actingAs($agente)
            ->post(route('gestion.movil.liberar'))
            ->assertRedirect();

        $this->assertSame(Cliente::SIN_AGENTE, $pendiente->fresh()->id_usuario);
        // A worked client stays with the agent who logged the call.
        $this->assertSame($agente->id_usuario, $gestionado->fresh()->id_usuario);
    }

    public function test_releasing_leaves_the_other_segment_alone(): void
    {
        $agente = $this->agente('gestion.movil', 'gestion.fija');
        $movil = $this->clienteDe($agente, 'MOVIL');
        $fija = $this->clienteDe($agente, 'FIJA');

        $this->actingAs($agente)->post(route('gestion.movil.liberar'))->assertRedirect();

        $this->assertSame(Cliente::SIN_AGENTE, $movil->fresh()->id_usuario);
        $this->assertSame($agente->id_usuario, $fija->fresh()->id_usuario);
    }

    public function test_the_distribution_actions_are_gated_by_the_segment_permiso(): void
    {
        $soloMovil = $this->agente('gestion.movil');

        $this->actingAs($soloMovil)->post(route('gestion.fija.asignar'), ['origen' => 'DB_UNO'])->assertForbidden();
        $this->actingAs($soloMovil)->post(route('gestion.fija.liberar'))->assertForbidden();
    }
}
