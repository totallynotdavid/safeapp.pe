# Contributing

[Install](../docs/installation.md) the toolchain and run `make setup` first. The
[architecture](../docs/architecture.md) maps the code.

## Checks

CI runs the same checks with `bun run check` in [`ci.yml`](workflows/ci.yml).
Run them before you push:

```sh
bun run check
```

The check job installs PHP, Composer, Bun and Mago from
[`mise.toml`](../mise.toml), using the same pins as `make setup`. Change a tool
version there only. It installs the Composer and Bun dependencies, prepares the
Laravel environment, then runs `bun run check`.

`bun run fix` formats the PHP code with Mago. `bun run lint` checks formatting,
runs Mago's linter, and runs static analysis. `bun run test` runs the Laravel
test suite.

## Tests

The suite uses an in-memory SQLite database (see
[`phpunit.xml`](../phpunit.xml)), so it does not need the local MariaDB server.
Run it with `make test`. Run one file or test with the artisan runner:

```sh
mise exec -- php artisan test tests/Feature/Cutover
mise exec -- php artisan test --filter=SaneamientoTest
```

[`tests/Feature/`](../tests/Feature/) has one directory per module and exercises
routes, permisos, and policies. [`tests/Unit/`](../tests/Unit/) covers the small
pure classes.
