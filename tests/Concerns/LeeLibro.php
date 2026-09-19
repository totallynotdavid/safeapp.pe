<?php

namespace Tests\Concerns;

use Illuminate\Testing\TestResponse;
use ZipArchive;

/**
 * Reads a streamed .xlsx download back. An xlsx is a zip, so opening one is
 * also the check that the download is the format it claims: legacy's exports
 * were HTML tables under a spreadsheet content type and would fail to open
 * here.
 */
trait LeeLibro
{
    protected function libro(TestResponse $respuesta): ZipArchive
    {
        $ruta = tempnam(sys_get_temp_dir(), 'xlsx');
        file_put_contents($ruta, $respuesta->streamedContent());

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($ruta) === true, 'el archivo no es un xlsx');

        return $zip;
    }

    /** The sheet XML, which is where cell values and any <f> formulas land. */
    protected function hoja(TestResponse $respuesta): string
    {
        $zip = $this->libro($respuesta);
        $xml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        return (string) $xml;
    }
}
