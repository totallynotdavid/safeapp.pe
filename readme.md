# safeapp.pe

[![CI](https://github.com/totallynotdavid/safeapp.pe/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/totallynotdavid/safeapp.pe/actions/workflows/ci.yml)

SafeApp supports Entel sales and service operations. It manages campaign data,
agent queues, customer contacts, charge notes, outage cases, settlement,
reporting, staff assignments, and access control.

SafeApp is a Laravel monolith. The server renders Blade views, MariaDB stores
application data, and Vite builds the CSS and JavaScript assets.

## Quickstart

Local development requires Linux or WSL on x86-64. After
[installing](docs/installation.md) the system libraries and the toolchain and
creating `.env`, run:

```sh
make setup
make dev
```

Laravel serves the application at `http://127.0.0.1:8000` by default. Run
`make help` to list the available maintenance commands.

## Documentation

Setup and daily work:

- [Installation](docs/installation.md): system requirements, the toolchain,
  `.env`, and the first run.
- [Database](docs/database.md): the local MariaDB server, resetting it, and the
  test database.
- [Development](docs/development.md): the format, lint, test, and build checks
  that CI runs, and how the tests are laid out.

How it works:

- [Architecture](docs/architecture.md): the modules and the flow of a request
  through the application.
- [Glossary](docs/glossary.md): the Spanish business terms used across the code
  and docs.
- [Cockpit](docs/cockpit.md): imports, queue distribution, contact handling, and
  customer registration.
- [Access control](docs/access-control.md): permisos, the sidebar, policies,
  customer visibility, and password hashing.
- [Notas de cargo](docs/notas-de-cargo.md): the four stages of a case and the
  period windowing of its queues.
- [Reports](docs/reports.md): the spreadsheet exports.
- [Schema](docs/schema.md): the tables and the models behind them.

Operations:

- [Production data cutover](docs/cutover.md): import a pre-rewrite production
  export into the current schema.
