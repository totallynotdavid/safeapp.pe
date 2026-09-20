# Architecture

SafeApp is a single Laravel application. A request passes through a route, a
permission check, a form request, a controller, and a model or domain class, and
it ends in a Blade view or a spreadsheet download. There is no separate API.

Class, table, and column names are Spanish because the business vocabulary is
Spanish. The [glossary](glossary.md) lists the terms that recur.

## Request flow

1. [`routes/web.php`](../routes/web.php) holds the login, the account settings,
   user and role administration, and the smaller modules. `bootstrap/app.php`
   also registers [`routes/cockpit.php`](../routes/cockpit.php) and
   [`routes/notas-cargo.php`](../routes/notas-cargo.php) under the `web` and
   `auth` middleware.
2. Each module's routes sit in a group with `can:<permiso>` middleware.
   [Access control](access-control.md) describes what that check does.
3. A form request in [`app/Http/Requests/`](../app/Http/Requests/) validates the
   input. There is one request class per write action, grouped by module.
4. The controller in [`app/Http/Controllers/`](../app/Http/Controllers/) reads
   or writes through a model or a `Cockpit` class and returns a view from
   `resources/views/`.
5. `AppServiceProvider` attaches the sidebar to `layouts.app` and the settings
   menu to `layouts.settings` with view composers, so a controller never builds
   navigation.

Controllers stay thin. A rule that more than one screen needs lives in one owner
and the screens call it. The docs linked at the end of this page name the
owners.

## Modules

| Module            | Routes            | Controllers                                                                                           | Permisos                                                                        |
| ----------------- | ----------------- | ----------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------- |
| Gestión comercial | `cockpit.php`     | `GestionController`, `DistribucionController`                                                         | `gestion.movil`, `gestion.fija`                                                 |
| Análisis          | `cockpit.php`     | `HistoricoController`, `SeguimientoController`, `ProduccionController`                                | `historico.ver`, `seguimiento.equipo`, `produccion.ver`                         |
| Notas de cargo    | `notas-cargo.php` | `NotaCargoMovilController`, `NotaCargoFijaController`, and their `Validacion` and `Back` counterparts | `notacargo.{movil,fija}.{registrar,validar,back}`                               |
| Control operativo | `web.php`         | `QuiebreRegistroController`, `QuiebreValidacionController`, `LiquidacionController`                   | `quiebre.registrar`, `quiebre.validar`, `liquidacion.gestionar`                 |
| Importación       | `web.php`         | `ImportacionController`                                                                               | `clientes.importar`                                                             |
| Administración    | `web.php`         | `UsuarioController`, `RolController`, `PersonalController`, `AsignacionController`                    | `usuarios.manage`, `roles.manage`, `personal.gestionar`, `asignacion.gestionar` |
| Cuenta            | `web.php`         | `AccountSettingsController`, `ConfiguracionController`, `EquipoController`, `TiendaActivaController`  | none, any authenticated user                                                    |

The `tienda-activa` route carries no permission check because `activarTienda()`
is self-scoped. It only lets a user switch among their own assignments. The
`gestion.show` and `gestion.store` routes carry no `can:` middleware either
because the permiso depends on the bound `Dato`. `GestionController` calls
`Gate::authorize()`, and `DatoPolicy` decides.

[`app/Cutover/`](../app/Cutover/) and the `cutover:transform` command in
[`app/Console/Commands/`](../app/Console/Commands/) move a pre-rewrite export
into this schema. Each `Traslado` class handles one module, because the steps
are not one to one. The legacy `datos` table becomes two tables, for example. The
[cutover runbook](cutover.md) describes how to run it.

## Detail by concern

- [Glossary](glossary.md): the Spanish business terms used across the code and
  docs.
- [Cockpit](cockpit.md): imports, queue distribution, contact handling, and
  customer registration.
- [Access control](access-control.md): permisos, the sidebar, policies, customer
  visibility, and password hashing.
- [Notas de cargo](notas-de-cargo.md): the four stages of a case and the period
  windowing of its queues.
- [Reports](reports.md): the spreadsheet exports.
- [Schema](schema.md): the tables and the models behind them.
- [Development](development.md): the checks CI runs and how the tests are laid
  out.
