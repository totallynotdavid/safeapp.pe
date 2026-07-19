<?php

namespace Tests\Feature\Quiebre;

use App\Models\Quiebre;
use App\Models\Tienda;
use App\Models\Usuario;

// Opening a case and amending it afterwards. How the case gets its client lives
// in QuiebreClienteTest.
class QuiebreRegistroTest extends QuiebreTestCase
{
    public function test_the_registrar_permiso_gates_the_index(): void
    {
        $this->actingAs(Usuario::factory()->create())->get(route('quiebres.registro.index'))->assertForbidden();
        $this->actingAs($this->ejecutivo())->get(route('quiebres.registro.index'))->assertOk();
    }

    public function test_lists_only_the_ejecutivos_own_quiebres(): void
    {
        $ejecutivo = $this->ejecutivo();

        Quiebre::factory()
            ->deRuc('20111111111')
            ->create([
                'id_usuario' => $ejecutivo->id_usuario,
                'fecha_registro' => now(),
            ]);
        Quiebre::factory()
            ->deRuc('20999999999')
            ->create([
                'id_usuario' => Usuario::factory(),
                'fecha_registro' => now(),
            ]);

        $this
            ->actingAs($ejecutivo)
            ->get(route('quiebres.registro.index'))
            ->assertOk()
            ->assertSee('20111111111')
            ->assertDontSee('20999999999');
    }

    public function test_filters_by_estado(): void
    {
        $ejecutivo = $this->ejecutivo();

        Quiebre::factory()
            ->estado('PENDIENTE')
            ->deRuc('20000000001')
            ->create(['id_usuario' => $ejecutivo->id_usuario, 'fecha_registro' => now()]);
        Quiebre::factory()
            ->estado('DEVUELTO')
            ->deRuc('20000000002')
            ->create(['id_usuario' => $ejecutivo->id_usuario, 'fecha_registro' => now()]);

        $this
            ->actingAs($ejecutivo)
            ->get(route('quiebres.registro.index', ['estado' => 'DEVUELTO']))
            ->assertOk()
            ->assertSee('20000000002')
            ->assertDontSee('20000000001');
    }

    public function test_registers_a_quiebre_with_server_owned_fields(): void
    {
        $ejecutivo = $this->ejecutivo();
        $tienda = Tienda::factory()->create();

        $this
            ->actingAs($ejecutivo)
            ->post(route('quiebres.registro.store'), $this->payload($tienda))
            ->assertRedirect(route('quiebres.registro.index'));

        $quiebre = Quiebre::query()->sole();
        $this->assertSame(self::RUC, $quiebre->cliente->ruc, 'the case is filed against the resolved client');
        $this->assertSame('PENDIENTE', $quiebre->estado);
        $this->assertSame($ejecutivo->id_usuario, (int) $quiebre->id_usuario, 'owner comes from the session');
        $this->assertSame(0, (int) $quiebre->id_validador);
        $this->assertSame('NO', $quiebre->ticket_atencion, 'a service activated today needs no ticket');
        $this->assertSame(now('America/Lima')->toDateString(), $quiebre->fecha_registro->toDateString());
    }

    public function test_requires_a_ticket_when_the_service_is_old_enough(): void
    {
        $ejecutivo = $this->ejecutivo();
        $tienda = Tienda::factory()->create();

        $this
            ->actingAs($ejecutivo)
            ->post(
                route('quiebres.registro.store'),
                $this->payload($tienda, [
                    'fecha_activacion' => now()->subDays(100)->toDateString(),
                ]),
            )
            ->assertSessionHasErrors(['numero_ticket', 'fecha_ticket_atencion']);
    }

    public function test_rejects_an_invalid_servicio(): void
    {
        $ejecutivo = $this->ejecutivo();
        $tienda = Tienda::factory()->create();

        $this
            ->actingAs($ejecutivo)
            ->post(route('quiebres.registro.store'), $this->payload($tienda, ['servicio' => 'INVENTADO']))
            ->assertSessionHasErrors('servicio');
    }

    public function test_amends_an_own_editable_quiebre(): void
    {
        $ejecutivo = $this->ejecutivo();
        $quiebre = Quiebre::factory()->estado('PENDIENTE')->create(['id_usuario' => $ejecutivo->id_usuario]);

        $this->actingAs($ejecutivo)->put(route('quiebres.registro.update', $quiebre), [
            'numero_problema' => 5,
            'contacto1' => 'NUEVO CONTACTO',
            'celular1' => '900900900',
            'correo1' => 'nuevo@example.com',
        ])->assertRedirect(route('quiebres.registro.index'));

        $this->assertSame('NUEVO CONTACTO', $quiebre->fresh()->contacto1);
    }

    // The update policy denies both non-owners and validated (frozen) cases;
    // either way the row must be untouched.
    public function test_amend_is_denied_for_non_owners_and_frozen_quiebres(): void
    {
        $ejecutivo = $this->ejecutivo();
        $ajeno = Quiebre::factory()
            ->estado('PENDIENTE')
            ->create(['id_usuario' => Usuario::factory(), 'contacto1' => 'AJENO']);
        $validado = Quiebre::factory()
            ->estado('ATENDIDO')
            ->create(['id_usuario' => $ejecutivo->id_usuario, 'contacto1' => 'ORIGINAL']);

        $amend = fn (Quiebre $quiebre) => $this->actingAs($ejecutivo)->put(
            route('quiebres.registro.update', $quiebre),
            [
                'numero_problema' => 5,
                'contacto1' => 'CAMBIADO',
                'celular1' => '900900900',
                'correo1' => 'x@example.com',
            ],
        );

        $amend($ajeno)->assertForbidden();
        $amend($validado)->assertForbidden();

        $this->assertSame('AJENO', $ajeno->fresh()->contacto1);
        $this->assertSame('ORIGINAL', $validado->fresh()->contacto1);
    }
}
