# Schema

Migrations in [`database/migrations/`](../database/migrations/) are the schema
source of truth. Models use the legacy singular table names and `id_<table>`
primary keys, and most have no timestamps.

| Table                          | Model                          | Holds                                                                |
| ------------------------------ | ------------------------------ | -------------------------------------------------------------------- |
| `cliente`                      | `Cliente`                      | Customer identity by RUC.                                            |
| `cliente_acceso`               | `AccesoCliente`                | Which user may see which customer.                                   |
| `datos`                        | `Dato`                         | A customer on a campaign list, with its `estado`.                    |
| `asignacion_datos`             | `AsignacionDato`               | Which agent holds a `dato`. At most one active row per `dato`.       |
| `llamada`                      | `Llamada`                      | Logged contacts. Append-only.                                        |
| `respuesta`                    | `Respuesta`                    | The outcomes an agent can record on a contact.                       |
| `nc_movil`, `nc_fija`          | `NcMovil`, `NcFija`            | Notas de cargo.                                                      |
| `producto`                     | `Producto`                     | The product catalog behind the fija form.                            |
| `cartera`                      | `Cartera`                      | Portfolios the back office assigns to a móvil case.                  |
| `quiebre`                      | `Quiebre`                      | Incident cases.                                                      |
| `usuario`, `rol`, `supervisor` | `Usuario`, `Rol`, `Supervisor` | Accounts, roles, and supervisors.                                    |
| `menu`, `acceso`, `accesoxrol` | `Menu`                         | Permisos, per-user grants, and per-role grants.                      |
| `tienda`, `asignacion_tienda`  | `Tienda`, `AsignacionTienda`   | Stores and each user's assignments. `estado` 0 marks the active one. |
