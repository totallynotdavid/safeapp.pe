<?php

namespace App\Exports;

use Closure;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Cell\StringCell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Turns a screen's query into a downloadable spreadsheet. Every export in the
 * app goes through here.
 *
 * There was no subsystem to port. Each of legacy's 18 export endpoints echoed
 * an HTML `<table>` under a `Content-Type: application/xls` header, which is not
 * a spreadsheet: Excel opens it by sniffing the markup and warns that the
 * extension does not match the contents. Worse, each one re-ran its own copy of
 * the screen's SQL, so the export and the screen drifted apart. That is where
 * two of the four windowing defects recorded on NotaCargoBuilder came from.
 *
 * The contract that prevents a repeat: an export is handed the same builder the
 * screen filters with, never its own query.
 *
 * Rows stream in batches keyed by primary key, so an export holds one batch in
 * memory rather than the whole result set, and no row is skipped or repeated
 * part-way through. That keying is also why **export order is registration
 * order, not the screen's order**: offset paging over a non-unique sort column
 * (every one of these screens sorts by a date) drops and duplicates rows. Sorting
 * a spreadsheet is the spreadsheet's job; not losing rows is ours.
 */
final class Reporte
{
    private const POR_LOTE = 500;

    // OpenSpout 5 made Style readonly, so a style is built once and shared
    // rather than rebuilt per cell. Every date cell used to allocate its own.
    private readonly Style $fecha;

    private function __construct()
    {
        $this->fecha = (new Style)->withFormat('dd/mm/yyyy');
    }

    /**
     * Streams the query as an .xlsx download.
     *
     * @param  string  $nombre  filename stem, dated on the way out
     * @param  list<string>  $encabezados  column titles, in order
     * @param  Builder<covariant Model>  $consulta  the screen's filter, unordered
     * @param  Closure(Model): list<null|bool|DateTimeInterface|float|int|string>  $fila
     */
    public static function descargar(
        string $nombre,
        array $encabezados,
        Builder $consulta,
        Closure $fila,
    ): StreamedResponse {
        $reporte = new self;

        return response()->streamDownload(
            static fn () => $reporte->escribir($encabezados, $consulta, $fila),
            $reporte->archivo($nombre),
            // streamDownload sets the disposition; the writer only sees the
            // stream. Calling OpenSpout's openToBrowser() here instead would
            // have it emit a second, conflicting set of headers.
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        );
    }

    /**
     * @param  list<string>  $encabezados
     * @param  Builder<covariant Model>  $consulta
     * @param  Closure(Model): list<null|bool|DateTimeInterface|float|int|string>  $fila
     */
    private function escribir(array $encabezados, Builder $consulta, Closure $fila): void
    {
        $writer = new Writer;
        $writer->openToFile('php://output');
        // OpenSpout 5 dropped the row-level default style, so the header's bold
        // is carried by its cells. StringCell for the same reason celda() uses
        // it: a title is text, never a formula.
        $negrita = (new Style)->withFontBold(true);
        $writer->addRow(new Row(array_map(
            static fn (string $titulo): Cell => new StringCell($titulo, $negrita),
            $encabezados,
        )));

        foreach ($consulta->lazyById(self::POR_LOTE) as $modelo) {
            $writer->addRow(new Row(array_map($this->celda(...), $fila($modelo))));
        }

        $writer->close();
    }

    /**
     * Legacy served every download under a fixed name (`reporteseguimientoFT.xls`
     * for ten different reports), so pulling the same report twice left the
     * browser silently renaming files. The date makes each pull distinct.
     */
    private function archivo(string $nombre): string
    {
        return $nombre.'-'.now()->format('Y-m-d').'.xlsx';
    }

    private function celda(bool|DateTimeInterface|float|int|string|null $valor): Cell
    {
        if ($valor instanceof DateTimeInterface) {
            return Cell::fromValue($valor, $this->fecha);
        }

        // Cell::fromValue promotes any string starting with '=' to a live
        // formula. These columns carry free text an agent typed, so a comment
        // beginning with '=' would land in the workbook as a formula instead of
        // as what they wrote. A data export has no business holding formulas.
        if (is_string($valor) && $valor !== '') {
            return new StringCell($valor, null);
        }

        return Cell::fromValue($valor);
    }
}
