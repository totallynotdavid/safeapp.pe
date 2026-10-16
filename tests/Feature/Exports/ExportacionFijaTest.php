<?php

namespace Tests\Feature\Exports;

use App\Models\NcFija;
use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Testing\TestResponse;

// The format and formula rules are Reporte's, so they hold for every export and
// are not repeated in the móvil and contacto suites.
class ExportacionFijaTest extends ExportacionTestCase
{
    public function test_the_export_is_a_real_workbook_not_an_html_table(): void
    {
        NcFija::factory()->liquidadaEsteMes()->deRuc(self::RUC)->create();

        $respuesta = $this->exportarBackFija();

        $respuesta->assertOk();
        $respuesta->assertHeader(
            'content-type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        );

        $zip = $this->libro($respuesta);
        $this->assertNotFalse($zip->locateName('xl/workbook.xml'));
        $zip->close();
    }

    public function test_the_filename_carries_the_date_so_two_pulls_do_not_collide(): void
    {
        NcFija::factory()->liquidadaEsteMes()->deRuc(self::RUC)->create();

        $this->exportarBackFija()->assertDownload(
            'notas-cargo-fija-back-'.now()->format('Y-m-d').'.xlsx',
        );
    }

    public function test_a_ruc_stays_text_rather_than_becoming_a_number(): void
    {
        // An 11-digit RUC read as a number is what makes a spreadsheet render it
        // as 2.01235E+10 and drop any leading zero. The column is a varchar and
        // has to arrive as one.
        NcFija::factory()->liquidadaEsteMes()->deRuc(self::RUC)->create();

        $hoja = $this->hoja($this->exportarBackFija());

        // A text cell serializes as t="inlineStr" with the value inside <t>; a
        // numeric one is a bare <v>. Asserting the digits appear somewhere would
        // pass either way, so this checks the type.
        $this->assertStringContainsString('<t>'.self::RUC.'</t>', $hoja);
        $this->assertStringNotContainsString('<v>'.self::RUC.'</v>', $hoja);
    }

    public function test_a_comment_starting_with_an_equals_sign_is_not_written_as_a_formula(): void
    {
        // comentario_back is free text an agent types. The writer promotes any
        // string beginning with '=' to a live formula unless it is told not to,
        // which would both corrupt the value and put a computed cell in a data
        // export.
        NcFija::factory()
            ->liquidadaEsteMes()
            ->deRuc(self::RUC)
            ->create(['comentario_back' => '=SUMA(A1:A9)']);

        $hoja = $this->hoja($this->exportarBackFija());

        $this->assertStringContainsString('=SUMA(A1:A9)', $hoja);
        $this->assertStringNotContainsString('<f>', $hoja, 'la celda quedó como fórmula');
    }

    public function test_the_export_carries_the_same_filter_as_the_screen(): void
    {
        // A case outside the requested month is absent from the download
        // exactly as it is absent from the list.
        NcFija::factory()->liquidadaEsteMes()->deRuc(self::RUC)->create();
        NcFija::factory()
            ->deRuc(self::RUC_AJENO)
            ->create([
                'fecha_ingreso' => now()->subMonths(4),
                'fecha_actualizacion' => now()->subMonths(4),
                'fecha_liquidacion' => now()->subMonths(4),
            ]);

        $hoja = $this->hoja($this->exportarBackFija());

        $this->assertStringContainsString(self::RUC, $hoja);
        $this->assertStringNotContainsString(self::RUC_AJENO, $hoja);
    }

    public function test_an_unvalidated_case_is_no_more_exportable_than_it_is_listable(): void
    {
        NcFija::factory()->rechazada()->liquidadaEsteMes()->deRuc(self::RUC_AJENO)->create();

        $this->assertStringNotContainsString(self::RUC_AJENO, $this->hoja($this->exportarBackFija()));
    }

    public function test_the_screens_permiso_gates_the_export(): void
    {
        // The export is a view of the queue, so it answers to the queue's permiso
        // and needs none of its own.
        $this
            ->actingAs(Usuario::factory()->create())
            ->get(route('notas-cargo.fija.back.exportar'))
            ->assertForbidden();
    }

    public function test_an_ejecutivo_exports_only_their_own_cases(): void
    {
        // The export scopes to the session user, never to a posted id.
        $ejecutivo = $this->usuarioCon('notacargo.fija.registrar');
        $otro = $this->usuarioCon('notacargo.fija.registrar');

        NcFija::factory()->registrada()->deRuc(self::RUC)->create(['id_usuario' => $ejecutivo->id_usuario]);
        NcFija::factory()->registrada()->deRuc(self::RUC_AJENO)->create(['id_usuario' => $otro->id_usuario]);

        $hoja = $this->hoja(
            $this->actingAs($ejecutivo)->get(route('notas-cargo.fija.exportar')),
        );

        $this->assertStringContainsString(self::RUC, $hoja);
        $this->assertStringNotContainsString(self::RUC_AJENO, $hoja);
    }

    private function exportarBackFija(): TestResponse
    {
        Tienda::factory()->create();

        return $this
            ->actingAs($this->usuarioCon('notacargo.fija.back'))
            ->get(route('notas-cargo.fija.back.exportar'));
    }
}
