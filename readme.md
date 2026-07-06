# safeapp.pe

PHP call center and debt collection system for Entel operations.

`public/SAFEAPP` is a separate, legacy PHP application, still fully in
service. It's nested under `public/` (rather than elsewhere in the repo)
because Laravel's dev server serves any real file under `public/` directly,
so one `make dev` runs both apps at once: the new one at `/`, the legacy one
at `/SAFEAPP/`.

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
make setup          # Laravel: composer install, .env, app key, migrate
make setup-legacy    # legacy: composer install, starts a local MariaDB, loads 127_0_0_1.sql
make dev             # starts the Laravel dev server: new app at /, legacy at /SAFEAPP/
```

`make help` lists the full command set, including `pint`/`pint-check` for the
Laravel app's code style, `fmt`/`fmt-check`/`lint` (Mago) for the legacy
app's, and `test` for the Laravel test suite.

## Local database

`scripts/db.sh` downloads and runs a MariaDB instance under `.mariadb/`, with
its own datadir, socket, and port. It does not touch any system MySQL/MariaDB
install or Hostinger's production database. Both apps point at this same
local instance.

`make db-reset` wipes the local database and reloads it from `127_0_0_1.sql`.

`scripts/db.sh` currently only supports Linux and WSL: it downloads a
`linux-systemd-x86_64` MariaDB binary.
