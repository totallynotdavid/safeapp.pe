<?php

namespace Tests\Feature\Asignacion;

use App\Models\Asigna;
use App\Models\Menu;
use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AsignacionManagementTest extends TestCase
{
    use RefreshDatabase;

    // A user holding the asignacion.gestionar grant.
    private function gestor(): Usuario
    {
        $menu = Menu::factory()->create(['permiso' => 'asignacion.gestionar']);
        $user = Usuario::factory()->create();
        $user->menus()->attach($menu);

        return $user;
    }

    public function test_the_gestionar_permiso_gates_the_index(): void
    {
        $this->actingAs(Usuario::factory()->create())->get(route('asignacion.index'))->assertForbidden();
        $this->actingAs($this->gestor())->get(route('asignacion.index'))->assertOk();
    }

    public function test_lists_active_personnel_with_their_active_tienda(): void
    {
        $tienda = Tienda::factory()->create(['nombre' => 'LIMA CENTRO']);
        $agente = Usuario::factory()->create(['personal' => 'AGENTE ACTIVO']);
        Asigna::factory()->create([
            'id_usuario' => $agente->id_usuario,
            'id_tienda' => $tienda->id_tienda,
            'estado' => 0,
        ]);
        Usuario::factory()->inactivo()->create(['personal' => 'AGENTE INACTIVO']);

        $this
            ->actingAs($this->gestor())
            ->get(route('asignacion.index'))
            ->assertOk()
            ->assertSee('AGENTE ACTIVO')
            ->assertSee('LIMA CENTRO')
            ->assertDontSee('AGENTE INACTIVO');
    }

    public function test_assigns_tiendas_and_sets_the_active_one(): void
    {
        $agente = Usuario::factory()->create();
        $centro = Tienda::factory()->create();
        $norte = Tienda::factory()->create();

        $this
            ->actingAs($this->gestor())
            ->put(route('asignacion.update', $agente), [
                'tiendas' => [$centro->id_tienda, $norte->id_tienda],
                'activa' => $norte->id_tienda,
            ])
            ->assertRedirect(route('asignacion.index'));

        $this->assertSame($norte->id_tienda, $agente->fresh()->tiendaAsignada());
        $this->assertSame(2, Asigna::query()->where('id_usuario', $agente->id_usuario)->count());
    }

    public function test_keeps_a_single_active_tienda_when_the_active_one_changes(): void
    {
        $agente = Usuario::factory()->create();
        $centro = Tienda::factory()->create();
        $norte = Tienda::factory()->create();
        $agente->sincronizarAsignaciones([$centro->id_tienda, $norte->id_tienda], $centro->id_tienda);

        $this
            ->actingAs($this->gestor())
            ->put(route('asignacion.update', $agente), [
                'tiendas' => [$centro->id_tienda, $norte->id_tienda],
                'activa' => $norte->id_tienda,
            ])
            ->assertRedirect(route('asignacion.index'));

        $this->assertSame($norte->id_tienda, $agente->fresh()->tiendaAsignada());
        $this->assertSame(1, Asigna::query()->where('id_usuario', $agente->id_usuario)->where('estado', 0)->count());
    }

    public function test_removing_a_tienda_deletes_its_assignment(): void
    {
        $agente = Usuario::factory()->create();
        $centro = Tienda::factory()->create();
        $norte = Tienda::factory()->create();
        $agente->sincronizarAsignaciones([$centro->id_tienda, $norte->id_tienda], $centro->id_tienda);

        $this
            ->actingAs($this->gestor())
            ->put(route('asignacion.update', $agente), [
                'tiendas' => [$centro->id_tienda],
                'activa' => $centro->id_tienda,
            ])
            ->assertRedirect(route('asignacion.index'));

        $this->assertSame($centro->id_tienda, $agente->fresh()->tiendaAsignada());
        $this->assertSame(1, Asigna::query()->where('id_usuario', $agente->id_usuario)->count());
    }

    public function test_clears_every_assignment_when_no_tienda_is_chosen(): void
    {
        $agente = Usuario::factory()->create();
        $centro = Tienda::factory()->create();
        $agente->sincronizarAsignaciones([$centro->id_tienda], $centro->id_tienda);

        $this
            ->actingAs($this->gestor())
            ->put(route('asignacion.update', $agente), [])
            ->assertRedirect(route('asignacion.index'));

        $this->assertNull($agente->fresh()->tiendaAsignada());
        $this->assertSame(0, Asigna::query()->where('id_usuario', $agente->id_usuario)->count());
    }

    public function test_rejects_an_active_tienda_outside_the_assigned_set(): void
    {
        $agente = Usuario::factory()->create();
        $centro = Tienda::factory()->create();
        $norte = Tienda::factory()->create();

        $this
            ->actingAs($this->gestor())
            ->put(route('asignacion.update', $agente), [
                'tiendas' => [$centro->id_tienda],
                'activa' => $norte->id_tienda,
            ])
            ->assertSessionHasErrors('activa');

        $this->assertSame(0, Asigna::query()->where('id_usuario', $agente->id_usuario)->count());
    }

    public function test_requires_an_active_tienda_when_tiendas_are_chosen(): void
    {
        $agente = Usuario::factory()->create();
        $centro = Tienda::factory()->create();

        $this
            ->actingAs($this->gestor())
            ->put(route('asignacion.update', $agente), [
                'tiendas' => [$centro->id_tienda],
            ])
            ->assertSessionHasErrors('activa');
    }
}
