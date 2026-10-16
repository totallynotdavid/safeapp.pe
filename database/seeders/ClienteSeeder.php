<?php

namespace Database\Seeders;

use App\Models\Dato;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

// Dev-only fixture for the gestión screens: clients already assigned to the dev
// admin, plus an unassigned pool to draw batches from.
class ClienteSeeder extends Seeder
{
    // Few origenes with deep stock behind each: a pool spread thin across many
    // origenes makes batch assignment untestable.
    private const ORIGENES = ['DB_RUC20_INF', 'BASE_SAFE-1704', 'DISTRICT-ATMIRSBO'];

    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            return;
        }

        $admin = Usuario::query()->where('usuario', UsuarioSeeder::USUARIO)->first();
        $idTienda = $admin?->tiendaAsignada();
        if ($idTienda === null) {
            return;
        }

        foreach (['MOVIL', 'FIJA'] as $tipoData) {
            Dato::factory()
                ->count(8)
                ->tipoData($tipoData)
                ->asignadoA($admin->id_usuario, $idTienda)
                ->create();

            foreach (self::ORIGENES as $origen) {
                Dato::factory()
                    ->count(40)
                    ->tipoData($tipoData)
                    ->create(['origen' => $origen]);
            }
        }
    }
}
