<?php

namespace App\Cockpit;

use OpenSpout\Common\Entity\Cell\StringCell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class PlantillaDeImportacion
{
    public static function csv(): StreamedResponse
    {
        return response()->streamDownload(
            static function (): void {
                $output = fopen('php://output', 'w');

                fwrite($output, "\xEF\xBB\xBF");
                fputcsv(
                    $output,
                    FormatoDeImportacion::columnas(),
                    separator: ';',
                    escape: '',
                );

                fclose($output);
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
                $bold = (new Style)->withFontBold(true);

                $writer->openToFile('php://output');
                $writer->addRow(
                    new Row(
                        array_map(
                            static fn (string $columna): StringCell => new StringCell(
                                $columna,
                                $bold,
                            ),
                            FormatoDeImportacion::columnas(),
                        ),
                    ),
                );
                $writer->close();
            },
            'plantilla-clientes.xlsx',
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ],
        );
    }
}
