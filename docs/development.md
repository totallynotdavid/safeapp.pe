# Development

Run the same PHP checks used by CI:

```sh
make fmt-check
make lint
make analyze
make test
```

Build the frontend assets with:

```sh
bun run build
```

CI runs these commands in [`ci.yml`](../.github/workflows/ci.yml). The PHP job
runs the four `make` targets and the assets job runs the build.

`make fmt` updates PHP formatting. `make analyze` runs Mago static analysis.
Issues that predate the gate are listed in
[`analyzer-baseline.toml`](../analyzer-baseline.toml) and the analyzer reports
only new ones. When you fix a baselined issue, run
`mago analyze --remove-outdated-baseline-entries` to drop its entry.

## Tests

The test suite uses an in-memory SQLite database, so it does not need the local
MariaDB server. See [Database](database.md).

[`tests/Feature/`](../tests/Feature/) has one directory per module and exercises
routes, permisos, and policies. [`tests/Unit/`](../tests/Unit/) covers the small
pure classes.
