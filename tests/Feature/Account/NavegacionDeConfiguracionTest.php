<?php

namespace Tests\Feature\Account;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class NavegacionDeConfiguracionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Configuración's landing page includes settings-nav twice: once in its own
     * content for the mobile pane, once in the layout aside. Those two run at
     * different points, the first inside the child section and the second after
     * the layout composes, so a source of data that reaches one can miss the
     * other and only the landing page shows it.
     */
    public function test_the_landing_page_renders_both_copies_of_the_nav(): void
    {
        $usuario = Usuario::factory()->create();

        $respuesta = $this->actingAs($usuario)->get(route('configuracion.index'));

        $respuesta->assertOk();
        $this->assertSame(2, $this->vecesQueApareceLaNavegacion($respuesta));
        $respuesta->assertSee('Información personal');
    }

    public function test_the_landing_page_marks_the_profile_item_as_current(): void
    {
        $usuario = Usuario::factory()->create();

        $respuesta = $this->actingAs($usuario)->get(route('configuracion.index'));

        $this->assertSame(route('configuracion.perfil'), $this->rutaMarcadaComoActual($respuesta));
    }

    public function test_an_inner_settings_page_renders_the_nav_once(): void
    {
        $usuario = Usuario::factory()->create();

        $respuesta = $this->actingAs($usuario)->get(route('configuracion.perfil'));

        $respuesta->assertOk();
        $this->assertSame(1, $this->vecesQueApareceLaNavegacion($respuesta));
    }

    private function vecesQueApareceLaNavegacion(TestResponse $respuesta): int
    {
        return substr_count((string) $respuesta->getContent(), 'settings-nav__heading');
    }

    /** The link inside the item the nav marks with aria-selected. */
    private function rutaMarcadaComoActual(TestResponse $respuesta): ?string
    {
        $coincidencia = [];
        $encontrado = preg_match(
            '/<li aria-selected="true".*?href="([^"]+)"/s',
            (string) $respuesta->getContent(),
            $coincidencia,
        );

        return $encontrado === 1 ? $coincidencia[1] : null;
    }
}
