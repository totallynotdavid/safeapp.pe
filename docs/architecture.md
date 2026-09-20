# Architecture

SafeApp is a single Laravel application. A request passes through a route, a
permission check, a form request, a controller, and a model or domain class, and
it ends in a Blade view or a spreadsheet download. There is no separate API.

Class, table, and column names are Spanish because the business vocabulary is
Spanish. The glossary below lists the terms that recur.

## Glossary

| Term                    | Meaning                                                                                                                       |
| ----------------------- | ----------------------------------------------------------------------------------------------------------------------------- |
| `cliente`               | A customer identified by its RUC. Identity only, with no campaign or workflow state.                                          |
| `dato`                  | One customer on one campaign list. The unit of work an agent calls, and the row behind Gestión móvil and Gestión fija.        |
| `origen`                | Where a `dato` came from, such as a campaign file or `INHOUSE` for a record an agent registered.                              |
| `tipo_data`             | The segment of a `dato`, `MOVIL` or `FIJA`. It selects the queue that works the record.                                       |
| `gestión`, `llamada`    | A contact an agent logged against a customer. `llamada` is the table.                                                         |
| `nota de cargo` (`nc`)  | A sales case filed by an `ejecutivo`, in a móvil form (`nc_movil`) and a fija form (`nc_fija`).                               |
| `ejecutivo`             | The user who registers a case and tracks their own.                                                                           |
| `validador`             | The user who rules on a case: `PROCEDE` or `RECHAZADO`.                                                                       |
| `back`                  | The back office. It moves a validated case through its `estado` lifecycle.                                                    |
| `liquidación`           | Settlement of a móvil case.                                                                                                   |
| `quiebre`               | An incident case, called an avería in the screens, registered for a customer and validated by a user assigned to its tienda.             |
| `tienda`                | A store or region. Users are assigned to tiendas, and a case records the tienda it belongs to.                                |
| `cartera`               | A portfolio that the back office records on a móvil case. It appears in the export as `EJECUTIVO TDP`.                        |
| `permiso`               | A string on a `menu` row that grants access to one screen, such as `gestion.movil`.                                           |
| `cockpit`               | The campaign-work domain: imports, customer registration, queue distribution, and contact handling.                           |

## Request flow

1. [`routes/web.php`](../routes/web.php) holds the login, the account settings,
   user and role administration, and the smaller modules. `bootstrap/app.php`
   also registers [`routes/cockpit.php`](../routes/cockpit.php) and
   [`routes/notas-cargo.php`](../routes/notas-cargo.php) under the `web` and
   `auth` middleware.
2. Each module's routes sit in a group with `can:<permiso>` middleware. The
   [access control](#access-control) section describes what that check does.
3. A form request in [`app/Http/Requests/`](../app/Http/Requests/) validates the
   input. There is one request class per write action, grouped by module.
4. The controller in [`app/Http/Controllers/`](../app/Http/Controllers/) reads
   or writes through a model or a `Cockpit` class and returns a view from
   `resources/views/`.
5. `AppServiceProvider` attaches the sidebar to `layouts.app` and the settings
   menu to `layouts.settings` with view composers, so a controller never builds
   navigation.

Controllers stay thin. A rule that more than one screen needs lives in one owner
and the screens call it. The owners are named in the sections below.

## Modules

| Module                | Routes          | Controllers                                                                 | Permisos                                                    |
| --------------------- | --------------- | --------------------------------------------------------------------------- | ----------------------------------------------------------- |
| Gestión comercial     | `cockpit.php`   | `GestionController`, `DistribucionController`                               | `gestion.movil`, `gestion.fija`                             |
| Análisis              | `cockpit.php`   | `HistoricoController`, `SeguimientoController`, `ProduccionController`      | `historico.ver`, `seguimiento.equipo`, `produccion.ver`     |
| Notas de cargo        | `notas-cargo.php` | `NotaCargoMovilController`, `NotaCargoFijaController`, and their `Validacion` and `Back` counterparts | `notacargo.{movil,fija}.{registrar,validar,back}` |
| Control operativo     | `web.php`       | `QuiebreRegistroController`, `QuiebreValidacionController`, `LiquidacionController` | `quiebre.registrar`, `quiebre.validar`, `liquidacion.gestionar` |
| Importación           | `web.php`       | `ImportacionController`                                                     | `clientes.importar`                                         |
| Administración        | `web.php`       | `UsuarioController`, `RolController`, `PersonalController`, `AsignacionController` | `usuarios.manage`, `roles.manage`, `personal.gestionar`, `asignacion.gestionar` |
| Cuenta                | `web.php`       | `AccountSettingsController`, `ConfiguracionController`, `EquipoController`, `TiendaActivaController` | none, any authenticated user                        |

The `tienda-activa` route carries no permission check because `activarTienda()`
is self-scoped: it only lets a user switch among their own assignments. The
`gestion.show` and `gestion.store` routes carry no `can:` middleware either, for
the reason no other record-bound route does: the permiso depends on the bound
`Dato`, so `GestionController` calls `Gate::authorize()` and `DatoPolicy` decides,
not route middleware.

## Cockpit

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
`PlantillaDeImportacion`, so the downloadable template cannot drift from what the
upload enforces. The import is all or nothing. It validates the whole file first
and reports up to 50 errors, including a repeated RUC. It then upserts `cliente`
and `datos` in batches of 500 inside one transaction. A reload updates the
campaign columns and leaves `estado` alone, so it never resets an agent's
progress. `ResultadoImportacion` carries either counts or errors, never both.

**Distribution.** An agent draws a batch of `LOTE` (30) pending, unassigned
`datos` of one `origen` and one `tipo_data` with `DistribucionDeDatos`. The
selection runs with `lockForUpdate` inside a transaction, so two concurrent
requests cannot draw the same records. Each draw writes an `asignacion_datos`
row and grants the agent access to the customer. Releasing returns only the
records still pending. Managed records stay assigned so their activity keeps an
owner.

**Queue.** `Dato::scopeEnCola` selects an agent's pending records for one segment
under their active tienda. A record assigned under a tienda the agent no longer
holds does not appear.

**Contact.** `Dato::registrarGestion` inserts the `llamada` and marks the `dato`
as gestionado in one transaction. The `llamada` takes its channel from the chosen
`respuesta`, never from the request. `Llamada` is append-only and points at the
customer, so a contact survives its campaign.

**Registering a customer.** `AltaDeClientes` lets an agent add a customer
directly. It creates or reuses the `cliente`, upserts an `INHOUSE` `dato`, and
assigns it to the agent unless it is already assigned. Registering again does not
reset a record that was already worked.

**Line mix.** `Operadores` is the single owner of the carrier list, the
`q_<carrier>` column naming, and the validation and coercion of the per-carrier
counts. `MezclaDeLineas` is the one shape a screen renders for a customer's lines
per carrier, whether they come from the campaign file (`datos`) or from what an
agent confirmed on a call (`llamada`). The two sources are stored separately.

## Access control

Access has three layers.

**Permisos.** A user's grants are the `menu` rows linked to them through the
`acceso` table. `Usuario::hasPermiso` checks a `menu.permiso` string. The `Gate::before`
hook in `AppServiceProvider` grants an ability when the user holds that permiso
and otherwise returns `null`, so a model policy can still decide. Creating a user
copies the role's grants from `accesoxrol` into `acceso`. The user's grants can
diverge from the role afterward.

**Sidebar.** [`Navigation\Sidebar`](../app/Navigation/Sidebar.php) owns the
structure and presentation of the navigation. It shows an item only when the user
holds its permiso. The menu tables store grants and nothing else. After login,
`Sidebar::startUrlForUser` sends the user to the first screen they may see, or to
Configuración when they have no operational access.

**Policies.** A route permiso admits a user to a screen. The classes in
[`app/Policies/`](../app/Policies/) decide whether that user may touch a
particular record, because a detail or update endpoint must not trust the posted
id.

- `NcMovilPolicy` and `NcFijaPolicy` let an ejecutivo view only the cases they
  registered. An update also requires the case to be editable, which means the
  validador has not ruled on it yet.
- `QuiebrePolicy` follows the same view and update rules. Its `validar` ability
  allows a user to validate only a quiebre whose `zonal_telefonica` is their
  assigned tienda.
- `DatoPolicy` lets an agent view or register a gestión only when they hold the
  permiso for the dato's segment, the dato is actively assigned to them, and its
  cliente is visible to them.
- `UsuarioPolicy` stops an operator from disabling their own account.

**Customer visibility.** `cliente_acceso` records which users may see which
customers. `Cliente::visiblePara` filters on it. `AccesoAClientes` grants access
and resolves a RUC for a user. A RUC that exists but is not visible to the user
raises `ClienteNoDisponible`, whose message does not say why. That keeps one
executive's history from leaking to another.

Passwords go through `LegacyPasswordHasher`. `usuario.clave` holds both unsalted
MD5 from the earlier system and `password_hash` output. A successful MD5 match
upgrades the stored value in place.

## Notas de cargo

A nota de cargo is one row that carries a case through four stages. Each stage
has its own controller and permiso, and each owns a separate set of columns.

| Stage      | Who         | What it writes                                    |
| ---------- | ----------- | ------------------------------------------------- |
| Registro   | ejecutivo   | The case itself.                                  |
| Validación | validador   | `validacion`: `PENDIENTE`, `PROCEDE`, `RECHAZADO`. |
| Back       | back office | `estado` and the fulfilment fields.               |
| Liquidación | liquidador | The settlement fields, for móvil only.            |

No stage writes another stage's columns, so the controllers assign fields
explicitly instead of sharing `$fillable`. Back only opens a validated case, and
the check is `NcMovil::esValidado`. The `Validable` trait in
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

## Reports

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

## Data model

Migrations in [`database/migrations/`](../database/migrations/) are the schema
source of truth. Models use the legacy singular table names and `id_<table>`
primary keys, and most have no timestamps.

| Table                              | Model                | Holds                                                       |
| ---------------------------------- | -------------------- | ----------------------------------------------------------- |
| `cliente`                          | `Cliente`            | Customer identity by RUC.                                   |
| `cliente_acceso`                   | `AccesoCliente`      | Which user may see which customer.                          |
| `datos`                            | `Dato`               | A customer on a campaign list, with its `estado`.           |
| `asignacion_datos`                 | `AsignacionDato`     | Which agent holds a `dato`. At most one active row per `dato`. |
| `llamada`                          | `Llamada`            | Logged contacts. Append-only.                               |
| `respuesta`                        | `Respuesta`          | The outcomes an agent can record on a contact.              |
| `nc_movil`, `nc_fija`              | `NcMovil`, `NcFija`  | Notas de cargo.                                             |
| `producto`                         | `Producto`           | The product catalog behind the fija form.                   |
| `cartera`                          | `Cartera`            | Portfolios the back office assigns to a móvil case.         |
| `quiebre`                          | `Quiebre`            | Incident cases.                                             |
| `usuario`, `rol`, `supervisor`     | `Usuario`, `Rol`, `Supervisor` | Accounts, roles, and supervisors.                 |
| `menu`, `acceso`, `accesoxrol`     | `Menu`               | Permisos, per-user grants, and per-role grants.             |
| `tienda`, `asignacion_tienda`      | `Tienda`, `AsignacionTienda` | Stores and each user's assignments. `estado` 0 marks the active one. |

## Cutover

[`app/Cutover/`](../app/Cutover/) and the `cutover:transform` command in
[`app/Console/Commands/`](../app/Console/Commands/) move a pre-rewrite export into
this schema. Each `Traslado` class handles one module, because the steps are not
one to one. The legacy `datos` table becomes two tables, for example. The
[cutover runbook](cutover.md) describes how to run it.

## Tests

[`tests/Feature/`](../tests/Feature/) has one directory per module and exercises
routes, permisos, and policies against an in-memory SQLite database.
[`tests/Unit/`](../tests/Unit/) covers the small pure classes. See
[Development](development.md) for how to run them.
