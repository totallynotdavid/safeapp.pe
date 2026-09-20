# Installation

## Requirements

Local development requires Linux or WSL on x86-64. The database helper downloads
a Linux MariaDB binary and keeps its server and data files under `.mariadb/`.
[Database](database.md) describes the helper.

[mise](https://mise.jdx.dev/getting-started.html) installs the pinned PHP, Bun,
and Mago versions from [`mise.toml`](../mise.toml). PHP is built locally and
needs the system development libraries installed first. On Ubuntu or Debian,
run:

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

## Next steps

- [Database](database.md) covers starting, stopping, and resetting the local
  MariaDB server.
- [Development](development.md) covers the checks to run before pushing.
