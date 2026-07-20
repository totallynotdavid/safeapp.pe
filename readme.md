# safeapp.pe

PHP call center and debt collection system for Entel operations.

## Prerequisites

- [mise](https://mise.jdx.dev/getting-started.html): installs and pins PHP
  8.2.31 for this project.

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
seeders. Migrations are the schema's source of truth; there is no dump to
restore, and the pre-rewrite production dump would not load into this schema.

`scripts/db.sh` currently only supports Linux and WSL: it downloads a
`linux-systemd-x86_64` MariaDB binary.
