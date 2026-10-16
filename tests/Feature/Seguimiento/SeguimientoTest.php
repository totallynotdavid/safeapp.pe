<?php

namespace Tests\Feature\Seguimiento;

use App\Models\Llamada;
use App\Models\Menu;
use App\Models\Supervisor;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\LeeLibro;
use Tests\TestCase;

class SeguimientoTest extends TestCase
{
    use LeeLibro;
    use RefreshDatabase;

    private const PERMISO = 'seguimiento.equipo';

    public function test_the_permiso_gates_the_screen(): void
    {
        $this
            ->actingAs(Usuario::factory()->create())
            ->get(route('seguimiento.index'))
            ->assertForbidden();

        $this
            ->actingAs($this->usuarioCon(self::PERMISO))
            ->get(route('seguimiento.index'))
            ->assertOk();
    }

    public function test_it_shows_only_the_chosen_supervisors_team(): void
    {
        [$supervisor, $agente] = $this->equipo();
        [, $ajeno] = $this->equipo();

        $this->contacto($agente, '20111111111');
        $this->contacto($ajeno, '20999999999');

        $this
            ->actingAs($this->usuarioCon(self::PERMISO))
            ->get(route('seguimiento.index', ['supervisor' => $supervisor->id_supervisor]))
            ->assertOk()
            ->assertSee('20111111111')
            ->assertDontSee('20999999999');
    }

    public function test_a_contact_on_the_last_day_of_the_range_is_included(): void
    {
        // The column is a datetime, so bounding on the bare date would drop
        // everything logged after midnight on the final day.
        [$supervisor, $agente] = $this->equipo();
        Llamada::factory()->create([
            'id_usuario' => $agente->id_usuario,
            'ruc' => '20111111111',
            'fecha' => now()->setTime(16, 40),
        ]);

        $this
            ->actingAs($this->usuarioCon(self::PERMISO))
            ->get(route('seguimiento.index', [
                'supervisor' => $supervisor->id_supervisor,
                'desde' => now()->toDateString(),
                'hasta' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('20111111111');
    }

    public function test_the_export_carries_the_line_counts_the_agent_recorded(): void
    {
        [$supervisor, $agente] = $this->equipo();
        $this->contacto($agente, '20111111111', ['q_entel' => 7]);

        $hoja = $this->hoja(
            $this->actingAs($this->usuarioCon(self::PERMISO))
                ->get(route('seguimiento.exportar', ['supervisor' => $supervisor->id_supervisor])),
        );

        $this->assertStringContainsString('<t>LINEAS ENTEL</t>', $hoja);
        $this->assertStringContainsString('<t>20111111111</t>', $hoja);
    }

    public function test_exporting_without_a_supervisor_asks_for_one_instead_of_sending_an_empty_file(): void
    {
        $this
            ->actingAs($this->usuarioCon(self::PERMISO))
            ->get(route('seguimiento.exportar'))
            ->assertRedirect(route('seguimiento.index'));
    }

    public function test_an_unknown_supervisor_is_treated_as_none_chosen(): void
    {
        $this
            ->actingAs($this->usuarioCon(self::PERMISO))
            ->get(route('seguimiento.index', ['supervisor' => 99_999]))
            ->assertOk()
            ->assertSee('Elija un supervisor');
    }

    /**
     * A contact logged today, so it falls inside the range the screen defaults
     * to. The factory dates contacts across three months.
     *
     * @param  array<string, mixed>  $atributos
     */
    private function contacto(Usuario $agente, string $ruc, array $atributos = []): Llamada
    {
        return Llamada::factory()->create(
            $atributos
            + [
                'id_usuario' => $agente->id_usuario,
                'ruc' => $ruc,
                'fecha' => now(),
            ],
        );
    }

    private function usuarioCon(string $permiso): Usuario
    {
        $user = Usuario::factory()->createOne();
        $user->menus()->attach(Menu::factory()->create(['permiso' => $permiso]));

        return $user;
    }

    /**
     * @return array{Supervisor, Usuario}
     */
    private function equipo(): array
    {
        $supervisor = Supervisor::factory()->create();

        return [$supervisor, Usuario::factory()->create(['id_supervisor' => $supervisor->id_supervisor])];
    }
}
