# Installation

## Requirements

Local development requires Linux or WSL on x86-64. The database helper downloads
a Linux MariaDB binary and keeps its server and data files under `.mariadb/`.
[Database](database.md) describes the helper.

[mise](https://mise.jdx.dev/getting-started.html) installs the pinned PHP,
Composer, Bun, and Mago versions from [`mise.toml`](../mise.toml). PHP and
Composer are prebuilt binaries, so no compiler or system libraries are needed.
The setup also uses `curl`, `tar`, `sha256sum`, and `openssl`.

## Get started

Run the setup:

```sh
make setup
```

It installs the toolchain with mise, runs `composer install` and
`bun install --frozen-lockfile`, builds the assets, creates `.env`, starts
MariaDB, and builds the development database.

A new `.env` is a copy of `.env.example` with a random `DB_PASSWORD` and a
generated application key. An existing `.env` is left alone, so rerunning the
setup keeps the key and the password the database was created with. Keep the
provided `DB_HOST`, `DB_PORT`, and `DB_DATABASE` unless the local database needs
a different port or database name.

The seeders create an administrator account with username `admin` and password
`admin`. This account and the seeded operational data are for local development.

Start Laravel:

```sh
make dev
```

Laravel serves the application at `http://127.0.0.1:8000` by default. Run
`make help` to list the available maintenance commands.

## Next steps

- [Database](database.md) covers starting, stopping, and resetting the local
  MariaDB server.
- [Contributing](../.github/contributing.md) covers the checks to run before
  pushing.
