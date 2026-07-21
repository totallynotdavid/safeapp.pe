# safeapp.pe

PHP call center and debt collection system for Entel operations.

## Prerequisites

- [mise](https://mise.jdx.dev/getting-started.html): installs and pins PHP
  8.5.8 for this project.
- The system headers mise's PHP build links against. It compiles from source and
  silently omits any extension whose library is missing, so install these
  **before** `mise install`. Without `libzip-dev` you get a PHP with no
  `ext-zip`, and every spreadsheet export fails at runtime:

  ```sh
  sudo apt-get install build-essential autoconf bison re2c pkg-config \
      libxml2-dev libssl-dev libicu-dev libzip-dev libonig-dev \
      libcurl4-openssl-dev libpng-dev libjpeg-dev libfreetype6-dev \
      libwebp-dev libgmp-dev libsodium-dev libreadline-dev libbz2-dev
  ```

## Setup

```sh
git clone https://github.com/totallynotdavid/safeapp.pe.git
cd safeapp.pe

cp .env.example .env
```

Edit `.env` and set `DB_USERNAME` and `DB_PASSWORD`. Any local values work;
`scripts/db.sh` creates that account. `DB_HOST`, `DB_PORT` and `DB_DATABASE`
work as provided.

```sh
make setup           # composer install, .env, app key, MariaDB, migrate + seed
make dev             # starts the Laravel dev server at /
```

`make setup` builds a fresh dev database from migrations and seeders, including
a known admin login (`admin` / `admin`). `make db-reset` rebuilds it from
scratch.

`make help` lists the full command set. Code quality runs through Mago (one
toolchain, scoped to the Laravel app via `mago.toml`): `fmt`/`fmt-check` and
`lint` are clean gates; `analyze` (static type analysis) is advisory, since it
is not Laravel-magic-aware. `test` runs the Laravel test suite.

## Local database

`scripts/db.sh` downloads and runs a MariaDB instance under `.mariadb/`, with
its own datadir, socket, and port. It does not touch any system MySQL/MariaDB
install or Hostinger's production database.

`make db-reset` wipes the dev database and rebuilds it from migrations and
seeders. Migrations are the schema's source of truth; a dev database is never
restored from a dump.

## Loading a production export

The pre-rewrite export does not load into this schema, so it is restored into a
scratch database of its own and carried across row by row:

```sh
make db-legacy FILE=export.sql            # restores into safeapp_legacy, read-only
php artisan migrate:fresh --drop-views    # build the target schema, without --seed
php artisan cutover:transform             # carry the rows across
```

`--drop-views` is not optional. The database being transformed is the legacy one,
and `migrate:fresh` drops tables while leaving views standing, so legacy's
`reporteusu_excel` would outlive the columns it selects. An invalid view makes
`mysqldump` fail for the entire database, which means the first thing it breaks
is the backup. The transform refuses to run while any view is left, rather than
letting that reach production.

The transform reports what it wrote and what it deliberately dropped. It refuses
a database that already holds rows, so a second run means resetting first. What
each rule does and why is in `app/Cutover`; the reasoning behind the numbers
those comments cite is in the plan.

`make db-legacy` strips the export's own `CREATE DATABASE` and `USE`
statements. Both name the production schema, which is the same name the dev app
uses, so running the file directly drops legacy tables into the app's database.

`scripts/db.sh` currently only supports Linux and WSL: it downloads a
`linux-systemd-x86_64` MariaDB binary.
