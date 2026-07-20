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
     * estado. fecha_actualizacion is what the year filter reads, and the ruc is
     * what identifies the row on the page.
     *
     * @param  array<string, mixed>  $overrides
     */
    protected function enCola(?string $ruc = null, array $overrides = []): NcMovil
    {
        $factory = NcMovil::factory();

        return ($ruc === null ? $factory : $factory->deRuc($ruc))
            ->create(array_merge(['fecha_actualizacion' => now()], $overrides));
    }
}
