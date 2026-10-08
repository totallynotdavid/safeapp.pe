# Contributing

[Install](../docs/installation.md) the toolchain and run `make setup` first. The
[architecture](../docs/architecture.md) maps the code.

## Checks

CI runs these in [`ci.yml`](workflows/ci.yml). Run them before you push:

```sh
make fmt-check
make lint
make analyze
make test
bun run build
```

Both jobs install PHP, Composer, Bun and Mago from [`mise.toml`](../mise.toml),
the same pins `make setup` uses. Change a tool version there only. The `php` job
runs the four `make` targets. The `assets` job runs
`bun install --frozen-lockfile` and `bun run build`.

`make fmt` formats the PHP code with Mago. `make lint` and `make analyze` run
Mago's linter and static analysis.

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
