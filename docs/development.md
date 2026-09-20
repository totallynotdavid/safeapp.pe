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

The test suite uses an in-memory SQLite database, so it does not need the local
MariaDB server. See [Database](database.md).
