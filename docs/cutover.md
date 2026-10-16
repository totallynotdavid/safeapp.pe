# Production data cutover

The cutover imports a pre-rewrite production export into the current schema.
`php artisan cutover:transform` reads the restored export through the `legacy`
database connection and writes the application's database. It only transfers
data. The migrations own the schema.

The destination must be migrated and empty. The command refuses to start, and
changes nothing, when migrations are pending, when an application table has
rows, or when views from the legacy schema remain. It copies everything in one
transaction, so a run leaves the destination empty or full. A rerun on a full
destination refuses.

## Take the export

Take the export with `mariadb-dump --no-create-db`. `make db-legacy` refuses a
file that has a `CREATE DATABASE`, `DROP DATABASE`, `ALTER DATABASE` or `USE`
statement at the start of a line, because those would write into the
application's own database.

## Run in production

Production is never dropped or rebuilt, and `cutover:transform --fresh` is
refused when `APP_ENV` is `production`. Nothing in the repository restores the
export on the production server, so do that yourself:

1. Restore the export into the database named by `LEGACY_DB_DATABASE` (default
   `safeapp_legacy`). The `legacy` connection takes its host, port and
   credentials from `LEGACY_DB_*` and falls back to the `DB_*` values. The
   account needs `SELECT` on that database.
2. Point `DB_DATABASE` at an empty database and migrate it:

    ```sh
    php artisan migrate --force
    ```

3. Run the transfer:

    ```sh
    php artisan cutover:transform
    ```

The command prints the source and destination names, then a table of the rows it
moved.

## Rehearse on the dev server

`make db-legacy` and `make cutover` are for the local development database.
`scripts/db.sh` starts a local MariaDB and never touches a production server.

```sh
make cutover FILE=/path/to/export.sql
```

It runs these steps in order:

1. `make db-legacy` restores the export into the `safeapp_legacy` database on
   the local server and grants the application account read-only access to it.
2. `php artisan cutover:transform --fresh` drops every table and view in the
   development database, migrates it again, and copies the data across.

Rerun the same command to start over. `make db-legacy FILE=...` restores the
export on its own.

## Accounts

The transform does not copy passwords. Every migrated account starts without one
and cannot sign in. Set the first password from the command line, then use the
Usuarios screen for the rest:

```sh
php artisan usuario:restablecer-clave <usuario>
```

The command sets a random password on the account and prints it once.

## What the transform changes

The classes are in [`app/Cutover/`](../app/Cutover/) and their feature coverage
is in [`tests/Feature/Cutover/`](../tests/Feature/Cutover/). Each `Traslado`
class owns one module, and the command runs them in this order: accounts,
permisos, catalogs, campaigns, contacts, notas de cargo fija, notas de cargo
móvil, then averías. Rows it cannot place are left out, and the summary table
counts them.

**Customers.** The export has no customer table. `MapaDeClientes` builds
`cliente` from the RUCs in `quiebre`, `nc_fija`, `nc_movil`, `llamada` and
`datos`. When the sources name one RUC differently, the later source wins, in
that order. It assigns the ids itself, so every later step resolves a RUC
without a query. A RUC that is blank or not all digits names no customer, so the
row that carries it is dropped. After the copy, `BackfillDeAccesosClientes`
grants each user access to the customers they worked.

**Values.** `Saneamiento` turns legacy's placeholders into the new schema's
shapes:

- `''` and `0000-00-00` become `NULL`.
- Text is trimmed and cut to the column width.
- A line count outside 0 to 100 becomes 0. The target columns are unsigned, and
  a larger number is a phone number typed into the wrong field.
- A charge that does not fit `decimal(10,2)` becomes 0 instead of being rounded.
- An id stored as text becomes an integer, and `''` or `0` becomes `NULL`.

`Mojibake` repairs text that was UTF-8 read as Latin-1 and re-encoded, possibly
several times. It decodes only while the result stays valid UTF-8 and otherwise
returns the original text, so a value is never half repaired.

**Accounts and permisos.** Roles are copied, except the role of the retired
back-office module. Passwords are not copied. `menu` and `respuesta` are seeded
with legacy's ids rather than copied, so a grant still points at the same
screen, and `menu` carries `permiso` keys in place of the legacy `url`. A grant
is dropped when it names a deleted user, a menu that never existed, or a screen
the rewrite removed. Holders of the user administration permiso receive the
roles and tienda assignment screens the rewrite added.

**Catalogs.** `tienda`, `supervisor`, `cartera` and `producto` are copied.
`asignacion_tienda` is copied too, without the rows that name a deleted user.
Its `estado` 0 marks the active assignment, the opposite of `cartera.estado`.

**Campaigns.** Each legacy `datos` row becomes a `dato`. A row with an assigned
agent also gets an open `asignacion_datos` row, dated at the cutover when legacy
recorded no date. Rows that repeat a customer, `origen` and `tipo_data` collapse
into the first one. A `tipo_data` that is not `MOVIL` or `FIJA`, such as
`CONVERGENTE`, is replaced by the segment of the nota de cargo the customer went
on to open, and by `MOVIL` when there is none. `origen` keeps where the record
came from.

**Contacts.** Every `llamada` keeps its `id_respuesta`, including 0 for no
disposition. `id_dato` is `NULL`, because a contact outlives its campaign. The
legacy `q_lineas` becomes `q_movistar`.

**Notas de cargo.** The tables lose the columns that carry nothing the app
reads.

| Table      | Not carried                                                                                            |
| ---------- | ------------------------------------------------------------------------------------------------------ |
| `nc_fija`  | `tipo`, `q_lineas`, `agenda`, `id_cartera`, `ejecutivo_telefonica`, `zonal_telefonica`                 |
| `nc_movil` | `tipo`, `ejecutivo_telefonica`, `tipo_etapa`, `zonal_telefonica`; `ejecutivo_tdp` becomes `id_cartera` |

`zonal_telefonica` is the back office's correction of the región, so it wins
over `zonal` when it is set. On móvil, the registro screen filled
`ejecutivo_tdp` and the back screen filled `id_cartera`, so `ejecutivo_tdp` wins
when both are set. For fija, a fixed list maps the many spellings of
`tecnologia` onto `FTTH`, `HFC` and `GPON`. Any other value, such as two
technologies in one field, is left unset and counted in the summary.
