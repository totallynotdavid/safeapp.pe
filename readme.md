# safeapp.pe

PHP call center and debt collection system for Entel operations.

## Prerequisites

- [mise](https://mise.jdx.dev/getting-started.html): installs and pins PHP
  8.2.31 for this project.

## Setup

```sh
git clone https://github.com/totallynotdavid/safeapp.pe.git
cd safeapp.pe

cp public/SAFEAPP/.env.example public/SAFEAPP/.env
```

Edit `public/SAFEAPP/.env` and set `DB_USER` and `DB_PASS`. Any local values
work. `DB_HOST` and `DB_NAME` in `.env.example` already work as provided;
`DB_NAME` can be renamed if needed (see comment in that file).

```sh
make setup          # composer install, .env, app key, MariaDB, migrate + seed
make dev             # starts the Laravel dev server at /
```

`make setup` builds a fresh dev database from migrations and seeders, including
a known admin login (`admin` / `admin`). `make db-reset` rebuilds it from
scratch. To capture legacy behavior before porting a module, load a real dump
into a scratch DB with `make setup-legacy` (see Local database).

`make help` lists the full command set, including `pint`/`pint-check` for the
Laravel app's code style, `fmt`/`fmt-check`/`lint` (Mago) for the legacy
app's, and `test` for the Laravel test suite.

## Local database

`scripts/db.sh` downloads and runs a MariaDB instance under `.mariadb/`, with
its own datadir, socket, and port. It does not touch any system MySQL/MariaDB
install or Hostinger's production database. Both apps point at this same
local instance.

`make db-reset` wipes the dev database and rebuilds it from migrations and
seeders. Loading a real dump (`make db-restore FILE=...`) is only for legacy
snapshot capture; it writes the old legacy schema, so point `DB_NAME` at a
scratch database rather than overwriting the dev one.

`scripts/db.sh` currently only supports Linux and WSL: it downloads a
`linux-systemd-x86_64` MariaDB binary.
