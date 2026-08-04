<?php

namespace Tests\Feature\Quiebre;

use App\Cockpit\AccesoAClientes;
use App\Models\Cliente;
use App\Models\Menu;
use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Shared fixture for the averías tests: an ejecutivo who may open cases, and a
// valid registration payload. A quiebre names a client, so the payload resolves
// one rather than carrying a typed identity.
abstract class QuiebreTestCase extends TestCase
{
    use RefreshDatabase;

    protected const RUC = '20123456789';

    protected ?Usuario $ejecutivoActual = null;

    protected function ejecutivo(): Usuario
    {
        $user = Usuario::factory()->create();
        $user->menus()->attach(Menu::factory()->create(['permiso' => 'quiebre.registrar']));
        $this->ejecutivoActual = $user;

        return $user;
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function payload(Tienda $tienda, array $overrides = []): array
    {
        $cliente = Cliente::factory()->create(['ruc' => self::RUC]);
        if ($this->ejecutivoActual !== null) {
            AccesoAClientes::otorgar($cliente, $this->ejecutivoActual);
        }

        return array_merge([
            'id_cliente' => $cliente->id_cliente,
            'zonal_telefonica' => $tienda->id_tienda,
            'servicio' => 'FIJA',
            'tipo_averia' => 'SIN TV',
            'numero_problema' => 3,
            'problema_equipo' => 'NO',
            'fecha_activacion' => now()->toDateString(),
            'contacto1' => 'JUAN PEREZ',
            'celular1' => '999888777',
            'correo1' => 'juan@example.com',
        ], $overrides);
    }
}
