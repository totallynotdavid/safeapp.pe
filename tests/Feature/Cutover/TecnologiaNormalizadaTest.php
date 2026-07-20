<?php

namespace Tests\Feature\Cutover;

use App\Models\NcFija;

/**
 * Three technologies spelled 28 ways, because legacy made this a free-text
 * input.
 *
 * Its own file rather than another few cases in SaneamientoTest: the other
 * rules are about what legacy stored to mean nothing, this one is a vocabulary
 * the screen now constrains.
 */
class TecnologiaNormalizadaTest extends CutoverTestCase
{
    /**
     * Three technologies were spelled 28 ways because legacy made this a
     * free-text input. The screen offers a select now, so a value it does not
     * offer would be unreachable from the form that has to edit it.
     */
    public function test_the_technology_spellings_collapse_onto_the_three_that_exist(): void
    {
        foreach (['FFTH', 'FIBRA OPTICA', 'FTTTHH', 'HCF', 'GPON'] as $indice => $escrito) {
            $this->casoFijaLegado($indice + 1, $escrito);
        }

        $this->transformar();

        $this->assertSame(
            ['FTTH', 'FTTH', 'FTTH', 'HFC', 'GPON'],
            NcFija::query()->orderBy('id_cargo')->pluck('tecnologia')->all(),
        );
    }

    public function test_a_technology_field_holding_something_else_is_left_unset(): void
    {
        // Three rows hold GPS coordinates, pasted into the wrong field, and one
        // names two technologies at once.
        $this->casoFijaLegado(1, '-12.071959726349125, -77.07335301123503');
        $this->casoFijaLegado(2, 'HFC/GPON');

        $this->transformar();

        $this->assertSame([null, null], NcFija::query()->orderBy('id_cargo')->pluck('tecnologia')->all());
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
