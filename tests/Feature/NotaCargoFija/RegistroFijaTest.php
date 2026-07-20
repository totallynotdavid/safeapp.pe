<?php

namespace Tests\Feature\NotaCargoFija;

use App\Models\NcFija;
use App\Models\Producto;
use App\Models\Usuario;

// REGISTRO: an ejecutivo files a case against a resolved client.
class RegistroFijaTest extends NotaCargoFijaTestCase
{
    private const PERMISO = 'notacargo.fija.registrar';

    public function test_the_registrar_permiso_gates_the_screen(): void
    {
        $ruta = route('notas-cargo.fija.index');

        $this->actingAs(Usuario::factory()->create())->get($ruta)->assertForbidden();
        $this->actingAs($this->usuarioCon(self::PERMISO))->get($ruta)->assertOk();
    }

    public function test_the_form_appears_only_once_a_ruc_resolves_to_a_client(): void
    {
        $ejecutivo = $this->usuarioCon(self::PERMISO);
        $this->cliente();

        $this
            ->actingAs($ejecutivo)
            ->get(route('notas-cargo.fija.create', ['ruc' => '20999999999']))
            ->assertOk()
            ->assertSee('no está registrado')
            ->assertDontSee('name="id_producto"', false);

        $this
            ->actingAs($ejecutivo)
            ->get(route('notas-cargo.fija.create', ['ruc' => self::RUC]))
            ->assertOk()
            ->assertSee('name="id_producto"', false);
    }

    public function test_it_files_a_case_awaiting_validation(): void
    {
        $ejecutivo = $this->usuarioCon(self::PERMISO);

        $this
            ->actingAs($ejecutivo)
            ->post(route('notas-cargo.fija.store'), $this->payload($this->cliente(), $this->producto()))
            ->assertRedirect(route('notas-cargo.fija.index'));

        $cargo = NcFija::query()->firstOrFail();
        $this->assertSame('XINGRESAR', $cargo->estado);
        $this->assertSame(NcFija::VALIDACION_PENDIENTE, $cargo->validacion);
        $this->assertSame($ejecutivo->id_usuario, $cargo->id_usuario);
        $this->assertSame(now()->toDateString(), $cargo->fecha_ingreso->toDateString());
        // Legacy hardcoded this on insert; the column default carries it now.
        $this->assertSame('SINNODO', $cargo->nodo);
    }

    public function test_the_product_and_its_price_come_from_the_catalog(): void
    {
        // Legacy posted subtipo, descripción and the price as three independent
        // fields, so a case could name a product the catalog never had: 335 of
        // the 5,970 rows do.
        $ejecutivo = $this->usuarioCon(self::PERMISO);

        $this
            ->actingAs($ejecutivo)
            ->post(route('notas-cargo.fija.store'), $this->payload($this->cliente(), $this->producto(), [
                'subtipo' => 'PRODUCTO INVENTADO',
                'descripcion' => '9999MB',
            ]))
            ->assertRedirect();

        $cargo = NcFija::query()->firstOrFail();
        $this->assertSame('DUO BA + TV', $cargo->subtipo);
        $this->assertSame('400MB', $cargo->descripcion);
        $this->assertSame('169.90', $cargo->cargo_fijo);
    }

    public function test_the_agent_may_sell_at_a_price_other_than_the_catalogs(): void
    {
        // Prices move: 2,029 of the 5,635 on-catalog rows were sold at something
        // other than the list price.
        $ejecutivo = $this->usuarioCon(self::PERMISO);

        $this
            ->actingAs($ejecutivo)
            ->post(route('notas-cargo.fija.store'), $this->payload($this->cliente(), $this->producto(), [
                'cargo_fijo' => '149.90',
            ]))
            ->assertRedirect();

        $this->assertSame('149.90', NcFija::query()->firstOrFail()->cargo_fijo);
    }

    public function test_a_retired_product_cannot_be_sold(): void
    {
        $ejecutivo = $this->usuarioCon(self::PERMISO);
        $retirado = Producto::factory()->retirado()->create();

        $this
            ->actingAs($ejecutivo)
            ->post(route('notas-cargo.fija.store'), $this->payload($this->cliente(), $retirado))
            ->assertSessionHasErrors('id_producto');

        $this->assertSame(0, NcFija::query()->count());
    }

    public function test_a_case_cannot_be_filed_against_a_client_that_does_not_exist(): void
    {
        // Legacy took ruc and razon_social as free text, so a case could name a
        // client nothing else in the system knew about.
        $this
            ->actingAs($this->usuarioCon(self::PERMISO))
            ->post(route('notas-cargo.fija.store'), $this->payload($this->cliente(), $this->producto(), [
                'id_cliente' => 9999,
            ]))
            ->assertSessionHasErrors('id_cliente');

        $this->assertSame(0, NcFija::query()->count());
    }

    public function test_the_technology_is_a_choice_and_not_free_text(): void
    {
        // The legacy input collected 27 spellings of three values, three of them
        // GPS coordinates pasted into the wrong box.
        $this
            ->actingAs($this->usuarioCon(self::PERMISO))
            ->post(route('notas-cargo.fija.store'), $this->payload($this->cliente(), $this->producto(), [
                'tecnologia' => 'FTTTHH',
            ]))
            ->assertSessionHasErrors('tecnologia');
    }

    public function test_the_second_phone_is_optional(): void
    {
        // Legacy marked it required on a form with no second number to give.
        $this
            ->actingAs($this->usuarioCon(self::PERMISO))
            ->post(route('notas-cargo.fija.store'), $this->payload($this->cliente(), $this->producto()))
            ->assertSessionHasNoErrors();

        $this->assertNull(NcFija::query()->firstOrFail()->telefono2);
    }
}
