# Reports

[`app/Exports/`](../app/Exports/) builds spreadsheet downloads. `Reporte` is the
one writer. It is handed the same builder the screen filters with, so an export
cannot drift from its screen. It streams XLSX through OpenSpout and reads rows
with `lazyById` in batches of 500. Export order is therefore registration order,
not the screen's sort order, because offset paging over a non-unique sort column
drops and repeats rows. `Reporte` writes text as string cells, so a free-text
comment that begins with `=` is never stored as a formula.

`ReporteNotaCargoMovil` and `ReporteNotaCargoFija` declare the columns of each
producto. `ReporteContacto` serves both call-log reports, Histórico for an
agent's own contacts and Seguimiento for a supervisor's team. They differ only in
who is in scope. `RangoFechas` in [`app/Http/`](../app/Http/) parses the date
range for those reports and falls back to the last seven days when the input is
missing, unparseable, or inverted.
