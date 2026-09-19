<?php

namespace Tests\Feature\Gestion;

use App\Cockpit\AccesoAClientes;
use App\Models\Cliente;
use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Support\Facades\Route;

class BusquedaClienteTest extends GestionTestCase
{
    public function test_the_generic_customer_routes_are_not_published(): void
    {
        $this->assertFalse(Route::has('clientes.create'));
        $this->assertFalse(Route::has('clientes.store'));
        $this->assertFalse(Route::has('clientes.tomar'));
    }

    public function test_an_executive_cannot_open_another_executives_held_record(): void
    {
        $otro = $this->agente('gestion.movil');
        $dato = $this->datoDe($otro, 'MOVIL', ['ruc' => '20888888888', 'razon_social' => 'CLIENTE AJENO']);
        $agente = $this->agente('gestion.movil');

        $this->actingAs($agente)->get(route('gestion.show', $dato))->assertForbidden();
        $this->assertDatabaseMissing('cliente_acceso', [
            'id_cliente' => $dato->id_cliente,
            'id_usuario' => $agente->id_usuario,
        ]);
    }

    public function test_an_existing_inaccessible_ruc_returns_the_same_neutral_identity_error(): void
    {
        $otro = Usuario::factory()->create();
        $cliente = Cliente::factory()->create(['ruc' => '20888888888', 'razon_social' => 'SECRETO SAC']);
        AccesoAClientes::otorgar($cliente, $otro);
        $agente = $this->agente('notacargo.movil.registrar');

        $respuesta = $this->actingAs($agente)->post(route('notas-cargo.movil.cliente.store'), [
            'ruc' => $cliente->ruc,
            'razon_social' => 'NOMBRE ENVIADO',
        ]);

        $respuesta->assertSessionHasErrors('ruc')->assertSessionMissing('status');
        $this->assertDatabaseMissing('cliente_acceso', [
            'id_cliente' => $cliente->id_cliente,
            'id_usuario' => $agente->id_usuario,
        ]);
        $this->assertSame('SECRETO SAC', $cliente->fresh()->razon_social);
    }

    public function test_an_executive_cannot_file_a_case_against_an_inaccessible_customer(): void
    {
        $otro = Usuario::factory()->create();
        $cliente = Cliente::factory()->create(['ruc' => '20777777777']);
        AccesoAClientes::otorgar($cliente, $otro);
        $agente = $this->agente('notacargo.movil.registrar');

        $this->actingAs($agente)->post(route('notas-cargo.movil.store'), [
            'id_cliente' => $cliente->id_cliente,
            'zonal' => Tienda::factory()->create()->id_tienda,
            'q_lineas' => 1,
            'cargo_fijo' => 1,
            'modalidad' => 'ALTA',
            'contacto' => 'Contacto',
            'telefono1' => '999999999',
            'nivel_negociacion' => '25',
            'fecha_estimada' => now()->addDay()->toDateString(),
            'tipo_producto' => 'M4',
            'etapa' => 'F1-PERDIDA',
        ])->assertSessionHasErrors('id_cliente');

        $this->assertDatabaseCount('nc_movil', 0);
    }
}
