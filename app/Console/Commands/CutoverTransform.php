<?php

namespace App\Console\Commands;

use App\Cutover\MapaDeClientes;
use App\Cutover\Traslado;
use App\Cutover\TrasladoDeAverias;
use App\Cutover\TrasladoDeCampanas;
use App\Cutover\TrasladoDeCatalogos;
use App\Cutover\TrasladoDeContactos;
use App\Cutover\TrasladoDeCuentas;
use App\Cutover\TrasladoDeNotasFija;
use App\Cutover\TrasladoDeNotasMovil;
use App\Cutover\TrasladoDePermisos;
use Database\Seeders\MenuSeeder;
use Database\Seeders\RespuestaSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Turns a restored pre-rewrite production export into a database this app can
 * run on.
 *
 *     ./scripts/db.sh restore-legacy <export.sql>
 *     php artisan migrate:fresh --drop-views
 *     php artisan cutover:transform
 *
 * Reads the `legacy` connection, writes the default one. Deliberately not a SQL
 * migration script: the migrations are the schema, and an ALTER sequence that
 * has to arrive at the same shape would be a second definition of it that could
 * drift. Here the target is built by the migrations and this only carries rows
 * across, so a schema change needs no change to the cutover.
 *
 * Rerunnable by construction rather than by being idempotent: it refuses to
 * touch a database that already has rows, so re-running means migrate:fresh
 * first. That is one behaviour instead of an upsert path that only ever runs
 * once and is therefore never tested.
 *
 * This runs once and is then deleted. Nothing in the web app opens the `legacy`
 * connection, so the app does not depend on a second database; only this command
 * and app/Cutover do. It lives in the autoload path anyway because it reuses the
 * migrations, the model constants and MenuSeeder (which *is* the url -> permiso
 * remap), and a standalone script would have to hardcode copies of all three,
 * free to drift from the schema. Once the cutover holds, delete app/Cutover,
 * this command, tests/Feature/Cutover, the `legacy` connection in
 * config/database.php, LEGACY_DB_* in .env.example, cmd_restore_legacy in
 * scripts/db.sh, the db-legacy Makefile target, and the readme's "Loading a
 * production export" section. After that the repo mentions no legacy database.
 */
class CutoverTransform extends Command
{
    protected $signature = 'cutover:transform';

    protected $description = 'Load a restored legacy export into the current schema';

    /**
     * Written by the transform in the order listed. Anything that resolves a
     * customer has to follow the client map, and grants have to follow both the
     * seeded menus and the copied users.
     */
    private const TABLAS_DESTINO = [
        'cliente',
        'datos',
        'asignacion_datos',
        'llamada',
        'nc_fija',
        'nc_movil',
        'quiebre',
        'rol',
        'usuario',
        'acceso',
        'accesoxrol',
        'tienda',
        'asignacion_tienda',
        'supervisor',
        'cartera',
        'producto',
        'menu',
        'respuesta',
    ];

    public function handle(): int
    {
        if (! $this->destinoVacio() || ! $this->destinoSinVistas()) {
            return self::FAILURE;
        }

        // The client map is 206,121 entries and PHP arrays are not cheap, so
        // the run peaks well past the 128M a default CLI allows. Raised here
        // rather than in php.ini because this is the only thing in the app that
        // needs it and it runs once, and here rather than left to the operator
        // because the alternative is a fatal error ten seconds into a cutover.
        // @mago-expect lint:no-ini-set
        ini_set('memory_limit', '1G');

        $this->components->info('Origen: '.DB::connection('legacy')->getDatabaseName());
        $this->components->info('Destino: '.DB::connection()->getDatabaseName());

        // Reference data the app owns rather than inherits. Both seeders carry
        // legacy's own ids, which is what lets the copied grants and the copied
        // contacts keep pointing at the right menu and the right disposition.
        // The menu is where the transform is most visible: legacy filtered on
        // menu.url, the app checks menu.permiso, and seeding is that remap.
        $this->call('db:seed', ['--class' => MenuSeeder::class, '--no-interaction' => true]);
        $this->call('db:seed', ['--class' => RespuestaSeeder::class, '--no-interaction' => true]);

        $clientes = new MapaDeClientes;
        $resumen = $clientes->construir();

        foreach ($this->pasos($clientes) as $paso) {
            $resumen = [...$resumen, ...$paso->ejecutar()];
        }

        $this->newLine();
        $this->table(
            ['concepto', 'filas'],
            array_map(
                static fn (string $k, int $v): array => [$k, number_format($v)],
                array_keys($resumen),
                array_values($resumen),
            ),
        );

        return self::SUCCESS;
    }

    /**
     * @return list<Traslado>
     */
    private function pasos(MapaDeClientes $clientes): array
    {
        return [
            new TrasladoDeCuentas,
            new TrasladoDePermisos,
            new TrasladoDeCatalogos,
            new TrasladoDeCampanas($clientes, now()->toDateString()),
            new TrasladoDeContactos($clientes),
            new TrasladoDeNotasFija($clientes),
            new TrasladoDeNotasMovil($clientes),
            new TrasladoDeAverias($clientes),
        ];
    }

    private function destinoVacio(): bool
    {
        foreach (self::TABLAS_DESTINO as $tabla) {
            if (DB::table($tabla)->exists()) {
                $this->components->error(
                    "`{$tabla}` already has rows. Run `php artisan migrate:fresh --drop-views` first, without --seed.",
                );

                return false;
            }
        }

        return true;
    }

    /**
     * migrate:fresh drops tables and leaves views standing, which matters here
     * because the database this writes into is the legacy one. Production
     * carries a reporting view, `reporteusu_excel`, joining `asigna`,
     * `datos.ruc` and `llamada.ruc`. The rewrite moved identity to `cliente`,
     * holds to `asignacion_datos` and renamed `asigna`, so after the cutover
     * the view references columns that no longer exist.
     *
     * Worth refusing over rather than warning about, because the damage is
     * silent and lands somewhere else: an invalid view makes mysqldump fail for
     * the *whole* database ("references invalid table(s)"), so the first thing
     * it breaks is the backup, which is also the rollback. Found by dumping a
     * transformed database and getting 873 bytes instead of a schema.
     */
    private function destinoSinVistas(): bool
    {
        $esquema = Schema::getCurrentSchemaName();

        $vistas = array_column(
            array_filter(
                Schema::getViews(),
                static fn (array $vista): bool => $vista['schema'] === $esquema,
            ),
            'name',
        );

        if ($vistas === []) {
            return true;
        }

        $this->components->error(
            'Views left over from the legacy schema: '
            .implode(', ', $vistas)
            .'. '
            .'Run `php artisan migrate:fresh --drop-views`, which removes them.',
        );

        return false;
    }
}
