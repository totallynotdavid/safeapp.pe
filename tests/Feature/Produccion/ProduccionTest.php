<?php

namespace Tests\Feature\Produccion;

use App\Models\Menu;
use App\Models\NcFija;
use App\Models\NcMovil;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProduccionTest extends TestCase
{
    use RefreshDatabase;

    private const PERMISO = 'produccion.ver';

    public function test_the_permiso_gates_the_screen(): void
    {
        $this
            ->actingAs(Usuario::factory()->create())
            ->get(route('produccion.index'))
            ->assertForbidden();

        $this
            ->actingAs($this->usuarioCon(self::PERMISO))
            ->get(route('produccion.index'))
            ->assertOk();
    }

    public function test_it_offers_a_column_for_every_estado_each_producto_actually_uses(): void
    {
        // Legacy's fija table listed the avanzado vocabulary, so COMPLETADO and
        // CANCELADO, 4,780 of the 5,970 production rows, had nowhere to appear.
        $respuesta = $this
            ->actingAs($this->usuarioCon(self::PERMISO))
            ->get(route('produccion.index'))
            ->assertOk();

        foreach ([...NcFija::ESTADOS, ...NcMovil::ESTADOS] as $estado) {
            $respuesta->assertSee($estado);
        }
    }

    public function test_a_completed_fija_case_is_counted_for_its_ejecutivo(): void
    {
        // The case legacy could not show at all: it summed fija on
        // estado='LIQUIDADO', which nc_fija has never contained.
        $ejecutivo = Usuario::factory()->create(['personal' => 'ROSA QUISPE']);
        NcFija::factory()
            ->liquidadaEsteMes()
            ->create([
                'id_usuario' => $ejecutivo->id_usuario,
                'cargo_fijo' => '169.90',
            ]);

        $this
            ->actingAs($this->usuarioCon(self::PERMISO))
            ->get(route('produccion.index'))
            ->assertOk()
            ->assertSee('ROSA QUISPE')
            ->assertSee('169.90');
    }

    public function test_a_rejected_case_is_not_counted(): void
    {
        $ejecutivo = Usuario::factory()->create(['personal' => 'ROSA QUISPE']);
        NcFija::factory()
            ->rechazada()
            ->liquidadaEsteMes()
            ->create([
                'id_usuario' => $ejecutivo->id_usuario,
                'cargo_fijo' => '169.90',
            ]);

        $this
            ->actingAs($this->usuarioCon(self::PERMISO))
            ->get(route('produccion.index'))
            ->assertOk()
            ->assertDontSee('ROSA QUISPE');
    }

    public function test_a_case_settled_in_another_month_is_not_counted(): void
    {
        // The window is the queues' window, so the dashboard and the screen a
        // supervisor checks it against cannot disagree about which month a case
        // belongs to.
        $ejecutivo = Usuario::factory()->create(['personal' => 'ROSA QUISPE']);
        NcFija::factory()->create([
            'id_usuario' => $ejecutivo->id_usuario,
            'fecha_ingreso' => now()->subMonths(4),
            'fecha_actualizacion' => now()->subMonths(4),
            'fecha_liquidacion' => now()->subMonths(4),
        ]);

        $this
            ->actingAs($this->usuarioCon(self::PERMISO))
            ->get(route('produccion.index'))
            ->assertOk()
            ->assertDontSee('ROSA QUISPE');
    }

    private function usuarioCon(string $permiso): Usuario
    {
        $user = Usuario::factory()->createOne();
        $user->menus()->attach(Menu::factory()->create(['permiso' => $permiso]));

        return $user;
    }
}
