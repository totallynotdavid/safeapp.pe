# safeapp.pe

[![CI](https://github.com/totallynotdavid/safeapp.pe/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/totallynotdavid/safeapp.pe/actions/workflows/ci.yml)

SafeApp is the operations app for Entel sales and service teams. Agents work
campaign call queues, and ejecutivos file charge notes that validators and the
back office carry to their close. It also covers outage cases, settlement,
reports, staff assignments and per-screen access control.

It is one Laravel application. The server renders Blade views, MariaDB stores
the data, and Vite builds the CSS and JavaScript. There is no separate API. The
interface and the domain vocabulary are Spanish. The
[glossary](docs/glossary.md) translates the terms.

## Get started

Local development needs Linux or WSL on x86-64 and
[mise](https://mise.jdx.dev/getting-started.html). Then:

```sh
mise install
mise run setup
mise run dev
```

`mise run setup` installs the dependencies, creates `.env`, builds the assets,
and starts a local MariaDB with seeded data.
[Installation](docs/installation.md) lists what it does.

Open `http://127.0.0.1:8000` and sign in as `admin` with password `admin`. The
seeders create that account for local use only. `mise tasks` lists every
maintenance command.

## Features

- **Campaign work.** Import a customer list from CSV or XLSX, draw batches of
  records from a queue, and log each contact.
- **Notas de cargo.** Móvil and fija cases pass through registration,
  validation, back office and, for móvil, settlement.
- **Quiebres.** Outage cases, validated by a user assigned to the case's tienda.
- **Reports.** XLSX exports for notas de cargo, call history and team follow-up,
  built from the same query the screen shows.
- **Administration.** Users, roles, personal data and tienda assignments.
- **Access control.** A permiso per screen, row-level policies, and per-user
  customer visibility.

## Documentation

- [Manual](docs/readme.md): installation, architecture and one page per
  subsystem.
- [Contributing](.github/contributing.md): the checks to run before a change and
  how the tests are laid out.
