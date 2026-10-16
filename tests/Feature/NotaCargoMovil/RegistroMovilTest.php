<?php

namespace Tests\Feature\NotaCargoMovil;

use App\Cockpit\AccesoAClientes;
use App\Models\Cliente;
use App\Models\NcMovil;
use App\Models\Usuario;

class RegistroMovilTest extends NotaCargoMovilTestCase
{
    private const PERMISO = 'notacargo.movil.registrar';

    public function test_the_registrar_permiso_gates_the_screen(): void
    {
        $this->actingAs(Usuario::factory()->create())->get(route('notas-cargo.movil.index'))->assertForbidden();
        $this->actingAs($this->usuarioCon(self::PERMISO))->get(route('notas-cargo.movil.index'))->assertOk();
    }

    public function test_the_form_appears_only_once_a_ruc_resolves_to_a_client(): void
    {
        $ejecutivo = $this->usuarioCon(self::PERMISO);
        $this->cliente();

        $this
            ->actingAs($ejecutivo)
            ->get(route('notas-cargo.movil.create'))
            ->assertOk();

        $this
            ->actingAs($ejecutivo)
            ->get(route('notas-cargo.movil.create', ['ruc' => '20999999999']))
            ->assertOk()
            ->assertSee('Cliente no disponible');

        $this
            ->actingAs($ejecutivo)
            ->get(route('notas-cargo.movil.create', ['ruc' => self::RUC]))
            ->assertOk();
    }

    public function test_it_files_a_case_awaiting_validation(): void
    {
        $ejecutivo = $this->usuarioCon(self::PERMISO);
        $cliente = $this->cliente();

        $this
            ->actingAs($ejecutivo)
            ->post(route('notas-cargo.movil.store'), $this->payload($cliente))
            ->assertRedirect(route('notas-cargo.movil.index'));

        $cargo = NcMovil::query()->sole();
        // Both estados start PENDIENTE: registro owns neither, and the stages
        // that do have not run.
        $this->assertSame('PENDIENTE', $cargo->estado);
        $this->assertSame(NcMovil::VALIDACION_PENDIENTE, $cargo->validacion);
        // Identity and date come from the session and the clock, never the form.
        $this->assertSame($ejecutivo->id_usuario, $cargo->id_usuario);
        $this->assertSame(now()->toDateString(), $cargo->fecha_ingreso->toDateString());
        $this->assertSame($cliente->id_cliente, $cargo->id_cliente);
    }

    public function test_the_client_name_is_never_an_input(): void
    {
        $ejecutivo = $this->usuarioCon(self::PERMISO);
        $cliente = $this->cliente();

        $this->actingAs($ejecutivo)->post(
            route('notas-cargo.movil.store'),
            $this->payload($cliente) + ['razon_social' => 'NOMBRE FALSO', 'ruc' => '20000000000'],
        );

        $atributos = NcMovil::query()->sole()->getAttributes();
        $this->assertArrayNotHasKey('razon_social', $atributos);
        $this->assertArrayNotHasKey('ruc', $atributos);
    }

    public function test_a_case_cannot_be_filed_against_a_client_that_does_not_exist(): void
    {
        $cliente = $this->cliente();

        $this
            ->actingAs($this->usuarioCon(self::PERMISO))
            ->post(route('notas-cargo.movil.store'), $this->payload($cliente, ['id_cliente' => 999_999]))
            ->assertSessionHasErrors('id_cliente');
    }

    public function test_a_case_cannot_be_filed_against_a_client_held_by_another_executive(): void
    {
        $ejecutivo = $this->usuarioCon(self::PERMISO);
        $cliente = Cliente::factory()->create();
        AccesoAClientes::otorgar($cliente, Usuario::factory()->create());

        $this
            ->actingAs($ejecutivo)
            ->post(route('notas-cargo.movil.store'), $this->payload($cliente))
            ->assertSessionHasErrors('id_cliente');

        $this->assertDatabaseCount('nc_movil', 0);
    }

    public function test_the_probability_scale_does_not_offer_an_estado(): void
    {
        // CAIDA is a lifecycle estado owned by the back office, so registro
        // must not accept it.
        $cliente = $this->cliente();

        $this
            ->actingAs($this->usuarioCon(self::PERMISO))
            ->post(route('notas-cargo.movil.store'), $this->payload($cliente, ['nivel_negociacion' => 'CAIDA']))
            ->assertSessionHasErrors('nivel_negociacion');
    }

    public function test_the_second_phone_is_optional(): void
    {
        $cliente = $this->cliente();
        $ejecutivo = $this->usuarioCon(self::PERMISO);
        AccesoAClientes::otorgar($cliente, $ejecutivo);

        $this
            ->actingAs($ejecutivo)
            ->post(route('notas-cargo.movil.store'), $this->payload($cliente))
            ->assertSessionHasNoErrors();
    }
}
