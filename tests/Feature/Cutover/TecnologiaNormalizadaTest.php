<?php

namespace Tests\Feature\Cutover;

use App\Models\NcFija;

class TecnologiaNormalizadaTest extends CutoverTestCase
{
    /**
     * The screen offers a select of three technologies, so a value outside them
     * would be unreachable from the form that has to edit it. Spellings of one
     * collapse onto it. Anything else is left unset.
     */
    public function test_the_technology_spellings_collapse_onto_the_three_that_exist(): void
    {
        $esperadas = [
            'FFTH' => 'FTTH',
            'FIBRA OPTICA' => 'FTTH',
            'FTTTHH' => 'FTTH',
            'HCF' => 'HFC',
            'GPON' => 'GPON',
            '-12.071959726349125, -77.07335301123503' => null,
            'HFC/GPON' => null,
        ];

        foreach (array_keys($esperadas) as $indice => $escrito) {
            $this->casoFijaLegado($indice + 1, $escrito);
        }

        $this->transformar();

        $this->assertSame(
            array_values($esperadas),
            NcFija::query()->orderBy('id_cargo')->pluck('tecnologia')->all(),
        );
    }

    private function casoFijaLegado(int $id, string $tecnologia): void
    {
        $this->filaLegado('nc_fija', [
            'id_cargo' => $id,
            'ruc' => '20123456789',
            'razon_social' => 'CLIENTE UNO SAC',
            'id_usuario' => 1,
            'tecnologia' => $tecnologia,
        ]);
    }
}
