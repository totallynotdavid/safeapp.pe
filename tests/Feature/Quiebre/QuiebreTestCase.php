<?php

namespace Tests\Feature\Quiebre;

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

    protected function ejecutivo(): Usuario
    {
        $user = Usuario::factory()->create();
        $user->menus()->attach(Menu::factory()->create(['permiso' => 'quiebre.registrar']));

        return $user;
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function payload(Tienda $tienda, array $overrides = []): array
    {
        return array_merge([
            'id_cliente' => Cliente::factory()->create(['ruc' => self::RUC])->id_cliente,
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
