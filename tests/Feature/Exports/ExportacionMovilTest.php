<?php

namespace Tests\Feature\Exports;

use App\Models\NcMovil;
use App\Models\Usuario;
use Illuminate\Testing\TestResponse;

class ExportacionMovilTest extends ExportacionTestCase
{
    public function test_each_endpoint_downloads_under_its_own_name(): void
    {
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

    public function test_the_export_carries_the_same_period_as_the_screen(): void
    {
        // An ACTIVADO case is windowed by fecha_actualizacion, so one updated
        // four months ago is outside the default month on both the list and the
        // download.
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
        $sinPermiso = Usuario::factory()->create();
        $ejecutivo = $this->usuarioCon('notacargo.movil.registrar');

        $this->actingAs($sinPermiso)->get(route('notas-cargo.movil.exportar'))->assertForbidden();
        $this->actingAs($sinPermiso)->get(route('notas-cargo.movil.back.exportar'))->assertForbidden();
        $this->actingAs($ejecutivo)->get(route('notas-cargo.movil.back.exportar'))->assertForbidden();
    }

    public function test_an_ejecutivo_exports_only_their_own_cases(): void
    {
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
