<?php

namespace Tests\Feature\Asignacion;

use App\Models\Menu;
use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

// The switcher only appears for operators who hold more than one tienda, so a
// regression here is invisible to anyone testing with a single-tienda account.
class SelectorDeTiendaTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_operator_on_two_tiendas_sees_both_with_the_active_one_chosen(): void
    {
        $lima = Tienda::factory()->create(['nombre' => 'Lima Centro']);
        $arequipa = Tienda::factory()->create(['nombre' => 'Arequipa Sur']);
        $operador = $this->operadorEn([$lima, $arequipa], $lima);

        $respuesta = $this->actingAs($operador)->get(route('gestion.movil'));

        $respuesta->assertOk();
        $respuesta->assertSee('Tienda activa');
        $respuesta->assertSee('Lima Centro');
        $respuesta->assertSee('Arequipa Sur');
        $this->assertSame($lima->id_tienda, $this->tiendaElegida($respuesta));
    }

    public function test_an_operator_on_one_tienda_sees_no_switcher(): void
    {
        $lima = Tienda::factory()->create(['nombre' => 'Lima Centro']);
        $operador = $this->operadorEn([$lima], $lima);

        $respuesta = $this->actingAs($operador)->get(route('gestion.movil'));

        $respuesta->assertOk();
        $respuesta->assertDontSee('Tienda activa');
    }

    /**
     * @param  array<int, Tienda>  $tiendas
     */
    private function operadorEn(array $tiendas, Tienda $activa): Usuario
    {
        $operador = Usuario::factory()->create();
        $operador->menus()->attach(Menu::factory()->create(['permiso' => 'gestion.movil']));
        $operador->sincronizarAsignaciones(
            array_map(static fn (Tienda $t): int => $t->id_tienda, $tiendas),
            $activa->id_tienda,
        );

        return $operador;
    }

    /** The id the switcher would post back, read off its hidden input. */
    private function tiendaElegida(TestResponse $respuesta): ?int
    {
        $encontrado = preg_match(
            '/name="id_tienda"\s+value="(\d+)"/',
            (string) $respuesta->getContent(),
            $coincidencia,
        );

        return $encontrado === 1 ? (int) $coincidencia[1] : null;
    }
}
