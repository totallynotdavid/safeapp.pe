<?php

namespace App\Console\Commands;

use App\Cutover\Destino;
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
use App\Support\BackfillDeAccesosClientes;
use Database\Seeders\MenuSeeder;
use Database\Seeders\RespuestaSeeder;
use Illuminate\Console\Command;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Facades\DB;

/**
 * Only transfers data; the migrations own the destination schema.
 * The destination must be migrated and empty. The transfer is one transaction,
 * so a run leaves it empty or full and a rerun refuses. Production never drops
 * anything, so `--fresh` is for rehearsals outside it.
 */
class CutoverTransform extends Command
{
    protected $signature = 'cutover:transform
        {--fresh : Drop every table and view in the destination and migrate it again first (never in production)}';

    protected $description = 'Load a restored legacy export into the current schema';

    public function handle(): int
    {
        if ($this->option('fresh')) {
            if ($this->laravel->environment('production')) {
                $this->components->error(
                    'Production is never rebuilt, so --fresh is refused and nothing was changed. '
                    .'Point DB_DATABASE at an empty database, run `php artisan migrate --force`, '
                    .'then run this command without --fresh.',
                );

                return self::FAILURE;
            }

            if ($this->call('migrate:fresh', ['--drop-views' => true]) !== self::SUCCESS) {
                return self::FAILURE;
            }
        }

        $rechazo = $this->destino()->rechazo();
        if ($rechazo !== null) {
            $this->components->error($rechazo);

            return self::FAILURE;
        }

        // The client map exceeds the default CLI memory limit.
        // @mago-expect lint:no-ini-set
        ini_set('memory_limit', '1G');

        $this->components->info(
            'Origen: '.DB::connection('legacy')->getDatabaseName(),
        );
        $this->components->info(
            'Destino: '.DB::connection()->getDatabaseName(),
        );

        $resumen = [];
        DB::transaction(function () use (&$resumen): void {
            $resumen = $this->trasladar();
        });

        $this->newLine();
        $this->table(
            ['concepto', 'filas'],
            array_map(
                static fn (string $k, int $v): array => [
                    $k,
                    number_format($v),
                ],
                array_keys($resumen),
                array_values($resumen),
            ),
        );

        $this->components->warn(
            'No migrated account has a password. Set the first with `php artisan usuario:restablecer-clave <usuario>`.',
        );

        return self::SUCCESS;
    }

    /** @return array<string, int> */
    private function trasladar(): array
    {
        // These seeders preserve the legacy identifiers used by copied rows.
        // Production db:seed asks for confirmation unless --force is present.
        $this->call('db:seed', [
            '--class' => MenuSeeder::class,
            '--force' => true,
        ]);
        $this->call('db:seed', [
            '--class' => RespuestaSeeder::class,
            '--force' => true,
        ]);

        $clientes = new MapaDeClientes;
        $resumen = $clientes->construir();

        foreach ($this->pasos($clientes) as $paso) {
            $resumen = [...$resumen, ...$paso->ejecutar()];
        }

        BackfillDeAccesosClientes::ejecutar();

        return $resumen;
    }

    /** @return list<Traslado> */
    private function pasos(MapaDeClientes $clientes): array
    {
        // Order matters: grants filter against the copied users and seeded
        // menus, and everything customer-dependent needs the client map.
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

    private function destino(): Destino
    {
        return new Destino(
            $this->laravel->make(Migrator::class),
            $this->laravel->databasePath('migrations'),
            app()->isProduction(),
        );
    }
}
