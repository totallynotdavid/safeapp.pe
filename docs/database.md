# Database

[`scripts/db.sh`](../scripts/db.sh) downloads and runs an isolated MariaDB
server. It reads the database name and credentials from `.env`, stores its data
under `.mariadb/`, and does not use a system MariaDB installation.

Start, stop, or inspect the local server with:

```sh
make db-up
make db-down
make db-status
```

Migrations are the schema source of truth. Rebuild the local database and load
the development seed data with:

```sh
make db-reset
```

`make db-reset` deletes all data in the configured development database. The
test suite uses an in-memory SQLite database and does not use the local MariaDB
data.

`make db-dump` writes a snapshot of the development database to `dev-dump.sql`.
Pass `FILE=path` to choose another file.

[Installation](installation.md) runs `make setup`, which starts the server and
builds the development database the first time.
[Production data cutover](cutover.md) uses `make db-legacy` to restore a
production export into a separate local database, and `make cutover` to rehearse
the import. Both are for development only.
