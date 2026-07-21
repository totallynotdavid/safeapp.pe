<?php

namespace Tests\Feature\Exports;

use App\Models\NcMovil;
use App\Models\Usuario;
use Illuminate\Testing\TestResponse;

/**
 * Notas de cargo móvil as a spreadsheet.
 *
 * Móvil is not a copy of fija with the names changed: it carries the cartera
 * attribution fija drops, four free-text comentario columns to fija's three, and
 * a period window that dates a CAIDA case by fecha_ingreso. Each of those is a
 * way this export can be wrong that the fija suite cannot catch.
 */
class ExportacionMovilTest extends ExportacionTestCase
{
    public function test_each_endpoint_downloads_under_its_own_name(): void
    {
        // Ten different legacy reports all downloaded as reporteseguimientoFT.xls,
        // so the browser silently renamed them and an operator could not tell two
        // pulls apart. The stem is written once per endpoint, which is exactly
        // where that gets copied wrong.
        $ejecutivo = $this->usuarioCon('notacargo.movil.registrar');
        NcMovil::factory()->registrada()->deRuc(self::RUC)->create(['id_usuario' => $ejecutivo->id_usuario]);

        $this
            ->actingAs($ejecutivo)
            ->get(route('notas-cargo.movil.exportar'))
            ->assertDownload('mis-notas-cargo-movil-'.now()->format('Y-m-d').'.xlsx');

        $this->exportarBack()->assertDownload(
            'notas-cargo-movil-back-'.now()->format('Y-m-d').'.xlsx',
        );
    }

    public function test_a_ruc_stays_text_rather_than_becoming_a_number(): void
    {
        $this->casoDelMes(self::RUC);

        $hoja = $this->hoja($this->exportarBack());

        // A text cell serializes as t="inlineStr" with the value inside <t>; a
        // numeric one is a bare <v>. Both would show the digits somewhere, so
        // this checks the type rather than the presence.
        $this->assertStringContainsString('<t>'.self::RUC.'</t>', $hoja);
        $this->assertStringNotContainsString('<v>'.self::RUC.'</v>', $hoja);
    }

    public function test_no_free_text_column_is_written_as_a_formula(): void
    {
        // Four columns on a móvil case are prose someone typed, one per stage.
        // Cell::fromValue promotes any string starting with '=' to a live
        // formula, so each of them is a way a comment becomes a computed cell.
        // Asserting on all four together is what keeps a fifth from being added
        // without the guard.
        $this->casoDelMes(self::RUC, [
            'comentario_ejecutivo' => '=SUMA(A1:A9)',
            'comentario_validador' => '=HOY()',
            'comentario_back' => '=1+1',
            'comentario_liquidador' => '=CONCAT(B1,B2)',
        ]);

        $hoja = $this->hoja($this->exportarBack());

        $this->assertStringNotContainsString('<f>', $hoja, 'una celda quedó como fórmula');
        foreach (['=SUMA(A1:A9)', '=HOY()', '=1+1', '=CONCAT(B1,B2)'] as $texto) {
            $this->assertStringContainsString($texto, $hoja);
        }
    }

    public function test_the_export_carries_the_same_period_as_the_screen(): void
    {
        // The contract legacy broke: its endpoints re-ran their own copy of the
        // screen's SQL and drifted from it. An ACTIVADO case is windowed by
        // fecha_actualizacion, so one updated four months ago is outside the
        // default month on both the list and the download.
        $this->casoDelMes(self::RUC);
        $this->casoDelMes(self::RUC_AJENO, [
            'fecha_ingreso' => now()->subMonths(4),
            'fecha_actualizacion' => now()->subMonths(4),
        ]);

        $hoja = $this->hoja($this->exportarBack());

        $this->assertStringContainsString(self::RUC, $hoja);
        $this->assertStringNotContainsString(self::RUC_AJENO, $hoja);
    }

    public function test_a_month_asked_for_is_the_month_downloaded(): void
    {
        $this->casoDelMes(self::RUC);
        $antiguo = now()->subMonths(4);
        $this->casoDelMes(self::RUC_AJENO, [
            'fecha_ingreso' => $antiguo,
            'fecha_actualizacion' => $antiguo,
        ]);

        $hoja = $this->hoja($this->exportarBack(['ano' => $antiguo->year, 'mes' => $antiguo->month]));

        $this->assertStringContainsString(self::RUC_AJENO, $hoja);
        $this->assertStringNotContainsString(self::RUC, $hoja);
    }

    public function test_an_unvalidated_case_is_no_more_exportable_than_it_is_listable(): void
    {
        NcMovil::factory()
            ->rechazada()
            ->deRuc(self::RUC_AJENO)
            ->create(['fecha_ingreso' => now(), 'fecha_actualizacion' => now()]);

        $this->assertStringNotContainsString(self::RUC_AJENO, $this->hoja($this->exportarBack()));
    }

    public function test_the_screens_permiso_gates_each_export(): void
    {
        // The export is a view of the queue, so it answers to the queue's permiso
        // and carries none of its own. Legacy's exports checked only for a
        // session, and the back queue's permiso is not the registro queue's.
        $sinPermiso = Usuario::factory()->create();
        $ejecutivo = $this->usuarioCon('notacargo.movil.registrar');

        $this->actingAs($sinPermiso)->get(route('notas-cargo.movil.exportar'))->assertForbidden();
        $this->actingAs($sinPermiso)->get(route('notas-cargo.movil.back.exportar'))->assertForbidden();
        $this->actingAs($ejecutivo)->get(route('notas-cargo.movil.back.exportar'))->assertForbidden();
    }

    public function test_an_ejecutivo_exports_only_their_own_cases(): void
    {
        // Legacy's registro export took the user id from a hidden form field, so
        // editing it dumped another ejecutivo's queue.
        $ejecutivo = $this->usuarioCon('notacargo.movil.registrar');
        $otro = $this->usuarioCon('notacargo.movil.registrar');

        NcMovil::factory()->registrada()->deRuc(self::RUC)->create(['id_usuario' => $ejecutivo->id_usuario]);
        NcMovil::factory()->registrada()->deRuc(self::RUC_AJENO)->create(['id_usuario' => $otro->id_usuario]);

        $hoja = $this->hoja(
            $this->actingAs($ejecutivo)->get(route('notas-cargo.movil.exportar')),
        );

        $this->assertStringContainsString(self::RUC, $hoja);
        $this->assertStringNotContainsString(self::RUC_AJENO, $hoja);
    }

    /**
     * A validated, activated case closed in the current month, which is what the
     * back queue lists by default.
     *
     * @param  array<string, mixed>  $atributos
     */
    private function casoDelMes(string $ruc, array $atributos = []): NcMovil
    {
        return NcMovil::factory()
            ->deRuc($ruc)
            ->create([
                'fecha_ingreso' => now(),
                'fecha_actualizacion' => now(),
                ...$atributos,
            ]);
    }

    /**
     * @param  array<string, mixed>  $filtro
     */
    private function exportarBack(array $filtro = []): TestResponse
    {
        return $this
            ->actingAs($this->usuarioCon('notacargo.movil.back'))
            ->get(route('notas-cargo.movil.back.exportar', $filtro));
    }
}
