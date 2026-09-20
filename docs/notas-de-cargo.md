# Notas de cargo

A nota de cargo is one row that carries a case through four stages. Each stage
has its own controller and permiso, and each owns a separate set of columns.

| Stage       | Who         | What it writes                                     |
| ----------- | ----------- | -------------------------------------------------- |
| Registro    | ejecutivo   | The case itself.                                   |
| Validación  | validador   | `validacion`: `PENDIENTE`, `PROCEDE`, `RECHAZADO`. |
| Back        | back office | `estado` and the fulfilment fields.                |
| Liquidación | liquidador  | The settlement fields, for móvil only.             |

No stage writes another stage's columns, so the controllers assign fields
explicitly instead of sharing `$fillable`. The back stage opens only a validated case,
and the check is `NcMovil::esValidado`. The `Validable` trait in
[`app/Models/Concerns/`](../app/Models/Concerns/) owns the validation constants
and the two state questions, `esEditable` and `esValidado`, for both productos.

`nc_movil` and `nc_fija` have different `estado` vocabularies. `NcMovil` and
`NcFija` each declare their own list.

`NotaCargoBuilder`, with `NcMovilBuilder` and `NcFijaBuilder` extending it in
[`app/Models/Builders/`](../app/Models/Builders/), windows a queue to a period.
A case still in flight is bounded by year alone, so it stays on the queue however
old it is. A case that closed shows in the month it closed, dated by the column
that records the closing. Each producto declares its in-flight estados and its
closing columns. `FiltroPeriodo` in [`app/Http/`](../app/Http/) reads the year,
month, and estado from the request once. It drops an estado the screen does not
offer instead of returning an error.
