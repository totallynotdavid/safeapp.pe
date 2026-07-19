<?php

namespace Tests\Feature\Liquidacion;

use App\Models\Menu;
use App\Models\NcMovil;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Shared fixture for the liquidación tests. The screen has no tienda scoping
// (legacy passed one and never filtered on it), so the fixture is just a
// liquidador and a case that reaches the queue.
abstract class LiquidacionTestCase extends TestCase
{
    use RefreshDatabase;

    protected function liquidador(): Usuario
    {
        $user = Usuario::factory()->create();
        $user->menus()->attach(Menu::factory()->create(['permiso' => 'liquidacion.gestionar']));

        return $user;
    }

    /**
     * A case that clears both queue gates: validación PROCEDE and a settleable
     * estado. fecha_actualizacion is what the year filter reads.
     *
     * @param  array<string, mixed>  $overrides
     */
    protected function enCola(array $overrides = []): NcMovil
    {
        return NcMovil::factory()->create(array_merge(['fecha_actualizacion' => now()], $overrides));
    }
}
