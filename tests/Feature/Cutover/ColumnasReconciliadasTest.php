<?php

namespace Tests\Feature\Cutover;

use App\Models\NcFija;
use App\Models\NcMovil;
use PHPUnit\Framework\Attributes\DataProvider;

class ColumnasReconciliadasTest extends CutoverTestCase
{
    /**
     * `zonal` is the región the ejecutivo chose and `zonal_telefonica` is the
     * back office's correction. The correction wins when it is set.
     *
     * @return array<string, array{string, string, int}>
     */
    public static function regiones(): array
    {
        return [
            'corrected' => ['4', '7', 7],
            'never corrected, empty' => ['4', '', 4],
            'never corrected, zero' => ['3', '0', 3],
        ];
    }

    #[DataProvider('regiones')]
    public function test_the_back_offices_correction_to_the_region_wins(
        string $zonal,
        string $corregida,
        int $esperada,
    ): void {
        $this->casoFijaLegado(1, ['zonal' => $zonal, 'zonal_telefonica' => $corregida]);
        $this->casoMovilLegado(['zonal' => $zonal, 'zonal_telefonica' => $corregida]);

        $this->transformar();

        $this->assertSame($esperada, NcFija::query()->sole()->zonal);
        $this->assertSame($esperada, NcMovil::query()->sole()->zonal);
    }

    /**
     * Registro wrote the carrier account executive into a text `ejecutivo_tdp`
     * and back wrote it into an int `id_cartera`. The populated column wins,
     * and `ejecutivo_tdp` is preferred when both are set.
     *
     * @return array<string, array{string, int, int}>
     */
    public static function atribuciones(): array
    {
        return [
            'both set' => ['65', 85, 65],
            'only id_cartera set' => ['', 12, 12],
        ];
    }

    #[DataProvider('atribuciones')]
    public function test_the_column_the_screens_actually_filled_owns_the_attribution(
        string $tdp,
        int $cartera,
        int $esperada,
    ): void {
        $this->casoMovilLegado(['ejecutivo_tdp' => $tdp, 'id_cartera' => $cartera]);

        $this->transformar();

        $this->assertSame($esperada, NcMovil::query()->sole()->id_cartera);
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
