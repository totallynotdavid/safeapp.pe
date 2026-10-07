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
use App\Support\BackfillDeAccesosClientes;
use Database\Seeders\MenuSeeder;
use Database\Seeders\RespuestaSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Only transfers data; the migrations own the destination schema.
 *
 * The destination must be empty. `--fresh` rebuilds it first with
 * `migrate:fresh --drop-views`, which is what a rerun needs.
 */
class CutoverTransform extends Command
{
    protected $signature = 'cutover:transform
        {--fresh : Drop every table and view in the destination and migrate it again first}';

    protected $description = 'Load a restored legacy export into the current schema';

    private const TABLAS_DESTINO = [
        'cliente',
        'cliente_acceso',
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
        if ($this->option('fresh')) {
            // Production refuses this (AppServiceProvider), which is the point.
            if ($this->call('migrate:fresh', ['--drop-views' => true]) !== self::SUCCESS) {
                return self::FAILURE;
            }
        }

        if (! $this->destinoVacio() || ! $this->destinoSinVistas()) {
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

        // These seeders preserve the legacy identifiers used by copied rows.
        $this->call('db:seed', [
            '--class' => MenuSeeder::class,
            '--no-interaction' => true,
        ]);
        $this->call('db:seed', [
            '--class' => RespuestaSeeder::class,
            '--no-interaction' => true,
        ]);

        $clientes = new MapaDeClientes;
        $resumen = $clientes->construir();

        foreach ($this->pasos($clientes) as $paso) {
            $resumen = [...$resumen, ...$paso->ejecutar()];
        }

        BackfillDeAccesosClientes::ejecutar();

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

    private function destinoVacio(): bool
    {
        foreach (self::TABLAS_DESTINO as $tabla) {
            if (! DB::table($tabla)->exists()) {
                continue;
            }

            $this->components->error(
                "`{$tabla}` already has rows. Run again with --fresh to rebuild the destination first.",
            );

            return false;
        }

        return true;
    }

    private function destinoSinVistas(): bool
    {
        $esquema = Schema::getCurrentSchemaName();

        $vistas = [];
        foreach (DB::connection()->getSchemaBuilder()->getViews() as $vista) {
            if ($vista['schema'] !== $esquema) {
                continue;
            }

            $vistas[] = $vista['name'];
        }

        if ($vistas === []) {
            return true;
        }

        // Legacy views reference columns removed by the current schema and can
        // make the entire database dump fail.
        $this->components->error(
            'Views left over from the legacy schema: '
            .implode(', ', $vistas)
            .'. Run again with --fresh, which removes them.',
        );

        return false;
    }
}
