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

Transformation rules live in [`app/Cutover/`](../app/Cutover/). Their feature
coverage lives in [`tests/Feature/Cutover/`](../tests/Feature/Cutover/).
