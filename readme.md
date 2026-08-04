# safeapp.pe

[![CI](https://github.com/totallynotdavid/safeapp.pe/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/totallynotdavid/safeapp.pe/actions/workflows/ci.yml)

SafeApp supports Entel sales and service operations. It manages campaign data,
agent queues, customer contacts, charge notes, outage cases, settlement,
reporting, staff assignments, and access control.

SafeApp is a Laravel monolith. The server renders Blade views, MariaDB stores
application data, and Vite builds the CSS and JavaScript assets.

## Repository map

- [`app/Cockpit/`](app/Cockpit/) owns campaign imports, customer registration,
  queue distribution, and contact handling.
- [`app/Http/`](app/Http/) contains controllers, request validation, and shared
  HTTP filters.
- [`app/Models/`](app/Models/) contains the application data model and query
  builders.
- [`app/Policies/`](app/Policies/) enforces record-level authorization. Route
  permissions protect each operational module.
- [`app/Exports/`](app/Exports/) builds spreadsheet reports from the same
  queries used by the application screens.
- [`routes/`](routes/) groups the application shell, cockpit, and charge-note
  routes.
- [`database/`](database/) contains the schema, factories, and development
  data.
- [`tests/`](tests/) contains unit and feature tests for the operational
  workflows.

## Requirements

Local development requires Linux or WSL on x86-64. The database helper
downloads a Linux MariaDB binary and keeps its server and data files under
`.mariadb/`.

[mise](https://mise.jdx.dev/getting-started.html) installs the pinned PHP, Bun,
and Mago versions from [`mise.toml`](mise.toml). PHP is built locally and needs
the system development libraries installed first. On Ubuntu or Debian, run:

```sh
sudo apt-get install build-essential autoconf bison re2c pkg-config \
    libxml2-dev libssl-dev libicu-dev libzip-dev libonig-dev \
    libcurl4-openssl-dev libpng-dev libjpeg-dev libfreetype6-dev \
    libwebp-dev libgmp-dev libsodium-dev libreadline-dev libbz2-dev
```

## Get started

Install the toolchain and dependencies:

```sh
mise install
bun install
```

Create the local environment file:

```sh
cp .env.example .env
```

Set `DB_USERNAME` and `DB_PASSWORD` in `.env`. Any local values work. Keep the
provided `DB_HOST`, `DB_PORT`, and `DB_DATABASE` unless the local database needs
a different port or database name.

Install the PHP dependencies, generate the application key, start MariaDB, and
build the development database:

```sh
make setup
```

The seeders create an administrator account with username `admin` and password
`admin`. This account and the seeded operational data are for local development.

Start Laravel:

```sh
make dev
```

Laravel serves the application at `http://127.0.0.1:8000` by default. Run
`make help` to list the available maintenance commands.

## Database development

[`scripts/db.sh`](scripts/db.sh) downloads and runs an isolated MariaDB server.
It reads the database name and credentials from `.env`, stores its data under
`.mariadb/`, and does not use a system MariaDB installation.

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

## Quality checks

Run the same PHP checks used by CI:

```sh
make fmt-check
make lint
make test
```

Build the frontend assets with:

```sh
bun run build
```

`make fmt` updates PHP formatting. `make analyze` runs Mago static analysis; it
is not a CI gate.

## Production data cutover

The cutover command imports a pre-rewrite production export into the current
schema. Restore the export into the separate `safeapp_legacy` database:

```sh
make db-legacy FILE=/path/to/export.sql
```

Build an empty destination schema and run the transform:

```sh
php artisan migrate:fresh --drop-views
php artisan cutover:transform
```

The restore command removes the export's `CREATE DATABASE` and `USE` statements
and grants the application account read-only access to the source database. The
transform refuses to run when the destination contains application rows or
database views.

Transformation rules live in [`app/Cutover/`](app/Cutover/). Their feature
coverage lives in [`tests/Feature/Cutover/`](tests/Feature/Cutover/).
