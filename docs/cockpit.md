# Cockpit

[`app/Cockpit/`](../app/Cockpit/) owns the campaign workflow. It has no HTTP
knowledge, so a controller hands it validated values and renders the result.

```text
campaign file ──► ImportacionDeClientes ──► cliente + datos
                                                 │
                                DistribucionDeDatos::asignarLote
                                                 │
                                  asignacion_datos + cliente_acceso
                                                 │
                                   Dato::registrarGestion
                                                 │
                                   llamada + datos.estado
```

**Import.** `ImportacionDeClientes` reads a CSV or XLSX file with `LectorCsv` or
`LectorXlsx` from [`app/Support/`](../app/Support/). `FormatoDeImportacion`
defines the columns, the required ones, and the validation rules. It also feeds
`PlantillaDeImportacion`, so the downloadable template cannot drift from what
the upload enforces. The import is all or nothing. It validates the whole file
first and reports up to 50 errors, including a repeated RUC. It then upserts
`cliente` and `datos` in batches of 500 inside one transaction. A reload updates
the campaign columns and leaves `estado` alone, so it never resets an agent's
progress. `ResultadoImportacion` carries either counts or errors, never both.

**Distribution.** An agent draws a batch of `LOTE` (30) pending, unassigned
`datos` of one `origen` and one `tipo_data` with `DistribucionDeDatos`. The
selection runs with `lockForUpdate` inside a transaction, so two concurrent
requests cannot draw the same records. Each draw writes an `asignacion_datos`
row and grants the agent access to the customer. Releasing returns only the
records still pending. Managed records stay assigned so their activity keeps an
owner.

**Queue.** `Dato::scopeEnCola` selects an agent's pending records for one
segment under their active tienda. A record assigned under a tienda the agent no
longer holds does not appear.

**Contact.** `Dato::registrarGestion` inserts the `llamada` and marks the `dato`
as gestionado in one transaction. The `llamada` takes its channel from the
chosen `respuesta`, never from the request. `Llamada` is append-only and points
at the customer, so a contact survives its campaign.

**Registering a customer.** `AltaDeClientes` lets an agent add a customer
directly. It creates or reuses the `cliente`, upserts an `INHOUSE` `dato`, and
assigns it to the agent unless it is already assigned. Registering again does
not reset a record that was already worked.

**Line mix.** `Operadores` is the single owner of the carrier list, the
`q_<carrier>` column naming, and the validation and coercion of the per-carrier
counts. `MezclaDeLineas` is the one shape a screen renders for a customer's
lines per carrier, whether they come from the campaign file (`datos`) or from
what an agent confirmed on a call (`llamada`). The two sources are stored
separately.
