<?php

namespace Tests\Feature\Exports;

use App\Cockpit\Operadores;
use App\Models\Llamada;
use App\Models\Usuario;
use Illuminate\Testing\TestResponse;

/**
 * The call log as a spreadsheet, from histórico: an agent's own contacts over a
 * date range.
 *
 * Seguimiento is the same report over a supervisor's whole team and is covered
 * with that screen. What is specific here is the scoping, which is to the signed
 * in agent and to nothing the request can name.
 */
class ExportacionContactoTest extends ExportacionTestCase
{
    private const PERMISO = 'historico.ver';

    public function test_the_download_carries_its_own_dated_name(): void
    {
        $agente = $this->usuarioCon(self::PERMISO);
        $this->contacto($agente, self::RUC);

        $this->exportar($agente)->assertDownload('mis-gestiones-'.now()->format('Y-m-d').'.xlsx');
    }

    public function test_an_agent_exports_only_their_own_contacts(): void
    {
        // The export takes the agent from the session. There is no parameter for
        // it, so no query string can widen the scope to another agent's log.
        $agente = $this->usuarioCon(self::PERMISO);
        $otro = $this->usuarioCon(self::PERMISO);

        $this->contacto($agente, self::RUC);
        $this->contacto($otro, self::RUC_AJENO);

        $hoja = $this->hoja($this->exportar($agente));

        $this->assertStringContainsString(self::RUC, $hoja);
        $this->assertStringNotContainsString(self::RUC_AJENO, $hoja);
    }

    public function test_the_export_carries_the_same_range_as_the_screen(): void
    {
        // Both default to the last week, and both widen to whatever the query
        // string asks for. Legacy's report re-parsed the range itself, which is
        // how an export came to cover a different window than the list above it.
        $agente = $this->usuarioCon(self::PERMISO);
        $this->contacto($agente, self::RUC, ['fecha' => now()->subDays(2)]);
        $this->contacto($agente, self::RUC_AJENO, ['fecha' => now()->subMonths(3)]);

        $this->assertStringNotContainsString(self::RUC_AJENO, $this->hoja($this->exportar($agente)));

        $hoja = $this->hoja($this->exportar($agente, [
            'desde' => now()->subMonths(4)->toDateString(),
            'hasta' => now()->toDateString(),
        ]));
        $this->assertStringContainsString(self::RUC_AJENO, $hoja);
    }

    public function test_an_unparseable_range_falls_back_instead_of_erroring(): void
    {
        // Same rule the screen follows: these arrive from a query string anyone
        // can edit, and a download should still be a workbook.
        $agente = $this->usuarioCon(self::PERMISO);
        $this->contacto($agente, self::RUC);

        $hoja = $this->hoja($this->exportar($agente, ['desde' => 'no-es-fecha', 'hasta' => '']));

        $this->assertStringContainsString(self::RUC, $hoja);
    }

    public function test_a_comment_starting_with_an_equals_sign_is_not_written_as_a_formula(): void
    {
        // comentario is the one column here an agent types freely, and it is the
        // reason the report exists: it is what the call established.
        $agente = $this->usuarioCon(self::PERMISO);
        $this->contacto($agente, self::RUC, ['comentario' => '=SUMA(A1:A9)']);

        $hoja = $this->hoja($this->exportar($agente));

        $this->assertStringContainsString('=SUMA(A1:A9)', $hoja);
        $this->assertStringNotContainsString('<f>', $hoja, 'la celda quedó como fórmula');
    }

    public function test_it_carries_a_column_per_carrier(): void
    {
        // The counts an agent confirms on every call. Legacy's report left them
        // out, so the report could not show what the call actually established.
        $agente = $this->usuarioCon(self::PERMISO);
        $this->contacto($agente, self::RUC);

        $hoja = $this->hoja($this->exportar($agente));

        foreach (Operadores::TODOS as $operador) {
            $this->assertStringContainsString('<t>LINEAS '.$operador.'</t>', $hoja);
        }
    }

    public function test_the_screens_permiso_gates_the_export(): void
    {
        $this
            ->actingAs(Usuario::factory()->create())
            ->get(route('historico.exportar'))
            ->assertForbidden();
    }

    /**
     * A contact logged today unless the test says otherwise, so it falls inside
     * the range the screen defaults to. The factory dates contacts across three
     * months.
     *
     * @param  array<string, mixed>  $atributos
     */
    private function contacto(Usuario $agente, string $ruc, array $atributos = []): Llamada
    {
        return Llamada::factory()->create([
            'id_usuario' => $agente->id_usuario,
            'ruc' => $ruc,
            'fecha' => now(),
            ...$atributos,
        ]);
    }

    /**
     * @param  array<string, mixed>  $rango
     */
    private function exportar(Usuario $agente, array $rango = []): TestResponse
    {
        return $this->actingAs($agente)->get(route('historico.exportar', $rango));
    }
}
