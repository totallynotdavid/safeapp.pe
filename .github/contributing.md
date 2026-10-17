# Contributing

[Install](../docs/installation.md) the toolchain and run `mise run setup` first.
The [architecture](../docs/architecture.md) maps the code.

## Checks

CI runs [`ci.yml`](workflows/ci.yml): `mise run install`, then `mise run check`.
Run the same before you push:

```sh
mise run install
mise run check
```

`mise.toml` pins PHP, Composer, Bun, Mago and gitleaks, and CI installs them
from it. Change a tool version there only. `check` runs these tasks without
writing any tracked file:

| Task        | Runs                                             |
| ----------- | ------------------------------------------------ |
| `lint`      | Mago format and lint, oxlint, oxfmt and gitleaks |
| `typecheck` | Mago static analysis                             |
| `test`      | The PHPUnit suite                                |
| `build`     | The Vite asset build                             |

`mise run fix` applies the safe lint fixes and formats PHP, JavaScript,
Markdown, JSON and YAML.

## Tests

The suite uses an in-memory SQLite database (see
[`phpunit.xml`](../phpunit.xml)), so it does not need the local MariaDB server.
Run it with `mise run test`. Run one file or test with the artisan runner:

```sh
mise exec -- php artisan test tests/Feature/Cutover
mise exec -- php artisan test --filter=SaneamientoTest
```

[`tests/Feature/`](../tests/Feature/) has one directory per module and exercises
routes, permisos, and policies. [`tests/Unit/`](../tests/Unit/) covers the small
pure classes.
