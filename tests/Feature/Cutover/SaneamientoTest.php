<?php

namespace Tests\Feature\Cutover;

use App\Models\Dato;
use App\Models\Llamada;
use App\Models\NcMovil;
use App\Models\Usuario;

class SaneamientoTest extends CutoverTestCase
{
    public function test_the_zero_date_becomes_no_date(): void
    {
        // Legacy declared these NOT NULL and then wrote '0000-00-00', including
        // in all 605 rows of usuario.fecha_nacimiento.
        $this->filaLegado('usuario', [
            'id_usuario' => 1,
            'usuario' => 'agente',
            'clave' => 'hash',
            'id_rol' => 1,
            'personal' => 'AGENTE UNO',
            'estado' => 1,
            'sexo' => 'M',
            'id_supervisor' => 0,
            'fecha_nacimiento' => '0000-00-00',
        ]);

        $this->transformar();

        $this->assertNull(Usuario::query()->sole()->fecha_nacimiento);
    }

    public function test_a_phone_number_typed_into_the_line_count_is_not_a_count(): void
    {
        // 78 contacts carry a q_lineas in the hundreds of millions, and the
        // values are Peruvian mobile numbers.
        $this->contactoLegado(['q_lineas' => 900_442_964, 'q_entel' => 3]);

        $this->transformar();

        $llamada = Llamada::query()->sole();
        $this->assertSame(0, $llamada->q_movistar);
        // The counts that are counts are left alone.
        $this->assertSame(3, $llamada->q_entel);
    }

    public function test_a_negative_line_count_is_not_a_count(): void
    {
        $this->datoLegado(['q_movistar' => -4, 'q_entel' => 2]);

        $this->transformar();

        $dato = Dato::query()->sole();
        $this->assertSame(0, $dato->q_movistar);
        $this->assertSame(2, $dato->q_entel);
    }

    public function test_the_legacy_single_count_column_becomes_the_carrier_it_meant(): void
    {
        $this->contactoLegado(['q_lineas' => 5]);

        $this->transformar();

        $this->assertSame(5, Llamada::query()->sole()->q_movistar);
    }

    public function test_a_phone_number_typed_into_the_charge_is_not_a_charge(): void
    {
        // One móvil case carries 996871768. Real charges run 31.90 to 539.90.
        $this->filaLegado('nc_movil', [
            'ruc' => '20123456789',
            'razon_social' => 'X',
            'id_usuario' => 1,
            'cargo_fijo' => 996_871_768,
        ]);

        $this->transformar();

        $this->assertSame('0.00', NcMovil::query()->sole()->cargo_fijo);
    }

    public function test_text_longer_than_the_column_is_kept_up_to_the_column(): void
    {
        // datos.zonal runs to 172 characters in 156 rows; the target holds 100.
        $this->datoLegado(['zonal' => str_repeat('A', 172)]);

        $this->transformar();

        $this->assertSame(100, mb_strlen((string) Dato::query()->sole()->zonal));
    }

    public function test_empty_text_becomes_no_text(): void
    {
        $this->contactoLegado(['nombre_contacto' => '   ', 'correo' => '']);

        $this->transformar();

        $llamada = Llamada::query()->sole();
        $this->assertNull($llamada->nombre_contacto);
        $this->assertNull($llamada->correo);
    }

    /**
     * @param  array<string, mixed>  $atributos
     */
    private function contactoLegado(array $atributos = []): void
    {
        $this->filaLegado('llamada', [
            'ruc' => '20123456789',
            'razon_social' => 'CLIENTE UNO SAC',
            'id_usuario' => 1,
            'id_respuesta' => 1,
            'fecha' => '2025-01-02 10:00:00',
            ...$atributos,
        ]);
    }
}
