<?php

namespace Tests\Feature\Cutover;

use App\Models\NcFija;
use App\Models\NcMovil;

/**
 * Pairs of legacy columns that held the same fact and were allowed to disagree,
 * collapsing onto the one column the rewrite kept.
 *
 * Each pair needs a rule about which side wins, and each rule is an answer the
 * data gave rather than a preference.
 */
class ColumnasReconciliadasTest extends CutoverTestCase
{
    /**
     * `zonal` is the región the ejecutivo chose; legacy wrote back's correction
     * to `zonal_telefonica` and left the original standing, so the two disagree
     * in 1,835 fija cases and each screen read whichever it joined through.
     * That the second column is back's is measured, not assumed: it is set in
     * none of the 806 cases the back office never touched.
     */
    public function test_the_back_offices_correction_to_the_region_wins(): void
    {
        $this->casoFijaLegado(1, ['zonal' => '4', 'zonal_telefonica' => '7']);

        $this->transformar();

        $this->assertSame(7, NcFija::query()->sole()->zonal);
    }

    public function test_the_region_stands_when_the_back_office_never_touched_the_case(): void
    {
        $this->casoFijaLegado(1, ['zonal' => '4', 'zonal_telefonica' => '']);
        $this->casoFijaLegado(2, ['zonal' => '3', 'zonal_telefonica' => '0']);

        $this->transformar();

        $this->assertSame([4, 3], NcFija::query()->orderBy('id_cargo')->pluck('zonal')->all());
    }

    public function test_the_same_rule_applies_to_movil(): void
    {
        $this->casoMovilLegado(['zonal' => '4', 'zonal_telefonica' => '1']);

        $this->transformar();

        $this->assertSame(1, NcMovil::query()->sole()->zonal);
    }

    /**
     * Registro wrote the carrier account executive into a text `ejecutivo_tdp`,
     * back wrote it into an int `id_cartera`, and the screens joined cartera
     * through different ones. The populated column wins: 576 rows to 3.
     */
    public function test_the_column_the_screens_actually_filled_owns_the_attribution(): void
    {
        $this->casoMovilLegado(['ejecutivo_tdp' => '65', 'id_cartera' => 85]);

        $this->transformar();

        $this->assertSame(65, NcMovil::query()->sole()->id_cartera);
    }

    public function test_the_other_column_is_still_read_when_it_is_the_only_one_set(): void
    {
        $this->casoMovilLegado(['ejecutivo_tdp' => '', 'id_cartera' => 12]);

        $this->transformar();

        $this->assertSame(12, NcMovil::query()->sole()->id_cartera);
    }

    /**
     * @param  array<string, mixed>  $atributos
     */
    private function casoFijaLegado(int $id, array $atributos): void
    {
        $this->filaLegado('nc_fija', [
            'id_cargo' => $id,
            'ruc' => '20123456789',
            'razon_social' => 'CLIENTE UNO SAC',
            'id_usuario' => 1,
            ...$atributos,
        ]);
    }

    /**
     * @param  array<string, mixed>  $atributos
     */
    private function casoMovilLegado(array $atributos): void
    {
        $this->filaLegado('nc_movil', [
            'ruc' => '20123456789',
            'razon_social' => 'CLIENTE UNO SAC',
            'id_usuario' => 1,
            ...$atributos,
        ]);
    }
}
