<?php

namespace App\Cockpit;

use OpenSpout\Common\Entity\Cell\StringCell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Generates empty, parser-backed campaign templates for operators to fill. */
final class PlantillaDeImportacion
{
    public static function csv(): StreamedResponse
    {
        return response()->streamDownload(
            static function (): void {
                $salida = fopen('php://output', 'w');
                fwrite($salida, "\xEF\xBB\xBF");
                fputcsv($salida, FormatoDeImportacion::columnas(), separator: ';', escape: '');
                fclose($salida);
            },
            'plantilla-clientes.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }

    public static function xlsx(): StreamedResponse
    {
        return response()->streamDownload(
            static function (): void {
                $writer = new Writer;
                $writer->openToFile('php://output');
                $negrita = (new Style)->withFontBold(true);
                $writer->addRow(new Row(array_map(
                    static fn (string $columna): StringCell => new StringCell($columna, $negrita),
                    FormatoDeImportacion::columnas(),
                )));
                $writer->close();
            },
            'plantilla-clientes.xlsx',
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ],
        );
    }
}
