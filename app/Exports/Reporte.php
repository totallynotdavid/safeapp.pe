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
 * An export is handed the same builder the screen filters with, never its own
 * query. Legacy's exports each re-ran a copy of the screen's SQL, so an export
 * and its screen drifted apart.
 */
final class Reporte
{
    private const POR_LOTE = 500;

    // OpenSpout 5 made Style readonly, so one instance is shared by every date
    // cell.
    private readonly Style $fecha;

    private function __construct()
    {
        $this->fecha = (new Style)->withFormat('dd/mm/yyyy');
    }

    /**
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

        // Batches are keyed by primary key, so no row is skipped or repeated
        // part-way through. That is also why export order is registration
        // order, not the screen's: offset paging over a non-unique sort column
        // (every one of these screens sorts by a date) drops and duplicates
        // rows. Sorting is the spreadsheet's job; not losing rows is ours.
        foreach ($consulta->lazyById(self::POR_LOTE) as $modelo) {
            $writer->addRow(new Row(array_map($this->celda(...), $fila($modelo))));
        }

        $writer->close();
    }

    /** The date keeps repeat downloads of a report from colliding in the browser. */
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
        // beginning with '=' would land as a formula instead of what was
        // written. A data export has no business holding formulas.
        if (is_string($valor) && $valor !== '') {
            return new StringCell($valor, null);
        }

        return Cell::fromValue($valor);
    }
}
