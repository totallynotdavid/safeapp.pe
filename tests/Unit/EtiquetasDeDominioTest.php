<?php

namespace Tests\Unit;

use App\Support\EtiquetasDeDominio;
use PHPUnit\Framework\TestCase;

class EtiquetasDeDominioTest extends TestCase
{
    public function test_it_keeps_storage_codes_and_exposes_sentence_case_labels(): void
    {
        $this->assertSame('Salida anticipada', EtiquetasDeDominio::texto('SALIDAANTICIPADA', 'estado'));
        $this->assertSame('Portabilidad', EtiquetasDeDominio::texto('PORTABILIDAD', 'modalidad'));
        $this->assertSame('Procede', EtiquetasDeDominio::texto('PROCEDE', 'validacion'));
        $this->assertSame('M4/Combo', EtiquetasDeDominio::texto('M4/COMBO', 'tipo_producto'));
        $this->assertSame('BAM', EtiquetasDeDominio::texto('BAM', 'modalidad'));
        $this->assertSame('Sin TV', EtiquetasDeDominio::texto('SIN TV', 'general'));
    }

    public function test_it_builds_selector_options_without_changing_submitted_values(): void
    {
        $this->assertSame(
            [
                ['value' => 'PENDIENTE', 'label' => 'Pendiente'],
                ['value' => 'ATENDIDO', 'label' => 'Atendido'],
            ],
            EtiquetasDeDominio::opciones(['PENDIENTE', 'ATENDIDO'], 'estado'),
        );
    }
}
