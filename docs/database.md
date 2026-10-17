# Database

[`scripts/db.sh`](../scripts/db.sh) downloads and runs an isolated MariaDB
server. It reads the database name and credentials from `.env`, stores its data
under `.mariadb/`, and does not use a system MariaDB installation.

Start, stop, or inspect the local server with:

```sh
mise run db:up
mise run db:down
mise run db:status
```

Migrations are the schema source of truth. Rebuild the local database and load
the development seed data with:

```sh
mise run db:reset
```

`mise run db:reset` deletes all data in the configured development database. The
test suite uses an in-memory SQLite database and does not use the local MariaDB
data.

`mise run db:dump` writes a snapshot of the development database to
`dev-dump.sql`. Pass a path to choose another file: `mise run db:dump path`.

[Installation](installation.md) runs `mise run setup`, which starts the server
and builds the development database the first time.
[Production data cutover](cutover.md) uses `mise run db:legacy` to restore a
production export into a separate local database, and `mise run cutover` to
rehearse the import. Both are for development only.
