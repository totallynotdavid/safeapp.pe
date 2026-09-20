# Production data cutover

The cutover command imports a pre-rewrite production export into the current
schema. Restore the export into the separate `safeapp_legacy` database:

```sh
make db-legacy FILE=/path/to/export.sql
```

Build an empty destination schema and run the transform:

```sh
php artisan migrate:fresh --drop-views
php artisan cutover:transform
```

The restore command removes the export's `CREATE DATABASE` and `USE` statements
and grants the application account read-only access to the source database. The
transform refuses to run when the destination contains application rows or
database views.

Transformation rules live in [`app/Cutover/`](../app/Cutover/). Their feature
coverage lives in [`tests/Feature/Cutover/`](../tests/Feature/Cutover/).
