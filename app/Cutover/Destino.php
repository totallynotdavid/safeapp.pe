<?php

namespace App\Cutover;

use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class Destino
{
    private const TABLAS = [
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

    public function __construct(
        private readonly Migrator $migrator,
        private readonly string $directorioDeMigraciones,
        private readonly bool $produccion,
    ) {}

    /** The reason the transfer cannot start, or null when it can. */
    public function rechazo(): ?string
    {
        return $this->migracionesPendientes() ?? $this->tablaConFilas() ?? $this->vistasHeredadas();
    }

    private function migracionesPendientes(): ?string
    {
        $archivos = array_keys($this->migrator->getMigrationFiles([
            ...$this->migrator->paths(),
            $this->directorioDeMigraciones,
        ]));
        $ejecutadas = $this->migrator->repositoryExists() ? $this->migrator->getRepository()->getRan() : [];
        $pendientes = count(array_diff($archivos, $ejecutadas));

        if ($pendientes === 0) {
            return null;
        }

        return (
            "{$pendientes} migrations are pending, so the destination is not the current schema. "
            .'Run `php artisan migrate --force` first; nothing was changed.'
        );
    }

    private function tablaConFilas(): ?string
    {
        foreach (self::TABLAS as $tabla) {
            if (! DB::table($tabla)->exists()) {
                continue;
            }

            return (
                "`{$tabla}` already has rows, so nothing was changed. "
                .(
                    $this->produccion
                        ? 'If the cutover already ran, it is complete. Otherwise use an empty database.'
                        : 'Run again with --fresh to rebuild the destination first.'
                )
            );
        }

        return null;
    }

    private function vistasHeredadas(): ?string
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
            return null;
        }

        // Legacy views reference columns removed by the current schema and can
        // make the entire database dump fail.
        return (
            'Views left over from the legacy schema: '
            .implode(', ', $vistas)
            .'. '
            .(
                $this->produccion
                    ? 'Production never drops them. Use an empty database.'
                    : 'Run again with --fresh, which removes them.'
            )
        );
    }
}
