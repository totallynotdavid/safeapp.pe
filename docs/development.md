# Development

Run the same PHP checks used by CI:

```sh
make fmt-check
make lint
make test
```

Build the frontend assets with:

```sh
bun run build
```

CI runs these commands in [`ci.yml`](../.github/workflows/ci.yml). The PHP job
runs the three `make` targets and the assets job runs the build.

`make fmt` updates PHP formatting. `make analyze` runs Mago static analysis. It
is not a CI gate.

## Tests

The test suite uses an in-memory SQLite database, so it does not need the local
MariaDB server. See [Database](database.md).

[`tests/Feature/`](../tests/Feature/) has one directory per module and exercises
routes, permisos, and policies. [`tests/Unit/`](../tests/Unit/) covers the small
pure classes.
