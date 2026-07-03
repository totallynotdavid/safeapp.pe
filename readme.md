# safeapp.pe

PHP call center and debt collection system for Entel operations.

## Prerequisites

- [mise](https://mise.jdx.dev/getting-started.html): installs and pins PHP
  8.2.31 for this project.

## Setup

```sh
git clone https://github.com/totallynotdavid/safeapp.pe.git
cd safeapp.pe

cp public_html/SAFEAPP/.env.example public_html/SAFEAPP/.env
```

Edit `public_html/SAFEAPP/.env` and set `DB_USER` and `DB_PASS`. Any local
values work. `DB_HOST` and `DB_NAME` in `.env.example` already work as
provided; `DB_NAME` can be renamed if needed (see comment in that file).

```sh
make setup   # installs PHP dependencies, starts a local MariaDB, loads 127_0_0_1.sql
make dev     # starts PHP's built-in server at http://127.0.0.1:8000/SAFEAPP/
```

`make help` lists the full command set: DB start, stop, restore, dump, reset.

## Local database

`scripts/db.sh` downloads and runs a MariaDB instance under `.mariadb/`, with
its own datadir, socket, and port. It does not touch any system MySQL/MariaDB
install or Hostinger's production database.

`make db-reset` wipes the local database and reloads it from `127_0_0_1.sql`.

`scripts/db.sh` currently only supports Linux and WSL: it downloads a
`linux-systemd-x86_64` MariaDB binary.
