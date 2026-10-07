<?php

namespace Tests\Feature\Gestion;

use App\Cockpit\DistribucionDeDatos;
use App\Models\AsignacionDato;
use App\Models\Dato;
use App\Models\Menu;
use App\Models\Usuario;

class GestionDistribucionTest extends GestionTestCase
{
    public function test_the_queue_offers_each_origen_with_its_available_stock(): void
    {
        Dato::factory()->count(3)->create(['origen' => 'DB_UNO']);
        Dato::factory()->count(1)->create(['origen' => 'DB_DOS']);
        Dato::factory()
            ->asignadoA($this->agente()->id_usuario, $this->tienda->id_tienda)
            ->create(['origen' => 'DB_TRES']);
        Dato::factory()->tipoData('FIJA')->create(['origen' => 'DB_CUATRO']);

        $this
            ->actingAs($this->agente('gestion.movil'))
            ->get(route('gestion.movil'))
            ->assertOk()
            ->assertSee('DB_UNO (3 disponibles)')
            ->assertSee('DB_DOS (1 disponibles)')
            ->assertDontSee('DB_TRES')
            ->assertDontSee('DB_CUATRO');
    }

    public function test_assigning_a_batch_moves_records_into_the_agents_queue(): void
    {
        $agente = $this->agente('gestion.movil');
        Dato::factory()->count(5)->create(['origen' => 'DB_UNO']);

        $this
            ->actingAs($agente)
            ->post(route('gestion.movil.asignar'), ['origen' => 'DB_UNO'])
            ->assertRedirect();

        $mios = $this->enPoderDe($agente);
        $this->assertCount(5, $mios);
        // The queue is scoped by tienda, so the hold has to carry it.
        $this->assertTrue($mios->every(
            fn (Dato $d): bool => $d->asignacionActiva->id_tienda === $this->tienda->id_tienda,
        ));
        $this->assertTrue($mios->every(static fn (Dato $d): bool => $d->asignacionActiva->fecha_asignacion !== null));
        $this->assertSame(5, \App\Models\AccesoCliente::query()->where('id_usuario', $agente->id_usuario)->count());
    }

    public function test_a_batch_is_capped_and_never_crosses_segments(): void
    {
        $agente = $this->agente('gestion.movil');
        Dato::factory()
            ->count(DistribucionDeDatos::LOTE + 10)
            ->create(['origen' => 'DB_UNO']);
        Dato::factory()->count(4)->tipoData('FIJA')->create(['origen' => 'DB_UNO']);

        $this
            ->actingAs($agente)
            ->post(route('gestion.movil.asignar'), ['origen' => 'DB_UNO'])
            ->assertRedirect();

        $mios = $this->enPoderDe($agente);
        $this->assertCount(DistribucionDeDatos::LOTE, $mios);
        $this->assertTrue($mios->every(static fn (Dato $d): bool => $d->tipo_data === 'MOVIL'));
    }

    public function test_two_agents_never_receive_the_same_record(): void
    {
        $primero = $this->agente('gestion.movil');
        $segundo = $this->agente('gestion.movil');

        // Only one batch worth exists, so the second grab must come back empty
        // rather than re-handing out rows the first agent already holds.
        Dato::factory()->count(DistribucionDeDatos::LOTE)->create(['origen' => 'DB_UNO']);

        DistribucionDeDatos::asignarLote($primero->id_usuario, $this->tienda->id_tienda, 'DB_UNO', 'MOVIL');
        $segundoLote = DistribucionDeDatos::asignarLote(
            $segundo->id_usuario,
            $this->tienda->id_tienda,
            'DB_UNO',
            'MOVIL',
        );

        $this->assertSame(0, $segundoLote);
        $this->assertCount(DistribucionDeDatos::LOTE, $this->enPoderDe($primero));
        $this->assertCount(0, $this->enPoderDe($segundo));
    }

    public function test_an_exhausted_origen_reports_back_instead_of_assigning(): void
    {
        $agente = $this->agente('gestion.movil');

        $this
            ->actingAs($agente)
            ->post(route('gestion.movil.asignar'), ['origen' => 'DB_VACIO'])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertCount(0, $this->enPoderDe($agente));
    }

    public function test_an_agent_without_an_active_tienda_cannot_take_records(): void
    {
        // Otherwise the batch would be written with a null tienda and disappear
        // from the queue that is scoped by it.
        $agente = Usuario::factory()->createOne();
        $agente->menus()->attach(Menu::factory()->create(['permiso' => 'gestion.movil']));
        Dato::factory()->count(3)->create(['origen' => 'DB_UNO']);

        $this
            ->actingAs($agente)
            ->post(route('gestion.movil.asignar'), ['origen' => 'DB_UNO'])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(0, AsignacionDato::query()->count());
    }

    public function test_releasing_returns_only_the_records_not_yet_worked(): void
    {
        $agente = $this->agente('gestion.movil');
        $pendiente = $this->datoDe($agente);
        $gestionado = Dato::factory()
            ->gestionado()
            ->asignadoA($agente->id_usuario, $this->tienda->id_tienda)
            ->createOne();

        $this
            ->actingAs($agente)
            ->post(route('gestion.movil.liberar'))
            ->assertRedirect();

        $this->assertNull($pendiente->fresh()->asignacionActiva);
        $this->assertSame($agente->id_usuario, $gestionado->fresh()->asignacionActiva?->id_usuario);
        // The hold is closed, not erased: who held what stays on the record.
        $this->assertNotNull($pendiente->asignaciones()->first()->fecha_liberacion);
    }

    public function test_releasing_leaves_the_other_segment_alone(): void
    {
        $agente = $this->agente('gestion.movil', 'gestion.fija');
        $movil = $this->datoDe($agente, 'MOVIL');
        $fija = $this->datoDe($agente, 'FIJA');

        $this->actingAs($agente)->post(route('gestion.movil.liberar'))->assertRedirect();

        $this->assertNull($movil->fresh()->asignacionActiva);
        $this->assertSame($agente->id_usuario, $fija->fresh()->asignacionActiva?->id_usuario);
    }

    public function test_the_distribution_actions_are_gated_by_the_segment_permiso(): void
    {
        $soloMovil = $this->agente('gestion.movil');

        $this->actingAs($soloMovil)->post(route('gestion.fija.asignar'), ['origen' => 'DB_UNO'])->assertForbidden();
        $this->actingAs($soloMovil)->post(route('gestion.fija.liberar'))->assertForbidden();
    }
}
