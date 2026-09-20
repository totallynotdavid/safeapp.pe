# Glossary

Class, table, and column names are Spanish because the business vocabulary is
Spanish. This table lists the terms that recur across the docs.

| Term                   | Meaning                                                                                                                      |
| ---------------------- | ---------------------------------------------------------------------------------------------------------------------------- |
| `cliente`              | A customer identified by its RUC. Identity only, with no campaign or workflow state.                                         |
| `dato`                 | One customer on one campaign list. The unit of work an agent calls, and the row behind Gestión móvil and Gestión fija.       |
| `origen`               | Where a `dato` came from, such as a campaign file or `INHOUSE` for a record an agent registered.                             |
| `tipo_data`            | The segment of a `dato`, `MOVIL` or `FIJA`. It selects the queue that works the record.                                      |
| `gestión`, `llamada`   | A contact an agent logged against a customer. `llamada` is the table.                                                        |
| `nota de cargo` (`nc`) | A sales case filed by an `ejecutivo`, in a móvil form (`nc_movil`) and a fija form (`nc_fija`).                              |
| `ejecutivo`            | The user who registers a case and tracks their own.                                                                          |
| `validador`            | The user who rules on a case: `PROCEDE` or `RECHAZADO`.                                                                      |
| `back`                 | The back office. It moves a validated case through its `estado` lifecycle.                                                   |
| `liquidación`          | Settlement of a móvil case.                                                                                                  |
| `quiebre`              | An incident case, called an avería in the screens, registered for a customer and validated by a user assigned to its tienda. |
| `tienda`               | A store or region. Users are assigned to tiendas, and a case records the tienda it belongs to.                               |
| `cartera`              | A portfolio that the back office records on a móvil case. It appears in the export as `EJECUTIVO TDP`.                       |
| `permiso`              | A string on a `menu` row that grants access to one screen, such as `gestion.movil`.                                          |
| `cockpit`              | The campaign-work domain: imports, customer registration, queue distribution, and contact handling.                          |
