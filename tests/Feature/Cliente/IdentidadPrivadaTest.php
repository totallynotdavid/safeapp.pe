<?php

namespace Tests\Feature\Cliente;

use App\Models\Menu;
use App\Models\Cliente;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class IdentidadPrivadaTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, array{string, string, string}> */
    public static function origenes(): array
    {
        return [
            'nota móvil' => [
                'notacargo.movil.registrar',
                'notas-cargo.movil.cliente.store',
                'notas-cargo.movil.create',
            ],
            'nota fija' => ['notacargo.fija.registrar', 'notas-cargo.fija.cliente.store', 'notas-cargo.fija.create'],
            'quiebre' => ['quiebre.registrar', 'quiebres.registro.cliente.store', 'quiebres.registro.create'],
        ];
    }

    #[DataProvider('origenes')]
    public function test_each_case_identity_dialog_creates_the_identity_and_private_grant(
        string $permiso,
        string $ruta,
        string $destino,
    ): void {
        $agente = Usuario::factory()->create();
        $agente->menus()->attach(Menu::factory()->create(['permiso' => $permiso]));

        $this->actingAs($agente)
            ->post(route($ruta), [
                'ruc' => '20123456789',
                'razon_social' => 'IDENTIDAD NUEVA SAC',
            ])
            ->assertRedirect(route($destino, ['ruc' => '20123456789']));

        $this->assertDatabaseHas('cliente', ['ruc' => '20123456789', 'razon_social' => 'IDENTIDAD NUEVA SAC']);
        $this->assertDatabaseHas('cliente_acceso', ['id_usuario' => $agente->id_usuario]);
    }

    public function test_an_existing_private_identity_cannot_be_discovered_or_claimed(): void
    {
        $agente = Usuario::factory()->create();
        $agente->menus()->attach(Menu::factory()->create(['permiso' => 'notacargo.movil.registrar']));
        $otro = Usuario::factory()->create();
        $cliente = Cliente::factory()->create(['ruc' => '20123456789']);
        $cliente->accesos()->create(['id_usuario' => $otro->id_usuario]);

        $this->actingAs($agente)
            ->from(route('notas-cargo.movil.create'))
            ->post(route('notas-cargo.movil.cliente.store'), [
                'ruc' => $cliente->ruc,
                'razon_social' => 'INTENTO DE ACCESO',
            ])
            ->assertRedirect(route('notas-cargo.movil.create'))
            ->assertSessionHasErrors('ruc');

        $this->assertDatabaseMissing('cliente_acceso', [
            'id_cliente' => $cliente->id_cliente,
            'id_usuario' => $agente->id_usuario,
        ]);
        $this->assertSame($cliente->razon_social, $cliente->fresh()->razon_social);
    }
}
